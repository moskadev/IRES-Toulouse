<?php

namespace irestoulouse\utils;

interface Locker {

    public const STATE_UNLOCKED = 0;
    public const STATE_LOCKED = 1;
    public const STATE_UNLOCKABLE = 2;

}