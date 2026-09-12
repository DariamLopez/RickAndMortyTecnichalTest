<?php

namespace App\Enums;

enum CharacterGender: string
{
    case Male = 'Male';
    case Female = 'Female';
    case Genderless = 'Genderless';
    case Unknown = 'unknown';
}
