<?php

namespace App\Enums;

enum CharacterStatus: string
{
    case Alive = 'Alive';
    case Dead = 'Dead';
    case Unknown = 'unknown';
}
