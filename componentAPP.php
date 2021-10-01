<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$componentCommentsParams1 = array(
    'COMMENT_IBLOCK_TYPE' => $arResult['IBLOCK_TYPE'],
    'COMMENT_IBLOCK_CODE' => $arParams['IBLOCK_CODE'],
    'COMMENT_ELEMENT_ID' => $arResult['ID'],
    'IBLOCK_TYPE' => "local_comment_text",
    'IBLOCK_ID' => 4,
    'PROPERTY_CODES' => '',
    'MAIL_TO' => '',
    'MAIL_FROM' => '',
    'OK_TEXT' => 'Все хорошо',
);
$APPLICATION->IncludeComponent('local:comment.list', '', $componentCommentsParams1, $component, array('HIDE_ICONS' => 'Y'));
