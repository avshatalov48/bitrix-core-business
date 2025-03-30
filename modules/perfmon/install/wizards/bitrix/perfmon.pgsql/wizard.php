<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

abstract class CBasePgWizardStep extends CWizardStep
{
	public function ShowStep()
	{
		$this->content .= '<style>
			li.pgwiz_erli { list-style-image:url(/bitrix/themes/.default/images/lamp/red.gif) }
			li.pgwiz_okli { list-style-image:url(/bitrix/themes/.default/images/lamp/green.gif) }
			p.pgwiz_err { color:red }
			span.pgwiz_ok { color:green }
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
					$this->content .= '<li class="pgwiz_okli">' . $rec['MESSAGE'] . '</li>';
				}
				else
				{
					$this->content .= '<li class="pgwiz_erli">' . $rec['MESSAGE'] . '</li>';
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

	public function getConnections()
	{
		$pgConnections = [];

		$configParams = \Bitrix\Main\Config\Configuration::getValue('connections');
		if (is_array($configParams))
		{
			foreach ($configParams as $connectionName => $connectionParams)
			{
				if (is_a($connectionParams['className'], '\Bitrix\Main\DB\PgsqlConnection', true))
				{
					$pgConnections[$connectionName] = $connectionName;
				}
			}
		}

		return $pgConnections;

	}

	public static function getNextTable()
	{
		$connection = \Bitrix\Main\Application::getConnection();

		$rs = $connection->query('SELECT * FROM b_perf_table ORDER BY ID LIMIT 1');

		return $rs->fetch();
	}

	public static function getTables()
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$result = [];

		$rs = $connection->query('SELECT * FROM b_perf_table ORDER BY ID');
		while ($ar = $rs->fetch())
		{
			$result[$ar['ID']] = $ar;
		}

		return $result;
	}

	public static function MakeTables($next, $etime)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		if (!$next)
		{
			$connection->query('DELETE FROM b_perf_table');
		}

		$tableList = $connection->query("
			SELECT TABLE_NAME
			FROM information_schema.tables
			WHERE table_type = 'BASE TABLE' AND table_schema = '" . $connection->getDatabase() . "'
			" . ($next ? "AND TABLE_NAME > '" . $helper->forSql($next) . "'" : '') . '
			ORDER BY TABLE_NAME ASC
		');
		while ($table = $tableList->fetch())
		{
			$tableName = $table['TABLE_NAME'];

			$keyColumn = false;
			$indexList = $connection->query('SHOW INDEX FROM ' . $helper->quote($tableName));
			$indexes = [];
			while ($index = $indexList->fetch())
			{
				if ($index['Non_unique'] == '0')
				{
					$keyName = $index['Key_name'];
					if (!isset($indexes[$keyName]))
					{
						$indexes[$keyName] = [];
					}
					$indexes[$keyName][$index['Seq_in_index']] = $index['Column_name'];
				}
			}

			foreach ($indexes as $indexName => $indexColumns)
			{
				if (count($indexColumns) == 1)
				{
					$keyColumn = current($indexColumns);
					break;
				}
			}

			$connection->add('b_perf_table', [
				'TABLE_NAME' => $tableName,
				'KEY_COLUMN' => $keyColumn,
			]);

			if ($etime && microtime(1) > $etime)
			{
				return $tableName;
			}
		}

		return '';
	}

	public static function installMainAddons($pgConnection)
	{
		$installAdd = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/install/pgsql/install_add.sql');
		foreach ($pgConnection->parseSqlBatch($installAdd) as $sql)
		{
			if (strpos($sql, 'ALTER TABLE b_group') === false)
			{
				$pgConnection->query($sql);
			}
		}
	}

	public static function deleteModule($pgConnection, $moduleName)
	{
		$module = $pgConnection->getSqlHelper()->forSql($moduleName);

		$pgConnection->queryExecute("DELETE FROM b_module WHERE ID = '" . $module . "'");
		$pgConnection->queryExecute("UPDATE b_agent SET ACTIVE = 'N' WHERE MODULE_ID = '" . $module . "' AND ACTIVE = 'Y'");
	}

	public function isDev()
	{
		return file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/dev');
	}
}

class CPgCheckStep extends CBasePgWizardStep
{
	protected $modules = [];

	public function InitStep()
	{
		$this->SetTitle(GetMessage('PGWIZ_STEP0_TITLE'));

		if ($this->modules)
		{
			$this->SetError(GetMessage('PGWIZ_NOT_SUPPORTED_MODULES_FOUND'));
		}

		$this->SetStepID('step0');
		$this->checkList = $this->MakeCheckList();
		if ($this->CheckListHasNoError($this->checkList))
		{
			$this->SetNextStep('step1');
		}
		else
		{
			$this->SetNextStep('step0');
		}
		$this->SetCancelStep('cancel');
	}

	public function MakeCheckList()
	{
		$arList = [];

		$isUtf = defined('BX_UTF') && constant('BX_UTF') === true;
		$arList[] = [
			'IS_OK' => $isUtf,
			'MESSAGE' => GetMessage('PGWIZ_REQUIRES_UTF8'),
		];

		$connection = \Bitrix\Main\Application::getConnection();
		if (is_a($connection, '\Bitrix\Main\DB\PgsqlConnection'))
		{
			$arList[] = [
				'IS_OK' => false,
				'MESSAGE' => GetMessage('PGWIZ_ALREADY_PGSQL'),
			];
		}

		//Report not supported modules
		$this->modules = [];
		foreach (\Bitrix\Main\ModuleManager::getInstalledModules() as $moduleId => $_)
		{
			if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $moduleId . '/install/mysql'))
			{
				if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $moduleId . '/install/pgsql'))
				{
					$this->modules[] = $moduleId;
				}
			}
			elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $moduleId . '/install/db/mysql'))
			{
				if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $moduleId . '/install/db/pgsql'))
				{
					$this->modules[] = $moduleId;
				}
			}
		}

		$arList[] = [
			'IS_OK' => empty($this->modules) || $this->isDev(),
			'MESSAGE' => GetMessage('PGWIZ_NOT_SUPPORTED_MODULES') . '<br>' . implode('<br>', $this->modules),
		];

		return $arList;
	}

	public function ShowStep()
	{
		parent::ShowStep();
		if (!$this->GetErrors())
		{
			$this->content .= GetMessage('PGWIZ_REQUIRES_KEY');
			$this->ShowCheckList($this->checkList);
			if ($this->modules)
			{
				if ($this->isDev())
				{
					$this->content .= '<br>' . GetMessage('PGWIZ_MODULES_WARNING');
				}
				else
				{
					$this->content .= '<br>' . GetMessage('PGWIZ_UNINSTALL_MODULES');
				}
			}
		}
	}
}

class CPgCreateDatabaseStep extends CBasePgWizardStep
{
	protected $modules = [];

	public function InitStep()
	{
		$this->SetTitle(GetMessage('PGWIZ_STEP1_TITLE'));

		$wizard = $this->GetWizard();
		$wizard->SetDefaultVar('create', 'by_wizard');

		$this->SetStepID('step1');
		$this->SetPrevStep('step0');
		$this->SetNextStep('step2');
		$this->SetCancelStep('cancel');
	}

	public function ShowStep()
	{
		parent::ShowStep();

		$this->content .= $this->ShowRadioField('create', 'by_wizard', [
			'id' => 'create_by_wizard',
		]) . '<label for="create_by_wizard">' . GetMessage('PGWIZ_CREATE_BY_WIZARD') . '</label><br>';
		$this->content .= '
		<table border="0" class="data-table">
		<tr>
			<td nowrap align="right" valign="top" width="40%" >' . GetMessage('PGWIZ_HOST') . '</td>
			<td width="60%" valign="top">' . $this->ShowInputField('text', 'host', [
				'size' => '30',
			]) . '</td>
		</tr>
		<tr>
			<td nowrap align="right" valign="top">' . GetMessage('PGWIZ_ROOT_USER') . '</td>
			<td valign="top">' . $this->ShowInputField('text', 'root_user', [
				'size' => '30',
			]) . '</td>
		</tr>
		<tr>
			<td nowrap align="right" valign="top">' . GetMessage('PGWIZ_ROOT_PASSWORD') . '</td>
			<td valign="top">' . $this->ShowInputField('password', 'root_password', [
				'size' => '30',
			]) . '</td>
		</tr>
		</table>
		';
		$this->content .= '<br>' . $this->ShowRadioField('create', 'by_user', [
			'id' => 'create_by_user',
		]) . '<label for="create_by_user">' . GetMessage('PGWIZ_CREATE_BY_USER') . '</label>';
		$this->content .= '<pre>
CREATE DATABASE &lt;database name&gt; lc_ctype=\'C.UTF-8\' template template0;
\c &lt;database name&gt
CREATE EXTENSION IF NOT EXISTS pgcrypto;
CREATE USER &lt;user name&gt; WITH PASSWORD \'&lt;user password&gt;\';
GRANT ALL PRIVILEGES ON DATABASE &lt;database name&gt TO &lt;user name&gt;;
GRANT CREATE ON SCHEMA PUBLIC TO &lt;user name&gt;;</pre>';
		$pgConnectionList = $this->getConnections();
		if ($pgConnectionList)
		{
			$this->content .= '<br>' . $this->ShowRadioField('create', 'already', [
				'id' => 'create_already',
			]) . '<label for="create_already">' . GetMessage('PGWIZ_CREATE_ALREADY') . '</label> ';
			$this->content .= $this->ShowSelectField('connection', $pgConnectionList);
		}
	}
}

class CPgUserStep extends CBasePgWizardStep
{
	public $checkList;

	public function InitStep()
	{
		$this->SetTitle(GetMessage('PGWIZ_STEP2_TITLE'));
		$this->SetStepID('step2');

		$this->SetPrevStep('step1');
		$this->SetNextStep('step3');
	}

	public function ShowStep()
	{
		$wizard = $this->GetWizard();
		parent::ShowStep();
		if ($wizard->GetVar('create') === 'by_wizard')
		{
			$this->content .= '
			<table border="0" class="data-table">
			<tr>
				<td nowrap align="right" valign="top">' . GetMessage('PGWIZ_USER') . '</td>
				<td valign="top">' . $this->ShowInputField('text', 'user', [
					'size' => '30',
				]) . '</td>
			</tr>
			<tr>
				<td nowrap align="right" valign="top">' . GetMessage('PGWIZ_PASSWORD') . '</td>
				<td valign="top">' . $this->ShowInputField('password', 'password', [
					'size' => '30',
				]) . '</td>
			</tr>
			<tr>
				<td nowrap align="right" valign="top">' . GetMessage('PGWIZ_DATABASE') . '</td>
				<td valign="top">' . $this->ShowInputField('text', 'database', [
					'size' => '30',
				]) . '</td>
			</tr>
			</table>
			';
		}
		elseif ($wizard->GetVar('create') === 'by_user')
		{
			$this->content .= '
			<table border="0" class="data-table">
			<tr>
				<td nowrap align="right" valign="top">' . GetMessage('PGWIZ_HOST') . '</td>
				<td valign="top">' . $this->ShowInputField('text', 'host', [
					'size' => '30',
				]) . '</td>
			</tr>
			<tr>
				<td nowrap align="right" valign="top">' . GetMessage('PGWIZ_USER') . '</td>
				<td valign="top">' . $this->ShowInputField('text', 'user', [
					'size' => '30',
				]) . '</td>
			</tr>
			<tr>
				<td nowrap align="right" valign="top">' . GetMessage('PGWIZ_PASSWORD') . '</td>
				<td valign="top">' . $this->ShowInputField('password', 'password', [
					'size' => '30',
				]) . '</td>
			</tr>
			<tr>
				<td nowrap align="right" valign="top">' . GetMessage('PGWIZ_DATABASE') . '</td>
				<td valign="top">' . $this->ShowInputField('text', 'database', [
					'size' => '30',
				]) . '</td>
			</tr>
			</table>
			';
		}
		else
		{
			$this->content .= GetMessage('PGWIZ_CONNECTION', ['#CONNECTION_NAME#' => $wizard->GetVar('connection')]);
			$this->SetNextStep('step5');
		}
	}

	public function OnPostForm()
	{
		$wizard = $this->GetWizard();
		if ($wizard->IsNextButtonClick())
		{
			if (!function_exists('pg_pconnect'))
			{
				$this->SetError(GetMessage('PGWIZ_ERRROR_EXTENSION'));
			}

			$config = [
				'host' => $wizard->GetVar('host'),
			];
			if ($wizard->GetVar('create') === 'by_wizard')
			{
				$config['login'] = $wizard->GetVar('root_user');
				$config['password'] = $wizard->GetVar('root_password');
				if ($wizard->GetVar('database') !== mb_strtolower($wizard->GetVar('database')))
				{
					$this->SetError(GetMessage('PGWIZ_ERROR_WRONG_DATABASE_NAME'));
				}
			}
			elseif ($wizard->GetVar('create') === 'by_user')
			{
				$config['login'] = $wizard->GetVar('user');
				$config['password'] = $wizard->GetVar('password');
				$config['database'] = $wizard->GetVar('database');
				if ($wizard->GetVar('database') !== mb_strtolower($wizard->GetVar('database')))
				{
					$this->SetError(GetMessage('PGWIZ_ERROR_WRONG_DATABASE_NAME'));
				}
			}
			else
			{
				$configParams = \Bitrix\Main\Config\Configuration::getValue('connections');
				$config = $configParams[$wizard->GetVar('connection')];
				if ($config['database'] !== mb_strtolower($config['database']))
				{
					$this->SetError(GetMessage('PGWIZ_ERROR_WRONG_DATABASE_NAME'));
				}
			}

			if (
				$config['login'] !== mb_strtolower($config['login'])
				|| (string)$wizard->GetVar('user') !== mb_strtolower($wizard->GetVar('user'))
			)
			{
				$this->SetError(GetMessage('PGWIZ_ERROR_WRONG_LOGIN'));
			}

			if ($this->GetErrors())
			{
				return;
			}

			$conn = new \Bitrix\Main\DB\PgsqlConnection($config);
			try
			{
				$conn->connect();
				//Check PostgreSQL version
				$version = $conn->getVersion();
			}
			catch (\Bitrix\Main\DB\ConnectionException $e)
			{
				$this->SetError(GetMessage('PGWIZ_ERRROR_CONNECT') . ' ' . $e->getDatabaseMessage());
				return;
			}

			$pgVersionMin = '11.0.0';
			if (version_compare($version[0], $pgVersionMin, '<'))
			{
				$this->SetError(GetMessage('PGWIZ_ERROR_VERSION', [
					'#CUR#' => $version[0],
					'#REQ#' => $pgVersionMin,
				]));
				return;
			}

			if ($wizard->GetVar('create') === 'by_wizard')
			{
				$dbResult = $conn->query("SELECT datname FROM pg_database WHERE datname = '" . $conn->getSqlHelper()->forSql($wizard->GetVar('database')) . "'");
				if ($dbResult->fetch())
				{
					$this->SetError(str_replace('#DB#', $wizard->GetVar('database'), GetMessage('PGWIZ_ERROR_EXISTS_DB')));
					return;
				}

				//Check default template1 collation
				$dbResult = $conn->query("SELECT DATCTYPE FROM pg_database where datname='template1'");
				$row = $dbResult->fetch();
				if (preg_match('/\.(UTF-8|UTF8)$/i', $row['DATCTYPE']))
				{
					try
					{
						$conn->queryExecute('CREATE DATABASE ' . $conn->getSqlHelper()->quote($wizard->GetVar('database')));
					}
					catch (\Bitrix\Main\DB\SqlQueryException $e)
					{
						$this->SetError(str_replace('#DB#', $wizard->GetVar('database'), GetMessage('PGWIZ_ERROR_CREATE_DB')) . ' ' . (string)$e);
						return;
					}
				}
				else //use slower template0 creation
				{
					try
					{
						$conn->queryExecute('CREATE DATABASE ' . $conn->getSqlHelper()->quote($wizard->GetVar('database')) . " lc_ctype='C.UTF-8' template template0");
					}
					catch (\Bitrix\Main\DB\SqlQueryException $e)
					{
						//Windows ?
						try
						{
							$conn->queryExecute('CREATE DATABASE ' . $conn->getSqlHelper()->quote($wizard->GetVar('database')) . " lc_ctype='us.UTF8' template template0");
						}
						catch (\Bitrix\Main\DB\SqlQueryException $e)
						{
							$this->SetError(str_replace('#DB#', $wizard->GetVar('database'), GetMessage('PGWIZ_ERROR_CREATE_DB')) . ' ' . (string)$e);
							return;
						}
					}
				}

				$config['database'] = $wizard->GetVar('database');
				$conn = new \Bitrix\Main\DB\PgsqlConnection($config);
				try
				{
					$conn->queryExecute('CREATE EXTENSION IF NOT EXISTS pgcrypto');
				}
				catch (\Bitrix\Main\DB\SqlQueryException $e)
				{
					$this->SetError(str_replace('#DB#', $wizard->GetVar('database'), GetMessage('PGWIZ_ERROR_CREATE_DB')) . ' ' . (string)$e);
					return;
				}

				$createUser = 'create user ' . $conn->getSqlHelper()->quote($wizard->GetVar('user')) . " with password '" . $conn->getSqlHelper()->forSql($wizard->GetVar('password')) . "'";
				try
				{
					$conn->queryExecute($createUser);
				}
				catch (\Bitrix\Main\DB\SqlQueryException $e)
				{
					$this->SetError(GetMessage('PGWIZ_ERROR_CREATE_USER') . ' ' . $e->getDatabaseMessage());
					return;
				}

				$grantPrivileges = [
					'grant all privileges on database ' . $conn->getSqlHelper()->quote($wizard->GetVar('database')) . ' to ' . $conn->getSqlHelper()->quote($wizard->GetVar('user')),
					'grant create on schema public to ' . $conn->getSqlHelper()->quote($wizard->GetVar('user')),
				];
				foreach ($grantPrivileges as $grant)
				{
					try
					{
						$conn->queryExecute($grant);
					}
					catch (\Bitrix\Main\DB\SqlQueryException $e)
					{
						$this->SetError(InstallGetMessage('PGWIZ_ERROR_GRANT_USER') . ' ' . $e->getDatabaseMessage());
						return false;
					}
				}
			}
		}
	}
}

class CPgConnectionAddStep extends CBasePgWizardStep
{
	public function InitStep()
	{
		$this->SetTitle(GetMessage('PGWIZ_STEP3_TITLE'));
		$this->SetStepID('step3');

		$this->SetPrevStep('step2');
		$this->SetNextStep('step4');
	}

	public function ShowStep()
	{
		$wizard = $this->GetWizard();
		parent::ShowStep();

		$settingsEditUrl = '/bitrix/admin/fileman_file_edit.php?path=%2Fbitrix%2F.settings.php&full_src=Y&lang=' . LANGUAGE_ID;
		$this->content .= GetMessage('PGWIZ_ADD_CONNECTION', [
			'#EDIT_HREF#' => $settingsEditUrl,
		]);
		$this->content .= '
			<pre>
			\'default_pgsql\' =&gt;
			array (
				\'className\' => \'\\\\Bitrix\\\\Main\\\\DB\\\\PgsqlConnection\',
				\'host\' =&gt; \'' . $wizard->GetVar('host') . '\',
				\'database\' =&gt; \'' . $wizard->GetVar('database') . '\',
				\'login\' =&gt; \'' . $wizard->GetVar('user') . '\',
				\'password\' =&gt; \'' . $wizard->GetVar('password') . '\',
				\'options\' =&gt; 2,
				\'charset\' =&gt; \'utf-8\',
				\'include_after_connected\' => \'\',
			),
			</pre>
		';
	}
}

class CPgConnectionStep extends CBasePgWizardStep
{
	public function InitStep()
	{
		$this->SetTitle(GetMessage('PGWIZ_STEP4_TITLE'));
		$this->SetStepID('step4');

		$this->SetPrevStep('step3');
		$this->SetNextStep('step5');
	}

	public function ShowStep()
	{
		parent::ShowStep();

		$pgConnectionList = $this->getConnections();
		$this->content .= GetMessage('PGWIZ_SELECT_CONNECTION');
		$this->content .= $this->ShowSelectField('connection', $pgConnectionList);
	}

	public function OnPostForm()
	{
		$wizard = $this->GetWizard();
		if ($wizard->IsNextButtonClick())
		{
			$pool = \Bitrix\Main\Application::getInstance()->getConnectionPool();
			/** @var \Bitrix\Main\DB\Connection $conn */
			$conn = $pool->getConnection($wizard->GetVar('connection'));

			try
			{
				$conn->connect();
				//Check PostgreSQL version
				$version = $conn->getVersion();
			}
			catch (\Bitrix\Main\DB\ConnectionException $e)
			{
				$this->SetError(GetMessage('PGWIZ_ERRROR_CONNECT') . ' ' . $e->getDatabaseMessage());
				return;
			}

			$pgVersionMin = '11.0.0';
			if (version_compare($version[0], $pgVersionMin, '<'))
			{
				$this->SetError(GetMessage('PGWIZ_ERROR_VERSION', [
					'#CUR#' => $version[0],
					'#REQ#' => $pgVersionMin,
				]));
				return;
			}
		}
	}
}

class CPgDatabaseCheckStep extends CBasePgWizardStep
{
	public $checkList;

	public function InitStep()
	{
		$this->SetTitle(GetMessage('PGWIZ_STEP5_TITLE'));
		$this->SetStepID('step5');

		$this->SetPrevStep('step4');
		$this->SetNextStep('step6');
	}

	public function ShowStep()
	{
		global $APPLICATION;
		$wizard = $this->GetWizard();
		$path = $wizard->package->path;

		parent::ShowStep();

		CJSCore::Init(['ajax']);
		\Bitrix\Main\UI\Extension::load('main.core');
		$APPLICATION->AddHeadScript($path . '/js/wizard.js');

		$init = [
			'nextButtonID' => $wizard->GetNextButtonID(),
			'formID' => $wizard->GetFormName(),
			'LANG' => LANGUAGE_ID,
			'path' => $path,
			'sessid' => bitrix_sessid(),
			'connection' => $wizard->GetVar('connection'),
		];
		$this->content .= '
			<script>
				BX.Wizard.PgSql.init(' . \Bitrix\Main\Web\Json::encode($init) . ');
			</script>
		';

		$wizard->SetVar('action', '');
		$tables = static::getTables();
		if ($tables)
		{
			$this->content .= GetMessage('PGWIZ_ALREADY_IN_PROGRESS') . '<br>';
			$this->content .= $this->ShowRadioField('action', 'continue', [
				'id' => 'action_continue',
				'onclick' => 'if(this.checked){BX.Wizard.PgSql.EnableButton();}',
			]) . '<label for="action_continue">' . GetMessage('PGWIZ_CONTINUE') . '</label><br />';
			$this->content .= $this->ShowRadioField('action', 'restart', [
				'id' => 'action_restart',
				'onclick' => 'if(this.checked){BX.Wizard.PgSql.EnableButton();}',
			]) . '<label for="action_restart">' . GetMessage('PGWIZ_RESTART') . '</label><br />';
			$this->content .= '
			<script>
				BX.ready(() => {BX.Wizard.PgSql.DisableButton()});
			</script>
		';
		}
		else
		{
			$pool = \Bitrix\Main\Application::getInstance()->getConnectionPool();
			/** @var \Bitrix\Main\DB\Connection $conn */
			$conn = $pool->getConnection($wizard->GetVar('connection'));

			$table = $conn->query("
				SELECT tablename
				FROM pg_tables
				WHERE schemaname = 'public'
			")->fetch();
			if ($table)
			{
				$this->content .= GetMessage('PGWIZ_TABLES_EXISTS') . '<br>';
				$this->content .= $this->ShowCheckboxField('action', 'overwrite', [
					'id' => 'action_overwrite',
					'onclick' => 'if(this.checked){BX.Wizard.PgSql.EnableButton();}else{BX.Wizard.PgSql.DisableButton();}',
				]) . '<label for="action_overwrite">' . GetMessage('PGWIZ_OVERWRITE') . '</label><br />';
				$this->content .= '
				<script>
					BX.ready(() => {BX.Wizard.PgSql.DisableButton()});
				</script>
			';
			}
		}
		$this->content .= '<br>' . GetMessage('PGWIZ_READY_TO_PROCEED');
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

class CPgCopyStep extends CBasePgWizardStep
{
	public function InitStep()
	{
		$this->SetTitle(GetMessage('PGWIZ_STEP6_TITLE'));
		$this->SetStepID('step6');

		$this->SetNextStep('step7');
	}

	public function ShowStep()
	{
		global $APPLICATION;
		$wizard = $this->GetWizard();
		$path = $wizard->package->path;

		parent::ShowStep();

		$this->content .= '<div id="output">' . GetMessage('PGWIZ_INIT') . '<br /></div>';

		CJSCore::Init(['ajax']);
		\Bitrix\Main\UI\Extension::load('main.core');
		$APPLICATION->AddHeadScript($path . '/js/wizard.js');

		$message = [
			'PGWIZ_FIX_AND_RETRY' => GetMessage('PGWIZ_FIX_AND_RETRY'),
			'PGWIZ_RETRYSTEP_BUTTONTITLE' => GetMessage('PGWIZ_RETRYSTEP_BUTTONTITLE'),
		];
		$init = [
			'nextButtonID' => $wizard->GetNextButtonID(),
			'formID' => $wizard->GetFormName(),
			'LANG' => LANGUAGE_ID,
			'path' => $path,
			'sessid' => bitrix_sessid(),
			'connection' => $wizard->GetVar('connection'),
		];
		$this->content .= '
			<script>
				BX.message(' . \Bitrix\Main\Web\Json::encode($message) . ');
				BX.Wizard.PgSql.init(' . \Bitrix\Main\Web\Json::encode($init) . ');
				BX.ready(() => {BX.Wizard.PgSql.DisableButton()});
				BX.ready(() => {BX.Wizard.PgSql.action(\'copy\', ' . \Bitrix\Main\Web\Json::encode($wizard->GetVar('action') !== 'continue' ? 'init' : 'continue') . ')});
			</script>
		';
	}
}

class CPgSetupConnectionStep extends CBasePgWizardStep
{
	public function InitStep()
	{
		$this->SetTitle(GetMessage('PGWIZ_STEP7_TITLE'));
		$this->SetStepID('step7');

		$this->SetNextStep('final');
	}

	public function ShowStep()
	{
		$wizard = $this->GetWizard();

		parent::ShowStep();

		if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/after_connect_d7.php'))
		{
			$settingsEditUrl = '/bitrix/admin/fileman_admin.php?lang=' . LANGUAGE_ID . '&path=%2Fbitrix%2Fphp_interface';
			$this->content .= GetMessage('PGWIZ_REMOVE_AFTER_CONNECT', [
				'#EDIT_HREF#' => $settingsEditUrl
			]) . '</br></br>';
		}

		$settingsEditUrl = '/bitrix/admin/fileman_file_edit.php?path=%2Fbitrix%2F.settings.php&full_src=Y&lang=' . LANGUAGE_ID;
		$this->content .= GetMessage('PGWIZ_RENAME_CONNECTION', [
			'#EDIT_HREF#' => $settingsEditUrl,
			'#CONNECTION#' => $wizard->GetVar('connection'),
		]);
	}

	public function OnPostForm()
	{
		$wizard = $this->GetWizard();
		if ($wizard->IsNextButtonClick())
		{
			$connection = \Bitrix\Main\Application::getConnection();
			if (is_a($connection, '\Bitrix\Main\DB\PgsqlConnection', true))
			{
				$this->OpenSite();
			}
			else
			{
				$this->SetError(GetMessage('PGWIZ_WRONG_CONNECTION'));
			}
		}
	}
}

class CPgFinalStep extends CBasePgWizardStep
{
	public function InitStep()
	{
		$this->SetTitle(GetMessage('PGWIZ_FINALSTEP_TITLE'));
		$this->SetStepID('final');
		$this->SetCancelStep('final');
		$this->SetCancelCaption(GetMessage('PGWIZ_FINALSTEP_BUTTONTITLE'));
	}

	public function ShowStep()
	{
		$this->OpenSite();
		parent::ShowStep();
		$this->content = GetMessage('PGWIZ_FINALSTEP_CONTENT');
	}
}

class CPgCancelStep extends CBasePgWizardStep
{
	public function InitStep()
	{
		$this->SetTitle(GetMessage('PGWIZ_CANCELSTEP_TITLE'));
		$this->SetStepID('cancel');
		$this->SetCancelStep('cancel');
		$this->SetCancelCaption(GetMessage('PGWIZ_CANCELSTEP_BUTTONTITLE'));
	}

	public function ShowStep()
	{
		$this->OpenSite();
		parent::ShowStep();
		$this->content = GetMessage('PGWIZ_CANCELSTEP_CONTENT');
	}
}
