<?
@set_time_limit(10000);
ini_set("track_errors", "1");
ignore_user_abort(true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 **/
$canSign = $USER->CanDoOperation('security_file_verifier_sign');
$canCollect = $USER->CanDoOperation('security_file_verifier_collect');
$canVerify = $USER->CanDoOperation('security_file_verifier_verify');

if(!$canSign && !$canCollect && !$canVerify)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

/*************************************************************************************************/
/*************************************************************************************************/
define("BX_FILE_CHECKER_REGION_KERNEL", 1);
define("BX_FILE_CHECKER_REGION_ROOT", 2);
define("BX_FILE_CHECKER_REGION_PERSONAL_ROOT", 4);
define("BX_FILE_CHECKER_REGION_PUBLIC", 8);

if (!defined("START_EXEC_TIME"))
	define("START_EXEC_TIME", getmicrotime());

class CFileChecker
{
	var $arCollectedExtensions;

	var $startPath;

	var $serverFileErrorLogName = "serverfilerr";
	var $serverErrorLog;
	var $serverErrorLogHandle;

	/** @var  CFileCheckerLog */
	var $fileLog;
	/** @var  CFileCheckerErrorLog */
	var $fileErrorLog;

	protected static $integrityKey = '';

	public static function getIntegrityKey()
	{
		if(!self::$integrityKey)
		{
			$fileString = file_get_contents(__FILE__);
			$fileString = preg_replace("#<"."\\?[\\s]*define\\(\"BX_INTEGRITY_VALUE\",[\\s]*'[^']*?'\\);?[\\s]*\\?".">#i", "", $fileString);
			self::$integrityKey = hash('sha256', $fileString);

		}
		return self::$integrityKey;
	}

	public static function GetList()
	{
		$arFiles = array();

		$vf = new CFileCheckerLog();

		if ($handle = @opendir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules"))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
					continue;

				if (!is_file($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$file))
					continue;

				if (substr($file, 0, strlen("serverfilelog-")) != "serverfilelog-")
					continue;
				if (substr($file, -4) != ".dat")
					continue;
				if (substr($file, -8) === "_tmp.dat")
					continue;

				$ts = substr($file, strlen("serverfilelog-"));
				$ts = substr($ts, 0, -4);
				if (intval($ts) <= 0)
					continue;

				$vf->__GetDescriptionList($ts);

				if ($vf->GetDescriptionTs() > 0)
				{
					$arFiles[] = array(
						"TIMESTAMP_X" => $vf->GetDescriptionTs(),
						"REGION" => $vf->GetDescriptionRegion(),
						"EXTENTIONS" => $vf->GetDescriptionCollectedExtensions(),
						"LOG" => "",
						"FILE" => $file,
					);
				}
			}

			closedir($handle);
		}

		return $arFiles;
	}

	function SetCollectedExtensions($arCollectedExtensions)
	{
		$this->arCollectedExtensions = array();
		foreach ($arCollectedExtensions as $ext)
		{
			$ext = trim($ext);
			if (strlen($ext) > 0)
				$this->arCollectedExtensions[] = strtolower($ext);
		}
	}

	function SetStartPoint($startPath, $region)
	{
		$this->startPath = "";

		if (strlen($startPath) > 0)
		{
			if (intval(substr($startPath, 0, 1)) == $region)
			{
				$startPath = str_replace("\\", "/", substr($startPath, 1));
				$startPath = trim(trim($startPath, "/\\"));
				if (strlen($startPath) > 0)
					$this->startPath = "/".$startPath;
			}
		}
	}

	function GetFileInfo($filename)
	{
		$filename = str_replace("\\", "/", $filename);

		$fileSize = filesize($_SERVER["DOCUMENT_ROOT"].$filename);
		$fileCRC = CFileCheckerUtil::GetFileCRC($filename);

		return array("filename" => $filename, "fileSize" => $fileSize, "fileCRC" => $fileCRC);
	}

	function __WalkThrougtTree($path, $arSkipPaths, $level, &$arTs, $fileFunction)
	{
		$path = str_replace("\\", "/", $path);
		$path = trim(trim($path, "/\\"));
		if (strlen($path) > 0)
			$path = "/".$path;

		$startPathPart = "";
		$le = false;
		if (strlen($this->startPath) > 0)
		{
			if (strlen($path) <= 0
				|| strlen($this->startPath) >= strlen($path) && substr($this->startPath, 0, strlen($path)) == $path)
			{
				if (strlen($path) > 0)
					$startPath = substr($this->startPath, strlen($path) + 1);
				else
					$startPath = $this->startPath;

				$pos = strpos($startPath, "/");
				$le = (($pos === false) ? false : true);
				if ($pos === false)
					$startPathPart = $startPath;
				else
					$startPathPart = substr($startPath, 0, $pos);
			}
		}

		$arFiles = array();

		if ($handle = @opendir($_SERVER["DOCUMENT_ROOT"].$path))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
					continue;

				if (strlen($startPathPart) > 0 && ($le && $startPathPart > $file || !$le && $startPathPart >= $file))
					continue;

				if (count($arSkipPaths) > 0)
				{
					$bSkip = False;
					for ($i = 0, $count = count($arSkipPaths); $i < $count; $i++)
					{
						if (strpos($path."/".$file, $arSkipPaths[$i]) === 0)
						{
							$bSkip = True;
							break;
						}
					}

					if ($bSkip)
						continue;
				}

				$arFiles[] = $file;
			}

			closedir($handle);
		}

		sort($arFiles, SORT_STRING);

		for ($i = 0, $count = count($arFiles); $i < $count; $i++)
		{
			if (is_dir($_SERVER["DOCUMENT_ROOT"].$path."/".$arFiles[$i]))
			{
				$res = $this->__WalkThrougtTree($path."/".$arFiles[$i], $arSkipPaths, $level + 1, $arTs, $fileFunction);
				if (!$res)
					return false;
			}
			else
			{
				if (count($this->arCollectedExtensions) > 0)
				{
					$fileExt = strtolower(GetFileExtension($arFiles[$i]));
					if (!in_array($fileExt, $this->arCollectedExtensions))
						continue;
				}

				call_user_func(array(&$this, $fileFunction), $path."/".$arFiles[$i]);

				$arTs["StatNum"]++;
			}

			if ($arTs["MaxExecutionTime"] > 0 && (getmicrotime() - START_EXEC_TIME > $arTs["MaxExecutionTime"]))
			{
				$arTs["StartPoint"] = $path."/".$arFiles[$i];
				return false;
			}
		}

		$arTs["StartPoint"] = "";
		return true;
	}

	function __CollectFile($file)
	{
		$this->fileLog->Write($this->GetFileInfo($file));
	}

	function __VerifyFile($file)
	{
		$arFileInfo = $this->GetFileInfo($file);
		$s = $this->fileLog->Search($arFileInfo["filename"]);
		if ($s)
		{
			if ($s["fileSize"] != $arFileInfo["fileSize"])
				$this->fileErrorLog->Write("FS*".$arFileInfo["filename"]."*".$arFileInfo["fileSize"]."*".$s["fileSize"]);
			elseif ($s["fileCRC"] != $arFileInfo["fileCRC"])
				$this->fileErrorLog->Write("FC*".$arFileInfo["filename"]."*".$arFileInfo["fileCRC"]."*".$s["fileCRC"]);
		}
		else
		{
			$this->fileErrorLog->Write("FN*".$arFileInfo["filename"]);
		}
	}

	function __VerifyLogFileRest()
	{
		while ($s = $this->fileLog->ReadLine())
			$this->fileErrorLog->Write("FM*".$s);
	}

	function CollectCrc($region, $arCollectedExtensions, $pwdString, &$arTs, &$arErrors)
	{
		if (BX_PERSONAL_ROOT == BX_ROOT)
			$region = (($region | BX_FILE_CHECKER_REGION_PERSONAL_ROOT) ^ BX_FILE_CHECKER_REGION_PERSONAL_ROOT);

		$this->SetCollectedExtensions($arCollectedExtensions);

		$this->fileLog = new CFileCheckerLog();

		if ($arTs["ts"] && intval($arTs["ts"]) > 0)
		{
			$region = (($region | $arTs["Completed"]) ^ $arTs["Completed"]);

			if (!$this->fileLog->CreateStep($arTs["ts"]))
			{
				$arErrors[] = "Can not open log file for appending. ";
				return true;
			}
		}
		else
		{
			$arTs["StartPoint"] = "";

			if (!$this->fileLog->Create($region, $this->arCollectedExtensions))
			{
				$arErrors[] = "Can not create log file. ";
				return true;
			}

			$arTs["ts"] = $this->fileLog->GetTs();
		}
		$arTs["StatNum"] = 0;

		$res = true;

		if ($res && (($region & BX_FILE_CHECKER_REGION_KERNEL) != 0))
		{
			$this->SetStartPoint($arTs["StartPoint"], BX_FILE_CHECKER_REGION_KERNEL);

			$res = $this->__WalkThrougtTree(
				BX_ROOT."/modules",
				array(
					$this->fileLog->GetLogCommonPathPart(),
					"/".$this->serverFileErrorLogName,
				),
				0,
				$arTs,
				"__CollectFile"
			);
			if ($res)
			{
				$arTs["Completed"] = $arTs["Completed"] | BX_FILE_CHECKER_REGION_KERNEL;
				$arTs["StartPoint"] = "";
			}
			else
			{
				$arTs["StartPoint"] = BX_FILE_CHECKER_REGION_KERNEL.$arTs["StartPoint"];
			}
		}

		if ($res && (($region & BX_FILE_CHECKER_REGION_ROOT) != 0))
		{
			$this->SetStartPoint($arTs["StartPoint"], BX_FILE_CHECKER_REGION_ROOT);

			$res = $this->__WalkThrougtTree(
				BX_ROOT,
				array(
					BX_ROOT."/modules",
					BX_PERSONAL_ROOT."/cache",
					BX_PERSONAL_ROOT."/cache_image",
					BX_PERSONAL_ROOT."/managed_cache",
					BX_PERSONAL_ROOT."/stack_cache",
				),
				0,
				$arTs,
				"__CollectFile"
			);
			if ($res)
			{
				$arTs["Completed"] = $arTs["Completed"] | BX_FILE_CHECKER_REGION_ROOT;
				$arTs["StartPoint"] = "";
			}
			else
			{
				$arTs["StartPoint"] = BX_FILE_CHECKER_REGION_ROOT.$arTs["StartPoint"];
			}
		}

		if ($res && (($region & BX_FILE_CHECKER_REGION_PERSONAL_ROOT) != 0))
		{
			$this->SetStartPoint($arTs["StartPoint"], BX_FILE_CHECKER_REGION_PERSONAL_ROOT);

			$res = $this->__WalkThrougtTree(
				BX_PERSONAL_ROOT,
				array(
					BX_ROOT."/modules",
					BX_PERSONAL_ROOT."/cache",
					BX_PERSONAL_ROOT."/cache_image",
					BX_PERSONAL_ROOT."/managed_cache",
					BX_PERSONAL_ROOT."/stack_cache",
				),
				0,
				$arTs,
				"__CollectFile"
			);
			if ($res)
			{
				$arTs["Completed"] = $arTs["Completed"] | BX_FILE_CHECKER_REGION_PERSONAL_ROOT;
				$arTs["StartPoint"] = "";
			}
			else
			{
				$arTs["StartPoint"] = BX_FILE_CHECKER_REGION_PERSONAL_ROOT.$arTs["StartPoint"];
			}
		}

		if ($res && (($region & BX_FILE_CHECKER_REGION_PUBLIC) != 0))
		{
			$this->SetStartPoint($arTs["StartPoint"], BX_FILE_CHECKER_REGION_PUBLIC);

			$res = $this->__WalkThrougtTree(
				"",
				array(
					BX_ROOT,
					"/upload",
				),
				0,
				$arTs,
				"__CollectFile"
			);
			if ($res)
			{
				$arTs["Completed"] = $arTs["Completed"] | BX_FILE_CHECKER_REGION_PUBLIC;
				$arTs["StartPoint"] = "";
			}
			else
			{
				$arTs["StartPoint"] = BX_FILE_CHECKER_REGION_PUBLIC.$arTs["StartPoint"];
			}
		}

		$flag = ((($region | $arTs["Completed"]) ^ $arTs["Completed"]) == 0);

		if ($flag)
			$this->fileLog->CloseCreate($pwdString);
		else
			$this->fileLog->CloseCreateStep();

		return $flag;
	}

	function VerifyCrc($ts, $pwdString, &$arTs, &$arErrors)
	{
		$this->fileLog = new CFileCheckerLog();
		$this->fileErrorLog = new CFileCheckerErrorLog();

		if ($arTs["ts"] && intval($arTs["ts"]) > 0)
		{
			if (!$this->fileLog->OpenStep($ts))
			{
				$arErrors[] = GetMessage("MFC1_CANT_LOAD_DATAFILE").". ";
				return true;
			}

			$region = $this->fileLog->GetDescriptionRegion();
			$region = (($region | $arTs["Completed"]) ^ $arTs["Completed"]);

			if (!$this->fileErrorLog->CreateStep())
			{
				$arErrors[] = GetMessage("MFC1_CANT_OPEN_ERRORFILE").". ";
				return true;
			}
		}
		else
		{
			$arTs["StartPoint"] = "";

			if (!$this->fileLog->Open($ts, $pwdString))
			{
				$arErrors[] = GetMessage("MFC1_CANT_LOAD_DATAFILE");
				return true;
			}

			$region = $this->fileLog->GetDescriptionRegion();

			if (!$this->fileErrorLog->Create())
			{
				$arErrors[] = GetMessage("MFC1_CANT_CREATE_ERRORFILE").". ";
				return true;
			}

			$arTs["ts"] = time();
		}
		$arTs["StatNum"] = 0;

		$this->SetCollectedExtensions($this->fileLog->GetDescriptionCollectedExtensions());

		$res = true;

		if ($res && (($region & BX_FILE_CHECKER_REGION_KERNEL) != 0))
		{
			$this->SetStartPoint($arTs["StartPoint"], BX_FILE_CHECKER_REGION_KERNEL);

			$res = $this->__WalkThrougtTree(
				BX_ROOT."/modules",
				array(
					$this->fileLog->GetLogCommonPathPart(),
					"/".$this->serverFileErrorLogName,
				),
				0,
				$arTs,
				"__VerifyFile"
			);
			if ($res)
			{
				$arTs["Completed"] = $arTs["Completed"] | BX_FILE_CHECKER_REGION_KERNEL;
				$arTs["StartPoint"] = "";
			}
			else
			{
				$arTs["StartPoint"] = BX_FILE_CHECKER_REGION_KERNEL.$arTs["StartPoint"];
			}
		}

		if ($res && (($region & BX_FILE_CHECKER_REGION_ROOT) != 0))
		{
			$this->SetStartPoint($arTs["StartPoint"], BX_FILE_CHECKER_REGION_ROOT);

			$res = $this->__WalkThrougtTree(
				BX_ROOT,
				array(
					BX_ROOT."/modules",
					BX_PERSONAL_ROOT."/cache",
					BX_PERSONAL_ROOT."/cache_image",
					BX_PERSONAL_ROOT."/managed_cache",
					BX_PERSONAL_ROOT."/stack_cache",
				),
				0,
				$arTs,
				"__VerifyFile"
			);
			if ($res)
			{
				$arTs["Completed"] = $arTs["Completed"] | BX_FILE_CHECKER_REGION_ROOT;
				$arTs["StartPoint"] = "";
			}
			else
			{
				$arTs["StartPoint"] = BX_FILE_CHECKER_REGION_ROOT.$arTs["StartPoint"];
			}
		}

		if ($res && (($region & BX_FILE_CHECKER_REGION_PERSONAL_ROOT) != 0))
		{
			$this->SetStartPoint($arTs["StartPoint"], BX_FILE_CHECKER_REGION_PERSONAL_ROOT);

			$res = $this->__WalkThrougtTree(
				BX_PERSONAL_ROOT,
				array(
					BX_ROOT."/modules",
					BX_PERSONAL_ROOT."/cache",
					BX_PERSONAL_ROOT."/cache_image",
					BX_PERSONAL_ROOT."/managed_cache",
					BX_PERSONAL_ROOT."/stack_cache",
				),
				0,
				$arTs,
				"__VerifyFile"
			);
			if ($res)
			{
				$arTs["Completed"] = $arTs["Completed"] | BX_FILE_CHECKER_REGION_PERSONAL_ROOT;
				$arTs["StartPoint"] = "";
			}
			else
			{
				$arTs["StartPoint"] = BX_FILE_CHECKER_REGION_PERSONAL_ROOT.$arTs["StartPoint"];
			}
		}

		if ($res && (($region & BX_FILE_CHECKER_REGION_PUBLIC) != 0))
		{
			$this->SetStartPoint($arTs["StartPoint"], BX_FILE_CHECKER_REGION_PUBLIC);

			$res = $this->__WalkThrougtTree(
				"",
				array(
					BX_ROOT,
					"/upload",
				),
				0,
				$arTs,
				"__VerifyFile"
			);
			if ($res)
			{
				$arTs["Completed"] = $arTs["Completed"] | BX_FILE_CHECKER_REGION_PUBLIC;
				$arTs["StartPoint"] = "";
			}
			else
			{
				$arTs["StartPoint"] = BX_FILE_CHECKER_REGION_PUBLIC.$arTs["StartPoint"];
			}
		}

		if ($res)
		{
			$this->__VerifyLogFileRest();
		}

		if ($res)
			$this->fileLog->CloseOpen();
		else
			$this->fileLog->CloseOpenStep();

		$this->fileErrorLog->CloseCreate();

		return $res;
	}
}

class CFileCheckerLog
{
	var $serverFileLogName = "serverfilelog";
	var $serverFileLogExt = "dat";
	var $serverFileLogPath = "/bitrix/modules";

	var $serverLog;
	var $serverLogTmp;

	var $serverLogHandle;
	var $serverLogTmpHandle;

	var $ts;

	var $logText;

	var $descrRegion;
	var $descrCollectedExtensions;
	var $descrTs;

	function __WriteDescription($region, $arCollectedExtensions)
	{
		$this->descrRegion = $region;
		$this->descrCollectedExtensions = $arCollectedExtensions;
		$this->descrTs = $this->ts;

		fwrite($this->serverLogTmpHandle, $this->ts."|".$region."|".implode(",", $arCollectedExtensions)."\n");
	}

	function __ReadDescription()
	{
		fseek($this->serverLogTmpHandle, 0);

		$line = fgets($this->serverLogTmpHandle, 4096);
		$line = trim($line);
		$arLine = explode("|", $line);
		$this->descrTs = $arLine[0];
		$this->descrRegion = $arLine[1];
		$exts = $arLine[2];
		$this->descrCollectedExtensions = explode(",", $exts);

		fseek($this->serverLogTmpHandle, -1);
	}

	function Create($region, $arCollectedExtensions)
	{
		$this->ts = time();

		$this->__SetLogFileNames();

		if ($this->serverLogTmpHandle = fopen($_SERVER["DOCUMENT_ROOT"].$this->serverLogTmp, "a+"))
		{
			$this->__WriteDescription($region, $arCollectedExtensions);
			return true;
		}

		return false;
	}

	function CreateStep($ts)
	{
		$this->ts = $ts;

		$this->__SetLogFileNames();

		if ($this->serverLogTmpHandle = fopen($_SERVER["DOCUMENT_ROOT"].$this->serverLogTmp, "a+"))
		{
			$this->__ReadDescription();
			return true;
		}

		return false;
	}

	function Write($arFileInfo)
	{
		fwrite($this->serverLogTmpHandle, $arFileInfo["filename"]."*".$arFileInfo["fileSize"]."*".$arFileInfo["fileCRC"]."\n");
	}

	function CloseCreateStep()
	{
		fclose($this->serverLogTmpHandle);
	}

	function __Crypt($pwdString)
	{
		$fileString = file_get_contents($_SERVER["DOCUMENT_ROOT"].str_replace("\\", "/", $this->serverLogTmp));
		$fileString = substr($fileString, strpos($fileString, "\n") + 1);

		$fileStringNew = CFileCheckerUtil::Encrypt($fileString, $pwdString);

		$this->serverLogHandle = fopen($_SERVER["DOCUMENT_ROOT"].$this->serverLog, "wb");
		fwrite($this->serverLogHandle, $this->descrTs."|".$this->descrRegion."|".implode(",", $this->descrCollectedExtensions)."\n");
		fwrite($this->serverLogHandle, $fileStringNew);
		fclose($this->serverLogHandle);
	}

	function CloseCreate($pwdString)
	{
		$this->CloseCreateStep();

		sleep(3);

		$this->__Crypt($pwdString);

		@unlink($_SERVER["DOCUMENT_ROOT"].$this->serverLogTmp);
	}

	function __GetDescriptionList($ts)
	{
		$this->ts = $ts;

		$this->__SetLogFileNames();

		$h = fopen($_SERVER["DOCUMENT_ROOT"].$this->serverLog, "r");

		$line = fgets($h, 4096);
		$line = trim($line);
		$arLine = explode("|", $line);
		$this->descrTs = $arLine[0];
		$this->descrRegion = $arLine[1];
		$exts = $arLine[2];
		$this->descrCollectedExtensions = explode(",", $exts);

		fclose($h);
	}

	function Open($ts, $pwdString)
	{
		$this->ts = $ts;

		$this->__SetLogFileNames();

		$fileString = file_get_contents($_SERVER["DOCUMENT_ROOT"].str_replace("\\", "/", $this->serverLog));
		$pos = strpos($fileString, "\n");
		$descr = substr($fileString, 0, $pos);

		$fileStringNew = CFileCheckerUtil::Decrypt($fileString, $pwdString, $pos + 1);
		if (substr($fileStringNew, 0, 1) != "/")
			return false;

		$this->serverLogTmpHandle = fopen($_SERVER["DOCUMENT_ROOT"].$this->serverLogTmp, "w");
		fwrite($this->serverLogTmpHandle, $descr."\n");
		fwrite($this->serverLogTmpHandle, $fileStringNew);
		fclose($this->serverLogTmpHandle);

		$this->OpenStep($ts);

		return true;
	}

	function OpenStep($ts)
	{
		$this->ts = $ts;

		$this->__SetLogFileNames();

		$this->logText = file_get_contents($_SERVER["DOCUMENT_ROOT"].str_replace("\\", "/", $this->serverLogTmp));

		$this->__ReadDescriptionFromString();

		return true;
	}

	function __ReadDescriptionFromString()
	{
		$pos = strpos($this->logText, "\n");
		$line = substr($this->logText, 0, $pos);
		$this->logText = substr($this->logText, $pos + 1);

		$line = trim($line);
		$arLine = explode("|", $line);
		$this->descrTs = $arLine[0];
		$this->descrRegion = $arLine[1];
		$exts = $arLine[2];
		$this->descrCollectedExtensions = explode(",", $exts);
	}

	function Search($filename)
	{
		$pos = -1;
		do {
			$pos = strpos($this->logText, $filename, $pos+1);
			if ($pos === false)
			{
				return false;
			}
		} while (($pos > 0) && (substr($this->logText, $pos-1, 1) !== "\n"));
		//check if it's begin of file or line

		$pos1 = strpos($this->logText, "\n", $pos);

		$line = substr($this->logText, $pos, $pos1 - $pos);

		$this->logText = substr($this->logText, 0, $pos).substr($this->logText, $pos1 + 1);

		$arLine = explode("*", $line);

		return array(
			"filename" => $arLine[0],
			"fileSize" => $arLine[1],
			"fileCRC" => $arLine[2]
		);
	}

	function ReadLine()
	{
		$pos = strpos($this->logText, "\n");
		$line = substr($this->logText, 0, $pos);
		$this->logText = substr($this->logText, $pos + 1);

		return $line;
	}

	function CloseOpen()
	{
		@unlink($_SERVER["DOCUMENT_ROOT"].$this->serverLogTmp);
	}

	function CloseOpenStep()
	{
		$this->serverLogTmpHandle = fopen($_SERVER["DOCUMENT_ROOT"].$this->serverLogTmp, "w");
		fwrite($this->serverLogTmpHandle, $this->descrTs."|".$this->descrRegion."|".implode(",", $this->descrCollectedExtensions)."\n");
		fwrite($this->serverLogTmpHandle, $this->logText);
		fclose($this->serverLogTmpHandle);
	}

	function __SetLogFileNames()
	{
		$this->ts = intval($this->ts);
		$this->serverLog = $this->serverFileLogPath."/".$this->serverFileLogName."-".$this->ts.".".$this->serverFileLogExt;
		$this->serverLogTmp = $this->serverFileLogPath."/".$this->serverFileLogName."-".$this->ts."_tmp.".$this->serverFileLogExt;
	}

	function GetTs()
	{
		return $this->ts;
	}

	function GetDescriptionTs()
	{
		return $this->descrTs;
	}

	function GetDescriptionRegion()
	{
		return $this->descrRegion;
	}

	function GetDescriptionCollectedExtensions()
	{
		return $this->descrCollectedExtensions;
	}

	function GetLogCommonPathPart()
	{
		return $this->serverFileLogPath."/".$this->serverFileLogName."-";
	}

	public static function GetDownloadName($ts)
	{
		$ts = intval($ts);
		if($ts <= 0)
			return "";
		elseif(function_exists("gzcompress"))
			return "serverfilelog-".$ts.".dat.gz";
		else
			return "serverfilelog-".$ts.".dat";
	}

	public static function StartDownload($ts)
	{
		$ts = intval($ts);
		if ($ts <= 0)
			return false;

		$serverFileLog = "/bitrix/modules/serverfilelog-".$ts.".dat";

		$streamFileName = $serverFileLog;
		if (function_exists("gzcompress"))
		{
			$streamFileName = $serverFileLog."2";
			$zp_file = gzopen($_SERVER["DOCUMENT_ROOT"].$serverFileLog."2", "wb9f");

			$hFile = fopen($_SERVER["DOCUMENT_ROOT"].$serverFileLog, "rb");
			while (!feof($hFile))
			{
				$buffer = fgets($hFile, 4096);
				if (strlen($buffer) > 0)
					gzwrite($zp_file, $buffer);
			}
			gzclose($zp_file);
			fclose($hFile);
		}

		return $streamFileName;
	}

	public static function StopDownload($ts)
	{
		$ts = intval($ts);
		if ($ts <= 0)
			return;

		$streamFileName = "/bitrix/modules/serverfilelog-".$ts.".dat2";
		if (function_exists("gzcompress"))
			@unlink($_SERVER["DOCUMENT_ROOT"].$streamFileName);
	}
}

class CFileCheckerErrorLog
{
	var $serverFileLogName = "serverfileerrorlog";
	var $serverFileLogExt = "dat";
	var $serverFileLogPath = "/bitrix/modules";

	var $serverLog;

	var $serverLogHandle;

	var $logText;

	function Create()
	{
		$this->__SetLogFileNames();

		if ($this->serverLogHandle = fopen($_SERVER["DOCUMENT_ROOT"].$this->serverLog, "w"))
			return true;

		return false;
	}

	function CreateStep()
	{
		$this->__SetLogFileNames();

		if ($this->serverLogHandle = fopen($_SERVER["DOCUMENT_ROOT"].$this->serverLog, "a"))
			return true;

		return false;
	}

	function Write($message)
	{
		fwrite($this->serverLogHandle, $message."\n");
	}

	function CloseCreate()
	{
		fclose($this->serverLogHandle);
	}

	function Open()
	{
		$this->__SetLogFileNames();

		$this->logText = file_get_contents($_SERVER["DOCUMENT_ROOT"].str_replace("\\", "/", $this->serverLog));

		return true;
	}

	function ReadLine()
	{
		$pos = strpos($this->logText, "\n");
		$line = substr($this->logText, 0, $pos);
		$this->logText = substr($this->logText, $pos + 1);

		return $line;
	}

	function CloseOpen()
	{
	}

	function __SetLogFileNames()
	{
		$this->serverLog = $this->serverFileLogPath."/".$this->serverFileLogName.".".$this->serverFileLogExt;
	}

	function GetLogCommonPathPart()
	{
		return $this->serverFileLogPath."/".$this->serverFileLogName.".".$this->serverFileLogExt;
	}
}

class CFileCheckerSubscriber
{
	public static function IsSubscribed($fileName)
	{
		$fileName = trim($fileName);
		if (strlen($fileName) <= 0)
			return false;

		$fileString = file_get_contents($_SERVER["DOCUMENT_ROOT"].str_replace("\\", "/", $fileName));
		if (strlen($fileString) <= 0)
			return false;

		return preg_match("#<"."\?[\s]*define\(\"BX_INTEGRITY_VALUE\",[\s]*'([^']*?)'\);?[\s]*\?".">#i", $fileString);
	}

	public static function Subscribe($fileName, $pwdString, $keyString, &$arErrors)
	{
		$fileName = trim($fileName);
		if (strlen($fileName) <= 0)
		{
			$arErrors[] = GetMessage("MFC1_FILE_NOT_SET").". ";
			return false;
		}

		$pwdString = trim($pwdString);
		if (strlen($pwdString) <= 0)
		{
			$arErrors[] = GetMessage("MFC1_PWD_NOT_SET").". ";
			return false;
		}

		$keyString = trim($keyString);
		if (strlen($keyString) <= 0)
		{
			$arErrors[] = GetMessage("MFC1_KEY_NOT_SET").". ";
			return false;
		}

		if ($keyString == $pwdString)
		{
			$arErrors[] = GetMessage("MFC1_PWD_KEY_EQ").". ";
			return false;
		}

		$fileString = file_get_contents($_SERVER["DOCUMENT_ROOT"].str_replace("\\", "/", $fileName));
		if (strlen($fileString) <= 0)
		{
			$arErrors[] = GetMessage("MFC1_EMPTY_FILE").". ";
			return false;
		}

		if(substr($fileString, 0, 4) === "Zend")
		{
			$arErrors[] = GetMessage("MFC1_ZEND_FILE").". ";
			return false;
		}

		$fileString = preg_replace("#<"."\\?[\\s]*define\\(\"BX_INTEGRITY_VALUE\",[\\s]*'[^']*?'\\);?[\\s]*\\?".">#i", "", $fileString);
		$currentCRC = sprintf("%u", crc32($fileString));

		$keyString = CFileCheckerUtil::Encrypt($keyString, CFileChecker::getIntegrityKey());
		$data = CFileCheckerSubscriber::__SetIntegrityParams(
				array("CRC" => $currentCRC, "KEY" => $keyString),
				$pwdString
			);

		$fileString = "<"."?define(\"BX_INTEGRITY_VALUE\",'".$data."');?".">".$fileString;

		return CFileCheckerUtil::SetFileContents($fileName, $fileString);
	}

	public static function __SetIntegrityParams($arData, $password)
	{
		if (!is_array($arData) || !isset($arData["CRC"]) || !isset($arData["KEY"]))
			return False;

		$data = $arData["CRC"]."*".$arData["KEY"];
		$dataNew = CFileCheckerUtil::Encrypt($data, $password);

		return $dataNew;
	}

	public static function Verify($fileName, $pwdString, &$keyString, &$arErrors)
	{
		$fileName = trim($fileName);
		if (strlen($fileName) <= 0)
		{
			$arErrors[] = GetMessage("MFC1_FILE_NOT_SET").". ";
			return false;
		}

		$pwdString = trim($pwdString);
		if (strlen($pwdString) <= 0)
		{
			$arErrors[] = GetMessage("MFC1_PWD_NOT_SET").". ";
			return false;
		}

		$fileString = file_get_contents($_SERVER["DOCUMENT_ROOT"].str_replace("\\", "/", $fileName));
		if (strlen($fileString) <= 0)
		{
			$arErrors[] = GetMessage("MFC1_EMPTY_FILE").". ";
			return false;
		}

		if (preg_match("#<"."\\?[\\s]*define\\(\"BX_INTEGRITY_VALUE\",[\\s]*'([^']*?)'\\);?[\\s]*\\?".">#i", $fileString, $arMatches))
		{
			$data = $arMatches[1];
			if (strlen($data) > 0)
			{
				$fileString = preg_replace("#<"."\\?[\\s]*define\\(\"BX_INTEGRITY_VALUE\",[\\s]*'[^']*?'\\);?[\\s]*\\?".">#i", "", $fileString);
				$currentCRC = sprintf("%u", crc32($fileString));

				if ($arIntegrityParams = CFileCheckerSubscriber::__GetIntegrityParams($data, $pwdString))
				{
					if ($arIntegrityParams["CRC"] != $currentCRC)
					{
						$arErrors[] .= GetMessage("MFC1_CRC_NOT_CORRECT").". ";
						return false;
					}
					else
					{
						$keyString = $arIntegrityParams["KEY"];
						$keyString = CFileCheckerUtil::Decrypt($keyString, CFileChecker::getIntegrityKey());
						return true;
					}
				}
				else
				{
					$arErrors[] .= GetMessage("MFC1_NO_CRC").". ";
					return false;
				}
			}
			else
			{
				$arErrors[] .= GetMessage("MFC1_NO_CRC").". ";
				return false;
			}
		}
		else
		{
			$arErrors[] .= GetMessage("MFC1_NO_CRC_NOT_SET").". ";
			return false;
		}
	}

	public static function __GetIntegrityParams($data, $password)
	{
		if (strlen($data) <= 0)
			return False;

		$dataNew = CFileCheckerUtil::Decrypt($data, $password);
		$arDataNew = explode("*", $dataNew);

		if (count($arDataNew) != 2)
			return False;

		return array("CRC" => $arDataNew[0], "KEY" => $arDataNew[1]);
	}
}

class CFileCheckerUtil
{
	public static function SetFileContents($filename, $content)
	{
		$filename = str_replace("\\", "/", $filename);

		if ($hFile = fopen($_SERVER["DOCUMENT_ROOT"].$filename, "wb"))
		{
			fwrite($hFile, $content);
			fclose($hFile);
			return true;
		}

		return false;
	}

	public static function GetFileCRC($filename)
	{
		$fileString = file_get_contents($_SERVER["DOCUMENT_ROOT"].str_replace("\\", "/", $filename));
		$crc = crc32($fileString);
		return sprintf("%u", $crc);
	}

	public static function GetFileLength($filename)
	{
		return filesize($_SERVER["DOCUMENT_ROOT"].$filename);
	}

	public static function Encrypt($data, $pwdString)
	{
		return static::__CryptData($data, $pwdString, "E");
	}

	public static function Decrypt($data, $pwdString, $startPosition = 0)
	{
		return static::__CryptData($data, $pwdString, "D", $startPosition);
	}

	protected static function __CryptData($data, $pwdString, $type, $startPosition = 0)
	{
		$type = strtoupper($type);
		if ($type != "D")
			$type = "E";

		if ($type == 'D')
			$data = urldecode($data);

		$key[] = "";
		$box[] = "";
		$pwdLength = strlen($pwdString);

		for ($i = 0; $i <= 255; $i++)
		{
			$key[$i] = ord(substr($pwdString, ($i % $pwdLength), 1));
			$box[$i] = $i;
		}
		$x = 0;

		for ($i = 0; $i <= 255; $i++)
		{
			$x = ($x + $box[$i] + $key[$i]) % 256;
			$temp_swap = $box[$i];
			$box[$i] = $box[$x];
			$box[$x] = $temp_swap;
		}

		$cipher = "";
		$a = 0;
		$j = 0;
		$data_len = defined("BX_UTF")? mb_strlen($data, 'latin1'): strlen($data);
		for ($i = $startPosition; $i < $data_len; $i++)
		{
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$temp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $temp;
			$k = $box[(($box[$a] + $box[$j]) % 256)];
			$cipherby = ord(
				defined("BX_UTF")? mb_substr($data, $i, 1, 'latin1'): substr($data, $i, 1)
			) ^ $k;
			$cipher .= chr($cipherby);
		}

		if ($type == 'D')
			return $cipher;
		else
			return urlencode($cipher);
	}
}
/*************************************************************************************************/
/*************************************************************************************************/

if (in_array($_REQUEST["fcajax"], array("cl", "vf", "df")))
{
	if (!check_bitrix_sessid())
	{
		echo "ERR|Your session has been expired.";
		die();
	}

	if ($_REQUEST["fcajax"] == "cl")
	{
		if(!$canCollect)
			$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

		if (intval($_REQUEST["tm"]) < 5)
			$_REQUEST["tm"] = 5;

		$collector = new CFileChecker();
		$arTs = array(
			"ts" => intval($_REQUEST["ts"]),
			"Completed" => intval($_REQUEST["completed"]),
			"StartPoint" => $_REQUEST["startpoint"],
			"MaxExecutionTime" => intval($_REQUEST["tm"]),
		);
		$arErrors = array();
		$res = $collector->CollectCrc(intval($_REQUEST["region"]), explode(",", $_REQUEST["exts"]), $_REQUEST["pwd"], $arTs, $arErrors);
		if (count($arErrors) > 0)
		{
			echo "ERR|".implode("|", $arErrors);
		}
		else
		{
			if ($res)
				echo "FIN|".$arTs["ts"]."|".$arTs["StatNum"];
			else
				echo "STP|".$arTs["Completed"]."|".$arTs["ts"]."|".$arTs["StartPoint"]."|".$arTs["StatNum"];
		}
	}
	elseif ($_REQUEST["fcajax"] == "vf")
	{
		if(!$canVerify)
			$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

		if (intval($_REQUEST["tm"]) < 5)
			$_REQUEST["tm"] = 5;

		$collector = new CFileChecker();
		$arTs = array(
			"ts" => intval($_REQUEST["ts"]),
			"Completed" => intval($_REQUEST["completed"]),
			"StartPoint" => $_REQUEST["startpoint"],
			"MaxExecutionTime" => intval($_REQUEST["tm"]),
		);
		$arErrors = array();
		$res = $collector->VerifyCrc(intval($_REQUEST["df"]), $_REQUEST["pwd"], $arTs, $arErrors);
		if (count($arErrors) > 0)
		{
			echo "ERR|".implode("|", $arErrors);
		}
		else
		{
			if ($res)
			{
				$io = CBXVirtualIo::GetInstance();
				echo "FIN|";
				$fl = new CFileCheckerErrorLog();
				$fl->Open();
				$i = 0;
				while ($s = $fl->ReadLine())
				{
					if ($i > 300)
					{
						echo GetMessage("MFC1_EXISTS_OTHER_DIF");
						break;
					}

					$arS = explode("*", $s);

					echo $io->GetLogicalName($arS[1])." - ";
					if ($arS[0] == "FS")
						echo GetMessage("MFC1_SIZE_DIF");
					elseif ($arS[0] == "FC")
						echo GetMessage("MFC1_CRC_DIF");
					elseif ($arS[0] == "FN")
						echo GetMessage("MFC1_NEW_DIF");
					elseif ($arS[0] == "FM")
						echo GetMessage("MFC1_DEL_DIF");
					echo "<br />";
					$i++;
				}
				if ($i == 0)
					echo GetMessage("MFC1_NO_DIF");
				$fl->CloseOpen();
				COption::SetOptionInt("security", "last_files_check", time());
			}
			else
			{
				echo "STP|".$arTs["Completed"]."|".$arTs["ts"]."|".$arTs["StartPoint"]."|".$arTs["StatNum"];
			}
		}
	}
	elseif ($_REQUEST["fcajax"] == "df")
	{
		if(!$canCollect && !$canVerify)
			$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

		$_REQUEST["df"] = intval($_REQUEST["df"]);
		unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serverfilelog-".$_REQUEST["df"].".dat");
		echo $_REQUEST["df"];
	}

	die();
}
if ($_REQUEST["fcdld"] == "Y" && check_bitrix_sessid() && $canCollect)
{
	if (intval($_REQUEST["ts"]) > 0)
	{
		$streamFileName = CFileCheckerLog::StartDownload($_REQUEST["ts"]);

		$filesize = filesize($_SERVER["DOCUMENT_ROOT"].$streamFileName);
		header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
		header("Content-Type: application/force-download; name=\"".CFileCheckerLog::GetDownloadName($_REQUEST["ts"])."\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$filesize);
		header("Content-Disposition: attachment; filename=\"".CFileCheckerLog::GetDownloadName($_REQUEST["ts"])."\"");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Expires: 0");
		header("Pragma: public");

		$p = file_get_contents($_SERVER["DOCUMENT_ROOT"].str_replace("\\", "/", $streamFileName));
		echo $p;
		flush();

		CFileCheckerLog::StopDownload($_REQUEST["ts"]);
	}

	die();
}

$tabStep = (isset($_REQUEST["tabStep"]) && intval($_REQUEST["tabStep"]) > 1 ? intval($_REQUEST["tabStep"]) : 1);
if (isset($_REQUEST["backButton"]))
	$tabStep = $tabStep - 2;
else if (isset($_REQUEST["backToStart"]))
	$tabStep = 1;

$scriptName = "/bitrix/modules/security/admin/security_file_verifier.php";
$isSubscribed = CFileCheckerSubscriber::IsSubscribed($scriptName);
$okMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && $tabStep == 2 && check_bitrix_sessid())
{
	if ($isSubscribed)
	{
		$errorMessageTmp = "";
		$okMessageTmp = "";

		if (strlen($_REQUEST["crc_password"]) <= 0)
			$errorMessageTmp .= GetMessage("MFC1_ERR_NO_PWD").". ";

		if (strlen($errorMessageTmp) <= 0)
		{
			$keyString = "";
			$arErrors = array();
			if (!CFileCheckerSubscriber::Verify($scriptName, $_REQUEST["crc_password"], $keyString, $arErrors))
				$errorMessageTmp .= GetMessage("MFC1_ERR_VERIFY")."<br />".implode("<br />", $arErrors);
			else
				$okMessageTmp = str_replace("#KEY#", $keyString, GetMessage("MFC1_OK_VERIFY"));
		}

		if (strlen($errorMessageTmp) > 0)
		{
			$errorMessage = $errorMessageTmp;
			$tabStep = 1;
		}
		if (strlen($okMessageTmp) > 0)
			$okMessage = $okMessageTmp;
	}
	elseif ($canSign)
	{
		$errorMessageTmp = "";
		$okMessageTmp = "";

		if (strlen($_REQUEST["crc_password"]) <= 0)
			$errorMessageTmp .= GetMessage("MFC1_ERR_C_PWD").". ";

		if (strlen($errorMessageTmp) <= 0 && $_REQUEST["crc_password"] != $_REQUEST["crc_password_check"])
			$errorMessageTmp .= GetMessage("MFC1_ERR_C_PWD_CHECK").". ";

		if (strlen($errorMessageTmp) <= 0 && strlen($_REQUEST["crc_key"]) <= 0)
			$errorMessageTmp .= GetMessage("MFC1_ERR_C_KEY").". ";

		if (strlen($errorMessageTmp) <= 0 && $_REQUEST["crc_key"] == $_REQUEST["crc_password"])
			$errorMessageTmp .= GetMessage("MFC1_ERR_C_PWD_KEY").". ";

		if (strlen($errorMessageTmp) <= 0)
		{
			$keyString = "";
			$arErrors = array();
			if (!CFileCheckerSubscriber::Subscribe($scriptName, $_REQUEST["crc_password"], $_REQUEST["crc_key"], $arErrors))
				$errorMessageTmp .= GetMessage("MFC1_ERR_C_ERR")."<br />".implode("<br />", $arErrors);
			else
				$okMessageTmp = GetMessage("MFC1_ERR_C_SUCCESS").".";
		}

		if (strlen($errorMessageTmp) > 0)
		{
			$errorMessage = $errorMessageTmp;
			$tabStep = 1;
		}
		if (strlen($okMessageTmp) > 0)
			$okMessage = $okMessageTmp;
	}
	else
	{
		$errorMessage = GetMessage("MFC1_ERR_C_ERR_RIGHT");
		$tabStep = 1;
	}
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $tabStep == 3 && check_bitrix_sessid())
{
	$bOK = $_REQUEST["action"] === "verify" && $canVerify;
	if(!$bOK)
		$bOK = $_REQUEST["action"] === "collect" && $canCollect;

	if(!$bOK)
	{
		$tabStep = 2;
		$errorMessage .= GetMessage("MFC1_ERR_NO_ACT").". ";
	}
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $tabStep == 4 && check_bitrix_sessid())
{
	if ($_REQUEST["action"] == "collect" && $canCollect)
	{
		$errorMessageTmp = "";
		$okMessageTmp = "";

		COption::SetOptionString("security", "checker_exts", trim($_REQUEST["checker_exts"]));
		COption::SetOptionInt("security", "checker_time", trim($_REQUEST["checker_time"]));

		$region = 0;

		if ($_REQUEST["checker_region_kernel"] == "Y")
			$region |= BX_FILE_CHECKER_REGION_KERNEL;
		COption::SetOptionString("security", "checker_region_kernel", $_REQUEST["checker_region_kernel"] == "Y"? "Y": "N");

		if ($_REQUEST["checker_region_root"] == "Y")
			$region |= BX_FILE_CHECKER_REGION_ROOT;
		COption::SetOptionString("security", "checker_region_root", $_REQUEST["checker_region_root"] == "Y"? "Y": "N");

		if ($_REQUEST["checker_region_personal_root"] == "Y")
			$region |= BX_FILE_CHECKER_REGION_PERSONAL_ROOT;
		COption::SetOptionString("security", "checker_region_personal_root", $_REQUEST["checker_region_personal_root"] == "Y"? "Y": "N");

		if ($_REQUEST["checker_region_public"] == "Y")
			$region |= BX_FILE_CHECKER_REGION_PUBLIC;
		COption::SetOptionString("security", "checker_region_public", $_REQUEST["checker_region_public"] == "Y"? "Y": "N");

		if ($region <= 0)
			$errorMessageTmp .= GetMessage("MFC1_ERR_C_NO_REG").". ";

		if (strlen($errorMessageTmp) <= 0 && strlen($_REQUEST['checker_pwd']) <= 0)
			$errorMessageTmp .= GetMessage("MFC1_ERR_C_NO_PWD1").". ";

		if (strlen($errorMessageTmp) > 0)
		{
			$errorMessage = $errorMessageTmp;
			$tabStep = 3;
		}
	}
	elseif ($_REQUEST["action"] == "verify" && $canVerify)
	{
		$errorMessageTmp = "";
		$okMessageTmp = "";

		if (isset($_FILES["crc_file"]) && is_uploaded_file($_FILES["crc_file"]["tmp_name"]))
		{
			$cf_select_dfile = "";

			if(function_exists("gzcompress"))
			{
				$hFile = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serverfilelog-1.dat", "wb");
				$zp_file = gzopen($_FILES["crc_file"]["tmp_name"], "rb9f");
				while (!gzeof($zp_file))
				{
					$buffer = gzread($zp_file, 4096);
					if (strlen($buffer) > 0)
						fwrite($hFile, $buffer);
				}
				gzclose($zp_file);
				fclose($hFile);
			}
			else
			{
				copy($_FILES["crc_file"]["tmp_name"], $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serverfilelog-1.dat");
			}

			$vf = new CFileCheckerLog();
			$vf->__GetDescriptionList(1);
			$ts = intval($vf->GetDescriptionTs());

			if ($ts > 0)
			{
				@unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serverfilelog-".$ts.".dat");
				copy($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serverfilelog-1.dat", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serverfilelog-".$ts.".dat");
				unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/serverfilelog-1.dat");

				$cf_select_dfile = $ts;
			}
		}
		else
		{
			$cf_select_dfile = $_REQUEST["cf_select_dfile"];
		}

		$cf_select_dfile = intval($cf_select_dfile);
		if ($cf_select_dfile <= 0)
			$errorMessageTmp .= GetMessage("MFC1_ERR_V_FILE").". ";

		if (strlen($errorMessageTmp) > 0)
		{
			$errorMessage = $errorMessageTmp;
			$tabStep = 3;
		}
	}
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $tabStep == 5 && check_bitrix_sessid())
{
	if ($_REQUEST["action"] == "verify" && $canVerify)
	{
		$errorMessageTmp = "";
		$okMessageTmp = "";

		if (strlen($_REQUEST['checker_pwd']) <= 0)
			$errorMessageTmp .= GetMessage("MFC1_ERR_V_PWD1").". ";

		if (strlen($errorMessageTmp) > 0)
		{
			$errorMessage = $errorMessageTmp;
			$tabStep = 4;
		}
	}
}

$APPLICATION->SetTitle(GetMessage("MFC1_TITLE"));
require_once ($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$arTabs = Array(
	Array("DIV" => "tabSign", "TAB" => GetMessage("MFC1_TAB_SIGN"), "TITLE" => GetMessage("MFC1_TAB_SIGN_DESCR")),
	Array("DIV" => "tabSelect", "TAB" => GetMessage("MFC1_TAB_SELECT"), "TITLE" => GetMessage("MFC1_TAB_SELECT_DESCR")),
);
if ($tabStep > 2)
{
	if ($_REQUEST["action"] == "collect" && $canCollect)
	{
		$arTabs[] = Array("DIV" => "tabCollect", "TAB" => GetMessage("MFC1_TAB_COLLECT"), "TITLE" => GetMessage("MFC1_TAB_COLLECT_DESCR"));
		$arTabs[] = Array("DIV" => "tabCollectResult", "TAB" => GetMessage("MFC1_TAB_COLLECT_REP"), "TITLE" => GetMessage("MFC1_TAB_COLLECT_REP_DESCR"));
	}
	if ($_REQUEST["action"] == "verify" && $canVerify)
	{
		$arTabs[] = Array("DIV" => "tabFile", "TAB" => GetMessage("MFC1_TAB_FILE"), "TITLE" =>	GetMessage("MFC1_TAB_FILE_DESCR"));
		$arTabs[] = Array("DIV" => "tabVerify", "TAB" => GetMessage("MFC1_TAB_VERIFY"), "TITLE" => GetMessage("MFC1_TAB_VERIFY_DESCR"));
		$arTabs[] = Array("DIV" => "tabVerifyResult", "TAB" => GetMessage("MFC1_TAB_VERIFY_REP"), "TITLE" => GetMessage("MFC1_TAB_VERIFY_REP_DESCR"));
	}
}

$tabControl = new CAdminTabControl("tabControl", $arTabs, false, true);
?>
<script language="JavaScript">
<!--
	function FCPrepareString(str)
	{
		str = str.replace(/^\s+|\s+$/, '');
		while (str.length > 0 && str.charCodeAt(0) == 65279)
			str = str.substring(1);
		return str;
	}
//-->
</script>
<form method="post" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>" name="check_file_form">
<?

if ($okMessage)
{
	$m = new CAdminMessage(array(
		"MESSAGE" => $okMessage,
		"TYPE" => "OK",
		"HTML" => true
	));
	echo $m->Show();
}

if ($errorMessage)
{
	$m = new CAdminMessage(array(
		"MESSAGE" => $errorMessage,
		"TYPE" => "ERROR",
		"HTML" => true
	));
	echo $m->Show();
}

$tabControl->Begin();
$tabControl->BeginNextTab();
if ($tabStep == 1):
	if ($isSubscribed):
		?>
		<tr class="adm-detail-required-field">
			<td class="adm-detail-valign-top" width="30%" style="padding-top:16px;"><?= GetMessage("MFC1_F_PWD") ?>:<br></td>
			<td width="70%"><input type="password" name="crc_password" style="width:80%;"><?echo BeginNote().GetMessage("MFCW_INT_PASS_SUBSCR").EndNote();?></td>
		</tr>
		<?
	elseif ($canSign):
		?>
		<tr class="adm-detail-required-field">
			<td class="adm-detail-valign-top" width="30%" style="padding-top:16px;"><?= GetMessage("MFC1_F_PWD") ?>:<br></td>
			<td width="70%"><input type="password" name="crc_password" style="width:80%;"><?echo BeginNote().GetMessage("MFCW_INT_PASS_NOTSUBSCR").EndNote();?></td>
		</tr>
		<tr class="adm-detail-required-field">
			<td><?= GetMessage("MFC1_F_PWD_CONF") ?>:</td>
			<td><input type="password" name="crc_password_check" style="width:80%;"></td>
		</tr>
		<tr class="adm-detail-required-field">
			<td class="adm-detail-valign-top" style="padding-top:16px;"><?= GetMessage("MFC1_F_KEY") ?>:</td>
			<td><input type="text" name="crc_key" style="width:80%;" value=""><?echo BeginNote().GetMessage("MFCW_INT_KEY_HINT_NOT_SUBSCR").EndNote();?></td>
		</tr>
		<?
	endif;
endif;
$tabControl->EndTab();
$tabControl->BeginNextTab();
if ($tabStep == 2):
?>

	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top" width="30%"><?= GetMessage("MFC1_F_ACT") ?>:<br></td>
		<td width="70%">
			<?if($canVerify):?>
			<input type="radio" name="action" value="verify" id="action_verify" checked><label for="action_verify"><?= GetMessage("MFC1_F_ACT_VERIFY") ?></label><br />
			<?endif?>
			<?if($canCollect):?>
			<input type="radio" name="action" value="collect" id="action_collect"><label for="action_collect"><?= GetMessage("MFC1_F_ACT_COLLECT") ?></label>
			<?endif?>
		</td>
	</tr>

<?
endif;
$tabControl->EndTab();

if ($tabStep > 2 && $_REQUEST["action"] == "verify" && $canVerify)
{
	$tabControl->BeginNextTab();
	if ($tabStep == 3):
		?>
		<tr>
			<td colspan="2"><b><?= GetMessage("MFC1_F_FILE_SUBTITLE1") ?></b></td>
		</tr>
		<tr>
			<td colspan="2">
				<script language="JavaScript">
				<!--
					function CFDeleteLog(ts)
					{
						if (!confirm("<?= GetMessage("MFC1_F_DELETE_CONFIRM") ?>"))
							return;

						BX.ajax.post(
							"/bitrix/admin/security_file_verifier.php",
							"fcajax=df&df=" + ts + "&<?= bitrix_sessid_get() ?>",
							function(result)
							{
								result = FCPrepareString(result);
								var o = document.getElementById("cf_select_dfile_" + result);
								if (o)
									o.disabled = true;
								var o1 = document.getElementById("cf_tr_select_dfile_" + result);
								if (o1)
									o1.disabled = true;
							}
						);
					}
				//-->
				</script>
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="internal">
					<tr class="heading">
						<td align="center"><b>&nbsp;</b></td>
						<td align="center"><b><?= GetMessage("MFC1_FT_DATE") ?></b></td>
						<td align="center"><b><?= GetMessage("MFC1_FT_REGION") ?></b></td>
						<td align="center"><b><?= GetMessage("MFC1_FT_EXTS") ?></b></td>
						<td align="center"><b><?= GetMessage("MFC1_FT_ACTS") ?></b></td>
					</tr>
					<?
					$f = true;
					$arFiles = CFileChecker::GetList();
					if (count($arFiles) > 0)
					{
						?>
						<script language="JavaScript">
						<!--
							function CFTrClick(v)
							{
								var o = document.getElementById("cf_select_dfile_" + v);
								if (o)
									o.checked = true;
							}
						//-->
						</script>
						<?
						foreach ($arFiles as $arFile)
						{
							?>
							<tr onclick="CFTrClick('<?= $arFile["TIMESTAMP_X"] ?>')" id="cf_tr_select_dfile_<?= $arFile["TIMESTAMP_X"] ?>">
								<td style="text-align: center;"><input type="radio" name="cf_select_dfile" id="cf_select_dfile_<?= $arFile["TIMESTAMP_X"] ?>"<?= $f ? " checked" : "" ?> value="<?= $arFile["TIMESTAMP_X"] ?>"></td>
								<td style="text-align: center;"><?= date(CDatabase::DateFormatToPHP(FORMAT_DATETIME), $arFile["TIMESTAMP_X"]) ?></td>
								<td style="text-align: center;"><?
								if (($arFile["REGION"] & BX_FILE_CHECKER_REGION_KERNEL) != 0)
									echo GetMessage("MFC1_R_KERNEL")." ( /bitrix/modules )<br />";
								if (($arFile["REGION"] & BX_FILE_CHECKER_REGION_ROOT) != 0)
									echo GetMessage("MFC1_R_SYSTEM")." ( /bitrix )<br />";
								if (($arFile["REGION"] & BX_FILE_CHECKER_REGION_PERSONAL_ROOT) != 0)
									echo GetMessage("MFC1_R_PSYSTEM")." ( ".BX_PERSONAL_ROOT." )<br />";
								if (($arFile["REGION"] & BX_FILE_CHECKER_REGION_PUBLIC) != 0)
									echo GetMessage("MFC1_R_PUBLIC")."<br />";
								?></td>
								<td style="text-align: center;"><?= implode(", ", $arFile["EXTENTIONS"]) ?></td>
								<td style="text-align: center;"><a href="javascript:CFDeleteLog('<?= $arFile["TIMESTAMP_X"] ?>')"><?= GetMessage("MFC1_ACT_DODELETE") ?></a></td>
							</tr>
							<?
							$f = false;
						}
					}
					else
					{
						?>
						<tr>
							<td colspan="5"><?= GetMessage("MFC1_F_NO_FILES") ?></td>
						</tr>
						<?
					}
					?>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2"><br><br><b><?= GetMessage("MFC1_F_FILE_SUBTITLE2") ?></b></td>
		</tr>
		<tr>
			<td width="30%"><?= GetMessage("MFC1_F_LOAD_FILE") ?>:<br></td>
			<td width="70%">
				<input type="hidden" name="MAX_FILE_SIZE" value="1000000000">
				<input type="file" name="crc_file" size="40">
			</td>
		</tr>
		<input type="hidden" name="action" value="<?= htmlspecialcharsbx($_REQUEST["action"]) ?>">
		<?
	endif;
	$tabControl->EndTab();
}

$tabControl->BeginNextTab();
if ($tabStep == 3 && $_REQUEST["action"] == "collect" && $canCollect):
?>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top" width="30%"><?= GetMessage("MFC1_F_COLLECT_REGION") ?>:<br></td>
		<td width="70%">
			<input type="checkbox" name="checker_region_kernel" id="id_checker_region_kernel" <?if(COption::GetOptionString("security", "checker_region_kernel")=="Y") echo "checked";?> value="Y"><label for="id_checker_region_kernel"><?= GetMessage("MFC1_R_KERNEL") ?> ( /bitrix/modules )</label><br />
			<input type="checkbox" name="checker_region_root" id="id_checker_region_root" <?if(COption::GetOptionString("security", "checker_region_root")=="Y") echo "checked";?> value="Y"><label for="id_checker_region_root"><?= GetMessage("MFC1_R_SYSTEM") ?> ( /bitrix )</label><br />
			<?if (BX_PERSONAL_ROOT != BX_ROOT):?>
				<input type="checkbox" name="checker_region_personal_root" id="id_checker_region_personal_root" <?if(COption::GetOptionString("security", "checker_region_personal_root")=="Y") echo "checked";?> value="Y"><label for="id_checker_region_personal_root"><?= GetMessage("MFC1_R_PSYSTEM") ?> ( <?= BX_PERSONAL_ROOT ?> )</label><br />
			<?endif;?>
			<input type="checkbox" name="checker_region_public" id="id_checker_region_public" <?if(COption::GetOptionString("security", "checker_region_public")=="Y") echo "checked";?> value="Y"><label for="id_checker_region_public"><?= GetMessage("MFC1_R_PUBLIC") ?></label><br />
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="30%"><?= GetMessage("MFC1_F_EXTS") ?>:<br></td>
		<td width="70%">
			<input type="text" name="checker_exts" value="<?echo htmlspecialcharsbx(COption::GetOptionString("security", "checker_exts"));?>">
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="30%"><?= GetMessage("MFC1_F_CRY_PWD") ?>:<br></td>
		<td width="70%">
			<input type="password" name="checker_pwd" value="">
		</td>
	</tr>
	<tr>
		<td width="30%"><?= GetMessage("MFC1_F_C_STEP") ?>:<br></td>
		<td width="70%">
			<input type="text" name="checker_time" value="<?echo COption::GetOptionInt("security", "checker_time");?>">
		</td>
	</tr>
	<input type="hidden" name="action" value="<?= htmlspecialcharsbx($_REQUEST["action"]) ?>">

<?elseif ($tabStep == 4 && $_REQUEST["action"] == "verify" && $canVerify):?>

	<tr class="adm-detail-required-field">
		<td width="30%"><?= GetMessage("MFC1_F_DEC_PWD") ?>:<br></td>
		<td width="70%">
			<input type="password" name="checker_pwd" value="">
		</td>
	</tr>
	<tr>
		<td width="30%"><?= GetMessage("MFC1_F_V_STEP") ?>:<br></td>
		<td width="70%">
			<input type="text" name="checker_time" value="30">
		</td>
	</tr>
	<input type="hidden" name="action" value="<?= htmlspecialcharsbx($_REQUEST["action"]) ?>">
	<input type="hidden" name="cf_select_dfile" value="<?= htmlspecialcharsbx($_REQUEST["cf_select_dfile"]) ?>">

<?
endif;
$tabControl->EndTab();
$tabControl->BeginNextTab();
if(
	($tabStep == 4 && $_REQUEST["action"] == "collect" && $canCollect)
	|| ($tabStep == 5 && $_REQUEST["action"] == "verify" && $canVerify)
):
?>

	<script language="JavaScript">
	<!--
		var updRand = 1;
		var globalCounter = 0;
		var globalQuantity = 30000;

		function FCCollectData()
		{
			ShowWaitWindow();

			__FCSuccessInit();
			__FCErrorInit();

			__FCProgressInit();
			__FCProgressStart();

			__FCCollectData(0, 0, "");
		}

		function __FCCollectData(ts, completed, startPoint)
		{
			var callback = function(result)
			{
				result = FCPrepareString(result);
				__FCLoadCollectDataResult(result);
			};

			updRand++;
			var data = null;
			<?if ($_REQUEST["action"] == "verify"):?>
				data = "fcajax=vf&df=<?= intval($_REQUEST["cf_select_dfile"]) ?>&pwd=<?= urlencode($_REQUEST['checker_pwd']) ?>&tm=<?= intval($_REQUEST['checker_time']) ?>&<?= bitrix_sessid_get() ?>&ts=" + ts + "&completed=" + completed + "&startpoint=" + startPoint + "&updRand=" + updRand;
			<?elseif ($_REQUEST["action"] == "collect"):?>
				data = "fcajax=cl&region=<?= intval($region) ?>&exts=<?= urlencode($_REQUEST['checker_exts']) ?>&pwd=<?= urlencode($_REQUEST['checker_pwd']) ?>&tm=<?= intval($_REQUEST['checker_time']) ?>&<?= bitrix_sessid_get() ?>&ts=" + ts + "&completed=" + completed + "&startpoint=" + startPoint + "&updRand=" + updRand;
			<?endif;?>
			BX.ajax.post("/bitrix/admin/security_file_verifier.php", data, callback);
		}

		function __FCLoadCollectDataResult(result)
		{
			var arData = result.split("|"); // code,Completed,ts,StartPoint,num

			if (arData[0] == "FIN")
			{
				if(arData.length > 2)
					globalCounter += parseInt(arData[2]);

				__FCProgressStop();
				__FCErrorClose();
				__FCSuccessShow();

				<?if ($_REQUEST["action"] == "verify"):?>
					__FCSuccessAdd("<b><?= GetMessage("MFC1_J_FINISH") ?></b><br /><br />");
					__FCSuccessAdd(arData[1]);

				<?elseif ($_REQUEST["action"] == "collect"):?>
					__FCSuccessAdd("<b><?= GetMessage("MFC1_J_FINISH") ?></b><br /><br />");
					__FCSuccessAdd("<?= GetMessage("MFC1_J_NUM_FILES") ?>: " + globalCounter + ".<br />");
					__FCSuccessAdd("<?= GetMessage("MFC1_J_DWL_PROMT1") ?> <a href='/bitrix/admin/security_file_verifier.php?fcdld=Y&ts=" + arData[1] + "&<?= bitrix_sessid_get() ?>'><?= GetMessage("MFC1_J_DWL_PROMT2") ?></a>.");
				<?endif;?>

				CloseWaitWindow();
			}
			else
			{
				if (arData[0] == "STP")
				{
					globalCounter += parseInt(arData[4]);
					var v = globalCounter;
					if (v > globalQuantity)
						v = v % globalQuantity;
					__FCProgressSet(v * 100 / globalQuantity);

					__FCCollectData(arData[2], arData[1], arData[3]);
				}
				else
				{
					__FCProgressStop();
					__FCSuccessClose();
					__FCErrorShow();

					__FCErrorAdd("<b><?= GetMessage("MFC1_J_PROCESS_ERR") ?></b><br /><br />");
					for (var i = 0; i < arData.length; i++)
						__FCErrorAdd(arData[i] + "<br />");

					CloseWaitWindow();
				}
			}
		}

		jsUtils.addEvent(window, "load", function() {FCCollectData();});
	//-->
	</script>

	<tr>
		<td colspan="2">
			<div id="fc_progress_bar_div" style="display:none">
				<div style="top:0; left:0; width:300px; height:15px; background-color:#365069; font-size:1px;">
					<div style="position:relative; top:1px; left:1px; width:298px; height:13px; background-color:#ffffff; font-size:1px;">
						<div id="FCPBdone" style="position:relative; top:0; left:0; width:0; height:13px; background-color:#D5E7F3; font-size:1px;"></div>
					</div>
				</div>
				<?= GetMessage("MFC1_SLEEP_A_MINUTE") ?>
			</div>

			<div id="fc_success_div" style="display:none">
			</div>

			<div id="fc_error_div" style="display:none">
			</div>
		</td>
	</tr>

	<script language="JavaScript">
	<!--
	var fcProgressBarDiv;
	var FCPBdone = document.getElementById('FCPBdone');

	function __FCProgressInit()
	{
		fcProgressBarDiv = document.getElementById("fc_progress_bar_div");
		__FCProgressSet(0);
	}

	function __FCProgressStart()
	{
		fcProgressBarDiv.style["display"] = "block";
	}

	function __FCProgressSet(val)
	{
			FCPBdone.style.width = (val*298/100) + 'px';
	}

	function __FCProgressStop()
	{
		fcProgressBarDiv.style["display"] = "none";
	}


	var fcSuccessDiv;

	function __FCSuccessInit()
	{
		fcSuccessDiv = document.getElementById("fc_success_div");
		__FCSuccessSet("");
	}

	function __FCSuccessShow()
	{
		fcSuccessDiv.style["display"] = "block";
	}

	function __FCSuccessSet(val)
	{
		fcSuccessDiv.innerHTML = val;
	}

	function __FCSuccessAdd(val)
	{
		fcSuccessDiv.innerHTML += val;
	}

	function __FCSuccessClose()
	{
		fcSuccessDiv.style["display"] = "none";
	}


	var fcErrorDiv;

	function __FCErrorInit()
	{
		fcErrorDiv = document.getElementById("fc_error_div");
		__FCErrorSet("");
	}

	function __FCErrorShow()
	{
		fcErrorDiv.style["display"] = "block";
	}

	function __FCErrorSet(val)
	{
		fcErrorDiv.innerHTML = val;
	}

	function __FCErrorAdd(val)
	{
		fcErrorDiv.innerHTML += val;
	}

	function __FCErrorClose()
	{
		fcErrorDiv.style["display"] = "none";
	}
	//-->
	</script>

<?
endif;
$tabControl->EndTab();
$tabControl->Buttons();
?>

<input type="hidden" name="tabStep" value="<?=($tabStep + 1)?>">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
<?=bitrix_sessid_post()?>

<?if ($tabStep > 1):?>
	<input type="submit" name="backToStart" value="&lt;&lt; <?= GetMessage("MFC1_B_FIRST") ?>">
<?endif?>

<?if(
	($tabStep < 3 && $canSign)
	|| ($tabStep < 4 && $_REQUEST["action"] == "collect" && $canCollect)
	|| ($tabStep < 5 && $_REQUEST["action"] == "verify" && $canVerify)
):?>
	<input type="submit" value="<?= GetMessage("MFC1_B_NEXT") ?> &gt;&gt;" name="nextButton">
<?endif?>

<?$tabControl->End();?>
</form>

<script type="text/javascript">
<!--
<?if ($tabStep == 1):?>
	tabControl.SelectTab("tabSign");
	tabControl.DisableTab("tabSelect");
<?elseif ($tabStep == 2):?>
	tabControl.SelectTab("tabSelect");
	tabControl.DisableTab("tabSign");
<?elseif ($tabStep == 3):?>
	tabControl.DisableTab("tabSign");
	tabControl.DisableTab("tabSelect");
	<?if ($_REQUEST["action"] == "collect"):?>
		tabControl.SelectTab("tabCollect");
		tabControl.DisableTab("tabCollectResult");
	<?elseif ($_REQUEST["action"] == "verify"):?>
		tabControl.SelectTab("tabFile");
		tabControl.DisableTab("tabVerify");
		tabControl.DisableTab("tabVerifyResult");
	<?endif;?>
<?elseif ($tabStep == 4):?>
	tabControl.DisableTab("tabSign");
	tabControl.DisableTab("tabSelect");
	<?if ($_REQUEST["action"] == "collect"):?>
		tabControl.SelectTab("tabCollectResult");
		tabControl.DisableTab("tabCollect");
	<?elseif ($_REQUEST["action"] == "verify"):?>
		tabControl.DisableTab("tabFile");
		tabControl.DisableTab("tabVerifyResult");
		tabControl.SelectTab("tabVerify");
	<?endif;?>
<?elseif ($tabStep == 5):?>
	tabControl.DisableTab("tabSign");
	tabControl.DisableTab("tabSelect");
	<?if ($_REQUEST["action"] == "collect"):?>
		tabControl.DisableTab("tabCollectResult");
		tabControl.DisableTab("tabCollect");
	<?elseif ($_REQUEST["action"] == "verify"):?>
		tabControl.DisableTab("tabFile");
		tabControl.SelectTab("tabVerifyResult");
		tabControl.DisableTab("tabVerify");
	<?endif;?>
<?endif;?>
//-->
</script>

<?
$legend = "";
if ($tabStep == 1)
	$legend = $isSubscribed? GetMessage("MFCW_LEGEND_SUBSCR_1"): GetMessage("MFCW_LEGEND_NOTSUBSCR_1");
elseif ($tabStep == 2)
	$legend = GetMessage("MFCW_LEGEND_2");
elseif ($tabStep == 3 && $_REQUEST["action"] == "collect")
	$legend = GetMessage("MFCW_LEGEND_3_collect");
elseif ($tabStep == 3 && $_REQUEST["action"] == "verify")
	$legend = GetMessage("MFCW_LEGEND_3_verify");
elseif ($tabStep == 4 && $_REQUEST["action"] == "verify")
	$legend = GetMessage("MFCW_LEGEND_4_verify");

if (strlen($legend) > 0)
	echo BeginNote().$legend.EndNote();
?>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>