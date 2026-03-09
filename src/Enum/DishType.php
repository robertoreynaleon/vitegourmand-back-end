<?php

namespace App\Enum;

enum DishType: string
{
    case Starter    = "entrée";
    case MainCourse = "plat_principal";
    case Dessert    = "dessert";
}
