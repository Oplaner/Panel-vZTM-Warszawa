<!DOCTYPE html>
<html lang="pl">
<?php

ViewBuilder::buildHead(Style::light, [Script::menu], null)

?>
<body>
<?php

    ViewBuilder::buildTopBar($_USER);
    ViewBuilder::buildMenu($_USER);

?>
    <div id="content">
        <h1>Przewoźnicy</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin euismod feugiat lorem sed tempus. Vestibulum lectus felis, tincidunt nec lacus eu, accumsan posuere metus. Donec congue diam a risus efficitur finibus. Sed finibus volutpat arcu sed mattis. Donec convallis orci nec quam aliquam, at feugiat leo eleifend. Praesent placerat eros mi, a consectetur purus gravida eu. Phasellus imperdiet dui diam. Aenean lacinia lobortis tortor nec tincidunt.</p>
    </div>
</body>
</html>