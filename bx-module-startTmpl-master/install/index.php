<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Sale\Internals\OrderPropsTable;

Loc::loadMessages(__FILE__);

class local_modexample extends CModule
{
    /** @var string */
    public $MODULE_ID;

    /** @var string */
    public $MODULE_VERSION;

    /** @var string */
    public $MODULE_VERSION_DATE;

    /** @var string */
    public $MODULE_NAME;

    /** @var string */
    public $MODULE_DESCRIPTION;

    /** @var string */
    public $MODULE_GROUP_RIGHTS;

    /** @var string */
    public $PARTNER_NAME;

    /** @var string */
    public $PARTNER_URI;

    /** @var string */
    public $SHOW_SUPER_ADMIN_GROUP_RIGHTS;

    /** @var string */
    public $MODULE_NAMESPACE;

    protected $exclAdminFiles;
    protected $arModConf;

    protected $PARTNER_CODE;
    protected $MODULE_CODE;

    private $arIblockTypes = [];

    private $arIblocks = [];

    private $siteId;

    public function __construct(){

        $arModuleVersion = [];
        include __DIR__.'/version.php';
        // Подключаем файл с настройками и списком инфоблоков
        $this->arModConf = include __DIR__ . '/../mod_conf.php';

        $this->exclAdminFiles = [
            '..',
            '.',
            'menu.php',
            'operation_description.php',
            'task_description.php',
        ];

        if ($this->arModConf['arIblockTypes']) {
            $this->arIblockTypes = $this->arModConf['arIblockTypes'];
        }

        if ($this->arModConf['arIblocks']) {
            $this->arIblocks = $this->arModConf['arIblocks'];
        }

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_ID = strtolower($this->arModConf['name']);
        $this->MODULE_NAME = Loc::getMessage($this->arModConf['name'].'_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage($this->arModConf['name'].'_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage($this->arModConf['name'].'_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage($this->arModConf['name'].'_PARTNER_URI');
        $this->MODULE_NAMESPACE = $this->arModConf['ns'];

        $this->MODULE_GROUP_RIGHTS = 'Y';
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';

        $this->PARTNER_CODE = $this->getPartnerCodeByModuleID();
        $this->MODULE_CODE = $this->getModuleCodeByModuleID();

        $rsSites = CSite::GetList($by="sort", $order="desc", ['ACTIVE' => 'Y']);
        $arSite = $rsSites->Fetch();
        $this->siteId = $arSite['ID'];
    }

    /**
     * Получение актуального пути к модулю с учетом многосайтовости
     * Как вариант можно использовать более производительную функцию str_pos
     * Недостатком данного метода является возможность "ложных срабатываний".
     * В том случае если в пути встретится два раза последовательность
     * local/modules или bitrix/modules.
     *
     * @param bool $notDocumentRoot
     * @return mixed|string
     */
    protected function getPath($notDocumentRoot = false) {
        return  ($notDocumentRoot)
            ? preg_replace('#^(.*)\/(local|bitrix)\/modules#','/$2/modules',dirname(__DIR__))
            : dirname(__DIR__);
    }

    /**
     * Получение кода партнера из ID модуля
     * @return string
     */
    protected function getPartnerCodeByModuleID() {
        $delimeterPos = strpos($this->MODULE_ID, '.');
        $pCode = substr($this->MODULE_ID, 0, $delimeterPos);

        if (!$pCode) {
            $pCode = $this->MODULE_ID;
        }

        return $pCode;
    }

    /**
     * Получение кода модуля из ID модуля
     * @return string
     */
    protected function getModuleCodeByModuleID() {
        $delimeterPos = strpos($this->MODULE_ID, '.') + 1;
        $mCode = substr($this->MODULE_ID, $delimeterPos);

        if (!$mCode) {
            $mCode = $this->MODULE_ID;
        }

        return $mCode;
    }

    /**
     * Проверка версии ядра системы
     *
     * @return bool
     */
    protected function isVersionD7() {
        return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
    }

    /**
     * Установка модуля
     */
    public function DoInstall() {
        global $APPLICATION;

        if ($this->isVersionD7()) {

            ModuleManager::registerModule($this->MODULE_ID);

            try {

                $this->InstallIblocks();
                $this->InstallFiles();

                $APPLICATION->IncludeAdminFile(Loc::getMessage($this->arModConf['name'].'_INSTALL_TITLE'), $this->getPath() . "/install/step.php");

            } catch (Exception $e) {
                ModuleManager::unRegisterModule($this->MODULE_ID);
                $APPLICATION->ThrowException('Произошла ошибка при установке ');
            }

        } else {
            $APPLICATION->ThrowException(Loc::getMessage($this->arModConf['name']."_INSTALL_ERROR_WRONG_VERSION"));
        }

    }

    /**
     * Удаление модуля
     */
    public function DoUnInstall() {
        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        if ($request->get('step') < 2) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage($this->arModConf['name']."_UNINSTALL_TITLE"), $this->getPath()."/install/unstep1.php");
        } elseif($request->get('step') == 2) {

            $this->UnInstallFiles();

            if($request->get('saveiblocks') != 'Y') {
                $this->UnInstallIblocks();
            }

            ModuleManager::unRegisterModule($this->MODULE_ID);

            $APPLICATION->IncludeAdminFile(Loc::getMessage($this->arModConf['name']."_UNINSTALL_TITLE"), $this->getPath()."/install/unstep2.php");
        }

    }




    /**
     * Работа с файлами при установке модуля
     */
    public function InstallFiles() {
        // Копируем компоненты в папки ядра, переименовывая их по шаблону КОД_МОДУЛЯ.ИМЯ_КОМПОНЕНТА
        if (Directory::isDirectoryExists($path = $this->GetPath() . '/install/components')) {
            if ($dir = opendir($path)) {
                while(false !== ($item = readdir($dir))) {

                    $compPath = $path .'/'. $item;

                    if(in_array($item, ['.', '..']) || !is_dir($compPath)) {
                        continue;
                    }
                    $newPath = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/components/" . $this->PARTNER_CODE . '/' . $this->MODULE_CODE  . '.' . $item;
                    CopyDirFiles($compPath, $newPath, true, true);
                }
                closedir($dir);
            }
        }

        // Копируем и создаем файлы с включениями административных страниц в ядро
        if (Directory::isDirectoryExists($path = $this->GetPath() . '/admin')) {
            CopyDirFiles($this->GetPath() . "/install/admin", $_SERVER['DOCUMENT_ROOT'] . "/bitrix/admin");

            if ($dir = opendir($path)) {

                while(false !== $item = readdir($dir)) {

                    $filePathRelative = $this->GetPath(true).'/admin/'.$item;
                    $filePathFull = $_SERVER["DOCUMENT_ROOT"] . $filePathRelative;

                    if (in_array($item, $this->exclAdminFiles) || !is_file($filePathFull)) {
                        continue;
                    }

                    $subName = str_replace('.','_',$this->MODULE_ID);
                    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$subName.'_'.$item,
                        '<'.'? require_once($_SERVER[\'DOCUMENT_ROOT\'] . "'.$filePathRelative.'");?'.'>');
                }
                closedir($dir);
            }
        }
    }

    /**
     * Работа с файлами при удалении модуля
     * @return bool
     */
    public function UnInstallFiles() {

        // Удалим файлы компонентов модуля, основываясь на принцепе их именования по шаблону КОД_МОДУЛЯ.ИМЯ_КОМПОНЕНТА
        if($this->PARTNER_CODE && $this->MODULE_CODE) {

            if (Directory::isDirectoryExists($partnerPath = $_SERVER['DOCUMENT_ROOT']. '/bitrix/components/' . $this->PARTNER_CODE)) {
                if ($dir = opendir($partnerPath)) {

                    while (false !== ($item = readdir($dir))) {
                        // имя папки компонента начитается с кода нашего модуля?
                        $isModuleComponent = (0 === strpos($item, $this->MODULE_CODE . '.'));
                        $compPath = $partnerPath . '/' . $item;

                        if (!$isModuleComponent || in_array($item, ['.', '..']) || !is_dir($compPath)) {
                            continue;
                        }

                        Directory::deleteDirectory($compPath);
                    }
                }
            }
        }

        // Удалим файлы подключений административных страниц
        if (Directory::isDirectoryExists($path = $this->GetPath() . '/admin')) {
            DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . $this->getPath() . '/install/admin/', $_SERVER["DOCUMENT_ROOT"] . '/bitrix/admin');

            if ($dir = opendir($path)) {
                while (false !== $item = readdir($dir)) {
                    if (in_array($item, $this->exclAdminFiles)) {
                        continue;
                    }

                    $subName = str_replace('.','_',$this->MODULE_ID);
                    File::deleteFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$subName.'_'.$item);
                }
                closedir($dir);
            }
        }

        return true;

    }



    /**
     * Работа с инфоблоками. (Данные из mod_conf загружаются в конструкторе)
     * @return bool
     * @throws Exception
     */
    public function InstallIblocks() {
        $db = $this->getDB();

        // создаем типы инфоблоков
        foreach ($this->arIblockTypes as $IBTypeCODE => $arIblockType) {

            $ibtCode = strtolower($this->arModConf['prefix'] . '_' . $IBTypeCODE);


            $dbIbt = CIBlockType::GetByID($ibtCode);
            $arIbt = $dbIbt->GetNext();
            // проверяем на уникальность, если существует идем к следующему
            if($arIbt) {
                continue;
            }

            $arFields = [
                'ID' => $ibtCode,
                'SECTIONS' => $arIblockType['SECTIONS'],
                'IN_RSS' => 'N',
                'SORT' => $arIblockType['SORT'],
                'LANG' => $arIblockType['LANG'],
            ];

            $obBlocktype = new \CIBlockType();
            $db->StartTransaction();
            $res = $obBlocktype->Add($arFields);

            if(!$res) {
                $db->Rollback();
                // TODO: изменить возврат сообщения об ошибке
                echo 'Error: '.$obBlocktype->LAST_ERROR.'<br>';
            } else {
                $db->Commit();
            }

        }
        // end: создаем типы инфоблоков

        // создаем инфоблоки
        foreach ($this->arIblocks as $IBCODE => $arIblock) {

            $ibCode = strtolower($this->arModConf['prefix'] . '_' . $IBCODE);
            $ibtCode = strtolower($this->arModConf['prefix'] . '_' . $arIblock['TYPE']);

            $ib = new CIBlock();
            $arFields = Array(
                "ACTIVE" => 'Y',
                "NAME" => $arIblock['NAME'],
                "CODE" => $ibCode,
                "IBLOCK_TYPE_ID" => $ibtCode,
                "SITE_ID" => [$this->siteId],
                "LID" => $this->siteId,
                "SORT" => 1000,
                "WORKFLOW" => 'N',
                //"GROUP_ID" => Array("2"=>"D", "3"=>"R")
            );

            $ibId = $ib->Add($arFields);

            if ($ibId > 0) {
                // добавляем свойства из массива 'PROPS'
                foreach ($arIblock['PROPS'] as $arProp) {

                    $dbProperties = CIBlockProperty::GetList([], ["IBLOCK_ID" => $ibId, 'CODE' => $arProp['CODE']]);
                    if ($dbProperties->SelectedRowsCount() > 0) {
                        continue;
                    }

                    $ibp = new CIBlockProperty;
                    // свойства свойств
                    $arFields = Array(
                        "NAME" => $arProp['NAME'],
                        "ACTIVE" => "Y",
                        "SORT" => 100, // Сортировка
                        "CODE" => $arProp['CODE'],
                        "PROPERTY_TYPE" => "S", // Строка
                        "ROW_COUNT" => 1, // Количество строк
                        "COL_COUNT" => 60, // Количество столбцов
                        "IBLOCK_ID" => $ibId
                    );
                    $propId = $ibp->Add($arFields);

                    if (!$propId) {
                        \Bitrix\Main\Diag\Debug::dump($ibp->LAST_ERROR);
                        die();
                    }
                }
            } else {
                \Bitrix\Main\Diag\Debug::dump($ib->LAST_ERROR);
                die();
            }

        }

        return true;
    }

    /**
     * Удаление инфоблоков
     * @return bool
     */
    public function UnInstallIblocks() {
        $db = $this->getDB();

        // удаляем инфоблоки
        foreach ($this->arIblocks as $IBCODE => $arIblock) {

            $ibCode = strtolower($this->arModConf['prefix'] . '_' . $IBCODE);
            $ibtCode = strtolower($this->arModConf['prefix'] . '_' . $arIblock['TYPE']);
            $arOrder = [];
            $arFilter = ['TYPE' => $ibtCode, 'CODE' => $ibCode];
            $dbIBList = CIBlock::GetList($arOrder, $arFilter);

            if ($dbIBList->SelectedRowsCount() == 1) {

                $arIBList = $dbIBList->GetNext();

                $db->StartTransaction();
                if (!CIBlock::Delete($arIBList['ID'])) {
                    $db->Rollback();
                    echo 'Delete error!';
                } else {
                    $db->Commit();
                }
            }
        }

        // удаляем типы инфоблоков
        foreach ($this->arIblockTypes as $IBTypeCODE => $arIblockType) {

            $ibtCode = strtolower($this->arModConf['prefix'] . '_' . $IBTypeCODE);
            $dbIbt = CIBlockType::GetByID($ibtCode);
            $arIbt = $dbIbt->GetNext();

            if(!$arIbt) {
                continue;
            }

            $db->StartTransaction();
            if(!CIBlockType::Delete($ibtCode)) {
                $db->Rollback();
                echo 'Delete error!';
            } else {
                $db->Commit();
            }
        }

        return true;
    }

    /**
     * Создание почтовых событий и шаблонов
     * @return bool
     */

}
