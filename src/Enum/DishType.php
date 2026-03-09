<?php

namespace App\Enum;

enum DishType: string
{
    case Starter    = 'starter';
    case MainCourse = 'main_course';
    case Dessert    = 'dessert';
}
