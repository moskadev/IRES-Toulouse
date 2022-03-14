<?php

namespace irestoulouse\group;

/**
 * Definition of all possible types for a group
 *
 * @version 2.0
 */
interface GroupType {

    public const RECHERCHE_ACTION = 0;
    public const MANIFESTATION = 1;
    public const AUTRE = 2;

    public const NAMES = [
        self::RECHERCHE_ACTION => "Recherche-action",
        self::MANIFESTATION => "Manifestation",
        self::AUTRE => "Autre"
    ];
}