<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$rsIBlocks = CIBlock::GetList(array(), array(
	"MIN_PERMISSION" => "X",
	"OPERATION" => "iblock_export",
));
if(!$rsIBlocks->Fetch())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(!isset($INTERVAL))
	$INTERVAL = 30;
else
	$INTERVAL = intval($INTERVAL);
if($INTERVAL <= 0)
	@set_time_limit(0);

$start_time = time();

$arErrors = array();
$arMessages = array();

if($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["Export"]=="Y")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	if(array_key_exists("NS", $_POST) && is_array($_POST["NS"]))
		$NS = $_POST["NS"];
	else
		$NS = array(
			"STEP" => 0,
			"IBLOCK_ID" => $_REQUEST["IBLOCK_ID"],
			"URL_DATA_FILE" => $_REQUEST["URL_DATA_FILE"],
			"SECTIONS_FILTER" => $_REQUEST["SECTIONS_FILTER"],
			"ELEMENTS_FILTER" => $_REQUEST["ELEMENTS_FILTER"],
			"DOWNLOAD_CLOUD_FILES" => $_REQUEST["DOWNLOAD_CLOUD_FILES"] === "N"? "N": "Y",
			"next_step" => array(),
		);

	$NS["catalog"] = CModule::IncludeModule('catalog');

	//We have to strongly check all about file names at server side
	$ABS_FILE_NAME = false;
	$WORK_DIR_NAME = false;
	if(isset($NS["URL_DATA_FILE"]) && ($NS["URL_DATA_FILE"] <> ''))
	{
		$filename = trim(str_replace("\\", "/", trim($NS["URL_DATA_FILE"])), "/");
		if (
			preg_match('/[^a-zA-Z0-9\s!#\$%&\(\)\[\]\{\}+\.;=@\^_\~\/\\\\\-]/i', $filename)
			|| HasScriptExtension($filename)
		)
		{
			$arErrors[] = GetMessage("IBLOCK_CML2_FILE_NAME_ERROR");
		}
		else
		{
			$FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$filename);
			if((mb_strlen($FILE_NAME) > 1) && ($FILE_NAME === "/".$filename))
			{
				$ABS_FILE_NAME = $_SERVER["DOCUMENT_ROOT"].$FILE_NAME;
				if (mb_strtolower(mb_substr($ABS_FILE_NAME, -4)) != ".xml")
					$ABS_FILE_NAME .= ".xml";
				$WORK_DIR_NAME = mb_substr($ABS_FILE_NAME, 0, mb_strrpos($ABS_FILE_NAME, "/") + 1);
			}
		}
	}

	$fp = false;
	if(!check_bitrix_sessid())
	{
		$arErrors[] = GetMessage("IBLOCK_CML2_ACCESS_DENIED");
	}
	elseif(!CIBlockRights::UserHasRightTo($NS["IBLOCK_ID"], $NS["IBLOCK_ID"], "iblock_export"))
	{
		$arErrors[] = GetMessage("IBLOCK_CML2_ACCESS_DENIED");
	}
	elseif($ABS_FILE_NAME && (count($arErrors) == 0))
	{
		if($NS["STEP"] < 1)
		{
			$_SESSION["BX_CML2_EXPORT"] = array(
				"PROPERTY_MAP" => false,
				"SECTION_MAP" => false,
				"PRICES_MAP" => false,
				"work_dir" => false,
				"file_dir" => false,
			);
			if($fp = fopen($ABS_FILE_NAME, "wb"))
			{
				@chmod($ABS_FILE_NAME, BX_FILE_PERMISSIONS);
				if(mb_strtolower(mb_substr($ABS_FILE_NAME, -4)) == ".xml")
				{
					$DIR_NAME = mb_substr($ABS_FILE_NAME, 0, -4)."_files";
					if(
						is_dir($DIR_NAME)
						|| @mkdir($DIR_NAME, BX_DIR_PERMISSIONS)
					)
					{
						$_SESSION["BX_CML2_EXPORT"]["work_dir"] = $WORK_DIR_NAME;
						$_SESSION["BX_CML2_EXPORT"]["file_dir"] = mb_substr($DIR_NAME."/", mb_strlen($WORK_DIR_NAME));
					}
				}
			}
			else
			{
				$arErrors[] = GetMessage("IBLOCK_CML2_FILE_ERROR");
			}
			$NS["STEP"]++;
		}
		elseif($NS["STEP"] < 4)
		{
			if($fp = fopen($ABS_FILE_NAME, "ab"))
			{
				$obExport = new CIBlockCMLExport;
				if($obExport->Init($fp, $NS["IBLOCK_ID"], $NS["next_step"], true, $_SESSION["BX_CML2_EXPORT"]["work_dir"], $_SESSION["BX_CML2_EXPORT"]["file_dir"]))
				{
					if($NS["DOWNLOAD_CLOUD_FILES"] === "N")
						$obExport->DoNotDownloadCloudFiles();

					if($NS["STEP"]==1)
					{
						$obExport->StartExport();
						$obExport->StartExportMetadata();
						$obExport->ExportProperties($_SESSION["BX_CML2_EXPORT"]["PROPERTY_MAP"]);
						$NS["STEP"]++;
					}
					elseif($NS["STEP"]==2)
					{
						$result = $obExport->ExportSections(
							$_SESSION["BX_CML2_EXPORT"]["SECTION_MAP"],
							$start_time,
							$INTERVAL,
							$NS["SECTIONS_FILTER"],
							$_SESSION["BX_CML2_EXPORT"]["PROPERTY_MAP"]
						);
						if($result)
						{
							$NS["SECTIONS"] += $result;
						}
						else
						{
							$obExport->EndExportMetadata();
							$obExport->StartExportCatalog();
							$NS["STEP"]++;
						}
					}
					elseif($NS["STEP"]==3)
					{
						$result = $obExport->ExportElements(
							$_SESSION["BX_CML2_EXPORT"]["PROPERTY_MAP"],
							$_SESSION["BX_CML2_EXPORT"]["SECTION_MAP"],
							$start_time,
							$INTERVAL,
							0,
							$NS["ELEMENTS_FILTER"]
						);
						if($result)
						{
							$NS["ELEMENTS"] += $result;
						}
						else
						{
							$obExport->EndExportCatalog();
							$obExport->ExportProductSets();
							$obExport->EndExport();
							$NS["STEP"]++;
						}
					}
					else
						$NS["STEP"]++;
					$NS["next_step"] = $obExport->next_step;
				}
				else
				{
					$arErrors[] = GetMessage("IBLOCK_CML2_IBLOCK_ERROR");
				}
			}
			else
			{
				$arErrors[] = GetMessage("IBLOCK_CML2_FILE_ERROR")."(1)";
			}
		}
	}
	else
	{
		$arErrors[] = GetMessage("IBLOCK_CML2_FILE_ERROR")."(2)";
	}

	if($fp)
		fclose($fp);

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
		if($NS["STEP"] < 4)
		{
			$progressItems = array(
				GetMessage("IBLOCK_CML2_METADATA_DONE"),
			);

			if($NS["STEP"] < 2)
				$progressItems[] = GetMessage("IBLOCK_CML2_SECTIONS");
			elseif($NS["STEP"] < 3)
				$progressItems[] = "<b>".GetMessage("IBLOCK_CML2_SECTIONS_PROGRESS", array("#COUNT#"=>intval($NS["SECTIONS"])))."</b>";
			else
				$progressItems[] = GetMessage("IBLOCK_CML2_SECTIONS_PROGRESS", array("#COUNT#"=>intval($NS["SECTIONS"])));

			if($NS["STEP"] < 3)
				$progressItems[] = GetMessage("IBLOCK_CML2_ELEMENTS");
			elseif($NS["STEP"] < 4)
				$progressItems[] = "<b>".GetMessage("IBLOCK_CML2_ELEMENTS_PROGRESS", array("#COUNT#"=>intval($NS["ELEMENTS"])))."</b>";
			else
				$progressItems[] = GetMessage("IBLOCK_CML2_ELEMENTS_PROGRESS", array("#COUNT#"=>intval($NS["ELEMENTS"])));

			CAdminMessage::ShowMessage(array(
				"DETAILS" => "<p>".implode("</p><p>", $progressItems)."</p>",
				"HTML" => true,
				"TYPE" => "PROGRESS",
			));

			if($NS["STEP"] > 0)
				echo '<script>DoNext('.CUtil::PhpToJSObject(array("NS"=>$NS)).');</script>';
		}
		else
		{
			$progressItems = array(
				GetMessage("IBLOCK_CML2_DONE_SECTIONS", array("#COUNT#"=>intval($NS["SECTIONS"]))),
				GetMessage("IBLOCK_CML2_DONE_ELEMENTS", array("#COUNT#"=>intval($NS["ELEMENTS"]))),
			);

			CAdminMessage::ShowMessage(array(
				"MESSAGE" => GetMessage("IBLOCK_CML2_DONE"),
				"DETAILS" => "<p>".implode("</p><p>", $progressItems)."</p>",
				"HTML" => true,
				"TYPE" => "PROGRESS",
			));

			echo '<script>EndExport();</script>';
		}
	}
	else
	{
		echo '<script>EndExport();</script>';
	}
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
}

$APPLICATION->SetTitle(GetMessage("IBLOCK_CML2_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<div id="tbl_iblock_export_result_div"></div>
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

<script>
var running = false;

function DoNext(NS)
{
	var interval = parseInt(document.getElementById('INTERVAL').value);
	var queryString =
		'Export=Y'
		+ '&lang=<?=LANGUAGE_ID?>'
		+ '&<?echo bitrix_sessid_get()?>'
		+ '&INTERVAL=' + interval
	;

	if(!NS)
	{
		queryString+='&URL_DATA_FILE='+jsUtils.urlencode(document.getElementById('URL_DATA_FILE').value);
		queryString+='&IBLOCK_ID='+jsUtils.urlencode(document.getElementById('IBLOCK_ID').value);
		queryString+='&SECTIONS_FILTER='+jsUtils.urlencode(document.getElementById('SECTIONS_FILTER').value);
		queryString+='&ELEMENTS_FILTER='+jsUtils.urlencode(document.getElementById('ELEMENTS_FILTER').value);
		if(document.getElementById('CK_DOWNLOAD_CLOUD_FILES'))
			queryString+='&DOWNLOAD_CLOUD_FILES='+(document.getElementById('CK_DOWNLOAD_CLOUD_FILES').checked? 'Y': 'N');
	}

	if(running)
	{
		ShowWaitWindow();
		BX.ajax.post(
			'iblock_xml_export.php?'+queryString,
			NS,
			function(result){
				document.getElementById('tbl_iblock_export_result_div').innerHTML = result;
			}
		);
	}
}
function StartExport()
{
	running = document.getElementById('start_button').disabled = true;
	DoNext();
}
function EndExport()
{
	running = document.getElementById('start_button').disabled = false;
}
</script>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo htmlspecialcharsbx(LANG)?>" name="form1" id="form1">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr>
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
					"operation" => 'S',// O - open, S - save
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
	<tr>
		<td><?echo GetMessage("IBLOCK_CML2_IBLOCK_ID")?>:</td>
		<td>
			<?echo GetIBlockDropDownListEx(
				$IBLOCK_ID,
				'IBLOCK_TYPE_ID',
				'IBLOCK_ID',
				array(
					"MIN_PERMISSION" => "X",
					"OPERATION" => "iblock_export",
				),
				'',
				'',
				'class="adm-detail-iblock-types"',
				'class="adm-detail-iblock-list"'
			);?>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_CML2_INTERVAL")?>:</td>
		<td>
			<input type="text" id="INTERVAL" name="INTERVAL" size="5" value="<?echo intval($INTERVAL)?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_CML2_SECTIONS_FILTER")?>:</td>
		<td>
			<select id="SECTIONS_FILTER" name="SECTIONS_FILTER">
				<option value="active"><?echo GetMessage("IBLOCK_CML2_FILTER_ACTIVE")?></option>
				<option value="all"><?echo GetMessage("IBLOCK_CML2_FILTER_ALL")?></option>
				<option value="none"><?echo GetMessage("IBLOCK_CML2_FILTER_NONE")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_CML2_ELEMENTS_FILTER")?>:</td>
		<td>
			<select id="ELEMENTS_FILTER" name="ELEMENTS_FILTER">
				<option value="active"><?echo GetMessage("IBLOCK_CML2_FILTER_ACTIVE")?></option>
				<option value="all"><?echo GetMessage("IBLOCK_CML2_FILTER_ALL")?></option>
				<option value="none"><?echo GetMessage("IBLOCK_CML2_FILTER_NONE")?></option>
			</select>
		</td>
	</tr>
	<?
	$bHaveClouds = false;
	if(CModule::IncludeModule("clouds"))
	{
		$rsData = CCloudStorageBucket::GetList(array("SORT"=>"DESC", "ID"=>"ASC"));
		if($rsData->Fetch())
			$bHaveClouds = true;
	}
	if($bHaveClouds):?>
	<tr>
		<td><label for="CK_DOWNLOAD_CLOUD_FILES"><?echo GetMessage("IBLOCK_CML2_DOWNLOAD_CLOUD_FILES")?>:</label></td>
		<td>
			<input name="DOWNLOAD_CLOUD_FILES" type="hidden" value="N">
			<input name="DOWNLOAD_CLOUD_FILES" id="CK_DOWNLOAD_CLOUD_FILES" type="checkbox" value="Y" checked="checked">
		</td>
	</tr>
	<?endif;?>
<?$tabControl->Buttons();?>
	<input type="button" id="start_button" value="<?echo GetMessage("IBLOCK_CML2_START_EXPORT")?>" OnClick="StartExport();" class="adm-btn-save">
	<input type="button" id="stop_button" value="<?echo GetMessage("IBLOCK_CML2_STOP_EXPORT")?>" OnClick="EndExport();">
<?$tabControl->End();?>
</form>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
