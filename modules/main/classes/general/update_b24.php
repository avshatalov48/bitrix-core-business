<?php

class CB24Updater
{
	private $arFileVersions = null;
	private $fileGenerateDate = 0;

	private $serverUniqID = "";
	private $isProcessingMain = false;

	const versionsFileNameConst = "/bitrix/modules/versions.php";
	private $versionsFileName = "";

	const versionsDatabaseFolderConst = "/bitrix/updates/bitrix24_clients/";
	private $versionsDatabaseFolder = "";

	private $updatersDir = "";

	public function __construct($isProcessingMain = false)
	{
		$this->serverUniqID = self::GetServerUniqID();
		$this->isProcessingMain = $isProcessingMain;

		$this->versionsFileName = $_SERVER["DOCUMENT_ROOT"].self::versionsFileNameConst;
		$this->versionsDatabaseFolder = $_SERVER["DOCUMENT_ROOT"].self::versionsDatabaseFolderConst;
		$this->updatersDir = $_SERVER["DOCUMENT_ROOT"].US_SAVE_UPDATERS_DIR."/";
	}

	private static function GetOption($name, $def = "")
	{
		$cache = \Bitrix\Main\Application::getInstance()->getManagedCache();

		$cacheFlags = \Bitrix\Main\Config\Configuration::getValue("cache_flags");
		if (isset($cacheFlags["config_options"]))
			$cacheTtl = $cacheFlags["config_options"];
		else
			$cacheTtl = 0;

		if ($cache->read($cacheTtl, "b_option:main", "b_option"))
		{
			$options = $cache->get("b_option:main");
			return $options["-"][$name];
		}

		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$query = "
			SELECT VALUE
			FROM b_option
			WHERE MODULE_ID = 'main'
				AND NAME = '{$sqlHelper->forSql($name)}'
		";

		$res = $con->query($query);
		if ($ar = $res->fetch())
		{
			return $ar["VALUE"];
		}

		return $def;
	}

	private static function SetOption($name, $value = "")
	{
		$con = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$updateFields = [
			"VALUE" => $value,
		];

		$insertFields = [
			"MODULE_ID" => "main",
			"NAME" => $name,
			"VALUE" => $value,
		];

		$keyFields = ["MODULE_ID", "NAME"];

		$sql = $sqlHelper->prepareMerge("b_option", $keyFields, $insertFields, $updateFields);

		$con->queryExecute(current($sql));

		$cache = \Bitrix\Main\Application::getInstance()->getManagedCache();
		$cache->clean("b_option:main", "b_option");

//		$path = \Bitrix\Main\Loader::getLocal("modules/main/option_triggers.php");
//		if ($path !== false)
//			include($path);
	}

	private static function GetServerUniqID()
	{
		$uniq = self::GetOption("server_uniq_id", "");
		if ($uniq == '')
		{
			$uniq = md5(uniqid(rand(), true));
			self::SetOption("server_uniq_id", $uniq);
		}
		return $uniq;
	}

	public function Lock()
	{
		global $DB;

		$uniq = $this->serverUniqID;

		if ($DB->type == "MYSQL")
		{
			$dbLock = $DB->Query("SELECT GET_LOCK('".$uniq."_DBUpdater', 0) as L");
			$arLock = $dbLock->Fetch();
			if ($arLock["L"] == "1")
				return true;
			else
				return false;
		}
		elseif ($DB->type == "ORACLE")
		{
			$dbLock = $DB->Query("
				declare
					my_lock_id number;
					my_result number;
					lock_failed exception;
					pragma exception_init(lock_failed, -54);
				begin
					my_lock_id:=dbms_utility.get_hash_value(to_char('".$uniq."_DBUpdater'), 0, 1024);
					my_result:=dbms_lock.request(my_lock_id, dbms_lock.x_mode, 0, true);
					--  Return value:
					--    0 - success
					--    1 - timeout
					--    2 - deadlock
					--    3 - parameter error
					--    4 - already own lock specified by 'id' or 'lockhandle'
					--    5 - illegal lockhandle
					if(my_result<>0 and my_result<>4)then
						raise lock_failed;
					end if;
				end;
			", true);
			return ($dbLock !== false);
		}
		else
		{
			$i = 60;
			$DB->Query("DELETE FROM b_option WHERE MODULE_ID = 'main' AND NAME = '".$uniq."_DBUpdater' AND SITE_ID IS NULL AND DATEDIFF(SECOND, CONVERT(DATETIME, DESCRIPTION), GETDATE()) > ".$i);
			$DB->Query("SET LOCK_TIMEOUT 1");
			$dbLock = $DB->Query("INSERT INTO b_option(MODULE_ID, NAME, SITE_ID, VALUE, DESCRIPTION) VALUES ('main', '".$uniq."_DBUpdater', NULL,  NULL, CONVERT(VARCHAR(128), GETDATE()))", true);
			$DB->Query("SET LOCK_TIMEOUT -1");
			return ($dbLock !== false);
		}
	}

	public function UnLock()
	{
		global $DB;

		$uniq = $this->serverUniqID;

		if ($DB->type == "MYSQL")
		{
			$dbLock = $DB->Query("SELECT RELEASE_LOCK('".$uniq."_DBUpdater') as L");
			$arLock = $dbLock->Fetch();
			if($arLock["L"] == "0")
				return false;
			else
				return true;
		}
		elseif ($DB->type == "ORACLE")
		{
			return true;
		}
		else
		{
			$DB->Query("DELETE FROM b_option WHERE MODULE_ID = 'main' AND NAME = '".$uniq."_DBUpdater' AND SITE_ID IS NULL");
			return true;
		}
	}

	private static function GetDatabaseVersions()
	{
		global $DB;

		$arDBVersions = array();

		$dbResult = $DB->Query("SELECT VALUE FROM b_option WHERE MODULE_ID='main' AND NAME='BITRIX24_VERSIONS' AND SITE_ID IS NULL", true);
		if ($arResult = $dbResult->Fetch())
		{
			$val = $arResult["VALUE"];
			if ($val <> '')
			{
				$arDBVersions = unserialize($val, ['allowed_classes' => false]);
				if (!is_array($arDBVersions))
					$arDBVersions = array();
			}
		}

		return $arDBVersions;
	}

	private static function SetDatabaseVersions($arDBVersions)
	{
		global $DB;

		if(is_array($arDBVersions))
		{
			self::SetOption("BITRIX24_VERSIONS", serialize($arDBVersions));
			$DB->Query("INSERT INTO b_sm_version_history(DATE_INSERT, VERSIONS) VALUES(NOW(), '".$DB->ForSql(serialize($arDBVersions))."')", true);
		}
	}

	private function InitializeFileData()
	{
		$generationDate = 0;
		$arVersions = array();

		if (file_exists($this->versionsFileName))
		{
			include($this->versionsFileName);
			if (!is_array($arVersions))
				$arVersions = array();
		}

		if (empty($arVersions))
		{
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");

			$errorMessage = "";
			$arVersions = CUpdateClient::GetCurrentModules($errorMessage, false);
			$generationDate = time();
			if ($errorMessage == '')
			{
				$f = fopen($this->versionsFileName, "w");
				fwrite($f, "<"."?\n");
				fwrite($f, "\$generationDate = ".$generationDate.";\n");
				fwrite($f, "\$arVersions = array(\n");
				foreach ($arVersions as $moduleId => $version)
					fwrite($f, "\t\"".htmlspecialchars($moduleId)."\" => \"".htmlspecialchars($version)."\",\n");
				fwrite($f, ");\n");
				fwrite($f, "?".">");
				fclose($f);
			}
		}

		$this->fileGenerateDate = $generationDate;
		$this->arFileVersions = $arVersions;
	}

	private function GetFileVersions()
	{
		if (is_null($this->arFileVersions))
			$this->InitializeFileData();

		return $this->arFileVersions;
	}

	private function GetFileGenerateDate()
	{
		if ($this->fileGenerateDate <= 0)
			$this->InitializeFileData();

		return $this->fileGenerateDate;
	}

	private function GetDatabaseGenerationDate()
	{
		return self::GetOption("BITRIX24_GENERATION_DATE_".($this->isProcessingMain ? "M" : "N"), 0);
	}

	public function PreCheckUpdates()
	{
		$generationDate = $this->GetFileGenerateDate();
		$dbGenerationDate = $this->GetDatabaseGenerationDate();

		if ($dbGenerationDate >= $generationDate)
			return false;

		return true;
	}

	private function Collect4PreCheckUpdates($generationDate)
	{
		self::SetOption("BITRIX24_GENERATION_DATE_".($this->isProcessingMain ? "M" : "N"), $generationDate);
	}

	public function CheckUpdates()
	{
		$arDBVersions = self::GetDatabaseVersions();
		if (empty($arDBVersions))
		{
			$this->CollectDatabaseVersions("ALL");
			$arDBVersions = self::GetDatabaseVersions();
		}

		$arFileVersions = $this->GetFileVersions();

		$arResult = array();
		foreach ($arFileVersions as $moduleId => $version)
		{
			if (($this->isProcessingMain && ($moduleId !== "main"))
				|| (!$this->isProcessingMain && ($moduleId === "main")))
				continue;

			if (CUpdateClient::CompareVersions($version, $arDBVersions[$moduleId]) > 0)
				$arResult[$moduleId] = $arDBVersions[$moduleId];
		}

		// Das ist strashnyy kostyl' for new Options
		global $DB;

		if ($this->isProcessingMain && !empty($arResult))
		{
			if(!$DB->TableExists("b_option_site"))
			{
				$DB->Query("
					CREATE TABLE b_option_site
					(
						MODULE_ID VARCHAR(50) not null,
						NAME VARCHAR(50) not null,
						SITE_ID CHAR(2) not null,
						VALUE TEXT,
						PRIMARY KEY(MODULE_ID, NAME, SITE_ID),
						INDEX ix_option_site_module_site(MODULE_ID, SITE_ID)
					)
				", true);
			}
			if(!$DB->Query("SELECT UNIQUE_ID FROM b_module_to_module WHERE 1=0", true))
			{
				$DB->Query("ALTER TABLE b_module_to_module ADD UNIQUE_ID VARCHAR(32) NOT NULL", true);
			}
		}
		// End of strashnyy kostyl'

		return $arResult;
	}

	public function UpdateFromVersion($moduleId, $dbVersion)
	{
		if ($moduleId == '')
		{
			return;
		}

		$errorMessage = "";

		if (file_exists($this->updatersDir.$moduleId) && is_dir($this->updatersDir.$moduleId))
		{
			$arUpdaters = array();

			if ($handle = @opendir($this->updatersDir.$moduleId))
			{
				while (false !== ($dir = readdir($handle)))
				{
					if ($dir == "." || $dir == "..")
					{
						continue;
					}

					if (substr($dir, 0, 7) == "updater")
					{
						if (is_file($this->updatersDir.$moduleId."/".$dir))
						{
							$num = substr($dir, 7, strlen($dir) - 11);
							if (substr($dir, strlen($dir) - 9) == "_post.php")
							{
								$num = substr($dir, 7, strlen($dir) - 16);
							}

							$arUpdaters[] = array("/".$dir, trim($num));
						}
						elseif (file_exists($this->updatersDir.$moduleId."/".$dir."/index.php"))
						{
							$num = substr($dir, 7);
							if (substr($dir, strlen($dir) - 5) == "_post")
							{
								$num = substr($dir, 7, strlen($dir) - 12);
							}

							$arUpdaters[] = array("/".$dir."/index.php", trim($num));
						}
					}
				}
				closedir($handle);
			}

			$ni1 = count($arUpdaters);
			for ($i1 = 0; $i1 < $ni1 - 1; $i1++)
			{
				for ($j1 = $i1 + 1; $j1 < $ni1; $j1++)
				{
					if (CUpdateClient::CompareVersions($arUpdaters[$i1][1], $arUpdaters[$j1][1]) > 0)
					{
						$tmp1 = $arUpdaters[$i1];
						$arUpdaters[$i1] = $arUpdaters[$j1];
						$arUpdaters[$j1] = $tmp1;
					}
				}
			}

			for ($i1 = 0; $i1 < $ni1; $i1++)
			{
				if (CUpdateClient::CompareVersions($arUpdaters[$i1][1], $dbVersion) <= 0)
				{
					continue;
				}

				$errorMessageTmp = "";

				syslog(LOG_INFO, $_SERVER["HTTP_HOST"]."\tstart\t".$moduleId.$arUpdaters[$i1][0]);

				CUpdateClient::RunUpdaterScript($this->updatersDir.$moduleId.$arUpdaters[$i1][0], $errorMessageTmp, "", $moduleId);

				syslog(LOG_INFO, $_SERVER["HTTP_HOST"]."\tend\t".$moduleId.$arUpdaters[$i1][0]."\t".$errorMessageTmp);

				if ($errorMessageTmp <> '')
				{
					$errorMessage .= str_replace("#MODULE#", $moduleId, str_replace("#VER#", $arUpdaters[$i1][1], GetMessage("SUPP_UK_UPDN_ERR"))).": ".$errorMessageTmp.".";
				}

				$this->CollectDatabaseVersions("MODULE", $moduleId, $arUpdaters[$i1][1]);
			}
		}

		if ($errorMessage <> '')
		{
			$message = "Database update error (".$moduleId."-".$dbVersion."): ".$errorMessage;
			CUpdateClient::AddMessage2Log($message, "DUE");
			syslog(LOG_INFO, $_SERVER["HTTP_HOST"] . "\terror\t" . $message);
		}
		else
		{
			$message = "Database updated successfully (".$moduleId."-".$dbVersion.")";
			CUpdateClient::AddMessage2Log($message, "DUS");
			syslog(LOG_INFO, $_SERVER["HTTP_HOST"] . "\tok\t" . $message);
		}
	}

	public function CollectDatabaseVersions($type, $moduleId = null, $version = null)
	{
		$arDBVersions = self::GetDatabaseVersions();
		$arFileVersions = $this->GetFileVersions();
		switch ($type)
		{
		case "MODULE":
			if (isset($moduleId) && isset($version))
			{
				//Only one selected module
				$arDBVersions[$moduleId] = $version;
				self::SetDatabaseVersions($arDBVersions);
			}
			break;

		case "HIT":
			if ($this->isProcessingMain)
			{
				//Main only
				$arDBVersions["main"] = $arFileVersions["main"];
				self::SetDatabaseVersions($arDBVersions);
			}
			else
			{
				//All except main
				$arFileVersions["main"] = $arDBVersions["main"];
				self::SetDatabaseVersions($arFileVersions);
			}
			$this->Collect4PreCheckUpdates($this->GetFileGenerateDate());
			break;

		default:
			//All
			self::SetDatabaseVersions($arFileVersions);
			$this->Collect4PreCheckUpdates($this->GetFileGenerateDate());
			break;
		}
	}
}
