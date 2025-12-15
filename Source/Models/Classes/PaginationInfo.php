<?php

final class PaginationInfo {
    private const CURRENT_PAGE_NOT_SET_EXCEPTION_MESSAGE = "Current page is not set.";

    private int $numberOfObjects;
    private int $numberOfObjectsPerPage;
    private ?int $currentPage;

    public function __construct(int $numberOfObjects, int $numberOfObjectsPerPage) {
        $this->numberOfObjects = $numberOfObjects;
        $this->numberOfObjectsPerPage = $numberOfObjectsPerPage;
        $this->currentPage = null;
    }

    public function getNumberOfObjects(): int {
        return $this->numberOfObjects;
    }

    public function getNumberOfObjectsPerPage(): int {
        return $this->numberOfObjectsPerPage;
    }

    public function getNumberOfPages(): int {
        return ceil($this->numberOfObjects / $this->numberOfObjectsPerPage);
    }

    public function getCurrentPage(): ?int {
        return $this->currentPage;
    }

    public function setCurrentPage(int $currentPage): void {
        $this->currentPage = $currentPage;
    }

    public function getPreviousPage(): ?int {
        if ($this->isFirstPage()) {
            return null;
        }

        return $this->currentPage - 1;
    }

    public function getNextPage(): ?int {
        if ($this->isLastPage()) {
            return null;
        }

        return $this->currentPage + 1;
    }

    public function isFirstPage(): bool {
        if (is_null($this->currentPage)) {
            throw new LogicException(self::CURRENT_PAGE_NOT_SET_EXCEPTION_MESSAGE);
        }

        return $this->currentPage == 1;
    }

    public function isLastPage(): bool {
        if (is_null($this->currentPage)) {
            throw new LogicException(self::CURRENT_PAGE_NOT_SET_EXCEPTION_MESSAGE);
        }

        return $this->currentPage == $this->getNumberOfPages();
    }

    public function getQueryLimitSubstring(): string {
        if (is_null($this->currentPage)) {
            throw new LogicException(self::CURRENT_PAGE_NOT_SET_EXCEPTION_MESSAGE);
        }

        return (($this->currentPage - 1) * $this->numberOfObjectsPerPage).", ".$this->numberOfObjectsPerPage;
    }
}

?>