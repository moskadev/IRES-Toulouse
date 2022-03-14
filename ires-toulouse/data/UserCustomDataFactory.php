<?php

namespace irestoulouse\data;

/**
 * Management of the UserCustomData with methods where we can
 * register all of them, unregister, get all, etc...
 *
 * @version 2.0
 */
class UserCustomDataFactory {

    /**
     * Register all new metas for the IRES Toulouse to the user
     *
     * @param int $userId the user's id
     */
    public static function registerExtraMetas(int $userId) : void {
        foreach (self::all() as $meta) {
            if (($user = get_userdata($userId)) !== false) {
                $meta->register($user);
            }
        }
    }

    /**
     * @return UserCustomData[] all the user's necessary data
     */
    public static function all(bool $labelIncluded = false) : array {
        $UserCustomData = [];
        $jsonData = json_decode(file_get_contents(__DIR__ . "/user_data.json"), true);
        foreach ($jsonData as $d) {
            if ($labelIncluded || $d["formType"] !== "label") {
                $UserCustomData[] = new UserCustomData(...array_values(UserCustomDataFactory::formatData($d)));
            }
        }

        return $UserCustomData;
    }

    /**
     * Should be used only to organize parameters when creating a new
     * UserInputData class from a JSON object
     *
     * @param array $data the data to convert
     *
     * @return array the organized data
     */
    private static function formatData(array $data) : array {
        $newData = [];
        foreach (UserCustomDataType::IDS as $valid) {
            $newData[$valid] = null; // init a value to avoid exceptions
        }
        foreach ($data as $key => $d) {
            $newData[$key] = $d;
        }

        return $newData;
    }

    /**
     * Find the user input's data from its ID
     *
     * @param string $searchedId the data identifier
     *
     * @return UserCustomData|null the user data which can be null
     */
    public static function fromId(string $searchedId) : ?UserCustomData {
        $filter = array_filter(self::all(),
            function ($a) use ($searchedId) {
                return $a->getId() === $searchedId;
            }
        );

        return array_values($filter)[0] ?? null;
    }
}