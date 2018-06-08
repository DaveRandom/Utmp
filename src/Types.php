<?php declare(strict_types=1);

namespace DaveRandom\Utmp;

use DaveRandom\Enum\Enum;

final class Types extends Enum
{
    public const EMPTY = 0;
    public const RUN_LVL = 1;
    public const BOOT_TIME = 2;
    public const NEW_TIME = 3;
    public const OLD_TIME = 4;
    public const INIT_PROCESS = 5;
    public const LOGIN_PROCESS = 6;
    public const USER_PROCESS = 7;
    public const DEAD_PROCESS = 8;
    public const ACCOUNTING = 9;
}
