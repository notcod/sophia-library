<?php

namespace Sophia;

class Stylesheet
{
    public static $styles = [];
    public static function add($name, $href)
    {
        self::$styles[$name] = ["href" => $href, "ext" => url_strip($href)];
    }
    public static function page($data)
    {
        foreach ($data["style"] as $style)
            $styles[] = self::$styles[$style];

        foreach ($styles as $style)
            $execute[] = $style["ext"] ? $style["href"] : ROOT . $style["href"] . '?v=' . filemtime($style["href"]);

        $execute[] = getFile("assets/css/" . $data["view"] . ".css");
        $execute[] = getFile("assets/css/" . $data["view"] . "/" . $data["page"] . ".css");

        $execute = array_unique($execute);

        echo PHP_EOL . '<!-- INCLUDED STYLES -->' . PHP_EOL;
        foreach ($execute as $style){
            if($style !== false) echo '<link rel="stylesheet" href="' . $style . '">' . PHP_EOL;
        }
        echo '<!-- END INCLUDED STYLES -->';
    }
}
