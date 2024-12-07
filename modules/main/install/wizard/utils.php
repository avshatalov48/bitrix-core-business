<?php

function InstallGetMessage($name, $aReplace=null)
{
	return GetMessage($name, $aReplace);
}

class BXInstallServices
{
	public static function CreateWizardIndex($wizardName, &$errorMessage)
	{
		$additionalInstallDefine = "";
		if (defined("ADDITIONAL_INSTALL"))
		{
			$additionalInstallDefine = (ADDITIONAL_INSTALL) ? 'define("ADDITIONAL_INSTALL", true);' : 'define("ADDITIONAL_INSTALL", false);';
		}

		$personTypeDefine = "";
		if (defined("NEED_PERSON_TYPE"))
		{
			$additionalInstallDefine = (NEED_PERSON_TYPE) ? 'define("NEED_PERSON_TYPE", true);' : 'define("NEED_PERSON_TYPE", false);';
		}

		$indexContent = '<'.'?'.
			'define("WIZARD_DEFAULT_SITE_ID", "'.(defined("WIZARD_DEFAULT_SITE_ID") ? WIZARD_DEFAULT_SITE_ID : "s1").'");'.
			$additionalInstallDefine.
			$personTypeDefine.
			'require('.'$'.'_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");'.
			'require_once('.'$'.'_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");'.
			'$'.'wizard = new CWizard("'.$wizardName.'");'.
			'$'.'wizard->Install();'.
			'require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");'.
			'?'.'>';

		$p = $_SERVER["DOCUMENT_ROOT"]."/index.php";
		if (defined("WIZARD_DEFAULT_SITE_ID"))
		{
			$rsSite = CSite::GetList("sort", "asc", array("ID" => WIZARD_DEFAULT_SITE_ID));
			$arSite = $rsSite->GetNext();
			$p = CSite::GetSiteDocRoot($arSite["LID"]).$arSite["DIR"]."/index.php";
		}

		$handler = @fopen($p,"wb");

		if (!$handler)
		{
			$errorMessage = InstallGetMessage("INST_WIZARD_INDEX_ACCESS_ERROR");
			return false;
		}

		$success = @fwrite($handler, $indexContent);
		if (!$success)
		{
			$errorMessage = InstallGetMessage("INST_WIZARD_INDEX_ACCESS_ERROR");
			return false;
		}

		if (defined("BX_FILE_PERMISSIONS"))
			@chmod($_SERVER["DOCUMENT_ROOT"]."/index.php", BX_FILE_PERMISSIONS);

		fclose($handler);

		return true;
	}

	public static function LoadWizardData($wizard)
	{
		$arTmp = explode(":", $wizard);
		$ar = array();
		foreach ($arTmp as $a)
		{
			$a = preg_replace("#[^a-z0-9_.-]+#i", "", $a);
			if ($a <> '')
				$ar[] = $a;
		}

		if (count($ar) > 2)
			$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$ar[0]."/install/wizards/".$ar[1]."/".$ar[2];
		elseif (count($ar) == 2)
			$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/".$ar[0]."/".$ar[1];
		else
			return false;

		if (!file_exists($path."/.description.php") || !is_file($path."/.description.php"))
			return false;

		if (!defined("B_PROLOG_INCLUDED"))
			define("B_PROLOG_INCLUDED", true);

		global $MESS;
		if(file_exists($path."/lang/en/.description.php"))
			include($path."/lang/en/.description.php");
		if (file_exists($path."/lang/".LANGUAGE_ID."/.description.php"))
			include($path."/lang/".LANGUAGE_ID."/.description.php");

		$arWizardDescription = array();
		include($path."/.description.php");

		if (empty($arWizardDescription))
			return false;

		if (!array_key_exists("WIZARD_TYPE", $arWizardDescription))
			return false;
		if (defined("WIZARD_DEFAULT_TONLY") && WIZARD_DEFAULT_TONLY === true && !defined("WIZARD_DEFAULT_DONLY") && strtoupper($arWizardDescription["WIZARD_TYPE"]) != "INSTALL")
			return false;
		if (defined("WIZARD_DEFAULT_TONLY") && WIZARD_DEFAULT_TONLY === true && defined("WIZARD_DEFAULT_DONLY") && strtoupper($arWizardDescription["WIZARD_TYPE"]) != "INSTALL" && strtoupper($arWizardDescription["WIZARD_TYPE"]) != "INSTALL_ONCE")
			return false;
		if ((!defined("WIZARD_DEFAULT_TONLY") || WIZARD_DEFAULT_TONLY !== true) && strtoupper($arWizardDescription["WIZARD_TYPE"]) != "INSTALL" && strtoupper($arWizardDescription["WIZARD_TYPE"]) != "INSTALL_ONCE")
			return false;
		if ($arWizardDescription["IMAGE"] <> '')
		{
			if (count($ar) > 2)
			{
				BXInstallServices::CopyDirFiles(
					$path."/".$arWizardDescription["IMAGE"],
					$_SERVER["DOCUMENT_ROOT"]."/bitrix/tmp/".$ar[1]."/".$ar[2]."/".$arWizardDescription["IMAGE"],
					true
				);
				$arWizardDescription["IMAGE"] = "/bitrix/tmp/".$ar[1]."/".$ar[2]."/".$arWizardDescription["IMAGE"];
			}
			else
			{
				$arWizardDescription["IMAGE"] = "/bitrix/wizards/".$ar[0]."/".$ar[1]."/".$arWizardDescription["IMAGE"];
			}
		}

		return array(
			"ID" => implode(":", $ar),
			"NAME" => $arWizardDescription["NAME"],
			"DESCRIPTION" => $arWizardDescription["DESCRIPTION"],
			"IMAGE" => $arWizardDescription["IMAGE"],
			"VERSION" => $arWizardDescription["VERSION"],
		);
	}

	public static function GetWizardsList($moduleName = "")
	{
		$arWizardsList = array();
		if ($moduleName == '')
		{
			$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards";
			if ($h1 = opendir($path))
			{
				while (($f1 = readdir($h1)) !== false)
				{
					if ($f1 == "." || $f1 == "..")
						continue;

					if (!is_dir($path."/".$f1))
						continue;

					if ($h2 = opendir($path."/".$f1))
					{
						while (($f2 = readdir($h2)) !== false)
						{
							if ($f2 == "." || $f2 == "..")
								continue;

							if (!is_dir($path."/".$f1."/".$f2))
								continue;

							if (!file_exists($path."/".$f1."/".$f2."/.description.php"))
								continue;

							if ($wizardData = BXInstallServices::LoadWizardData($f1.":".$f2))
							{
								$arWizardsList[$f1.":".$f2] = $wizardData;
							}
						}
						closedir($h2);
					}
				}
				closedir($h1);
			}
		}

		$path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules";
		if ($h1 = opendir($path))
		{
			while (($f1 = readdir($h1)) !== false)
			{
				if ($f1 == "." || $f1 == "..")
					continue;

				if ($moduleName <> '' && $f1 != $moduleName)
					continue;

				if (!is_dir($path."/".$f1) || !file_exists($path."/".$f1."/install/wizards") || !is_dir($path."/".$f1."/install/wizards"))
					continue;

				if ($h2 = opendir($path."/".$f1."/install/wizards"))
				{
					while (($f2 = readdir($h2)) !== false)
					{
						if ($f2 == "." || $f2 == "..")
							continue;

						if (!is_dir($path."/".$f1."/install/wizards/".$f2))
							continue;

						if ($h3 = opendir($path."/".$f1."/install/wizards/".$f2))
						{
							while (($f3 = readdir($h3)) !== false)
							{
								if ($f3 == "." || $f3 == "..")
									continue;

								if (!is_dir($path."/".$f1."/install/wizards/".$f2."/".$f3))
									continue;

								if (array_key_exists($f2.":".$f3, $arWizardsList))
									continue;

								if (!file_exists($path."/".$f1."/install/wizards/".$f2."/".$f3."/.description.php"))
									continue;

								if ($wizardData = BXInstallServices::LoadWizardData($f1.":".$f2.":".$f3))
									$arWizardsList[$f2.":".$f3] = $wizardData;
							}
							closedir($h3);
						}
					}
					closedir($h2);
				}
			}
			closedir($h1);
		}

		if(LANGUAGE_ID != 'ru')
			unset($arWizardsList['bitrix:demo']);

		ksort($arWizardsList);

		return array_values($arWizardsList);
	}

	public static function CopyDirFiles($path_from, $path_to, $rewrite = true)
	{
		if (str_starts_with($path_to . "/", $path_from . "/"))
			return false;

		if (is_dir($path_from))
		{
			BXInstallServices::CheckDirPath($path_to."/");
		}
		elseif (is_file($path_from))
		{
			$p = strrpos($path_to, "/");
			$path_to_dir = substr($path_to, 0, $p);
			BXInstallServices::CheckDirPath($path_to_dir."/");

			if (file_exists($path_to) && !$rewrite)
				return false;

			@copy($path_from, $path_to);
			if(is_file($path_to) && defined("BX_FILE_PERMISSIONS"))
				@chmod($path_to, BX_FILE_PERMISSIONS);

			return true;
		}
		else
		{
			return true;
		}

		if ($handle = @opendir($path_from))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
					continue;

				if (is_dir($path_from."/".$file))
				{
					BXInstallServices::CopyDirFiles($path_from."/".$file, $path_to."/".$file, $rewrite);
				}
				elseif (is_file($path_from."/".$file))
				{
					if (file_exists($path_to."/".$file) && !$rewrite)
						continue;

					@copy($path_from."/".$file, $path_to."/".$file);
					if(defined("BX_FILE_PERMISSIONS"))
					{
						@chmod($path_to."/".$file, BX_FILE_PERMISSIONS);
					}
				}
			}
			@closedir($handle);
		}
	}

	public static function GetDBTypes()
	{
		global $arWizardConfig;
		$dbTypes = [];


		if (isset($arWizardConfig['pgsql']) && $arWizardConfig['pgsql'] === 'yes')
		{
			$dbTypes['pgsql'] = function_exists('pg_pconnect');
		}
		else
		{
			$dbTypes['mysql'] = function_exists('mysqli_connect');
		}

		return $dbTypes;
	}

	public static function CheckDirPath($path, $dirPermissions = 0755)
	{
		$badDirs = Array();
		$path = str_replace("\\", "/", $path);
		$path = str_replace("//", "/", $path);

		if ($path[strlen($path) - 1] != "/")
		{
			$p = strrpos($path, "/");
			$path = substr($path, 0, $p);
		}

		while (strlen($path) > 1 && $path[strlen($path) - 1]=="/")
			$path = substr($path, 0, strlen($path) - 1);

		$p = strrpos($path, "/");
		while ($p > 0)
		{
			if (file_exists($path) && is_dir($path))
			{
				if (!is_writable($path))
					@chmod($path, $dirPermissions);
				break;
			}
			$badDirs[] = substr($path, $p + 1);
			$path = substr($path, 0, $p);
			$p = strrpos($path, "/");
		}

		for ($i = count($badDirs)-1; $i>=0; $i--)
		{
			$path = $path."/".$badDirs[$i];
			$success = @mkdir($path, $dirPermissions);
			if (!$success)
				return false;
		}

		return true;
	}

	public static function DeleteDirRec($path)
	{
		$path = str_replace("\\", "/", $path);
		if (!file_exists($path)) return;
		if (!is_dir($path))
		{
			@unlink($path);
			return;
		}
		if ($handle = opendir($path))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..") continue;
				if (is_dir($path."/".$file))
				BXInstallServices::DeleteDirRec($path."/".$file);
				else
					@unlink($path."/".$file);
			}
		}
		closedir($handle);
		@rmdir($path);
	}

	public static function VersionCompare($strCurver, $strMinver, $strMaxver = "0.0.0")
	{
		$curver = explode(".", $strCurver);for ($i = 0; $i < 3; $i++) $curver[$i] = (isset($curver[$i]) ? intval($curver[$i]) : 0);
		$minver = explode(".", $strMinver);  for ($i = 0; $i < 3; $i++) $minver[$i] = (isset($minver[$i]) ? intval($minver[$i]) : 0);
		$maxver = explode(".", $strMaxver);  for ($i = 0; $i < 3; $i++) $maxver[$i] = (isset($maxver[$i]) ? intval($maxver[$i]) : 0);

		if (($minver[0]>0 || $minver[1]>0 || $minver[2]>0)
			&&
			($curver[0]<$minver[0]
				|| (($curver[0]==$minver[0]) && ($curver[1]<$minver[1]))
				|| (($curver[0]==$minver[0]) && ($curver[1]==$minver[1]) && ($curver[2]<$minver[2]))
			))
			return false;
		elseif (($maxver[0]>0 || $maxver[1]>0 || $maxver[2]>0)
			&&
			($curver[0]>$maxver[0]
				|| (($curver[0]==$maxver[0]) && ($curver[1]>$maxver[1]))
				|| (($curver[0]==$maxver[0]) && ($curver[1]==$maxver[1]) && ($curver[2]>=$maxver[2]))
			))
			return false;
		else
			return true;
	}

	public static function Add2Log($sText, $sErrorCode = "")
	{
		$MAX_LOG_SIZE = 1000000;
		$READ_PSIZE = 8000;
		$LOG_FILE = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/install.log";
		$LOG_FILE_TMP = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/install_tmp.log";

		if ($sText == '' && $sErrorCode == '')
			return;

		$old_abort_status = ignore_user_abort(true);

		if (file_exists($LOG_FILE))
		{
			$log_size = @filesize($LOG_FILE);
			$log_size = intval($log_size);

			if ($log_size > $MAX_LOG_SIZE)
			{
				if (!($fp = @fopen($LOG_FILE, "rb")))
				{
					ignore_user_abort($old_abort_status);
					return false;
				}

				if (!($fp1 = @fopen($LOG_FILE_TMP, "wb")))
				{
					ignore_user_abort($old_abort_status);
					return false;
				}

				$iSeekLen = intval($log_size-$MAX_LOG_SIZE/2.0);
				fseek($fp, $iSeekLen);

				do
				{
					$data = fread($fp, $READ_PSIZE);
					if ($data == '')
						break;

					@fwrite($fp1, $data);
				}
				while(true);

				@fclose($fp);
				@fclose($fp1);

				@copy($LOG_FILE_TMP, $LOG_FILE);
				@unlink($LOG_FILE_TMP);
			}
			clearstatcache();
		}

		if ($fp = @fopen($LOG_FILE, "ab+"))
		{
			if (flock($fp, LOCK_EX))
			{
				@fwrite($fp, date("Y-m-d H:i:s")." - ".$sErrorCode." - ".$sText."\n");
				@fflush($fp);
				@flock($fp, LOCK_UN);
				@fclose($fp);
			}
		}
		ignore_user_abort($old_abort_status);
	}

	public static function ParseForSql($sqlString)
	{
	}

	public static function GetConfigWizard()
	{
		if (isset($GLOBALS["arWizardConfig"]) && array_key_exists("demoWizardName", $GLOBALS["arWizardConfig"]) && CWizardUtil::CheckName($GLOBALS["arWizardConfig"]["demoWizardName"]))
			return $GLOBALS["arWizardConfig"]["demoWizardName"];

		return false;
	}

	public static function GetDemoWizard()
	{
		if (!defined("B_PROLOG_INCLUDED"))
			define("B_PROLOG_INCLUDED",true);

		if(($demo = self::GetConfigWizard()) !== false)
			return $demo;

		$arWizards = CWizardUtil::GetWizardList();

		$defaultWizard = false;
		foreach ($arWizards as $arWizard)
		{
			$wizardID = $arWizard["ID"];

			if ($wizardID == "bitrix:demo")
			{
				$defaultWizard = "bitrix:demo";
				continue;
			}

			$position = strpos($wizardID, ":");
			if ($position !== false)
				$wizardName = substr($wizardID, $position + 1);
			else
				$wizardName = $wizardID;

			if ($wizardName == "demo")
				return $wizardID;
		}

		return $defaultWizard;
	}

	public static function GetWizardCharset($wizardName)
	{
		if (!defined("B_PROLOG_INCLUDED"))
			define("B_PROLOG_INCLUDED",true);

		$wizardPath = CWizardUtil::GetRepositoryPath().CWizardUtil::MakeWizardPath($wizardName);
		if (!file_exists($_SERVER["DOCUMENT_ROOT"].$wizardPath."/.description.php"))
			return false;

		$arWizardDescription = Array();
		include($_SERVER["DOCUMENT_ROOT"].$wizardPath."/.description.php");

		if (array_key_exists("CHARSET", $arWizardDescription) && $arWizardDescription["CHARSET"] <> '')
			return $arWizardDescription["CHARSET"];

		return false;
	}

	public static function IsShortInstall()
	{
		$dbconnPath = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbconn.php";

		if (!file_exists($dbconnPath))
			return false;

		@include($dbconnPath);

		return defined("SHORT_INSTALL");
	}

	//UTF Functions
	public static function IsUTF8Support()
	{
		return (
			extension_loaded("mbstring")
			&& strtoupper(ini_get("default_charset")) == "UTF-8"
		);
	}

	public static function SetStatus($status)
	{
		$bCgi = (stristr(php_sapi_name(), "cgi") !== false);
		$bFastCgi = ($bCgi && (array_key_exists('FCGI_ROLE', $_SERVER) || array_key_exists('FCGI_ROLE', $_ENV)));
		if($bCgi && !$bFastCgi)
			header("Status: ".$status);
		else
			header($_SERVER["SERVER_PROTOCOL"]." ".$status);
	}

	public static function LocalRedirect($url)
	{
		global $HTTP_HOST, $SERVER_PORT;

		$url = str_replace("&amp;","&",$url);
		$url = str_replace ("\r", "", $url);
		$url = str_replace ("\n", "", $url);

		BXInstallServices::SetStatus("302 Found");

		if (
			strtolower(substr($url, 0, 7)) == "http://" ||
			strtolower(substr($url, 0, 8)) == "https://" ||
			strtolower(substr($url, 0, 6)) == "ftp://")
		{
			header("Request-URI: $url");
			header("Content-Location: $url");
			header("Location: $url");
		}
		else
		{
			if ($SERVER_PORT!="80" && $SERVER_PORT != 443 && $SERVER_PORT>0 && strpos($HTTP_HOST, ":".$SERVER_PORT) <= 0)
				$HTTP_HOST .= ":".$SERVER_PORT;

			$protocol = ($_SERVER["SERVER_PORT"]==443 || strtolower($_SERVER["HTTPS"]) == "on" ? "https" : "http");

			header("Request-URI: $protocol://$HTTP_HOST$url");
			header("Content-Location: $protocol://$HTTP_HOST$url");
			header("Location: $protocol://$HTTP_HOST$url");
		}
		exit;
	}


	public static function SetSession()
	{
		if (!function_exists("session_start"))
			return false;

		session_start();
		$_SESSION["session_check"] = "Y";

		return true;
	}

	public static function CheckSession()
	{
		if (!function_exists("session_start"))
			return false;

		session_start();

		return ( isset($_SESSION["session_check"]) && $_SESSION["session_check"] == "Y" );
	}

	public static function GetWizardsSettings()
	{
		$arWizardConfig = [];
		$configFile = $_SERVER["DOCUMENT_ROOT"]."/install.config";

		require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/xml.php');

		$xml = new CDataXML();
		if (!$xml->Load($configFile))
		{
			return $arWizardConfig;
		}

		$tree = $xml->GetTree()->toSimpleArray();

		if (isset($tree['config']))
		{
			$arWizardConfig = $tree['config'];

			if (isset($arWizardConfig['options']) && is_array($arWizardConfig['options']))
			{
				// normalize options array
				foreach ($arWizardConfig['options'] as $module => $options)
				{
					if (isset($options['option']) && !isset($options['option'][0]))
					{
						$arWizardConfig['options'][$module]['option'] = [$options['option']];
					}
				}
			}
		}

		return $arWizardConfig;
	}

	public static function GetRegistrationKey($lic_key_user_name, $lic_key_user_surname, $lic_key_email, $DBType)
	{
		$lic_site = $_SERVER["HTTP_HOST"];
		if($lic_site == '')
			$lic_site = "localhost";

		$arClientModules = Array();
		$handle = @opendir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules");
		if ($handle)
		{
			while (false !== ($dir = readdir($handle)))
			{
				if (is_dir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$dir)
					&& $dir!="." && $dir!="..")
				{
					$module_dir = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$dir;
					if (file_exists($module_dir."/install/index.php"))
					{
						$arClientModules[] = $dir;
					}
				}
			}
			closedir($handle);
		}

		$lic_edition = serialize($arClientModules);

		if(LANGUAGE_ID == "ru")
			$host = "www.1c-bitrix.ru";
		else
			$host = "www.bitrixsoft.com";

		$maxUsers = 0;
		$path = "/bsm_register_key.php";
		$query = "sur_name=$lic_key_user_surname&first_name=$lic_key_user_name&email=$lic_key_email&site=$lic_site&modules=".urlencode($lic_edition)."&db=$DBType&lang=".LANGUAGE_ID."&bx=Y&max_users=".$maxUsers;

		if(defined("install_license_type"))
			$query .= "&cp_type=".install_license_type;
		if(defined("install_edition"))
			$query .= "&edition=".install_edition;

		$page_content = "";
		$fp = @fsockopen("ssl://" . $host, 443, $errnum, $errstr, 30);
		if ($fp)
		{
			fputs($fp, "POST {$path} HTTP/1.1\r\n");
			fputs($fp, "Host: {$host}\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded; charset=\"UTF-8\"\r\n");
			fputs($fp, "User-Agent: bitrixKeyReq\r\n");
			fputs($fp, "Content-length: " . strlen($query) . "\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $query."\r\n\r\n");
			$headersEnded = 0;
			while(!feof($fp))
			{
				$returned_data = fgets($fp, 128);
				if($returned_data=="\r\n")
				{
					$headersEnded = 1;
				}

				if($headersEnded==1)
				{
					$page_content .= htmlspecialcharsbx($returned_data);
				}
			}
			fclose($fp);
		}
		$arContent = explode("\n", $page_content);

		$bOk = false;
		$key = "";
		foreach($arContent as $v)
		{
			if($v == "OK")
				$bOk = true;

			if(strlen($v) > 10)
				$key = trim($v);
		}
		if($bOk && $key <> '')
			return $key;

		return false;
	}

	public static function CreateLicenseFile($licenseKey)
	{
		if ($licenseKey == '')
			$licenseKey = "DEMO";

		$filePath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/license_key.php";

		if (!$fp = @fopen($filePath, "wb"))
			return false;

		$fileContent = "<"."? \$"."LICENSE_KEY = \"".addslashes($licenseKey)."\"; ?".">";

		if (!fwrite($fp, $fileContent))
			return false;

		@fclose($fp);

		return true;
	}
}
