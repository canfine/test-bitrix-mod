<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
if (!CModule::IncludeModule("iblock"))
    return;

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

$arComponentParameters = [
    "GROUPS" => array(),
    "PARAMETERS" => [
        "SEF_MODE" => Array(),
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
        ] ,
        "PROPERTY_CODES" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("IBLOCK_PROPERTY"),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $arProperty,
        ] ,
        "MAIL_TO" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("SEND_TO_MAIL"),
            "TYPE" => "STRING",
            "DEFAULT" => htmlspecialcharsbx(COption::GetOptionString("main", "email_from")),
        ] ,
        "MAIL_FROM" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("SEND_FROM_MAIL"),
            "TYPE" => "STRING",
            "DEFAULT" => htmlspecialcharsbx(COption::GetOptionString("main", "email_from")),
        ] ,
        "OK_TEXT" => [
            "PARENT" => "BASE",
            "NAME" => GetMessage("OK_TEXT"),
            "TYPE" => "STRING",
        ] ,
    ],
];

$arComponentParameters["PARAMETERS"]["USE_CAPTCHA"] = [
    "PARENT" => "PARAMS",
    "NAME" => GetMessage("IBLOCK_USE_CAPTCHA"),
    "TYPE" => "CHECKBOX",
];

$arComponentParameters["PARAMETERS"]["AJAX_MODE"] = [
    "PARENT" => "PARAMS",
    "NAME" => GetMessage("USE_AJAX"),
    "TYPE" => "CHECKBOX",
    "DEFAULT" => "Y",
];
?>