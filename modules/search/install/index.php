<?php

IncludeModuleLangFile(__FILE__);

class search extends CModule
{
	var $MODULE_ID = 'search';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	var $errors;

	public function __construct()
	{
		$arModuleVersion = [];

		include __DIR__ . '/version.php';

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->MODULE_NAME = GetMessage('SEARCH_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('SEARCH_MODULE_DESC');
	}

	function InstallDB($arParams = [])
	{
		global $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;

		$node_id = $arParams['DATABASE'] <> '' ? intval($arParams['DATABASE']) : false;
		if ($node_id !== false)
		{
			$DB = $GLOBALS['DB']->GetDBNodeConnection($node_id);
		}
		else
		{
			$DB = $GLOBALS['DB'];
		}

		// Database tables creation
		if (!$DB->TableExists("b_search_content"))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/db/' . $connection->getType() . '/install.sql');
		}

		if ($this->errors === false && !$DB->TableExists('b_search_phrase'))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/db/' . $connection->getType() . '/stat_install.sql');
		}

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));
			return false;
		}
		else
		{
			RegisterModule('search');

			CModule::IncludeModule('search');

			RegisterModuleDependences('main', 'OnChangePermissions', 'search', 'CSearch', 'OnChangeFilePermissions');
			RegisterModuleDependences('main', 'OnChangeFile', 'search', 'CSearch', 'OnChangeFile');
			RegisterModuleDependences('main', 'OnGroupDelete', 'search', 'CSearch', 'OnGroupDelete');
			RegisterModuleDependences('main', 'OnLangDelete', 'search', 'CSearch', 'OnLangDelete');
			RegisterModuleDependences('main', 'OnAfterUserUpdate', 'search', 'CSearchUser', 'OnAfterUserUpdate');
			RegisterModuleDependences('main', 'OnUserDelete', 'search', 'CSearchUser', 'DeleteByUserID');
			RegisterModuleDependences('cluster', 'OnGetTableList', 'search', 'search', 'OnGetTableList');
			RegisterModuleDependences('perfmon', 'OnGetTableSchema', 'search', 'search', 'OnGetTableSchema');

			if ($node_id !== false)
			{
				COption::SetOptionString('search', 'dbnode_id', $node_id);
				if (CModule::IncludeModule('cluster'))
				{
					CClusterDBNode::SetOnline($node_id);
				}
			}
			else
			{
				COption::SetOptionString('search', 'dbnode_id', 'N');
			}
			COption::SetOptionString('search', 'dbnode_status', 'ok');

			CAgent::AddAgent('CSearchSuggest::CleanUpAgent();','search', 'N', 86400, '', 'Y', '', 10);
			CAgent::AddAgent('CSearchStatistic::CleanUpAgent();','search', 'N', 86400, '', 'Y', '', 10);

			CSearchStatistic::SetActive(COption::GetOptionString('search', 'stat_phrase') == 'Y');

			return true;
		}
	}

	function UnInstallDB($arParams = [])
	{
		global $APPLICATION;
		$connection = \Bitrix\Main\Application::getConnection();
		$this->errors = false;
		$DB = CDatabase::GetModuleConnection('search', true);

		UnRegisterModuleDependences('main', 'OnEpilog', 'search', 'CSearchStatistic', 'OnEpilog');
		UnRegisterModuleDependences('main', 'OnChangePermissions', 'search', 'CSearch', 'OnChangeFilePermissions');
		UnRegisterModuleDependences('main', 'OnChangeFile', 'search', 'CSearch', 'OnChangeFile');
		UnRegisterModuleDependences('main', 'OnGroupDelete', 'search', 'CSearch', 'OnGroupDelete');
		UnRegisterModuleDependences('main', 'OnLangDelete', 'search', 'CSearch', 'OnLangDelete');
		UnRegisterModuleDependences('main', 'OnAfterUserUpdate', 'search', 'CSearchUser', 'OnAfterUserUpdate');
		UnRegisterModuleDependences('main', 'OnUserDelete', 'search', 'CSearchUser', 'DeleteByUserID');
		UnRegisterModuleDependences('cluster', 'OnGetTableList', 'search', 'search', 'OnGetTableList');
		UnRegisterModuleDependences('perfmon', 'OnGetTableSchema', 'search', 'search', 'OnGetTableSchema');

		if (is_object($DB))
		{
			if (!array_key_exists('savedata', $arParams) || ($arParams['savedata'] != 'Y'))
			{
				$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/db/' . $connection->getType() . '/uninstall.sql');
			}

			if (!array_key_exists('savestat', $arParams) || ($arParams['savestat'] != 'Y'))
			{
				$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/db/' . $connection->getType() . '/stat_uninstall.sql');
			}
		}

		UnRegisterModule('search');

		COption::SetOptionString('search', 'dbnode_id', 'N');
		COption::SetOptionString('search', 'dbnode_status', 'ok');

		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode('<br>', $this->errors));
			return false;
		}

		return true;
	}

	function InstallFiles($arParams = [])
	{
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/js', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/search', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/images', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/images/search', true, true);
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/components', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);

		$bReWriteAdditionalFiles = ($arParams['public_rewrite'] == 'Y');

		if (array_key_exists('public_dir', $arParams) && mb_strlen($arParams['public_dir']))
		{
			$rsSite = CSite::GetList();
			while ($site = $rsSite->Fetch())
			{
				$source = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/public/';
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

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
		DeleteDirFilesEx('/bitrix/images/search/');
		DeleteDirFilesEx('/bitrix/js/search/');

		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;

		$step = intval($step);
		if ($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage('SEARCH_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/step1.php');
		}
		elseif ($step == 2)
		{
			$db_install_ok = $this->InstallDB([
				'DATABASE' => $_REQUEST['DATABASE'],
			]);
			if ($db_install_ok)
			{
				$this->InstallFiles([
					'public_dir' => $_REQUEST['public_dir'],
					'public_rewrite' => $_REQUEST['public_rewrite'],
				]);
			}
			$GLOBALS['errors'] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage('SEARCH_INSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/step2.php');
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

		$step = intval($step);
		if ($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage('SEARCH_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/unstep1.php');
		}
		elseif ($step == 2)
		{
			$this->UnInstallDB([
				'savedata' => $_REQUEST['savedata'],
				'savestat' => $_REQUEST['savestat'],
			]);
			$this->UnInstallFiles();
			$GLOBALS['errors'] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage('SEARCH_UNINSTALL_TITLE'), $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/search/install/unstep2.php');
		}
	}

	public static function OnGetTableList()
	{
		return [
			'MODULE' => new search,
			'TABLES' => [
				'b_search_content' => 'ID',
				'b_search_content_right' => 'SEARCH_CONTENT_ID',
				'b_search_content_site' => 'SEARCH_CONTENT_ID',
				'b_search_content_stem' => 'SEARCH_CONTENT_ID',
				'b_search_content_title' => 'SEARCH_CONTENT_ID',
				'b_search_content_freq' => 'STEM',
				'b_search_custom_rank' => 'ID',
				'b_search_tags' => 'SEARCH_CONTENT_ID',
				'b_search_suggest' => 'ID',
				'b_search_phrase' => 'ID',
				'b_search_user_right' => 'USER_ID',
				'b_search_content_param' => 'SEARCH_CONTENT_ID',
				'b_search_stem' => 'ID',
				'b_search_content_text' => 'SEARCH_CONTENT_ID',
			],
		];
	}

	public static function OnGetTableSchema()
	{
		return [
			'search' => [
				'b_search_content' => [
					'ID' => [
						'b_search_content_stem' => 'SEARCH_CONTENT_ID',
						'b_search_content_text' => 'SEARCH_CONTENT_ID',
						'b_search_content_param' => 'SEARCH_CONTENT_ID',
						'b_search_content_right' => 'SEARCH_CONTENT_ID',
						'b_search_content_site' => 'SEARCH_CONTENT_ID',
						'b_search_content_title' => 'SEARCH_CONTENT_ID',
						'b_search_tags' => 'SEARCH_CONTENT_ID',
					]
				],
				'b_search_stem' => [
					'ID' => [
						'b_search_content_stem' => 'STEM',
						'b_search_content_freq' => 'STEM',
					],
				],
			],
			'main' => [
				'b_user' => [
					'ID' => [
						'b_search_user_right' => 'USER_ID',
					]
				],
				'b_lang' => [
					'LID' => [
						'b_search_content_site' => 'SITE_ID',
						'b_search_content_title' => 'SITE_ID',
						'b_search_content_freq' => 'SITE_ID',
						'b_search_custom_rank' => 'SITE_ID',
						'b_search_tags' => 'SITE_ID',
						'b_search_suggest' => 'SITE_ID',
						'b_search_phrase' => 'SITE_ID',
					]
				],
				'b_language' => [
					'LID' => [
						'b_search_content_stem' => 'LANGUAGE_ID',
						'b_search_content_freq' => 'LANGUAGE_ID',
					]
				],
				'b_module' => [
					'ID' => [
						'b_search_custom_rank' => 'MODULE_ID',
					]
				],
			],
			'statistic' => [
				'b_stat_session' => [
					'ID' => [
						'b_search_phrase' => 'STAT_SESS_ID',
					]
				],
			],
		];
	}
}
