<?php

namespace irestoulouse\utils;

/**
 * 3 different states of locker
 *
 * @version 2.0
 */
interface Locker {

    public const STATE_UNLOCKED = 0;
    public const STATE_LOCKED = 1;
    public const STATE_UNLOCKABLE = 2;

}