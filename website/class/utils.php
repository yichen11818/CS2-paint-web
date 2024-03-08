<?php
class UtilsClass
{
    public static function skinsFromJson(): array
    {
        $skins = [];
        $json = json_decode(file_get_contents(__DIR__ . "/../data/skins.json"), true);

        foreach ($json as $skin) {
            $skins[(int) $skin['weapon_defindex']][(int) $skin['paint']] = [
                'weapon_name' => $skin['weapon_name'],
                'paint_name' => $skin['paint_name'],
                'image_url' => $skin['image'],
            ];
        }

        return $skins;
    }

    public static function agentsFromJson(): array
    {
        $agents = [];
        $json = json_decode(file_get_contents(__DIR__ . "/../data/agents.json"), true);

        foreach ($json as $agent) {
            $agents[] = [
                'team' => (int) $agent['team'],
                'image_url' => $agent['image'],
                'model' => $agent['model'],
                'agent_name' => $agent['agent_name'],
            ];
        }

        return $agents;
    }

    public static function glovesFromJson(): array
    {
        $gloves = [];
        $json = json_decode(file_get_contents(__DIR__ . "/../data/gloves.json"), true);

        foreach ($json as $glove) {
            $gloves[(int) $glove['weapon_defindex']][(int) $glove['paint']] = [
                'paint_name' => $glove['paint_name'],
                'image_url' => $glove['image'],
            ];
        }

        return $gloves;
    }

    public static function getWeaponsFromArray()
    {
        $weapons = [];
        $temp = self::skinsFromJson();

        foreach ($temp as $key => $value) {
            if (key_exists($key, $weapons))
                continue;

            $weapons[$key] = [
                'weapon_name' => $value[0]['weapon_name'],
                'paint_name' => $value[0]['paint_name'],
                'image_url' => $value[0]['image_url'],
            ];
        }

        return $weapons;
    }

    public static function getKnifeTypes()
    {
        $knifes = [];
        $temp = self::getWeaponsFromArray();

        foreach ($temp as $key => $weapon) {
            if (
                !in_array($key, [
                    500,
                    503,
                    505,
                    506,
                    507,
                    508,
                    509,
                    512,
                    514,
                    515,
                    516,
                    517,
                    518,
                    519,
                    520,
                    521,
                    522,
                    523,
                    525,
                    526
                ])
            )
                continue;

            $knifes[$key] = [
                'weapon_name' => $weapon['weapon_name'],
                'paint_name' => rtrim(explode("|", $weapon['paint_name'])[0]),
                'image_url' => $weapon['image_url'],
            ];
            $knifes[0] = [
                'weapon_name' => "weapon_knife",
                'paint_name' => "Default knife",
                'image_url' => "https://raw.githubusercontent.com/Nereziel/cs2-WeaponPaints/main/website/img/skins/weapon_knife.png",
            ];
        }

        ksort($knifes);
        return $knifes;
    }
    public static function getSelectedSkins(array $temp)
    {
        $selected = [];

        foreach ($temp as $weapon) {
            $selected[$weapon['weapon_defindex']] =  [
                'weapon_paint_id' => $weapon['weapon_paint_id'],
                'weapon_seed' => $weapon['weapon_seed'],
                'weapon_wear' => $weapon['weapon_wear'],
            ];
        }

        return $selected;
    }
    public static function getGloves()
    {
        $gloves = [];
        $temp = self::glovesFromJson();

        foreach ($temp as $key => $glove) {
            if(
                !in_array($key,[
                    4725,
                    5027,
                    5030,
                    5031,
                    5032,
                    5033,
                    5034,
                    5035,
                ])
            )
            continue;

            $gloves[$key] = [
                'paint_name' => $glove[0]['paint_name'],
                'image_url' => $glove[0]['image_url'],
            ];
            
        }

        return $gloves;
    }
}