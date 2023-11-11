<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (class_exists('translate'))
{
	return;
}

class translate extends \CModule
{
	public $MODULE_ID = 'translate';
	public $MODULE_GROUP_RIGHTS = 'Y';

	public function __construct()
	{
		$arModuleVersion = array();

		include(__DIR__.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = Loc::getMessage('TRANS_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('TRANS_MODULE_DESCRIPTION');
	}

	/**
	 * @return bool
	 */
	public function InstallDB()
	{
		global $APPLICATION, $DB;

		if (!$DB->query("SELECT 'x' FROM b_translate_path WHERE 1=0", true))
		{
			$errors = $DB->runSqlBatch(sprintf(
				'%s/bitrix/modules/%s/install/db/mysql/install.sql',
				$_SERVER['DOCUMENT_ROOT'],
				mb_strtolower($this->MODULE_ID)
			));
			if ($errors !== false)
			{
				$APPLICATION->ThrowException(implode("", $errors));

				return false;
			}

			\CAgent::AddAgent('\Bitrix\Translate\Index\Internals\PhraseFts::checkTables();', $this->MODULE_ID, 'N', 1);
		}

		Main\ModuleManager::registerModule($this->MODULE_ID);

		$this->InstallEvents();

		return true;
	}

	/**
	 * @return bool
	 */
	public function InstallEvents()
	{
		$eventManager = Main\EventManager::getInstance();
		$eventManager->registerEventHandlerCompatible('main', 'OnPanelCreate', $this->MODULE_ID, '\\Bitrix\\Translate\\Ui\\Panel', 'onPanelCreate');
		$eventManager->registerEventHandlerCompatible('perfmon', 'OnGetTableSchema', $this->MODULE_ID, 'translate', 'onGetTableSchema');

		return true;
	}

	/**
	 * @param array $params
	 * @return bool
	 */
	public function UnInstallDB($params = array())
	{
		global $APPLICATION, $DB;

		if (!isset($params['savedata']) || $params['savedata'] !== true)
		{
			$errors = $DB->runSqlBatch(sprintf(
				'%s/bitrix/modules/%s/install/db/mysql/uninstall.sql',
				$_SERVER['DOCUMENT_ROOT'],
				mb_strtolower($this->MODULE_ID)
			));
			if ($errors !== false)
			{
				$APPLICATION->ThrowException(implode("<br>", $errors));

				return false;
			}
		}

		$tablesRes = $DB->Query("SHOW TABLES LIKE 'b_translate_phrase_fts_%'");
		while ($row = $tablesRes->fetch())
		{
			$tableName = array_shift($row);
			$DB->Query("DROP TABLE IF EXISTS `{$tableName}`");
		}

		Main\Config\Option::delete($this->MODULE_ID);

		$this->UnInstallEvents();

		\CAgent::RemoveModuleAgents($this->MODULE_ID);

		Main\ModuleManager::unRegisterModule($this->MODULE_ID);

		return true;
	}

	/**
	 * @return bool
	 */
	public function UnInstallEvents()
	{
		$eventManager = Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('main', 'OnPanelCreate', $this->MODULE_ID, '\\Bitrix\\Translate\\Ui\\Panel', 'onPanelCreate');
		$eventManager->unRegisterEventHandler('perfmon', 'OnGetTableSchema', $this->MODULE_ID, 'translate', 'onGetTableSchema');

		return true;
	}

	/**
	 * @return bool
	 */
	public function InstallFiles()
	{
		\CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/translate/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin', true, true);
		\CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/translate/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		\CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/translate/install/images', $_SERVER['DOCUMENT_ROOT'].'/bitrix/images/translate', true, true);
		\CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/translate/install/js', $_SERVER['DOCUMENT_ROOT'].'/bitrix/js', true, true);
		\CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/translate/install/themes', $_SERVER['DOCUMENT_ROOT'].'/bitrix/themes', true, true);

		return true;
	}

	/**
	 * @return bool
	 */
	public function UnInstallFiles()
	{
		\DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/translate/install/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
		\DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/translate/install/components', $_SERVER["DOCUMENT_ROOT"].'/bitrix/components/bitrix');
		\DeleteDirFilesEx('/bitrix/images/translate/');
		\DeleteDirFilesEx('/bitrix/js/translate/');
		\DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/translate/install/themes/.default/', $_SERVER["DOCUMENT_ROOT"].'/bitrix/themes/.default');//css
		\DeleteDirFilesEx('/bitrix/themes/.default/start_menu/translate/');//start_menu
		\DeleteDirFilesEx('/bitrix/themes/.default/icons/translate/');//icons

		return true;
	}

	/**
	 * @return void
	 */
	public function DoInstall()
	{
		global $APPLICATION;
		$this->InstallDB();
		$this->InstallFiles();
		$APPLICATION->IncludeAdminFile(Loc::getMessage('TRANSLATE_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/translate/install/step.php');
	}

	/**
	 * @return void
	 */
	public function DoUninstall()
	{
		global $APPLICATION;

		$step = (int)$_REQUEST['step'];

		if ($step < 2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage('TRANSLATE_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/translate/install/unstep1.php');
		}
		elseif ($step == 2)
		{
			$this->UnInstallFiles();

			$this->UnInstallDB(array(
				'savedata' => ($_REQUEST['savedata'] === 'Y'),
			));

			$APPLICATION->IncludeAdminFile(Loc::getMessage('TRANSLATE_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/translate/install/unstep2.php');
		}
	}

	/**
	 * Event handler 'perfmon::OnGetTableSchema'.
	 * @see \CPerfomanceSchema::Init
	 * @return array
	 */
	public static function OnGetTableSchema()
	{
		return array(
			'translate' => array(
				'b_translate_path' => array(
					'ID' => array(
						'b_translate_file' => 'PATH_ID',
						'b_translate_phrase' => 'PATH_ID',
						'b_translate_path' => 'PARENT_ID',
					),
				),
				'b_translate_file' => array(
					'ID' => array(
						'b_translate_phrase' => 'FILE_ID',
					),
					'LANG_ID' => array(
						'b_language' => 'LID',
					),
				),
			),
			'main' => array(
				'b_language' => array(
					'LID' => array(
						'b_translate_phrase' => 'LANG_ID',
						'b_translate_file' => 'LANG_ID',
					)
				),
			),
		);
	}
}