<?php

final class PathBuilder {
    private const FORWARD_SLASH = "/";
    private const IMAGES_COMPONENT = "Views/Images/";
    private const SCRIPTS_COMPONENT = "Views/Scripts/";
    private const STYLES_COMPONENT = "Views/Styles/";

    private static ?string $root = null;

    public static function root(): string {
        if (is_null(self::$root)) {
            $directory = dirname($_SERVER["SCRIPT_NAME"]);
            self::$root = $directory == "." ? "/" : $directory;
        }

        return self::$root;
    }

    public static function action(string $path): string {
        return self::build(self::root(), $path);
    }

    public static function image(string $name): string {
        return self::build(self::root(), self::IMAGES_COMPONENT, $name);
    }

    public static function script(string $name): string {
        return self::build(self::root(), self::SCRIPTS_COMPONENT, $name);
    }

    public static function stylesheet(string $name): string {
        return self::build(self::root(), self::STYLES_COMPONENT, $name);
    }

    private static function build(string ...$components): string {
        $components = array_filter(
            array_map(
                fn($component) => trim($component, "\/"),
                $components
            ),
            fn($component) => $component != ""
        );
        return self::FORWARD_SLASH.join(self::FORWARD_SLASH, $components);
    }
}

?>