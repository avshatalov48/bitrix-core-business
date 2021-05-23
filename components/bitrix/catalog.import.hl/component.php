<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentName */
/** @var string $componentPath */
/** @var string $componentTemplate */
/** @var string $parentComponentName */
/** @var string $parentComponentPath */
/** @var string $parentComponentTemplate */
$this->setFrameMode(false);

$arParams["INTERVAL"] = intval($arParams["INTERVAL"]);

if (!is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);

$arParams["FILE_SIZE_LIMIT"] = intval($arParams["FILE_SIZE_LIMIT"]);
if ($arParams["FILE_SIZE_LIMIT"] < 1)
	$arParams["FILE_SIZE_LIMIT"] = 200*1024; //200KB

$arParams["USE_ZIP"] = false;//TODO: $arParams["USE_ZIP"]!="N";
$arParams["SKIP_ROOT_SECTION"] = $arParams["SKIP_ROOT_SECTION"]=="Y";
if ($arParams["USE_TEMP_DIR"] !== "Y" && $arParams["USE_TEMP_DIR"] !== "N")
	$arParams["USE_TEMP_DIR"] = (defined("BX24_HOST_NAME")? "Y": "N");

if ($arParams["INTERVAL"] <= 0)
	@set_time_limit(0);

$start_time = time();

$bUSER_HAVE_ACCESS = false;
if (isset($USER) && is_object($USER))
{
	$bUSER_HAVE_ACCESS = $USER->IsAdmin();
	if (!$bUSER_HAVE_ACCESS)
	{
		$arUserGroupArray = $USER->GetUserGroupArray();
		foreach ($arParams["GROUP_PERMISSIONS"] as $PERM)
		{
			if (in_array($PERM, $arUserGroupArray))
			{
				$bUSER_HAVE_ACCESS = true;
				break;
			}
		}
	}
}

$bDesignMode = $APPLICATION->GetShowIncludeAreas()
		&& !isset($_GET["mode"])
		&& is_object($USER)
		&& $USER->IsAdmin();

if (!$bDesignMode)
{
	if (!isset($_GET["mode"]))
		return;
	$APPLICATION->RestartBuffer();
	header("Pragma: no-cache");
}

//We have to strongly check all about file names at server side
$FILE_NAME = false;
$ABS_FILE_NAME = false;
$WORK_DIR_NAME = false;

if ($arParams["USE_TEMP_DIR"] === "Y" && $_SESSION["BX_HL_IMPORT"]["TEMP_DIR"] <> '')
	$DIR_NAME = $_SESSION["BX_HL_IMPORT"]["TEMP_DIR"];
else
	$DIR_NAME = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload")."/1c_highloadblock/";

if (
	isset($_GET["filename"])
	&& ($_GET["filename"] <> '')
	&& ($DIR_NAME <> '')
)
{
	//This check for 1c server on linux
	$filename = preg_replace("#^(/tmp/|upload/1c/webdata)#", "", $_GET["filename"]);
	$filename = trim(str_replace("\\", "/", trim($filename)), "/");

	$io = CBXVirtualIo::GetInstance();
	$bBadFile = HasScriptExtension($filename)
		|| IsFileUnsafe($filename)
		|| !$io->ValidatePathString("/".$filename)
	;

	if (!$bBadFile)
	{
		$FILE_NAME = rel2abs($DIR_NAME, "/".$filename);
		if ((mb_strlen($FILE_NAME) > 1) && ($FILE_NAME === "/".$filename))
		{
			$ABS_FILE_NAME = $DIR_NAME.$FILE_NAME;
			$WORK_DIR_NAME = mb_substr($ABS_FILE_NAME, 0, mb_strrpos($ABS_FILE_NAME, "/") + 1);
		}
	}
}

ob_start();

//This is the first exchange command
if ($_GET["mode"] == "checkauth" && $USER->IsAuthorized())
{
	if (
		(COption::GetOptionString("main", "use_session_id_ttl", "N") == "Y")
		&& (COption::GetOptionInt("main", "session_id_ttl", 0) > 0)
		&& !defined("BX_SESSION_ID_CHANGE")
	)
	{
		echo "failure\n",GetMessage("CC_BCIH_ERROR_SESSION_ID_CHANGE");
	}
	else
	{
		echo "success\n";
		echo session_name()."\n";
		echo session_id() ."\n";
		echo bitrix_sessid_get()."\n";
	}
}
//Security checks are follow
elseif (!check_bitrix_sessid())
{
	echo "failure\n",GetMessage("CC_BCIH_ERROR_SOURCE_CHECK");
}
elseif (!$USER->IsAuthorized())
{
	echo "failure\n",GetMessage("CC_BCIH_ERROR_AUTHORIZE");
}
elseif (!$bUSER_HAVE_ACCESS)
{
	echo "failure\n",GetMessage("CC_BCIH_PERMISSION_DENIED");
}
elseif (!CModule::IncludeModule('highloadblock'))
{
	echo "failure\n",GetMessage("CC_BCIH_ERROR_MODULE");
}
//Prepare exchange place
elseif ($_GET["mode"]=="init")
{
	if ($arParams["USE_TEMP_DIR"] === "Y")
	{
		$DIR_NAME = CTempFile::GetDirectoryName(6, "1c_highloadblock");
	}
	else
	{
		//Cleanup previous import files
		$directory = new \Bitrix\Main\IO\Directory($DIR_NAME);
		if ($directory->isExists())
			$directory->delete();
	}

	CheckDirPath($DIR_NAME);
	if (!is_dir($DIR_NAME))
	{
		echo "failure\n",GetMessage("CC_BCIH_ERROR_INIT");
	}
	else
	{
		$_SESSION["BX_HL_IMPORT"] = array(
			"zip" => $arParams["USE_ZIP"] && function_exists("zip_open"),
			"TEMP_DIR" => ($arParams["USE_TEMP_DIR"] === "Y"? $DIR_NAME: ""),
			"NS" => array(
				"XMLPOS" => "",
				"SESSID" => md5($_REQUEST["sessid"]),
			),
		);
		echo "zip=".($_SESSION["BX_HL_IMPORT"]["zip"]? "yes": "no")."\n";
		echo "file_limit=".$arParams["FILE_SIZE_LIMIT"];
	}
}
//Receive files
elseif (($_GET["mode"] == "file") && $ABS_FILE_NAME)
{
	//Read http data
	$DATA = file_get_contents("php://input");
	$DATA_LEN = defined("BX_UTF")? mb_strlen($DATA, 'latin1') : mb_strlen($DATA);

	//And save it the file
	if (isset($DATA) && $DATA !== false)
	{
		CheckDirPath($ABS_FILE_NAME);
		if ($fp = fopen($ABS_FILE_NAME, "ab"))
		{
			$result = fwrite($fp, $DATA);
			if ($result === $DATA_LEN)
			{
				echo "success\n";
				if ($_SESSION["BX_HL_IMPORT"]["zip"])
					$_SESSION["BX_HL_IMPORT"]["zip"] = $ABS_FILE_NAME;
			}
			else
			{
				echo "failure\n",GetMessage("CC_BCIH_ERROR_FILE_WRITE", array("#FILE_NAME#"=>$FILE_NAME));
			}
		}
		else
		{
			echo "failure\n",GetMessage("CC_BCIH_ERROR_FILE_OPEN", array("#FILE_NAME#"=>$FILE_NAME));
		}
	}
	else
	{
		echo "failure\n",GetMessage("CC_BCIH_ERROR_HTTP_READ");
	}
}
//Unpack them if necessary
elseif (($_GET["mode"] == "import") && $_SESSION["BX_HL_IMPORT"]["zip"])
{
	if (!array_key_exists("last_zip_entry", $_SESSION["BX_HL_IMPORT"]))
		$_SESSION["BX_HL_IMPORT"]["last_zip_entry"] = "";

	$result = CIBlockXMLFile::UnZip($_SESSION["BX_HL_IMPORT"]["zip"], $_SESSION["BX_HL_IMPORT"]["last_zip_entry"]);
	if ($result===false)
	{
		echo "failure\n",GetMessage("CC_BCIH_ZIP_ERROR");
	}
	elseif ($result===true)
	{
		$_SESSION["BX_HL_IMPORT"]["zip"] = false;
		echo "progress\n".GetMessage("CC_BCIH_ZIP_DONE");
	}
	else
	{
		$_SESSION["BX_HL_IMPORT"]["last_zip_entry"] = $result;
		echo "progress\n".GetMessage("CC_BCIH_ZIP_PROGRESS");
	}
}
//Step by step import
elseif (($_GET["mode"] == "import") && $ABS_FILE_NAME)
{
	$this->NS = &$_SESSION["BX_HL_IMPORT"]["NS"];
	$this->xmlStream = new CXMLFileStream;

	$this->xmlStream->registerElementHandler(
		"/".GetMessage("CC_BCIH_XML_COM_INFO").
		"/".GetMessage("CC_BCIH_XML_REFERENCES"),
		array($this, "referenceHead")
	);
	$this->xmlStream->registerElementHandler(
		"/".GetMessage("CC_BCIH_XML_COM_INFO").
		"/".GetMessage("CC_BCIH_XML_REFERENCES").
		"/".GetMessage("CC_BCIH_XML_REFERENCE"),
		array($this, "referenceStart")
	);
	$this->xmlStream->registerElementHandler(
		"/".GetMessage("CC_BCIH_XML_COM_INFO").
		"/".GetMessage("CC_BCIH_XML_REFERENCES").
		"/".GetMessage("CC_BCIH_XML_REFERENCE").
		"/".GetMessage("CC_BCIH_XML_FIELDS"),
		array($this, "referenceItemsStart")
	);
	$this->xmlStream->registerNodeHandler(
		"/".GetMessage("CC_BCIH_XML_COM_INFO").
		"/".GetMessage("CC_BCIH_XML_REFERENCES").
		"/".GetMessage("CC_BCIH_XML_REFERENCE").
		"/".GetMessage("CC_BCIH_XML_FIELDS").
		"/".GetMessage("CC_BCIH_XML_FIELD"),
		array($this, "referenceField")
	);
	$this->xmlStream->registerElementHandler(
		"/".GetMessage("CC_BCIH_XML_COM_INFO").
		"/".GetMessage("CC_BCIH_XML_REFERENCES").
		"/".GetMessage("CC_BCIH_XML_REFERENCE").
		"/".GetMessage("CC_BCIH_XML_REFERENCE_ITEMS"),
		array($this, "referenceValuesStart")
	);
	$this->xmlStream->registerNodeHandler(
		"/".GetMessage("CC_BCIH_XML_COM_INFO").
		"/".GetMessage("CC_BCIH_XML_REFERENCES").
		"/".GetMessage("CC_BCIH_XML_REFERENCE").
		"/".GetMessage("CC_BCIH_XML_REFERENCE_ITEMS").
		"/".GetMessage("CC_BCIH_XML_REFERENCE_ITEM"),
		array($this, "referenceValue")
	);

	if ($this->NS["XMLPOS"] === "")
	{
		foreach (GetModuleEvents("catalog", "OnBeforeCatalogImportHL", true) as $arEvent)
		{
			$strError = ExecuteModuleEventEx($arEvent, array($arParams, $ABS_FILE_NAME));
		}
	}

	$this->xmlStream->setPosition($this->NS["XMLPOS"]);
	if ($this->xmlStream->openFile($ABS_FILE_NAME))
	{
		while($this->xmlStream->findNext())
		{
			if ($this->error)
				break;

			if($arParams["INTERVAL"] > 0)
			{
				$this->NS["XMLPOS"] = $this->xmlStream->getPosition();
				if(time()-$start_time > $arParams["INTERVAL"])
				{
					break;
				}
			}

			if ($this->step)
			{
				$this->NS["XMLPOS"] = $this->xmlStream->getPosition();
				break;
			}
		}
	}

	if ($this->error)
	{
		echo "failure\n";
		echo str_replace("<br>", "", $this->error);
	}
	elseif (!$this->xmlStream->endOfFile())
	{
		echo "progress\n",$this->message;
	}
	else
	{
		$this->deleteBySessid($this->NS["HL"], $this->NS["SESSID"]);
		foreach (GetModuleEvents("catalog", "OnSuccessCatalogImportHL", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($arParams, $ABS_FILE_NAME));
		}
		echo "success\n",GetMessage("CC_BCIH_IMPORT_SUCCESS");
		$_SESSION["BX_HL_IMPORT"] = array(
			"zip" => $_SESSION["BX_HL_IMPORT"]["zip"], //save from prev load
			"TEMP_DIR" => $_SESSION["BX_HL_IMPORT"]["TEMP_DIR"], //save from prev load
			"NS" => array(
				"XMLPOS" => "",
			),
		);
	}
}
else
{
	echo "failure\n",GetMessage("CC_BCIH_ERROR_UNKNOWN_COMMAND");
}

$contents = ob_get_contents();
ob_end_clean();

if ($DIR_NAME != "")
{
	$ht_name = $DIR_NAME.".htaccess";
	CheckDirPath($ht_name);
	file_put_contents($ht_name, "Deny from All");
	@chmod($ht_name, BX_FILE_PERMISSIONS);
}

if (!$bDesignMode)
{
	if (toUpper(LANG_CHARSET) != "WINDOWS-1251")
		$contents = $APPLICATION->ConvertCharset($contents, LANG_CHARSET, "windows-1251");
	header("Content-Type: text/html; charset=windows-1251");

	echo $contents;
	die();
}
else
{
	$this->IncludeComponentLang(".parameters.php");
	$arAction = array(
		"N" => GetMessage("CC_BCIH_NONE"),
		"A" => GetMessage("CC_BCIH_DEACTIVATE"),
		"D" => GetMessage("CC_BCIH_DELETE"),
	);

	if (
		(COption::GetOptionString("main", "use_session_id_ttl", "N") == "Y")
		&& (COption::GetOptionInt("main", "session_id_ttl", 0) > 0)
		&& !defined("BX_SESSION_ID_CHANGE")
	)
		ShowError(GetMessage("CC_BCIH_ERROR_SESSION_ID_CHANGE"));
	?><table class="data-table">
	<tr><td><?echo GetMessage("CC_BCIH_INTERVAL")?></td><td><?echo $arParams["INTERVAL"]?></td></tr>
	<tr><td><?echo GetMessage("CC_BCIH_FILE_SIZE_LIMIT")?></td><td><?echo $arParams["FILE_SIZE_LIMIT"]?></td></tr>
	<tr><td><?echo GetMessage("CC_BCIH_USE_ZIP")?></td><td><?echo $arParams["USE_ZIP"]? GetMessage("MAIN_YES"): GetMessage("MAIN_NO")?></td></tr>
	</table>
	<?
}
?>