window.addEventListener("DOMContentLoaded", () => {
    const searchContainers = document.querySelectorAll(".searchContainer");
    const minCharactersForSearch = 3;
    const fetchDelayMilliseconds = 300;
    const selectionStringSeparator = ";";

    searchContainers.forEach(container => {
        const searchEndpoint = container.dataset.source;
        const selectionLimit = container.dataset.selectionLimit;
        const searchInput = container.querySelector("input[type=text]");
        const searchMatchesContainer = container.querySelector(".searchMatchesContainer");
        const selectionInput = container.querySelector("input[type=hidden]");
        const selectionContainer = container.querySelector(".selectionContainer");
        const loaderContainer = container.querySelector(".loaderContainer");
        let inputHasFocus = false;
        let results = [];
        let timeout;

        searchInput.addEventListener("focus", () => {
            inputHasFocus = true;
            const substring = searchInput.value;

            if (!substringLengthIsBelowThreshold(substring)) {
                buildAndShowSearchMatches(results, searchMatchesContainer);
            }
        });

        searchInput.addEventListener("blur", () => {
            inputHasFocus = false;
        });

        searchInput.addEventListener("input", () => {
            clearTimeout(timeout);
            const substring = searchInput.value;

            if (substringLengthIsBelowThreshold(substring)) {
                results = [];
                buildAndShowSearchMatches(results, searchMatchesContainer);
                return;
            }

            timeout = setTimeout(async () => {
                showLoader(loaderContainer);
                results = await fetchResults(searchEndpoint, substring);
                hideLoader(loaderContainer);

                if (inputHasFocus) {
                    buildAndShowSearchMatches(results, searchMatchesContainer);
                }
            }, fetchDelayMilliseconds);
        });

        document.addEventListener("click", (event) => {
            const target = event.target;

            if (searchInput.contains(target)) {
                return;
            }

            if (searchMatchesContainer.contains(target)) {
                results = [];
                searchInput.value = "";
                handleSelection(searchInput, target, selectionInput, selectionContainer, selectionLimit);
            }

            if (selectionContainer.contains(target) && target.nodeName == "A") {
                const selection = target.parentElement;
                handleRemoval(searchInput, selection, selectionInput, selectionContainer, selectionLimit);
                event.preventDefault();
            }

            if (!inputHasFocus) {
                hideSearchMatches(searchMatchesContainer);
            }
        });
    });

    async function fetchResults(endpoint, substring) {
        const form = new FormData();
        form.append("substring", substring);
        let results;

        try {
            const response = await fetch(endpoint, {
                method: "POST",
                body: form
            });
            results = await response.json();
        } catch(error) {
            results = [];
            console.log(error);
        }

        return results;
    }

    function buildAndShowSearchMatches(results, searchMatchesContainer) {
        const searchMatches = searchMatchesContainer.querySelector(".searchMatches");
        searchMatches.innerHTML = "";

        if (results.length == 0) {
            hideSearchMatches(searchMatchesContainer);
            return;
        }

        results.forEach(result => {
            const match = document.createElement("div");
            match.dataset.key = result.key;
            match.innerHTML = result.value;
            searchMatches.appendChild(match);
        });

        showSearchMatches(searchMatchesContainer);
    }

    function handleSelection(searchInput, selection, selectionInput, selectionContainer, selectionLimit) {
        const key = selection.dataset.key;
        const value = selection.innerText;
        addToSelectionInput(key, selectionInput);
        addToSelectionContainer(key, value, selectionContainer);
        handleSelectionLimit(searchInput, selectionContainer, selectionLimit);
    }

    function handleRemoval(searchInput, selection, selectionInput, selectionContainer, selectionLimit) {
        const key = selection.dataset.key;
        removeFromSelectionInput(key, selectionInput);
        removeFromSelectionContainer(key, selectionContainer);
        handleSelectionLimit(searchInput, selectionContainer, selectionLimit);
    }

    function addToSelectionInput(key, selectionInput) {
        let selections = readSelectionsFromSelectionInputString(selectionInput.value);
        selections.add(key);
        selectionInput.value = makeSelectionInputString(selections);
    }

    function removeFromSelectionInput(key, selectionInput) {
        let selections = readSelectionsFromSelectionInputString(selectionInput.value);
        selections.delete(key);
        selectionInput.value = makeSelectionInputString(selections);
    }

    function readSelectionsFromSelectionInputString(selectionInputString) {
        if (selectionInputString == "") {
            return new Set();
        }

        return new Set(selectionInputString.split(selectionStringSeparator));
    }

    function makeSelectionInputString(selections) {
        return Array.from(selections).sort((a, b) => a - b).join(selectionStringSeparator);
    }

    function addToSelectionContainer(key, value, selectionContainer) {
        let selections = readSelectionsFromSelectionContainer(selectionContainer);
        const selectionAlreadyExists = selections.some(selection => selection.key == key);

        if (selectionAlreadyExists) {
            return;
        }

        const selection = {
            key: key,
            value: value
        };
        selections.push(selection);
        buildSelections(selections, selectionContainer);
    }

    function removeFromSelectionContainer(key, selectionContainer) {
        let selections = readSelectionsFromSelectionContainer(selectionContainer);
        const index = selections.findIndex(selection => selection.key == key);

        if (index < 0) {
            return;
        }

        selections.splice(index, 1);
        buildSelections(selections, selectionContainer);
    }

    function readSelectionsFromSelectionContainer(selectionContainer) {
        return Array.from(selectionContainer.children).map(div => {
            const key = div.dataset.key;
            const value = div.querySelector("span").innerText;
            const selection = {
                key: key,
                value: value
            };
            return selection;
        });
    }

    function buildSelections(selections, selectionContainer) {
        selections.sort((a, b) => a.key - b.key);
        selectionContainer.innerHTML = "";
        selections.forEach(selection => {
            const div = document.createElement("div");
            div.className = "selection";
            div.dataset.key = selection.key;

            const span = document.createElement("span");
            span.innerHTML = selection.value;

            const nbsp = document.createTextNode("\u00A0");

            const removeButton = document.createElement("a");
            removeButton.href = "#";
            removeButton.innerHTML = "[&times;]";

            div.append(span);
            div.append(nbsp);
            div.append(removeButton);
            selectionContainer.append(div);
        });
    }

    function handleSelectionLimit(searchInput, selectionContainer, selectionLimit) {
        if (selectionLimit == null) {
            return;
        }

        const selectedItems = selectionContainer.querySelectorAll(".selection").length;
        searchInput.disabled = selectedItems >= selectionLimit;
    }

    function substringLengthIsBelowThreshold(substring) {
        return substring.length < minCharactersForSearch;
    }

    function hideLoader(loaderContainer) {
        loaderContainer.style.display = "none";
    }

    function showLoader(loaderContainer) {
        loaderContainer.style.display = "inline-block";
    }

    function hideSearchMatches(searchMatchesContainer) {
        searchMatchesContainer.style.display = "none";
    }

    function showSearchMatches(searchMatchesContainer) {
        searchMatchesContainer.style.display = "block";
    }
});