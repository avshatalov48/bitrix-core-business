<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

if(!$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$ID = intVal($ID);
$arError = $arSmile = $arFields = $arLang = array();

/* LANGS */
$arLangTitle = array("reference_id" => array(), "reference" => array());
$db_res = CLanguage::GetList(($b="sort"), ($o="asc"));
while ($res = $db_res->GetNext(true, false))
{
	$arLang[$res["LID"]] = $res;
	$arLangTitle["reference_id"][] = $res["LID"];
	$arLangTitle["reference"][] = $res["NAME"];
}

$bInitVars = false;
$APPLICATION->SetTitle($ID > 0 ? GetMessage("SMILE_EDIT_RECORD") : GetMessage("SMILE_NEW_RECORD"));

$fileName = '';
if ($REQUEST_METHOD == "POST" && (strlen($save) > 0 || strlen($apply) > 0 || strlen($save_and_add) > 0))
{
	if (isset($_FILES["IMAGE"]["name"]))
		$fileName = RemoveScriptExtension($_FILES["IMAGE"]["name"]);

	if (!check_bitrix_sessid())
	{
		$arError[] = array(
			"id" => "bad_sessid",
			"text" => GetMessage("ERROR_BAD_SESSID"));
	}
	elseif (!empty($_FILES["IMAGE"]["tmp_name"]))
	{
		$sUploadDir = ($_POST['TYPE'] == CSmile::TYPE_ICON? CSmile::PATH_TO_ICON: CSmile::PATH_TO_SMILE).intval($_REQUEST["SET_ID"]).'/';
		CheckDirPath($_SERVER["DOCUMENT_ROOT"].$sUploadDir);
		
		$arSmile = ($ID > 0 ? CSmile::getByID($ID) : $arSmile);
		$res = CFile::CheckImageFile($_FILES["IMAGE"], 300000, 0, 0);
		if (strLen($res) > 0)
		{
			$arError[] = array(
				"id" => "IMAGE", 
				"text" => $res
			);
		}
		elseif (file_exists($_SERVER["DOCUMENT_ROOT"].$sUploadDir . $fileName) && !(isset($arSmile["IMAGE"]) && $arSmile["IMAGE"] == $fileName))
		{
			$arError[] = array(
				"id" => "IMAGE", 
				"text" => GetMessage("ERROR_EXISTS_IMAGE", array("#FILE#" => str_replace("//", "/", $sUploadDir.$fileName)))
			);
		}
		elseif (!@copy($_FILES["IMAGE"]["tmp_name"], $_SERVER["DOCUMENT_ROOT"].$sUploadDir.$fileName))
		{
			$arError[] = array(
				"id" => "IMAGE", 
				"text" => GetMessage("ERROR_COPY_IMAGE"));
		}
		else
		{
			@chmod($_SERVER["DOCUMENT_ROOT"].$sUploadDir.$fileName, BX_FILE_PERMISSIONS);
			$imgArray = CFile::GetImageSize($_SERVER["DOCUMENT_ROOT"].$sUploadDir.$fileName);
			if (is_array($imgArray))
			{
				$arImageSize['WIDTH'] = $imgArray[0];
				$arImageSize['HEIGHT'] = $imgArray[1];
			}
			else
			{
				$arImageSize['WIDTH'] = 0;
				$arImageSize['HEIGHT'] = 0;
			}
		}
	}

	if (empty($arError))
	{
		$GLOBALS["APPLICATION"]->ResetException();
		
		$arFields = array(
			"SET_ID" => $_REQUEST["SET_ID"],
			"SORT" => $_REQUEST["SORT"],
			"TYPE" => $_REQUEST["TYPE"],
			"HIDDEN" => isset($_REQUEST["HIDDEN"])? 'Y': 'N',
			"TYPING" => trim($_REQUEST["TYPING"]),
			"LANG" => array()
		);
		if (!empty($_FILES["IMAGE"]["tmp_name"]))
		{
			$arFields["IMAGE"] = $fileName;
			$arFields["IMAGE_WIDTH"] = $arImageSize['WIDTH'];
			$arFields["IMAGE_HEIGHT"] = $arImageSize['HEIGHT'];
			$arFields["IMAGE_DEFINITION"] = $_REQUEST["IMAGE_DEFINITION"];
		}

		foreach ($arLang as $key => $val)
			$arFields["LANG"][$key] = $_REQUEST["LANG"][$key];

		if ($ID > 0)
		{
			$arSmile = (empty($arSmile) ? CSmile::getByID($ID) : $arSmile);
			CSmile::update($ID, $arFields);
		}
		else
		{
			$ID = CSmile::add($arFields);
		}

		if ($e = $GLOBALS["APPLICATION"]->GetException())
		{
			$arError[] = array(
				"id" => "",
				"text" => $e->getString()
			);
			if (!empty($_FILES["IMAGE"]["tmp_name"]) && isset($sUploadDir))
			{
				@unlink($_SERVER["DOCUMENT_ROOT"].$sUploadDir.$fileName);
				unset($arFields["IMAGE"]);
			}
		}
		else
		{
			if (!empty($arSmile))
			{
				$res = CSmile::getByID($ID);
				if ($arSmile["IMAGE"] != $res["IMAGE"])
				{
					@unlink($_SERVER["DOCUMENT_ROOT"].($arSmile['TYPE'] == CSmile::TYPE_ICON? CSmile::PATH_TO_ICON: CSmile::PATH_TO_SMILE).intval($arSmile["SET_ID"]).'/'.$arSmile["IMAGE"]);
				}
				elseif ($arSmile["TYPE"] != $res["TYPE"] || $arSmile["SET_ID"] != $res["SET_ID"])
				{
					CopyDirFiles(
						$_SERVER["DOCUMENT_ROOT"].($arSmile['TYPE'] == CSmile::TYPE_ICON? CSmile::PATH_TO_ICON: CSmile::PATH_TO_SMILE).intval($arSmile["SET_ID"]).'/'.$arSmile["IMAGE"],
						$_SERVER["DOCUMENT_ROOT"].($res['TYPE'] == CSmile::TYPE_ICON? CSmile::PATH_TO_ICON: CSmile::PATH_TO_SMILE).intval($res["SET_ID"]).'/'.$arSmile["IMAGE"],
						false,
						false,
						true,
						""
					);
				}
			}
			LocalRedirect(strlen($apply) > 0?
				"smile_edit.php?lang=".LANG."&ID=".$ID."&".GetFilterParams("filter_", false) :
				(strlen($save_and_add) > 0 ?
					"smile_edit.php?lang=".LANG."&TYPE=".($arSmile['TYPE'] == CSmile::TYPE_ICON? CSmile::TYPE_ICON: CSmile::TYPE_SMILE)."&SET_ID=".intval($_REQUEST['SET_ID'])."&".GetFilterParams("filter_", false) :
					"smile.php?SET_ID=".intval($_REQUEST['SET_ID'])."&lang=".LANG."&".GetFilterParams("filter_", false))
			);
		}
	}
	$e = new CAdminException($arError);
	$message = new CAdminMessage(($ID > 0 ? GetMessage("ERROR_EDIT_SMILE") : GetMessage("ERROR_ADD_SMILE")), $e);
	$bInitVars = true;
}

if ($bInitVars && !empty($arFields))
{
	if (isset($arFields['LANG']))
		foreach ($arFields['LANG'] as $key => $value)
			$arFields['LANG'][htmlspecialcharsbx($key)] = htmlspecialcharsbx($value);

	$arSmile = array(
		"SORT" => isset($arFields['SORT'])? intval($arFields['SORT']): 300,
		"TYPE" => isset($arFields['TYPE'])? htmlspecialcharsbx($arFields['TYPE']): CSmile::TYPE_SMILE,
		"TYPING" => isset($arFields['TYPING'])? htmlspecialcharsbx($arFields['TYPING']): "",
		"HIDDEN" => isset($arFields['HIDDEN'])? $arFields['HIDDEN']: "N",
		"IMAGE" => "",
		"IMAGE_DEFINITION" => isset($arFields['IMAGE_DEFINITION'])? $arFields['IMAGE_DEFINITION']: CSmile::IMAGE_SD,
		"SET_ID" => isset($arFields['SET_ID'])? intval($arFields['SET_ID']): 0,
		"LANG" => isset($arFields['LANG'])? $arFields['LANG']: array()
	);
}
elseif ($ID > 0)
{
	$arSmile = CSmile::getById($ID, CSmile::GET_ALL_LANGUAGE);
	$arSmile['LANG'] = $arSmile['NAME'];
}
else 
{
	if (isset($_REQUEST['LANG']))
		foreach ($_REQUEST['LANG'] as $key => $value)
			$_REQUEST['LANG'][htmlspecialcharsbx($key)] = htmlspecialcharsbx($value);

	$arSmile = array(
		"SORT" => isset($_REQUEST['SORT'])? intval($_REQUEST['SORT']): 300,
		"TYPE" => isset($_REQUEST['TYPE'])? htmlspecialcharsbx($_REQUEST['TYPE']): CSmile::TYPE_SMILE,
		"TYPING" => isset($_REQUEST['TYPING'])? htmlspecialcharsbx($_REQUEST['TYPING']): "",
		"HIDDEN" => isset($_REQUEST['HIDDEN'])? "Y": "N",
		"IMAGE" => "",
		"IMAGE_DEFINITION" => isset($_REQUEST['IMAGE_DEFINITION'])? $_REQUEST['IMAGE_DEFINITION']: CSmile::IMAGE_SD,
		"SET_ID" => isset($_REQUEST['SET_ID'])? intval($_REQUEST['SET_ID']): 0,
		"LANG" => isset($_REQUEST['LANG'])? $_REQUEST['LANG']: array()
	);
}

$smileSet = CSmileSet::getById($arSmile['SET_ID']);
$arSmile['PARENT_ID'] = $smileSet['PARENT_ID'];

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT" => GetMessage("SMILE_BTN_BACK"),
		"LINK" => "/bitrix/admin/smile.php?SET_ID=".$arSmile['SET_ID']."&lang=".LANG."&".GetFilterParams("filter_", false),
		"ICON" => "btn_list",
	)
);

if ($ID > 0)
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("SMILE_BTN_NEW"),
		"LINK" => "/bitrix/admin/smile_edit.php?lang=".LANG."&SET_ID=".$arSmile['SET_ID']."&".GetFilterParams("filter_", false),
		"ICON" => "btn_new",
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("SMILE_BTN_DELETE"),
		"LINK" => "javascript:if(confirm('".GetMessage("SMILE_BTN_DELETE_CONFIRM")."')) window.location='/bitrix/admin/smile.php?SET_ID=".$arSmile['SET_ID']."&action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"ICON" => "btn_delete",
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
if (isset($message) && $message)
	echo $message->Show();

?>
	<form method="POST" action="<?=$APPLICATION->GetCurPageParam()?>" name="smile_edit" enctype="multipart/form-data">
	<input type="hidden" name="Update" value="Y" />
	<input type="hidden" name="lang" value="<?=LANG?>" />
	<input type="hidden" name="ID" value="<?=$ID?>" />
	<?=bitrix_sessid_post()?>
<?
	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("SMILE_TAB_SMILE"), "ICON" => "smile", "TITLE" => GetMessage("SMILE_TAB_SMILE_DESCR"))
	);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$tabControl->BeginNextTab();
?>
	<tr>
		<td><?=GetMessage("SMILE_TYPE")?>:</td>
		<td>
			<select name="TYPE">
				<option value="<?=CSmile::TYPE_SMILE?>" <?=($arSmile["TYPE"] == CSmile::TYPE_SMILE ? "selected" : "")?>><?=GetMessage("SMILE_TYPE_SMILE");?></option>
				<option value="<?=CSmile::TYPE_ICON?>" <?=($arSmile["TYPE"] == CSmile::TYPE_ICON ? "selected" : "")?>><?=GetMessage("SMILE_TYPE_ICON");?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("SMILE_SET_ID")?>:</td>
		<td>
			<select name="SET_ID">
			<?foreach (CSmileSet::getListForForm($arSmile['PARENT_ID']) as $key => $value):?>
				<option value="<?=$key?>" <?=($arSmile["SET_ID"] == $key ? "selected" : "")?>><?=$value;?></option>
			<?endforeach;?>
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("SMILE_TYPING")?>:<br><small><?=GetMessage("SMILE_TYPING_NOTE")?></small></td>
		<td valign="top">
			<input type="text" name="TYPING" value="<?=$arSmile["TYPING"]?>" size="40" />
		</td>
	</tr>
	<?if (!empty($arSmile["IMAGE"])):?>
	<tr>
		<td>
			<?=GetMessage("SMILE_IMAGE")?>:</td>
		<td>
			<div style="margin-top: 5px">
				<img src="<?=($arSmile["TYPE"] == CSmile::TYPE_ICON? CSmile::PATH_TO_ICON: CSmile::PATH_TO_SMILE).$arSmile["SET_ID"]."/".$arSmile["IMAGE"]?>" width="<?=$arSmile["IMAGE_WIDTH"]?>" height="<?=$arSmile["IMAGE_HEIGHT"]?>" style="vertical-align: text-top" />
				&nbsp;<?=($arSmile["TYPE"] == CSmile::TYPE_ICON? CSmile::PATH_TO_ICON: CSmile::PATH_TO_SMILE).$arSmile["SET_ID"]."/".$arSmile["IMAGE"]?>
			</div>
		</td>
	</tr>
	<?endif;?>
	<tr<?if ($ID <= 0){ ?> class="adm-detail-required-field"<? }?>>
		<td>
			<?=GetMessage(($ID <= 0)?"SMILE_IMAGE" :"SMILE_IMAGE_UPLOAD")?> <span title="<?=GetMessage('SMILE_IMAGE_HR_TITLE_2')?>">(?)</span>:<br><small><?=GetMessage("SMILE_IMAGE_NOTE_2")?></small></td>
		<td>
			<input type="file" name="IMAGE" size="30" />
			<div style="margin-top: 10px">

				<div><label><input type="radio" name="IMAGE_DEFINITION" value="<?=CSmile::IMAGE_SD?>" checked="true" /><?=GetMessage('SMILE_IMAGE_SD')?></label></div>
				<div><label><input type="radio" name="IMAGE_DEFINITION" value="<?=CSmile::IMAGE_HD?>" /><?=GetMessage('SMILE_IMAGE_HD')?></label></div>
				<div><label><input type="radio" name="IMAGE_DEFINITION" value="<?=CSmile::IMAGE_UHD?>" /><?=GetMessage('SMILE_IMAGE_UHD')?></label></div>
		</td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("SMILE_HIDDEN")?>:</td>
		<td width="60%">
			<input type="checkbox" name="HIDDEN" <?=($arSmile["HIDDEN"] == 'Y'? 'checked="true"':'')?> />
		</td>
	</tr>
	<tr>
		<td width="40%"><?=GetMessage("SMILE_SORT")?>:</td>
		<td width="60%">
			<input type="text" name="SORT" value="<?=$arSmile["SORT"]?>" size="10" />
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("SMILE_IMAGE_NAME")?></td>
	</tr>
	<?foreach ($arLang as $key => $val):?>
	<tr>
		<td><? $word = GetMessage('SMILE_IMAGE_NAME_'.strtoupper($key)); if (strlen($word) > 0) { echo $word; } else { echo $val["NAME"]; }?>:</td>
		<td><input type="text" name="LANG[<?=$key?>]" value="<?=$arSmile["LANG"][$key]?>" size="40" /></td>
	</tr>
	<?endforeach;?>

<?
$tabControl->EndTab();

$tabControl->Buttons(array(
	"btnSaveAndAdd" => true,
	"back_url" => "/bitrix/admin/smile.php?SET_ID=".$arSmile['SET_ID']."&lang=".LANG."&".GetFilterParams("filter_", false)));
?>
</form>
<?
$tabControl->End();
$tabControl->ShowWarnings("smile_edit", $message);
?>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>
