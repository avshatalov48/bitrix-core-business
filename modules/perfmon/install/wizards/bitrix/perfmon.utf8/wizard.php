<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

abstract class CBaseUtf8WizardStep extends CWizardStep
{
	public function ShowStep()
	{
		$this->content .= '<style>
			li.utf8wiz_erli { list-style-image:url(/bitrix/themes/.default/images/lamp/red.gif) }
			li.utf8wiz_okli { list-style-image:url(/bitrix/themes/.default/images/lamp/green.gif) }
			p.utf8wiz_err { color:red }
			span.utf8wiz_ok { color:green }
		</style>
		';
	}

	public function ShowCheckList($arList)
	{
		if (count($arList) > 0)
		{
			$this->content .= '<ul>';
			foreach ($arList as $rec)
			{
				if ($rec['IS_OK'])
				{
					$this->content .= '<li class="utf8wiz_okli">' . $rec['MESSAGE'] . '</li>';
				}
				else
				{
					$this->content .= '<li class="utf8wiz_erli">' . $rec['MESSAGE'] . '</li>';
				}
			}
			$this->content .= '</ul>';
		}
	}

	public function CheckListHasNoError($arList)
	{
		foreach ($arList as $rec)
		{
			if (!$rec['IS_OK'])
			{
				return false;
			}
		}
		return true;
	}

	public function MakeCheckList()
	{
		$arList = [];

		return $arList;
	}

	public function CloseSite()
	{
		COption::SetOptionString('main', 'site_stopped', 'Y');
		COption::SetOptionString('main', 'check_agents', 'N');
		COption::SetOptionString('main', 'check_events', 'N');
	}

	public function OpenSite()
	{
		COption::SetOptionString('main', 'site_stopped', 'N');
		COption::SetOptionString('main', 'check_agents', 'Y');
		COption::SetOptionString('main', 'check_events', 'Y');
	}

	public static $allowedUnserializeClassesList = [
		\Bitrix\Main\Type\Date::class,
		\Bitrix\Main\Type\DateTime::class,
		\DateTime::class,
		\DateTimeZone::class,
		\Bitrix\Main\Web\Uri::class
	];

	public static function getDatabaseDefaultEncoding()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$result = $connection->query('SELECT DEFAULT_CHARACTER_SET_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = \'' . $helper->forSql($connection->getDatabase()) . '\'')->fetch();

		return $result ? $result['DEFAULT_CHARACTER_SET_NAME'] : '';
	}

	public static function getDatabaseAlter()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		return 'ALTER DATABASE ' . $helper->quote($connection->getDatabase()) . ' CHARACTER SET utf8 COLLATE utf8_unicode_ci';
	}

	public static function getTables()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$result = [];

		$rs = $connection->query('SELECT TABLE_NAME, TABLE_COLLATION FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = \'' . $helper->forSql($connection->getDatabase()) . '\' and TABLE_COLLATION not like \'utf8%\' ORDER BY 1');
		while ($ar = $rs->fetch())
		{
			$result[$ar['TABLE_NAME']] = $ar['TABLE_COLLATION'];
		}

		return $result;
	}

	public static function getTableAlter($tableName)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		return 'ALTER TABLE ' . $helper->quote($connection->getDatabase()) . '.' . $helper->quote($tableName) . ' CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci';
	}

	public static function GetMessage($messageId, $params = [])
	{
		$message = GetMessage($messageId, $params);
		if (strtoupper(SITE_CHARSET) === 'UTF-8')
		{
			if (!\Bitrix\Main\Text\Encoding::detectUtf8($message))
			{
				return \Bitrix\Main\Text\Encoding::convertEncoding($message, SITE_CHARSET, 'UTF-8');
			}
		}
		else
		{
			if (\Bitrix\Main\Text\Encoding::detectUtf8($message))
			{
				return \Bitrix\Main\Text\Encoding::convertEncoding($message, 'UTF-8', SITE_CHARSET);
			}
		}
		return $message;
	}
}

class CUtf8BackupWarningStep extends CBaseUtf8WizardStep
{
	public function InitStep()
	{
		$connection = \Bitrix\Main\Application::getConnection();

		$this->SetTitle(GetMessage('UTFWIZ_STEP1_TITLE'));

		if (!is_a($connection, '\Bitrix\Main\DB\MysqliConnection'))
		{
			$this->SetError(GetMessage('UTFWIZ_DATABASE_NOT_SUPPORTED'));
		}
		else
		{
			$this->SetNextStep('step2');
		}

		$this->SetStepID('step1');
		$this->SetCancelStep('cancel');
	}

	public function ShowStep()
	{
		global $APPLICATION;
		$wizard = $this->GetWizard();
		$path = $wizard->package->path;

		parent::ShowStep();
		if (count($this->GetErrors()) == 0)
		{
			$this->content .= GetMessage('UTFWIZ_BACKUP_WARNING');
			$this->content .= '<br /><br />' . $this->ShowCheckboxField('consent', 'proceed', [
				'id' => 'consent',
				'onclick' => 'if(this.checked){BX.Wizard.Utf8.EnableButton();}else{BX.Wizard.Utf8.DisableButton();}',
			]) . '<label for="consent">' . GetMessage('UTFWIZ_BACKUP_CONSENT') . '</label>';
			$this->content .= '<br /><br />' . GetMessage('UTFWIZ_SITE_CLOSED_WARNING');
			$this->content .= '<br /><br />' . GetMessage('UTFWIZ_CHECK_SITE_WARNING');
			$this->content .= '<br /><br />' . GetMessage('UTFWIZ_CONVERT_NOTICE');

			CJSCore::Init(['ajax']);
			\Bitrix\Main\UI\Extension::load('main.core');
			$APPLICATION->AddHeadScript($path . '/js/wizard.js');

			$init = [
				'nextButtonID' => $wizard->GetNextButtonID(),
				'formID' => $wizard->GetFormName(),
			];
			$this->content .= '
				<script>
					BX.Wizard.Utf8.init(' . \Bitrix\Main\Web\Json::encode($init) . ');
					BX.ready(() => {BX.Wizard.Utf8.DisableButton()});
				</script>
			';
		}
	}

	public function OnPostForm()
	{
		$wizard = $this->GetWizard();
		if ($wizard->IsNextButtonClick())
		{
			$this->CloseSite();
		}
	}
}

class CUtf8CheckStep extends CBaseUtf8WizardStep
{
	public $checkList;

	public function InitStep()
	{
		$this->SetTitle(GetMessage('UTFWIZ_STEP2_TITLE'));
		$this->SetStepID('step2');
		$this->checkList = $this->MakeCheckList();
		if ($this->CheckListHasNoError($this->checkList))
		{
			$this->SetNextStep('step3');
		}
		else
		{
			$this->SetNextStep('step2');
		}
	}

	public function MakeCheckList()
	{
		$arList = [];

		$defaultCharset = strtolower(ini_get('default_charset'));
		$arList[] = [
			'IS_OK' => $defaultCharset === 'utf-8',
			'MESSAGE' => GetMessage('UTFWIZ_STEP2_DEFAULT_CHARSET'),
		];

		$dbconnEditUrl = '/bitrix/admin/fileman_file_edit.php?path=%2Fbitrix%2Fphp_interface%2Fdbconn.php&full_src=Y&lang=' . LANGUAGE_ID;
		$arList[] = [
			'IS_OK' => defined('BX_UTF') && constant('BX_UTF') === true,
			'MESSAGE' => GetMessage('UTFWIZ_STEP2_BX_UTF_CONSTANT', [
				'#EDIT_HREF#' => $dbconnEditUrl,
			]),
		];

		$dbconnContent = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/dbconn.php');
		//Remove comments
		$dbconnContent = preg_replace_callback('#(//.*?$)#m', function($m)
		{
			return str_repeat(' ', strlen($m[0]));
		} , $dbconnContent);
		$dbconnContent = preg_replace_callback('#(/+\\*.*?\\*/+)#is', function($m)
		{
			return preg_replace_callback("/[^\n]+/", function($m)
			{
				return str_repeat(' ', strlen($m[0]));
			}, $m[0]);
		}
		, $dbconnContent);
		$arList[] = [
			'IS_OK' => $dbconnContent && !preg_match('/setlocale\s*\(\s*LC_ALL/i', $dbconnContent),
			'MESSAGE' => GetMessage('UTFWIZ_STEP2_SETLOCALE', [
				'#EDIT_HREF#' => $dbconnEditUrl,
			]),
		];

		$hasMbString = function_exists('mb_internal_encoding');
		$arList[] = [
			'IS_OK' => $hasMbString,
			'MESSAGE' => GetMessage('UTFWIZ_STEP2_MB_INSTALLED'),
		];

		$internalEncoding = $hasMbString ? strtolower(mb_internal_encoding()) : '';
		$arList[] = [
			'IS_OK' => $internalEncoding === 'utf-8',
			'MESSAGE' => GetMessage('UTFWIZ_STEP2_MB_INTERNAL_ENCODING', [
				'#EDIT_HREF#' => $dbconnEditUrl,
			]),
		];

		$arList[] = [
			'IS_OK' => $hasMbString && (ini_get('mbstring.func_overload') == 0),
			'MESSAGE' => GetMessage('UTFWIZ_STEP2_MB_FUNC_OVERLOAD'),
		];

		$settingsEditUrl = '/bitrix/admin/fileman_file_edit.php?path=%2Fbitrix%2F.settings.php&full_src=Y&lang=' . LANGUAGE_ID;
		$utfMode = \Bitrix\Main\Config\Configuration::getValue('utf_mode');
		$arList[] = [
			'IS_OK' => $utfMode === true,
			'MESSAGE' => GetMessage('UTFWIZ_STEP2_UTF_MODE', [
				'#EDIT_HREF#' => $settingsEditUrl,
			]),
		];

		return $arList;
	}

	public function ShowStep()
	{
		parent::ShowStep();
		if (count($this->GetErrors()) == 0)
		{
			$this->ShowCheckList($this->checkList);
		}
	}
}

class CUtf8DatabaseCheckStep extends CBaseUtf8WizardStep
{
	public function InitStep()
	{
		$this->SetTitle(GetMessage('UTFWIZ_STEP3_TITLE'));
		$this->SetStepID('step3');
	}

	public function ShowStep()
	{
		parent::ShowStep();
		$isOk = true;
		$databaseDefaultEncoding = static::getDatabaseDefaultEncoding();
		$isOk = preg_match('/^utf8/i', $databaseDefaultEncoding) > 0;
		if ($isOk)
		{
			$isOk = empty(static::getTables());
		}

		if (!$isOk)
		{
			$this->content .= $this->ShowRadioField('db_convert', 'manual', ['id' => 'db_convert_manual']) . '<label for="db_convert_manual">' . GetMessage('UTFWIZ_DB_CONVERT_MANUAL') . '</label><br />';
			$this->content .= $this->ShowRadioField('db_convert', 'wizard', ['id' => 'db_convert_wizard']) . '<label for="db_convert_wizard">' . GetMessage('UTFWIZ_DB_CONVERT_WIZARD') . '</label><br />';
			$this->SetNextStep('step4');
		}
		else
		{
			$this->content .= GetMessage('UTFWIZ_DB_CONVERT_ALREADY');
			$this->SetNextStep('step5');
		}
	}
}

class CUtf8DatabaseConvertStep extends CBaseUtf8WizardStep
{
	public function InitStep()
	{
		$this->SetTitle(GetMessage('UTFWIZ_STEP4_TITLE'));
		$this->SetStepID('step4');
	}

	public function ShowStep()
	{
		global $APPLICATION;
		$wizard = $this->GetWizard();
		$path = $wizard->package->path;

		parent::ShowStep();
		if ($wizard->GetVar('db_convert') === 'wizard')
		{
			$this->content .= '<div id="output">' . GetMessage('UTFWIZ_INIT') . '<br /></div>';

			CJSCore::Init(['ajax']);
			\Bitrix\Main\UI\Extension::load('main.core');
			$APPLICATION->AddHeadScript($path . '/js/wizard.js');

			$message = [
				'UTFWIZ_FIX_AND_RETRY' => GetMessage('UTFWIZ_FIX_AND_RETRY'),
				'UTFWIZ_RETRYSTEP_BUTTONTITLE' => GetMessage('UTFWIZ_RETRYSTEP_BUTTONTITLE'),
			];
			$init = [
				'nextButtonID' => $wizard->GetNextButtonID(),
				'formID' => $wizard->GetFormName(),
				'LANG' => LANGUAGE_ID,
				'path' => $path,
				'sessid' => bitrix_sessid(),
			];
			$this->content .= '
				<script>
					BX.message(' . \Bitrix\Main\Web\Json::encode($message) . ');
					BX.Wizard.Utf8.init(' . \Bitrix\Main\Web\Json::encode($init) . ');
					BX.ready(() => {BX.Wizard.Utf8.DisableButton()});
					BX.ready(() => {BX.Wizard.Utf8.action(\'database\')});
				</script>
			';

			$this->SetNextStep('step5');
		}
		else
		{
			$this->content .= GetMessage('UTFWIZ_RUN_SQL');
			$ddl = '';
			$databaseDefaultEncoding = static::getDatabaseDefaultEncoding();
			if (!preg_match('/^utf8/i', $databaseDefaultEncoding))
			{
				$ddl .= static::getDatabaseAlter() . ";\n";
			}

			foreach (static::getTables() as $tableName => $_)
			{
				$ddl .= static::getTableAlter($tableName) . ";\n";
			}

			$this->content .= '<br /><textarea style="width:100%;" rows="15">' . htmlspecialcharsEx($ddl) . '</textarea>';
			$this->SetNextStep('step3');
		}
	}
}

class CUtf8DatabaseConnectionStep extends CBaseUtf8WizardStep
{
	public $checkList;

	public function InitStep()
	{
		$this->SetTitle(GetMessage('UTFWIZ_STEP5_TITLE'));
		$this->SetStepID('step5');
		$this->checkList = $this->MakeCheckList();
		if ($this->CheckListHasNoError($this->checkList))
		{
			$this->SetNextStep('step6');
		}
		else
		{
			$this->SetNextStep('step5');
		}
	}

	public function MakeCheckList()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$arList = [];

		if (is_a($connection, '\Bitrix\Main\DB\MysqliConnection'))
		{
			$res = $connection->query('SHOW VARIABLES LIKE "character_set_connection"');
			$f = $res->fetch();
			$character_set_connection = $f['Value'];

			$res = $connection->query('SHOW VARIABLES LIKE "character_set_results"');
			$f = $res->fetch();
			$character_set_results = $f['Value'];

			$res = $connection->query('SHOW VARIABLES LIKE "collation_connection"');
			$f = $res->fetch();
			$collation_connection = $f['Value'];

			$arList[] = [
				'IS_OK' => in_array($character_set_connection, ['utf8', 'utf8mb3', 'utf8mb4']),
				'MESSAGE' => GetMessage('UTFWIZ_CONNECTION_CHARSET'),
			];

			$arList[] = [
				'IS_OK' => preg_match('/^(utf8|utf8mb3|utf8mb4)_/', $collation_connection),
				'MESSAGE' => GetMessage('UTFWIZ_CONNECTION_COLLATION'),
			];

			$arList[] = [
				'IS_OK' => $character_set_connection === $character_set_results,
				'MESSAGE' => GetMessage('UTFWIZ_CHARSET_CONN_VS_RES', [
					'#CONN#' => $character_set_connection,
					'#RES#' => $character_set_results,
				]),
			];
		}

		return $arList;
	}

	public function ShowStep()
	{
		parent::ShowStep();
		if (count($this->GetErrors()) == 0)
		{
			$settingsEditUrl = '/bitrix/admin/fileman_file_edit.php?path=%2Fbitrix%2Fphp_interface%2Fafter_connect_d7.php&full_src=Y&lang=' . LANGUAGE_ID;
			$this->content .= GetMessage('UTFWIZ_EDIT_AFTER_CONNECT', [
				'#EDIT_HREF#' => $settingsEditUrl,
			]);
			$this->content .= '<pre>' . "\n"
				. '	&lt;?' . "php\n"
				. '	$this->queryExecute("SET NAMES \'utf8\'");' . "\n"
				. '	$this->queryExecute(\'SET collation_connection = "utf8_unicode_ci"\');' . "\n"
				. '</pre>';
			$this->ShowCheckList($this->checkList);
		}
	}

	public function OnPostForm()
	{
		$wizard = $this->GetWizard();
		if ($wizard->IsNextButtonClick())
		{
			$wizard->SetVar('source_encoding', '');
		}
	}
}

class CUtf8SerializeFixStep extends CBaseUtf8WizardStep
{
	public function InitStep()
	{
		$wizard = $this->GetWizard();

		$this->SetTitle(GetMessage('UTFWIZ_STEP6_TITLE'));
		$this->SetStepID('step6');
		if ($wizard->GetVar('source_encoding') || $wizard->GetVar('source_encoding_other'))
		{
			$this->SetNextStep('step7');
		}
		else
		{
			$this->SetNextStep('step6');
		}
	}

	public function ShowStep()
	{
		global $APPLICATION;
		$wizard = $this->GetWizard();
		$path = $wizard->package->path;

		parent::ShowStep();
		if ($wizard->GetVar('source_encoding') || $wizard->GetVar('source_encoding_other'))
		{
			$this->content .= '<div id="output">' . GetMessage('UTFWIZ_INIT') . '<br /></div>';

			CJSCore::Init(['ajax']);
			\Bitrix\Main\UI\Extension::load('main.core');
			$APPLICATION->AddHeadScript($path . '/js/wizard.js');

			$message = [
				'UTFWIZ_FIX_AND_RETRY' => GetMessage('UTFWIZ_FIX_AND_RETRY'),
				'UTFWIZ_RETRYSTEP_BUTTONTITLE' => GetMessage('UTFWIZ_RETRYSTEP_BUTTONTITLE'),
			];
			$init = [
				'nextButtonID' => $wizard->GetNextButtonID(),
				'formID' => $wizard->GetFormName(),
				'LANG' => LANGUAGE_ID,
				'path' => $path,
				'sessid' => bitrix_sessid(),
				'sourceEncoding' => $wizard->GetVar('source_encoding') ?: $wizard->GetVar('source_encoding_other'),
			];
			$this->content .= '
				<script>
					BX.message(' . \Bitrix\Main\Web\Json::encode($message) . ');
					BX.Wizard.Utf8.init(' . \Bitrix\Main\Web\Json::encode($init) . ');
					BX.ready(() => {BX.Wizard.Utf8.DisableButton()});
					BX.ready(() => {BX.Wizard.Utf8.action(\'fix\')});
				</script>
			';
		}
		else
		{
			$this->content .= GetMessage('UTFWIZ_CHOOSE') . ':<br />';
			$encodings = [];
			$cultureList = \Bitrix\Main\Localization\CultureTable::getList();
			while ($culture = $cultureList->fetch())
			{
				$charset = mb_strtolower($culture['CHARSET']);
				$encodings[$charset] = $charset;
			}
			foreach ($encodings as $encoding)
			{
				$this->content .= $this->ShowRadioField('source_encoding', $encoding, [
					'id' => 'source_encoding_' . $encoding
				]) . '<label for="source_encoding_' . htmlspecialcharsbx($encoding) . '">' . htmlspecialcharsEx($encoding) . '</label><br>';
			}
			$this->content .= GetMessage('UTFWIZ_OR_OTHER') . ': ';
			$this->content .= $this->ShowInputField('text', 'source_encoding_other', [
				'size' => 20,
			]);
		}
	}

	public function OnPostForm()
	{
		$wizard = $this->GetWizard();
		if ($wizard->IsNextButtonClick())
		{
			$wizard->UnSetVar('skip_links');
		}
	}
}

class CUtf8FileConvertStep extends CBaseUtf8WizardStep
{
	public function InitStep()
	{
		$wizard = $this->GetWizard();

		$this->SetTitle(GetMessage('UTFWIZ_STEP7_TITLE'));
		$this->SetStepID('step7');
		if ($wizard->GetVar('skip_links') !== null)
		{
			$this->SetNextStep('step8');
		}
		else
		{
			$uploadDir = rtrim(COption::GetOptionString('main', 'upload_dir', 'upload'), '/\\') . '/*';
			$wizard->SetDefaultVar('exclude_mask', $uploadDir . ';*/.hg/*;*/.git/*');
			$this->SetNextStep('step7');
		}
	}

	public function ShowStep()
	{
		global $APPLICATION;
		$wizard = $this->GetWizard();
		$path = $wizard->package->path;

		parent::ShowStep();
		if ($wizard->GetVar('skip_links') !== null)
		{
			COption::SetOptionString('perfmon', 'utf_wizard_exclude_mask', $wizard->GetVar('exclude_mask'));

			$this->content .= '<div id="output">' . GetMessage('UTFWIZ_INIT') . '<br /></div>';

			CJSCore::Init(['ajax']);
			\Bitrix\Main\UI\Extension::load('main.core');
			$APPLICATION->AddHeadScript($path . '/js/wizard.js');

			$message = [
				'UTFWIZ_FIX_AND_RETRY' => GetMessage('UTFWIZ_FIX_AND_RETRY'),
				'UTFWIZ_RETRYSTEP_BUTTONTITLE' => GetMessage('UTFWIZ_RETRYSTEP_BUTTONTITLE'),
			];
			$init = [
				'nextButtonID' => $wizard->GetNextButtonID(),
				'formID' => $wizard->GetFormName(),
				'LANG' => LANGUAGE_ID,
				'path' => $path,
				'sessid' => bitrix_sessid(),
				'sourceEncoding' => $wizard->GetVar('source_encoding') ?: $wizard->GetVar('source_encoding_other'),
				'skipLinks' => $wizard->GetVar('skip_links'),
			];
			$this->content .= '
				<script>
					BX.message(' . \Bitrix\Main\Web\Json::encode($message) . ');
					BX.Wizard.Utf8.init(' . \Bitrix\Main\Web\Json::encode($init) . ');
					BX.ready(() => {BX.Wizard.Utf8.DisableButton()});
					BX.ready(() => {BX.Wizard.Utf8.action(\'files\')});
				</script>
			';
		}
		else
		{
			$this->content .= $this->ShowCheckboxField('skip_links', 'Y', [
				'id' => 'skip_links'
			]) . '<label for="skip_links">' . GetMessage('UTFWIZ_SKIP_LINKS') . '</label><br />';
			$this->content .= '<br /><label for="exclude_mask">' . GetMessage('UTFWIZ_EXCLUDE_MASK') . '</label>:<br />' . $this->ShowInputField('text', 'exclude_mask', [
				'id' => 'exclude_mask',
				'style' => 'width:100%',
			]);
		}
	}

	public function OnPostForm()
	{
		$wizard = $this->GetWizard();

		if ($wizard->IsPrevButtonClick())
		{
			$wizard->UnSetVar('skip_links');
		}
	}
}

class CUtf8CacheResetStep extends CBaseUtf8WizardStep
{
	public function InitStep()
	{
		$this->SetTitle(GetMessage('UTFWIZ_STEP8_TITLE'));
		$this->SetStepID('step8');
		$this->SetNextStep('final');
	}

	public function ShowStep()
	{
		global $APPLICATION;
		$wizard = $this->GetWizard();
		$path = $wizard->package->path;

		parent::ShowStep();
		$this->content .= '<div id="output">' . GetMessage('UTFWIZ_INIT') . '<br /></div>';

		CJSCore::Init(['ajax']);
		\Bitrix\Main\UI\Extension::load('main.core');
		$APPLICATION->AddHeadScript($path . '/js/wizard.js');

		$init = [
			'nextButtonID' => $wizard->GetNextButtonID(),
			'formID' => $wizard->GetFormName(),
			'LANG' => LANGUAGE_ID,
			'path' => $path,
			'sessid' => bitrix_sessid(),
		];
		$this->content .= '
			<script>
				BX.Wizard.Utf8.init(' . \Bitrix\Main\Web\Json::encode($init) . ');
				BX.ready(() => {BX.Wizard.Utf8.DisableButton()});
				BX.ready(() => {BX.Wizard.Utf8.action(\'cache\')});
			</script>
		';
	}
}

class CUtf8FinalStep extends CBaseUtf8WizardStep
{
	public function InitStep()
	{
		$this->SetTitle(GetMessage('UTFWIZ_FINALSTEP_TITLE'));
		$this->SetStepID('final');
		$this->SetCancelStep('final');
		$this->SetCancelCaption(GetMessage('UTFWIZ_FINALSTEP_BUTTONTITLE'));
	}

	public function ShowStep()
	{
		$this->OpenSite();
		parent::ShowStep();
		$this->content = GetMessage('UTFWIZ_FINALSTEP_CONTENT');
	}
}

class CUtf8CancelStep extends CBaseUtf8WizardStep
{
	public function InitStep()
	{
		$this->SetTitle(GetMessage('UTFWIZ_CANCELSTEP_TITLE'));
		$this->SetStepID('cancel');
		$this->SetCancelStep('cancel');
		$this->SetCancelCaption(GetMessage('UTFWIZ_CANCELSTEP_BUTTONTITLE'));
	}

	public function ShowStep()
	{
		$this->OpenSite();
		parent::ShowStep();
		$this->content = GetMessage('UTFWIZ_CANCELSTEP_CONTENT');
	}
}
