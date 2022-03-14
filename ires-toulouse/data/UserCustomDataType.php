<?php

namespace irestoulouse\data;

/**
 * All different types for UserCustomData
 * Type for values, ids and form
 *
 * @version 2.0
 */
interface UserCustomDataType {

    public const VALUE_TYPE_INT = 0;
    public const VALUE_TYPE_FLOAT = 1;
    public const VALUE_TYPE_STRING = 2;
    public const VALUE_TYPE_BOOL = 3;

    public const IDS = [
        "name",
        "formType",
        "id",
        "description",
        "parent",
        "uppercase",
        "required",
        "regex",
        "extraData",
        "disabled",
        "wordpressMeta"
    ];
    public const FORM_TYPES = [
        "label",
        "text",
        "email",
        "checklist",
        "radio",
        "dropdown"
    ];
}