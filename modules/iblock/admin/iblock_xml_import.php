<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

if(!$USER->IsAdmin())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(!isset($INTERVAL))
	$INTERVAL = 30;
else
	$INTERVAL = intval($INTERVAL);

@set_time_limit(0);

$start_time = time();

$arErrors = array();
$arMessages = array();

if($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["Import"]=="Y")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	//Initialize NS variable which will save step data
	if(array_key_exists("NS", $_POST) && is_array($_POST["NS"]))
	{
		$NS = $_POST["NS"];
		if(array_key_exists("charset", $NS) && $NS["charset"] === "false") $NS["charset"] = false;
		if(array_key_exists("PREVIEW", $NS) && $NS["PREVIEW"] === "false") $NS["PREVIEW"] = false;
		if(array_key_exists("bOffer", $NS) && $NS["bOffer"] === "false") $NS["bOffer"] = false;
	}
	else
	{
		$NS = array(
			"STEP" => 0,
			"IBLOCK_TYPE" => $_REQUEST["IBLOCK_TYPE"],
			"LID" => is_array($_REQUEST["LID"])? $_REQUEST["LID"]: array(),
			"URL_DATA_FILE" => $_REQUEST["URL_DATA_FILE"],
			"ACTION" => $_REQUEST["outFileAction"],
			"PREVIEW" => $_REQUEST["GENERATE_PREVIEW"]==="Y",
		);
	}

	//We have to strongly check all about file names at server side
	$ABS_FILE_NAME = false;
	$WORK_DIR_NAME = false;
	if(isset($NS["URL_DATA_FILE"]) && ($NS["URL_DATA_FILE"] <> ''))
	{
		$filename = trim(str_replace("\\", "/", trim($NS["URL_DATA_FILE"])), "/");
		$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$filename);
		if((mb_strlen($FILE_NAME) > 1) && ($FILE_NAME === "/".$filename) && ($APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W"))
		{
			$ABS_FILE_NAME = $_SERVER["DOCUMENT_ROOT"].$FILE_NAME;
			$WORK_DIR_NAME = mb_substr($ABS_FILE_NAME, 0, mb_strrpos($ABS_FILE_NAME, "/") + 1);
		}
	}

	//This object will load file into database
	$obXMLFile = new CIBlockXMLFile;

	if(!check_bitrix_sessid())
	{
		$arErrors[] = GetMessage("IBLOCK_CML2_ACCESS_DENIED");
	}
	elseif($ABS_FILE_NAME)
	{
		if($NS["STEP"] < 1)
		{
			//This will save mapping for ID to XML_ID translation
			$_SESSION["BX_CML2_IMPORT"] = array(
				"SECTION_MAP" => false,
				"PRICES_MAP" => false,
			);

			$obXMLFile->DropTemporaryTables();
			if(CIBlockCMLImport::CheckIfFileIsCML($ABS_FILE_NAME))
				$NS["STEP"]++;
			else
				$arErrors[] = GetMessage("IBLOCK_CML2_WRONG_FILE_ERROR");
		}
		elseif($NS["STEP"] < 2)
		{
			if($obXMLFile->CreateTemporaryTables())
				$NS["STEP"]++;
			else
				$arErrors[] = GetMessage("IBLOCK_CML2_TABLE_CREATE_ERROR");
		}
		elseif($NS["STEP"] < 3)
		{
			if(file_exists($ABS_FILE_NAME) && is_file($ABS_FILE_NAME) && ($fp = fopen($ABS_FILE_NAME, "rb")))
			{
				if($obXMLFile->ReadXMLToDatabase($fp, $NS, $INTERVAL))
					$NS["STEP"]++;
				fclose($fp);
			}
			else
			{
				$arErrors[] = GetMessage("IBLOCK_CML2_FILE_ERROR");
			}
		}
		elseif($NS["STEP"] < 4)
		{
			if($obXMLFile->IndexTemporaryTables())
				$NS["STEP"]++;
			else
				$arErrors[] = GetMessage("IBLOCK_CML2_INDEX_ERROR");
		}
		elseif($NS["STEP"] < 5)
		{
			$obCatalog = new CIBlockCMLImport;
			$obCatalog->Init($NS, $WORK_DIR_NAME, true, $NS["PREVIEW"], false, true);
			$result = $obCatalog->ImportMetaData(array(1, 2), $NS["IBLOCK_TYPE"], $NS["LID"]);
			if($result === true)
			{
				$result = $obCatalog->ImportSections();
				if($result === true)
					$obCatalog->DeactivateSections("A");
			}

			if($result === true)
				$NS["STEP"]++;
			else
				$arErrors[] = $result;
		}
		elseif($NS["STEP"] < 6)
		{
			if(($NS["DONE"]["ALL"] <= 0) && $NS["XML_ELEMENTS_PARENT"])
			{
				$rs = $DB->Query("select count(*) C from b_xml_tree where PARENT_ID = ".intval($NS["XML_ELEMENTS_PARENT"]));
				$ar = $rs->Fetch();
				$NS["DONE"]["ALL"] = $ar["C"];
			}

			$obCatalog = new CIBlockCMLImport;
			$obCatalog->Init($NS, $WORK_DIR_NAME, true, $NS["PREVIEW"], false, true);
			$obCatalog->ReadCatalogData($_SESSION["BX_CML2_IMPORT"]["SECTION_MAP"], $_SESSION["BX_CML2_IMPORT"]["PRICES_MAP"]);
			$result = $obCatalog->ImportElements($start_time, $INTERVAL);

			$counter = 0;
			foreach($result as $key=>$value)
			{
				$NS["DONE"][$key] += $value;
				$counter+=$value;
			}

			if(!$counter)
				$NS["STEP"]++;
		}
		elseif($NS["STEP"] < 7)
		{
			$obCatalog = new CIBlockCMLImport;
			$obCatalog->Init($NS, $WORK_DIR_NAME, true, $NS["PREVIEW"], false, true);
			$result = $obCatalog->DeactivateElement($NS["ACTION"], $start_time, $INTERVAL);

			$counter = 0;
			foreach($result as $key=>$value)
			{
				$NS["DONE"][$key] += $value;
				$counter+=$value;
			}

			if(!$counter)
				$NS["STEP"]++;
		}
		elseif($NS["STEP"] < 8)
		{
			$obCatalog = new CIBlockCMLImport;
			$obCatalog->Init($NS, $WORK_DIR_NAME, true, $NS["PREVIEW"], false, true);
			$obCatalog->ImportProductSets();
			$NS["STEP"]++;
		}
	}
	else
	{
		$arErrors[] = GetMessage("IBLOCK_CML2_FILE_ERROR");
	}

	?>
	<script>
		CloseWaitWindow();
	</script>
	<?

	foreach($arErrors as $strError)
		CAdminMessage::ShowMessage($strError);
	foreach($arMessages as $strMessage)
		CAdminMessage::ShowMessage(array("MESSAGE"=>$strMessage,"TYPE"=>"OK"));

	if(count($arErrors) == 0)
	{
		if($NS["STEP"] < 8)
		{
			$progressItems = array(
				GetMessage("IBLOCK_CML2_TABLES_DROPPED"),
			);
			$progressTotal = 0;
			$progressValue = 0;

			if($NS["STEP"] < 1)
				echo GetMessage("IBLOCK_CML2_TABLES_CREATION");
			elseif($NS["STEP"] < 2)
				echo "<b>".GetMessage("IBLOCK_CML2_TABLES_CREATION")."</b>";
			else
				echo GetMessage("IBLOCK_CML2_TABLES_CREATED");

			if($NS["STEP"] < 2)
				$progressItems[] = GetMessage("IBLOCK_CML2_FILE_READING");
			elseif($NS["STEP"] < 3)
			{
				if(file_exists($ABS_FILE_NAME))
					$file_size = filesize($ABS_FILE_NAME);
				else
					$file_size = 0;
				$progressItems[] = "<b>".GetMessage("IBLOCK_CML2_FILE_PROGRESS2")."</b><br>#PROGRESS_BAR#";
				$progressTotal = $file_size;
				$progressValue = $obXMLFile->GetFilePosition();
			}
			else
				$progressItems[] = GetMessage("IBLOCK_CML2_FILE_READ");

			if($NS["STEP"] < 3)
				$progressItems[] = GetMessage("IBLOCK_CML2_INDEX_CREATION");
			elseif($NS["STEP"] < 4)
				$progressItems[] = "<b>".GetMessage("IBLOCK_CML2_INDEX_CREATION")."</b>";
			else
				$progressItems[] = GetMessage("IBLOCK_CML2_INDEX_CREATED");

			if($NS["STEP"] < 4)
				$progressItems[] = GetMessage("IBLOCK_CML2_METADATA");
			elseif($NS["STEP"] < 5)
				$progressItems[] = "<b>".GetMessage("IBLOCK_CML2_METADATA")."</b>";
			else
				$progressItems[] = GetMessage("IBLOCK_CML2_METADATA_DONE");

			if($NS["STEP"] < 5)
				$progressItems[] = GetMessage("IBLOCK_CML2_ELEMENTS");
			elseif($NS["STEP"] < 6)
			{
				$progressItems[] = "<b>".GetMessage("IBLOCK_CML2_ELEMENTS_PROGRESS", array(
					"#DONE#" => intval($NS["DONE"]["CRC"]),
					"#TOTAL#"=> intval($NS["DONE"]["ALL"]),
				))."</b><br>";
				$progressTotal = intval($NS["DONE"]["ALL"]);
				$progressValue = intval($NS["DONE"]["CRC"]);
			}
			else
				$progressItems[] = GetMessage("IBLOCK_CML2_ELEMENTS_DONE");

			if($NS["ACTION"]=="A" || $NS["ACTION"]=="D")
			{
				if($NS["ACTION"]=="A")
					if($NS["STEP"] < 6)
						$progressItems[] = GetMessage("IBLOCK_CML2_DEACTIVATION");
					elseif($NS["STEP"] < 7)
						$progressItems[] = "<b>".GetMessage("IBLOCK_CML2_DEACTIVATION_PROGRESS", array(
							"#DONE#" => intval($NS["DONE"]["DEA"]),
						))."</b>";
					else
						$progressItems[] = GetMessage("IBLOCK_CML2_DEACTIVATION_DONE");
				else
					if($NS["STEP"] < 6)
						$progressItems[] = GetMessage("IBLOCK_CML2_DELETE");
					elseif($NS["STEP"] < 7)
						$progressItems[] = "<b>".GetMessage("IBLOCK_CML2_DELETE_PROGRESS", array(
							"#DONE#" => intval($NS["DONE"]["DEL"]),
						))."</b>";
					else
						$progressItems[] = GetMessage("IBLOCK_CML2_DELETE_DONE");
			}

			CAdminMessage::ShowMessage(array(
				"DETAILS" => "<p>".implode("</p><p>", $progressItems)."</p>",
				"HTML" => true,
				"TYPE" => "PROGRESS",
				"PROGRESS_TOTAL" => $progressTotal,
				"PROGRESS_VALUE" => $progressValue,
			));

			if($NS["STEP"] > 0)
				echo '<script>DoNext('.CUtil::PhpToJSObject(array("NS"=>$NS)).');</script>';
		}
		else
		{
			$progressItems = array(
				GetMessage("IBLOCK_CML2_ADDED", array("#COUNT#"=>intval($NS["DONE"]["ADD"]))),
				GetMessage("IBLOCK_CML2_UPDATED", array("#COUNT#"=>intval($NS["DONE"]["UPD"]))),
				GetMessage("IBLOCK_CML2_DELETED", array("#COUNT#"=>intval($NS["DONE"]["DEL"]))),
				GetMessage("IBLOCK_CML2_DEACTIVATED", array("#COUNT#"=>intval($NS["DONE"]["DEA"]))),
				GetMessage("IBLOCK_CML2_WITH_ERRORS", array("#COUNT#"=>intval($NS["DONE"]["ERR"]))),
			);

			CAdminMessage::ShowMessage(array(
				"MESSAGE" => GetMessage("IBLOCK_CML2_DONE"),
				"DETAILS" => "<p>".implode("</p><p>", $progressItems)."</p>",
				"HTML" => true,
				"TYPE" => "PROGRESS",
				"BUTTONS" => array(
					array(
						"VALUE" => GetMessage("IBLOCK_CML2_ELEMENTS_LIST"),
						"ONCLICK" => "window.location = '".CUtil::JSEscape(CIBlock::GetAdminElementListLink(
							$NS["IBLOCK_ID"] , array('find_el_y'=>'Y', 'clear_filter'=>'Y', 'apply_filter'=>'Y')))."';",
					),
				),
			));

			echo '<script>EndImport();</script>';
		}
	}
	else
	{
		echo '<script>EndImport();</script>';
	}

	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
}

$APPLICATION->SetTitle(GetMessage("IBLOCK_CML2_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<div id="tbl_iblock_import_result_div"></div>
<?
$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("IBLOCK_CML2_TAB"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage("IBLOCK_CML2_TAB_TITLE"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);
?>
<script language="JavaScript" type="text/javascript">
var running = false;
var oldNS = '';
function DoNext(NS)
{
	var interval = parseInt(document.getElementById('INTERVAL').value);
	var queryString = 'Import=Y'
		+ '&lang=<?echo LANG?>'
		+ '&<?echo bitrix_sessid_get()?>'
		+ '&INTERVAL=' + interval;
	;

	if(!NS)
	{
		//Check for necessery fields
		if('['+document.form1.elements['LID[]'].type+']'=='[undefined]')
			lid = document.form1.elements['LID[]'];
		else
			lid = new Array(document.form1.elements['LID[]']);
		var found = false;
		for(var i=0; i < lid.length; i++)
		{
			if(lid[i].checked)
			{
				queryString += '&LID[]=' + lid[i].value;
				found = true;
			}
		}
		if(!found)
		{
			alert('<?echo GetMessage("IBLOCK_CML2_LID_ERROR")?>');
			EndImport();
			return;
		}
		//Make URL
		queryString+='&URL_DATA_FILE='+document.getElementById('URL_DATA_FILE').value;
		queryString+='&IBLOCK_TYPE='+document.getElementById('IBLOCK_TYPE').value;
		if(document.getElementById('outFileAction_N').checked)
			queryString+='&outFileAction='+document.getElementById('outFileAction_N').value;
		if(document.getElementById('outFileAction_A').checked)
			queryString+='&outFileAction='+document.getElementById('outFileAction_A').value;
		if(document.getElementById('outFileAction_D').checked)
			queryString+='&outFileAction='+document.getElementById('outFileAction_D').value;
		if(document.getElementById('GENERATE_PREVIEW').checked)
			queryString+='&GENERATE_PREVIEW='+document.getElementById('GENERATE_PREVIEW').value;
	}

	if(running)
	{
		ShowWaitWindow();
		BX.ajax.post(
			'iblock_xml_import.php?'+queryString,
			NS,
			function(result){
				document.getElementById('tbl_iblock_import_result_div').innerHTML = result;
			}
		);
	}
}
function StartImport()
{
	running = document.getElementById('start_button').disabled = true;
	DoNext();
}
function EndImport()
{
	running = document.getElementById('start_button').disabled = false;
}
</script>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo htmlspecialcharsbx(LANG)?>" name="form1" id="form1">
<?
$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
$arIBlockType = array("reference"=>array(), "reference_id"=>array());
while($arr = $rsIBlockType->Fetch())
{
	if($ar = CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
	{
		$arIBlockType["reference"][] = "[".$arr["ID"]."] ".$ar["~NAME"];
		$arIBlockType["reference_id"][] = $arr["ID"];
	}
}

$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("IBLOCK_CML2_URL_DATA_FILE")?>:</td>
		<td width="60%">
			<input type="text" id="URL_DATA_FILE" name="URL_DATA_FILE" size="30" value="<?=htmlspecialcharsbx($URL_DATA_FILE)?>">
			<input type="button" value="<?echo GetMessage("IBLOCK_CML2_OPEN")?>" OnClick="BtnClick()">
			<?
			CAdminFileDialog::ShowScript
			(
				Array(
					"event" => "BtnClick",
					"arResultDest" => array("FORM_NAME" => "form1", "FORM_ELEMENT_NAME" => "URL_DATA_FILE"),
					"arPath" => array("SITE" => SITE_ID, "PATH" =>"/upload"),
					"select" => 'F',// F - file only, D - folder only
					"operation" => 'O',
					"showUploadTab" => true,
					"showAddToMenuTab" => false,
					"fileFilter" => 'xml',
					"allowAllFiles" => true,
					"SaveConfig" => true,
				)
			);
			?>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("IBLOCK_CML2_IBLOCK_TYPE")?>:</td>
		<td><?=SelectBoxFromArray("IBLOCK_TYPE", $arIBlockType, $IBLOCK_TYPE, "", "");?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><?echo GetMessage("IBLOCK_CML2_LID")?></td>
		<td><?echo CLang::SelectBoxMulti("LID", htmlspecialcharsbx($LID));?></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("IBLOCK_CML2_ACTION")?>:</td>
		<td>
			<input type="radio" name="outFileAction" value="N" id="outFileAction_N" checked="checked"><label for="outFileAction_N"><?echo GetMessage("IBLOCK_CML2_ACTION_NONE")?></label><br>
			<input type="radio" name="outFileAction" value="A" id="outFileAction_A"><label for="outFileAction_A"><?echo GetMessage("IBLOCK_CML2_ACTION_DEACTIVATE")?></label><br>
			<input type="radio" name="outFileAction" value="D" id="outFileAction_D"><label for="outFileAction_D"><?echo GetMessage("IBLOCK_CML2_ACTION_DELETE")?></label><br>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_CML2_INTERVAL")?>:</td>
		<td>
			<input type="text" id="INTERVAL" name="INTERVAL" size="5" value="<?echo intval($INTERVAL)?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_CML2_IMAGE_RESIZE")?>:</td>
		<td>
			<input type="checkbox" id="GENERATE_PREVIEW" name="GENERATE_PREVIEW" value="Y" checked>
		</td>
	</tr>
<?$tabControl->Buttons();?>
	<input type="button" id="start_button" value="<?echo GetMessage("IBLOCK_CML2_START_IMPORT")?>" OnClick="StartImport();" class="adm-btn-save">
	<input type="button" id="stop_button" value="<?echo GetMessage("IBLOCK_CML2_STOP_IMPORT")?>" OnClick="EndImport();">
<?$tabControl->End();?>
</form>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
