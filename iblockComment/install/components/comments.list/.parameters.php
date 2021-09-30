<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc as Loc;

Loc::loadMessages(__FILE__);

try {
    if (!Main\Loader::includeModule('iblock')) {
        throw new Main\LoaderException(Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_IBLOCK_MODULE_NOT_INSTALLED'));
    }
    $iblockTypes = \CIBlockParameters::GetIBlockTypes(["-" => " "]);
    $iblocksCode = ["" => " "];
    if (isset($arCurrentValues['IBLOCK_TYPE']) && strlen($arCurrentValues['IBLOCK_TYPE'])) {
        $filter = [
            'TYPE' => $arCurrentValues['IBLOCK_TYPE'],
            'ACTIVE' => 'Y'
        ];
        $iterator = \CIBlock::GetList(['SORT' => 'ASC'], $filter);
        while ($iblock = $iterator->GetNext()) {
            $iblocksCode[$iblock['CODE']] = $iblock['NAME'];
        }
    }


    $arIBlock = CIBlock::GetArrayByID($arCurrentValues["IBLOCK_ID"]);
    $arIBlockType = CIBlockParameters::GetIBlockTypes();

    $arIBlock = array();
    $rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE" => "Y"));
    while ($arr = $rsIBlock->Fetch()) {
        $arIBlock[$arr["ID"]] = "[" . $arr["ID"] . "] " . $arr["NAME"];
    }

    $rsProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("ACTIVE" => "Y", "IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"]));
    while ($arr = $rsProp->Fetch()) {
        $arProperty[$arr["ID"]] = "[" . $arr["CODE"] . "] " . $arr["NAME"];
    }

    $sortFields = [
        'ID' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_SORT_ID'),
        'NAME' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_SORT_NAME'),
        'ACTIVE_FROM' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_SORT_ACTIVE_FROM'),
        'SORT' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_SORT_SORT')
    ];
    $sortDirection = [
        'ASC' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_SORT_ASC'),
        'DESC' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_SORT_DESC')
    ];

    $arComponentParameters = [
        'GROUPS' => array(),
        'PARAMETERS' => [
            'COMMENT_IBLOCK_TYPE' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_IBLOCK_TYPE'),
                'TYPE' => 'LIST',
                'VALUES' => $iblockTypes,
                'DEFAULT' => '',
                'REFRESH' => 'Y'
            ],
            'COMMENT_IBLOCK_CODE' => [
                'PARENT' => 'BASE',
                'NAME' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_IBLOCK_CODE'),
                'TYPE' => 'LIST',
                'VALUES' => $iblocksCode
            ],
            "COMMENT_ELEMENT_ID" => [
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}',
		] ,
            "IBLOCK_TYPE" => [
                "PARENT" => "BASE",
                "NAME" => GetMessage("IBLOCK_TYPE"),
                "TYPE" => "LIST",
                "ADDITIONAL_VALUES" => "Y",
                "VALUES" => $arIBlockType,
                "REFRESH" => "Y",
            ] ,
            "IBLOCK_ID" => [
                "PARENT" => "BASE",
                "NAME" => GetMessage("IBLOCK_IBLOCK"),
                "TYPE" => "LIST",
                "ADDITIONAL_VALUES" => "Y",
                "VALUES" => $arIBlock,
                "REFRESH" => "Y",
            ],
            "PROPERTY_CODES" => [
                "PARENT" => "BASE",
                "NAME" => GetMessage("IBLOCK_PROPERTY"),
                "TYPE" => "LIST",
                "MULTIPLE" => "Y",
                "VALUES" => $arProperty,
            ],
            "MAIL_TO" => [
                "PARENT" => "BASE",
                "NAME" => GetMessage("SEND_TO_MAIL"),
                "TYPE" => "STRING",
                "DEFAULT" => htmlspecialcharsbx(COption::GetOptionString("main", "email_from")),
            ],
            "MAIL_FROM" => [
                "PARENT" => "BASE",
                "NAME" => GetMessage("SEND_FROM_MAIL"),
                "TYPE" => "STRING",
                "DEFAULT" => htmlspecialcharsbx(COption::GetOptionString("main", "email_from")),
            ],
            "OK_TEXT" => [
                "PARENT" => "BASE",
                "NAME" => GetMessage("OK_TEXT"),
                "TYPE" => "STRING",
            ],
            /*
              'SORT_FIELD1' => [
              'PARENT' => 'BASE',
              'NAME' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_SORT_FIELD1'),
              'TYPE' => 'LIST',
              'VALUES' => $sortFields
              ],
              'SORT_DIRECTION1' => [
              'PARENT' => 'BASE',
              'NAME' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_SORT_DIRECTION1'),
              'TYPE' => 'LIST',
              'VALUES' => $sortDirection
              ],
              'SORT_FIELD2' => [
              'PARENT' => 'BASE',
              'NAME' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_SORT_FIELD2'),
              'TYPE' => 'LIST',
              'VALUES' => $sortFields
              ],
              'SORT_DIRECTION2' => [
              'PARENT' => 'BASE',
              'NAME' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_SORT_DIRECTION2'),
              'TYPE' => 'LIST',
              'VALUES' => $sortDirection
              ],
              'TITLE' => Array(
              'PARENT' => 'BASE',
              'NAME' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_TITLE'),
              'TYPE' => 'STRING',
              ),
              'TEXT' => Array(
              'PARENT' => 'BASE',
              'NAME' => Loc::getMessage('MAIN_BLOCK_TEAM_PARAMETERS_TEXT'),
              'TYPE' => 'STRING',
              ),
             *
             */
            'CACHE_TIME' => [
                'DEFAULT' => 3600
            ],
        ]
    ];
} catch (Main\LoaderException $e) {
    ShowError($e->getMessage());
}
?>