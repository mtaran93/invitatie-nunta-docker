<?php

declare(strict_types=1);

namespace App\Enums;

enum Menu: string
{
    case MenuStandard = 'menuStandard';
    case MenuVegetarian = 'menuVegetarian';
    case menuFaraGluten = 'menuFaraGluten';

    public static function label($menu): string
    {
        return match ($menu) {
            Menu::MenuStandard => 'standard',
            Menu::MenuVegetarian => 'vegetarian',
            Menu::menuFaraGluten => 'fara_gluten',
        };
    }
}
