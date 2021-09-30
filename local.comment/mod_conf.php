<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();



/**
 * @var array $arIblockTypes
 * типы инфоблоков
 */
$arIblockTypes = [
    'TEXT' => [
        'SECTIONS' => 'N',
        'SORT' => '100',
        'LANG' => [
            'ru' => [
                'NAME'=>'Формы модуля обмена',
//                'SECTION_NAME'=>'Sections',
                'ELEMENT_NAME'=>'Формы'
            ]
        ]
    ],
];

/**
 * @var array $arIblocks
 * инфоблоки
 */
$arIblocks = [
    'COMMENTS' => [
        'TYPE' => 'TEXT',
        'NAME' => 'Запросы на регистрацию',

        'PROPS' => [
            ['NAME' => 'ФИО', 'CODE' => 'FIO'],
            ['NAME' => 'EMAIL', 'CODE' => 'EMAIL'],
            ['NAME' => 'Привязка к элементу', 'CODE' => 'COMMENT_ELEMENT_ID'],
        ]
    ],
];


$baseDir = basename(__DIR__);
$moduleName = strtoupper($baseDir);
$baseNS = 'Local';
$parts = explode('.', $baseDir);
$moduleNS = $baseNS . '\\' . ucfirst($parts[1]);

$arConfig = [
    'id' => strtolower($moduleName),
    'name' => $moduleName,
    'ns' => $moduleNS,
    'nsTables' => $moduleNS . '\Tables',
    'prefix' => 'local_comment',
    'arIblockTypes' => $arIblockTypes,
    'arIblocks' => $arIblocks,
];

return $arConfig;
