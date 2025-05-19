<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo PathBuilder::stylesheet("style-light.css") ?>">
    <script src="<?php echo PathBuilder::script("menu.js") ?>"></script>
    <title>Panel vZTM Warszawa</title>
</head>
<body>
    <div id="topBar">
        <div id="menuButton">
            <div></div>
            <div></div>
            <div></div>
        </div>
        <img src="<?php echo PathBuilder::image("vztm-logo-short.svg") ?>" alt="Logo vZTM Warszawa">
        <div id="topBarUserInfo">#<?php echo $_USER->getLogin() ?> &bull; <?php echo $_USER->getUsername() ?><br><a href="<?php echo PathBuilder::action("/logout") ?>">Wyloguj się</a></div>
    </div>
    <div id="menu">
        <div class="menuSection">
            <h2>Kierownik R-1</h2>
            <ul>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
            </ul>
        </div>
        <div class="menuSection">
            <h2>Kierownik R-2</h2>
            <ul>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
            </ul>
        </div>
        <div class="menuSection">
            <h2>Kierownik R-3</h2>
            <ul>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
            </ul>
        </div>
        <div class="menuSection">
            <h2>Kierownik R-4</h2>
            <ul>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
            </ul>
        </div>
        <div class="menuSection">
            <h2>Kierownik R-5</h2>
            <ul>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
            </ul>
        </div>
        <div class="menuSection">
            <h2>Kierownik R-6</h2>
            <ul>
                <li><a href="#">Zadania</a></li>
                <li><a href="#">Kierowcy</a></li>
                <li><a href="#">Tabor</a></li>
            </ul>
        </div>
        <div class="menuSection">
            <h2>Dyrektor</h2>
            <ul>
                <li><a href="#">Pracownicy funkcyjni</a></li>
                <li><a href="#">Zakłady</a></li>
                <li><a href="#">Ustawienia</a></li>
            </ul>
        </div>
    </div>
    <div id="content">
        <h1>Bonjour</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin euismod feugiat lorem sed tempus. Vestibulum lectus felis, tincidunt nec lacus eu, accumsan posuere metus. Donec congue diam a risus efficitur finibus. Sed finibus volutpat arcu sed mattis. Donec convallis orci nec quam aliquam, at feugiat leo eleifend. Praesent placerat eros mi, a consectetur purus gravida eu. Phasellus imperdiet dui diam. Aenean lacinia lobortis tortor nec tincidunt.</p>
        <p>Praesent tincidunt odio non placerat luctus. Sed eget mollis urna, et gravida urna. In maximus ligula in odio ullamcorper, eu auctor arcu tempor. In eu tortor non arcu viverra tincidunt sed porta sapien. Nam placerat neque ullamcorper, placerat nisl vel, feugiat urna. Nulla quis fermentum est. Proin laoreet, massa in iaculis luctus, felis ex sodales sapien, at commodo nibh magna eget sapien. Sed quis ultrices nisl. Sed fermentum massa at volutpat euismod. Maecenas at diam eu turpis pulvinar volutpat. Nulla feugiat magna vitae congue ornare.</p>
        <p>In commodo vehicula quam vel consectetur. Aliquam erat volutpat. Cras cursus purus eget metus ullamcorper, a tempor neque tristique. Donec sapien sapien, venenatis eget neque eget, fermentum fermentum lectus. Maecenas aliquam maximus efficitur. Curabitur convallis molestie dui id egestas. Phasellus ultricies, neque nec mollis lacinia, velit nunc accumsan metus, a venenatis nisi magna in risus. Nam dictum, enim in pretium hendrerit, nibh orci pellentesque massa, sed ultrices lectus nisl blandit augue. Donec euismod interdum sapien quis maximus. Curabitur et nulla at mauris gravida sollicitudin eget non orci. Etiam sollicitudin justo at accumsan placerat.</p>
        <p>Vivamus diam dolor, vehicula quis dignissim in, finibus eu lorem. Maecenas leo mi, fermentum vel volutpat non, dignissim id risus. Vestibulum diam felis, cursus et feugiat sit amet, ultrices at neque. Curabitur blandit molestie convallis. Maecenas tempor fringilla dui, ac molestie elit consectetur nec. Suspendisse at consequat orci, et pellentesque mi. Sed ultrices arcu non turpis bibendum, nec maximus nisi molestie. Etiam facilisis, nibh eget vehicula egestas, arcu nisi lacinia libero, in luctus ligula lectus eu magna.</p>
        <p>Morbi ligula arcu, aliquet vitae arcu at, ultricies commodo purus. Proin mauris neque, lacinia ac mattis vitae, ornare ac est. Ut sed velit placerat, venenatis ligula eget, tristique tortor. Integer placerat mi aliquam nulla aliquet tempus. Curabitur efficitur velit vitae bibendum vulputate. Donec eu consequat tortor, non aliquet mi. Quisque dictum augue lacinia condimentum pretium. Aliquam vitae aliquam est. Praesent et fringilla lacus. Aliquam id volutpat risus. Maecenas a felis ut magna faucibus hendrerit. Vivamus finibus id nibh a egestas. Morbi velit neque, blandit eleifend condimentum vel, convallis in massa. Donec scelerisque gravida metus a sodales. Ut lacinia lobortis massa, non auctor odio venenatis in. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
    </div>
</body>
</html>