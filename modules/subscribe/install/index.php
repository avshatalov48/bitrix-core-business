<?php
IncludeModuleLangFile(__FILE__);

if (class_exists('subscribe'))
{
	return;
}

class subscribe extends CModule
{
	public $MODULE_ID = 'subscribe';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_CSS;
	public $MODULE_GROUP_RIGHTS = 'Y';

	public $errors;

	public function __construct()
	{
		$arModuleVersion = [];

		include __DIR__ . '/version.php';

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = GetMessage('inst_module_name');
		$this->MODULE_DESCRIPTION = GetMessage('inst_module_desc');
		$this->MODULE_CSS = '/bitrix/modules/subscribe/styles.css';
	}

	public function InstallDB($arParams = [])
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		// Database tables creation
		if (!$DB->Query("SELECT 'x' FROM b_list_rubric WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/install/db/' . $connection->getType() . '/install.sql');
		}

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));
			return false;
		}
		else
		{
			RegisterModule('subscribe');
			CModule::IncludeModule('subscribe');

			RegisterModuleDependences('main', 'OnBeforeLangDelete', 'subscribe', 'CRubric', 'OnBeforeLangDelete');
			RegisterModuleDependences('main', 'OnUserDelete', 'subscribe', 'CSubscription', 'OnUserDelete');
			RegisterModuleDependences('main', 'OnUserLogout', 'subscribe', 'CSubscription', 'OnUserLogout');
			RegisterModuleDependences('main', 'OnGroupDelete', 'subscribe', 'CPosting', 'OnGroupDelete');
			RegisterModuleDependences('sender', 'OnConnectorList', 'subscribe', 'Bitrix\\Subscribe\\SenderEventHandler', 'onConnectorListSubscriber');
			RegisterModuleDependences('perfmon', 'OnGetTableSchema', 'subscribe', 'subscribe', 'OnGetTableSchema');

			//agents
			CAgent::RemoveAgent('CSubscription::CleanUp();', 'subscribe');

			CTimeZone::Disable();
			CAgent::Add([
				'NAME' => 'CSubscription::CleanUp();',
				'MODULE_ID' => 'subscribe',
				'ACTIVE' => 'Y',
				'NEXT_EXEC' => date('d.m.Y H:i:s', mktime(3,0,0,date('m'),date('j') + 1,date('Y'))),
				'AGENT_INTERVAL' => 86400,
				'IS_PERIOD' => 'Y'
			]);
			CTimeZone::Enable();

			return true;
		}
	}

	public function UnInstallDB($arParams = [])
	{
		global $DB, $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		if (!array_key_exists('save_tables', $arParams) || ($arParams['save_tables'] != 'Y'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/install/db/' . $connection->getType() . '/uninstall.sql');
			$strSql = "SELECT ID FROM b_file WHERE MODULE_ID='subscribe'";
			$rsFile = $DB->Query($strSql);
			while ($arFile = $rsFile->Fetch())
			{
				CFile::Delete($arFile['ID']);
			}
		}

		UnRegisterModuleDependences('main', 'OnBeforeLangDelete', 'subscribe', 'CRubric', 'OnBeforeLangDelete');
		UnRegisterModuleDependences('main', 'OnUserDelete', 'subscribe', 'CSubscription', 'OnUserDelete');
		UnRegisterModuleDependences('main', 'OnGroupDelete', 'subscribe', 'CPosting', 'OnGroupDelete');
		UnRegisterModuleDependences('main', 'OnUserLogout', 'subscribe', 'CSubscription', 'OnUserLogout');
		UnRegisterModuleDependences('sender', 'OnConnectorList', 'subscribe', 'Bitrix\\Subscribe\\SenderEventHandler', 'onConnectorListSubscriber');
		UnRegisterModuleDependences('perfmon', 'OnGetTableSchema', 'subscribe', 'subscribe', 'OnGetTableSchema');

		UnRegisterModule('subscribe');

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));
			return false;
		}

		return true;
	}

	public function InstallEvents()
	{
		global $DB;
		$sIn = "'LIST_MESSAGE','SUBSCRIBE_CONFIRM'";
		$rs = $DB->Query('SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (' . $sIn . ') ');
		$ar = $rs->Fetch();
		if ($ar['C'] <= 0)
		{
			include $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/install/events.php';
		}
		return true;
	}

	public function UnInstallEvents()
	{
		global $DB;
		$sIn = "'LIST_MESSAGE','SUBSCRIBE_CONFIRM'";
		$DB->Query('DELETE FROM b_event_message WHERE EVENT_NAME IN (' . $sIn . ') ');
		$DB->Query('DELETE FROM b_event_type WHERE EVENT_NAME IN (' . $sIn . ') ');
		return true;
	}

	public function InstallFiles($arParams = [])
	{
		if ($_ENV['COMPUTERNAME'] != 'BX')
		{
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/install/themes', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes', false, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/install/components', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);
		}

		if (array_key_exists('install_auto_templates', $arParams) && $arParams['install_auto_templates'] == 'Y')
		{
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/install/php_interface', $_SERVER['DOCUMENT_ROOT'] . BX_PERSONAL_ROOT . '/php_interface', false, true);
		}

		$bReWriteAdditionalFiles = ($arParams['public_rewrite'] == 'Y');

		if (
			array_key_exists('install_public', $arParams) && ($arParams['install_public'] == 'Y')
			&& array_key_exists('public_dir', $arParams) && mb_strlen($arParams['public_dir'])
		)
		{
			$rsSite = CSite::GetList();
			while ($site = $rsSite->Fetch())
			{
				$source = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/public/';
				$target = $site['ABS_DOC_ROOT'] . $site['DIR'] . $arParams['public_dir'] . '/';
				if (file_exists($source))
				{
					CheckDirPath($target);
					$dh = opendir($source);
					while ($file = readdir($dh))
					{
						if ($file == '.' || $file == '..')
						{
							continue;
						}
						if ($bReWriteAdditionalFiles || !file_exists($target . $file))
						{
							$fh = fopen($source . $file, 'rb');
							$php_source = fread($fh, filesize($source . $file));
							fclose($fh);
							if (preg_match_all('/GetMessage\("(.*?)"\)/', $php_source, $matches))
							{
								IncludeModuleLangFile($source . $file, $site['LANGUAGE_ID']);
								foreach ($matches[0] as $i => $text)
								{
									$php_source = str_replace(
										$text,
										'"' . GetMessage($matches[1][$i]) . '"',
										$php_source
									);
								}
							}
							$fh = fopen($target . $file, 'wb');
							fwrite($fh, $php_source);
							fclose($fh);
						}
					}
				}
			}
		}

		return true;
	}

	public function UnInstallFiles()
	{
		if ($_ENV['COMPUTERNAME'] != 'BX')
		{
			//admin files
			DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
			//css
			DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/install/themes/.default/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default');
		}
		return true;
	}

	public function DoInstall()
	{
		global $APPLICATION, $step, $errors;
		$POST_RIGHT = CMain::GetUserRight('subscribe');
		if ($POST_RIGHT == 'W')
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage('inst_inst_title'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/install/inst1.php');
			}
			elseif ($step == 2)
			{
				if ($this->InstallDB())
				{
					$this->InstallEvents();
					$this->InstallFiles([
						'install_auto_templates' => $_REQUEST['install_auto_templates'],
						'install_public' => $_REQUEST['install_public'],
						'public_dir' => $_REQUEST['public_dir'],
						'public_rewrite' => $_REQUEST['public_rewrite'],
					]);
				}
				$errors = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage('inst_inst_title'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/install/inst2.php');
			}
		}
	}

	public function DoUninstall()
	{
		global $APPLICATION, $step, $errors;
		$POST_RIGHT = CMain::GetUserRight('subscribe');
		if ($POST_RIGHT == 'W')
		{
			$step = intval($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage('inst_uninst_title'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/install/uninst1.php');
			}
			elseif ($step == 2)
			{
				$this->UnInstallDB([
					'save_tables' => $_REQUEST['save_tables'],
				]);
				//message types and templates
				if ($_REQUEST['save_templates'] != 'Y')
				{
					$this->UnInstallEvents();
				}
				$this->UnInstallFiles();
				$errors = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage('inst_uninst_title'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/subscribe/install/uninst2.php');
			}
		}
	}

	public static function OnGetTableSchema()
	{
		return [
			'subscribe' => [
				'b_list_rubric' => [
					'ID' => [
						'b_subscription_rubric' => 'LIST_RUBRIC_ID',
						'b_posting_rubric' => 'LIST_RUBRIC_ID',
					]
				],
				'b_subscription' => [
					'ID' => [
						'b_subscription_rubric' => 'SUBSCRIPTION_ID',
						'b_posting_email' => 'SUBSCRIPTION_ID',
					]
				],
				'b_posting' => [
					'ID' => [
						'b_posting_email' => 'POSTING_ID',
						'b_posting_rubric' => 'POSTING_ID',
						'b_posting_group' => 'POSTING_ID',
						'b_posting_file' => 'POSTING_ID',
					]
				],
			],
			'main' => [
				'b_file' => [
					'ID' => [
						'b_posting_file' => 'FILE_ID',
					]
				],
				'b_lang' => [
					'LID' => [
						'b_list_rubric' => 'LID',
					]
				],
				'b_user' => [
					'ID' => [
						'b_subscription' => 'USER_ID',
						'b_posting_email' => 'USER_ID',
					]
				],
				'b_group' => [
					'ID' => [
						'b_posting_group' => 'GROUP_ID',
					]
				],
			],
		];
	}
}
