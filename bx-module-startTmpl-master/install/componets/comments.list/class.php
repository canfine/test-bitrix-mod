<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main;
use Bitrix\Main\Localization\Loc as Loc;

\CBitrixComponent::includeComponentClass("system:standard.elements.list");

class CommentsListComponent extends StandardElementListComponent
{
    public function onPrepareComponentParams($params)
    {
        $result = parent::onPrepareComponentParams($params);
        $result = array_merge($result, [
            'TITLE' => trim($params['TITLE']),
            'TEXT' => trim($params['TEXT'])
        ]);
        return $result;
    }

    protected function getResult()
    {
        $sort = [
     //       $this->arParams['SORT_FIELD1'] => $this->arParams['SORT_DIRECTION1'],
    //        $this->arParams['SORT_FIELD2'] => $this->arParams['SORT_DIRECTION2'],
        ];
        $filter = [
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ACTIVE' => 'Y',
            'COMMENT_ELEMENT_ID' => $this->arParams["COMMENT_ELEMENT_ID"],
        ];
        $select = [
            'ID',
            'NAME',
            'FIO',
            'EMAIL',
            'DETAIL_TEXT',
        ];
        $iterator = \CIBlockElement::GetList($sort, $filter, false, false, $select);
        while ($element = $iterator->Fetch()) {
            $this->arResult['ITEMS'][] = [
                'ID' => $element['ID'],
                'FIO' => $element['FIO'],
                'EMAIL' => $element['EMAIL'],

            ];
        }
    }
}
