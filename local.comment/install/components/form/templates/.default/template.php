<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(false);
?>
<div class="custom_send_form">
    <? if (!empty($arResult['ERROR'])) { ?>
        <div class="succes_send_form">
            <? echo $arResult["OK_MESSAGE"]; ?>
        </div>
    <? } ?>
    <form id="algin-form" name="iblock_add" action="<?= POST_FORM_ACTION_URI ?>" method="post" enctype="multipart/form-data">
        <?= bitrix_sessid_post() ?>



        <? foreach ($arResult['PROPERTY_DATAS'] as $field) { ?>
            <div class="send_form_field form-group">
                <strong><? echo $field['NAME'] ?>:</strong>
                <? if ($field['PROPERTY_TYPE'] == "F") { ?>
                    <? $APPLICATION->IncludeComponent("bitrix:main.file.input", "dragn_n_drop", Array(
                        "INPUT_NAME" => $field["CODE"],
                        "MULTIPLE" => "Y",
                        "MODULE_ID" => "main",
                        "MAX_FILE_SIZE" => "",
                        "ALLOW_UPLOAD" => "A",
                        "ALLOW_UPLOAD_EXT" => ""
                    ),
                        false
                    ); ?>
                <? } elseif ($field['DEFAULT_VALUE']['TYPE'] == "HTML") { ?>
                    <textarea name="<? echo $field['CODE'] ?>" cols="30" rows="10"></textarea>
                <? } else { ?>
                    <input type="text" name="<? echo $field['CODE'] ?>" maxlength="255" value="">
                <? } ?>

                <? //echo $field['HINT'] ?>
            </div>
        <? } ?>
        <div class="send_form_field form-group">
            <strong>Текст комментария</strong>
            <input type="text" name="DETAIL_TEXT" maxlength="255" value="" required>
        </div>
        <div class="send_form_submit form-group">
            <input class="btn" type="submit" value="Отправить">
        </div>
        <?if ((!empty($_REQUEST['DETAIL_TEXT'])) && (!empty($_REQUEST['sessid'])) ){?>
        <div class="send_form_field form-group">
            <p>
                <?=$this->arResult["OK_MESSAGE"]?>
            </p>
        </div>
        <?}?>
    </form>
</div>
<!--
 <form id="algin-form">
                        <div class="form-group">
                            <h4>Leave a comment</h4> <label for="message">Message</label> <textarea name="msg" id="" msg cols="30" rows="5" class="form-control" style="background-color: black;"></textarea>
                        </div>
                        <div class="form-group"> <label for="name">Name</label> <input type="text" name="name" id="fullname" class="form-control"> </div>
                        <div class="form-group"> <label for="email">Email</label> <input type="text" name="email" id="email" class="form-control"> </div>
                        <div class="form-group">
                            <p class="text-secondary">If you have a <a href="#" class="alert-link">gravatar account</a> your address will be used to display your profile picture.</p>
                        </div>
                        <div class="form-inline"> <input type="checkbox" name="check" id="checkbx" class="mr-1"> <label for="subscribe">Subscribe me to the newlettter</label> </div>
                        <div class="form-group"> <button type="button" id="post" class="btn">Post Comment</button> </div>
                    </form>
-->