<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}



Loc::loadMessages(__FILE__);
$this->setFrameMode(true);
?>
<section>
    <div class="container">
        <div class="row">
            <? if (sizeof($arResult['ITEMS'])): ?>
                <div class="col-sm-5 col-md-6 col-12 pb-4">
                    <h2>Comments</h2>
                    <? foreach ($arResult['ITEMS'] as $key => $item) : ?>
                        <div class="comment mt-4 text-justify float-left"> <img src="https://i.imgur.com/yTFUilP.jpg" alt="" class="rounded-circle" width="40" height="40">
                            <h4><?= $item["FIO"] ?></h4> <span></span> <br>
                            <p><?= $item["DETAIL_TEXT"] ?></p>
                        </div>
                    <? endforeach ?>
                </div>
            <? endif; ?>
            <div class="col-lg-4 col-md-5 col-sm-4 offset-md-1 offset-sm-1 col-12 mt-4">
                <? $compParams= [
                    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                    "COMMENT_ELEMENT_ID" => $arParams["COMMENT_ELEMENT_ID"],
                    "PROPERTY_CODES" => ["FIO", "EMAIL"], // $arParams["PROPERTY_CODES"],
                    "MAIL_TO" => $arParams["MAIL_TO"],
                    "MAIL_FROM" => $arParams["MAIL_FROM"],
                    "OK_TEXT" => $arParams["OK_TEXT"],
                    "USE_CAPTCHA" => "Y",
                        ];
                $APPLICATION->IncludeComponent("local:comment.form", "",$compParams);
                ?>
            </div>
        </div>
    </div>
</section>


