<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
// ??  $this->setFrameMode(false);
use Bitrix\Main;
use Bitrix\Main\Localization\Loc as Loc;

// !!!!!!! Расскоментируй - если хочешь отправлять штатным почтовым событием FEEDBACK_FORM
// use Bitrix\Main\Mail\Event;

class CommentFormComponent extends CBitrixComponent {

    /**
     * Поля для создания нового комментария
     * @var array
     */
    protected $sendFields = array();

    /**
     * проверяет подключение необходиимых модулей
     * @throws LoaderException
     */
    protected function checkModules() {
        if (!Main\Loader::includeModule('iblock'))
            throw new Main\LoaderException(Loc::getMessage('STANDARD_ELEMENTS_LIST_CLASS_IBLOCK_MODULE_NOT_INSTALLED'));
    }

    /**
     * проверяет заполнение обязательных параметров
     * @throws SystemException
     */
    protected function checkParams() {
        if ($this->arParams['IBLOCK_ID'] <= 0 && strlen($this->arParams['IBLOCK_CODE']) <= 0)
            throw new Main\ArgumentNullException('IBLOCK_ID');
    }

    /**
     *   Действие если был передан Post запрос
     */
    public function postAction() {
        // POST формы
        $this->arResult['ERROR'] = array();
        if ((!empty($_REQUEST['NAME'])) && (!empty($_REQUEST['sessid'])) && (empty($_REQUEST['USER']))) {

            //$el = new \CIBlockElement;
            $section_id = false;
            foreach ($this->arResult['PROPERTY_DATAS'] as $sendProps) {
                $this->sendFields[$sendProps['CODE']] = strip_tags($_POST[$sendProps['CODE']]);
            }
            $fields = [
                "IBLOCK_ID" => $this->arParams['IBLOCK_ID'],
                "PROPERTY_VALUES" => $this->sendFields,
                "NAME" => strip_tags($_REQUEST['NAME']),
        ];

            if ($ID = \CIBlockElement::Add($fields)) {
                array_push($this->arResult['ERROR'], "NOT_ERROR");
                $this->arResult["OK_MESSAGE"] = $this->arParams['OK_TEXT'];

                $this->notificationModer();
            }
        } else {
            // Бот антикапча
            // Капча будет позже, пока проходит тупая проверка- на пустое поле USER
            // Совсем тупенькие боты - будут пытаться его заполнить
        }
    }

    public function notificationModer() {
        // !!!!!!! Расскоментируй - если хочешь отправлять штатным почтовым событием FEEDBACK_FORM
//        Event::send(array(
//            "EVENT_NAME" => "FEEDBACK_FORM",
//            "LID" => "s1",
//            "C_FIELDS" => array(
//                "EMAIL_TO" => $this->arParams['MAIL_TO'],
//                "PROPERTY_VALUES" => implode('<br>',$this->sendFields),
//                "TEXT" => $_REQUEST['NAME'],
//            ),
//        ));
        $toMail = $this->arParams['MAIL_TO'];
        $fromMail = $this->arParams['MAIL_FROM'];
        $subjectMail = $_REQUEST['NAME'];
        $messageMail = implode(',', $this->sendFields);
        if (mail($toMail, $subjectMail, $messageMail, $fromMail))
            echo "Mail sended";
        else
            echo "Mail not sended, check php-mail";
    }

    /**
     * получение свойств для отправки в бд
     */
    protected function getPropsResult() {

        $this->arResult[] = [
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'PROPERTY_IDS' => $this->arParams['PROPERTY_CODES'],
        ];

        $this->arResult['PROPERTY_DATAS'] = array();
        $properties = CIBlockProperty::GetList(
                        Array("name" => "asc"),
                        Array("ACTIVE" => "Y", "IBLOCK_ID" => $this->arParams['IBLOCK_ID'],)
        );
        while ($prop_fields = $properties->GetNext()) {
            array_push($this->arResult['PROPERTY_DATAS'], $prop_fields);
        }
    }

    /**
     * выполняет логику работы компонента
     */
    public function executeComponent() {
        global $APPLICATION;
        try {
            $this->checkModules();
            $this->checkParams();
            $this->getPropsResult();
            $this->postAction();
            $this->includeComponentTemplate();

            return $this->returned;
        } catch (Exception $e) {
            //  $this->abortDataCache();
            ShowError($e->getMessage());
        }
    }

}
?>


