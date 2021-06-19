<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

use Bitrix\Main\Loader;

Loader::includeModule('socialnetwork');

$sonetPermissions = $APPLICATION->GetGroupRight("socialnetwork");
if ($sonetPermissions < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/include.php");

IncludeModuleLangFile(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/prolog.php");

ClearVars();

$ID = intval($ID);
$arSysLangs = array();
$arSysLangNames = array();

$db_lang = CLangAdmin::GetList();
$langCount = 0;
while ($arLang = $db_lang->Fetch())
{
	$arSysLangs[$langCount] = $arLang["LID"];
	$arSysLangNames[$langCount] = htmlspecialcharsbx($arLang["NAME"]);
	$langCount++;
}

$strErrorMessage = "";
$bInitVars = false;
if (($save <> '' || $apply <> '') && $REQUEST_METHOD=="POST" && $sonetPermissions=="W" && check_bitrix_sessid())
{
	$SORT = intval($SORT);
	if ($SORT<=0) $SORT = 150;

	if ($SMILE_TYPE!="S" && $SMILE_TYPE!="I")
		$strErrorMessage .= GetMessage("ERROR_NO_TYPE").". \n";

	for ($i = 0; $i<count($arSysLangs); $i++)
	{
		${"NAME_".$arSysLangs[$i]} = Trim(${"NAME_".$arSysLangs[$i]});
		if (${"NAME_".$arSysLangs[$i]} == '')
			$strErrorMessage .= GetMessage("ERROR_NO_NAME")." [".$arSysLangs[$i]."] ".$arSysLangNames[$i].". \n";
	}

	if ($ID<=0 && (!is_set($_FILES, "IMAGE1") || $_FILES["IMAGE1"]["name"] == ''))
		$strErrorMessage .= GetMessage("ERROR_NO_IMAGE").". \n";

	$strFileName = "";
	if ($strErrorMessage == '')
	{
		$arOldSmile = false;
		if ($ID>0) $arOldSmile = CSocNetSmile::GetByID($ID);

		if (is_set($_FILES, "IMAGE1") && $_FILES["IMAGE1"]["name"] <> '')
		{
			$res = CFile::CheckImageFile($_FILES["IMAGE1"], 0, 0, 0);

			if ($res <> '')
				$strErrorMessage .= $res."\n";
			else
			{
				$io = CBXVirtualIo::GetInstance();

				$strFileName = basename($_FILES["IMAGE1"]["name"]);
				$strFileExt = strrchr($_FILES["IMAGE1"]["name"], ".");

				if(
					!$io->ValidateFilenameString($strFileName)
					|| HasScriptExtension($strFileName)
				)
					$strErrorMessage .= GetMessage("FSE_ERROR_EXT").". \n";
			}

			if ($strErrorMessage == '')
			{
				$strDirName = $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/socialnetwork/";
				if ($SMILE_TYPE=="I") $strDirName .= "icon";
				else $strDirName .= "smile";
				$strDirName .= "/";

				CheckDirPath($strDirName);

				if (file_exists($strDirName.$strFileName) 
					&& (!$arOldSmile
						|| $arOldSmile["SMILE_TYPE"] != $SMILE_TYPE
						|| $arOldSmile["IMAGE"] != $strFileName
					))
					$strErrorMessage .= GetMessage("ERROR_EXISTS_IMAGE").". \n";
				else
				{
					if (!@copy($_FILES["IMAGE1"]["tmp_name"], $strDirName.$strFileName))
						$strErrorMessage .= GetMessage("ERROR_COPY_IMAGE").". \n";
					else
					{
						@chmod($strDirName.$strFileName, BX_FILE_PERMISSIONS);
						$imgArray = CFile::GetImageSize($strDirName.$strFileName);
						if (is_array($imgArray))
						{
							$iIMAGE_WIDTH = $imgArray[0];
							$iIMAGE_HEIGHT = $imgArray[1];
						}
						else
						{
							$iIMAGE_WIDTH = 0;
							$iIMAGE_HEIGHT = 0;
						}
					}
					if ($arOldSmile && ($arOldSmile["SMILE_TYPE"]!=$SMILE_TYPE || $arOldSmile["IMAGE"]!=$strFileName) && $arOldSmile["IMAGE"] <> '')
					{
						$strDirNameOld = $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/socialnetwork/";
						if ($arOldSmile["SMILE_TYPE"]=="I") $strDirNameOld .= "icon";
						else $strDirNameOld .= "smile";
						$strDirNameOld .= "/".$arOldSmile["IMAGE"];
						@unlink($strDirNameOld);
					}
				}
			}

			if ($strFileName == '')
				$strErrorMessage .= GetMessage("ERROR_NO_IMAGE").". \n";
		}
		elseif ($arOldSmile && $arOldSmile["SMILE_TYPE"]!=$SMILE_TYPE)
		{
			$strDirNameOld = $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/socialnetwork/";
			if ($arOldSmile["SMILE_TYPE"]=="I") $strDirNameOld .= "icon";
			else $strDirNameOld .= "smile";
			$strDirNameOld .= "/".$arOldSmile["IMAGE"];

			$strDirName = $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/socialnetwork/";
			if ($SMILE_TYPE=="I") $strDirName .= "icon";
			else $strDirName .= "smile";
			$strDirName .= "/".$arOldSmile["IMAGE"];

			if (!@copy($strDirNameOld, $strDirName))
				$strErrorMessage .= GetMessage("ERROR_COPY_IMAGE").". \n";
			else
			{
				CheckDirPath($strDirName);
				@unlink($strDirNameOld);
			}
		}
	}

	if ($strErrorMessage == '')
	{
		$arFields = array(
		"SORT" => $SORT,
		"SMILE_TYPE" => $SMILE_TYPE,
		"TYPING" => $TYPING,
		"DESCRIPTION" => $DESCRIPTION
		);

		if ($strFileName <> '')
		{
			$arFields["IMAGE"] = $strFileName;
			$arFields["IMAGE_WIDTH"] = $iIMAGE_WIDTH;
			$arFields["IMAGE_HEIGHT"] = $iIMAGE_HEIGHT;
		}

		for ($i = 0; $i<count($arSysLangs); $i++)
		{
			$arFields["LANG"][] = array(
				"LID" => $arSysLangs[$i],
				"NAME" => ${"NAME_".$arSysLangs[$i]}
			);
		}

		if ($ID>0)
		{
			$ID1 = CSocNetSmile::Update($ID, $arFields);
			if (intval($ID1)<=0)
				$strErrorMessage .= GetMessage("ERROR_EDIT_SMILE").". \n";
		}
		else
		{
			$ID = CSocNetSmile::Add($arFields);
			if (intval($ID)<=0)
				$strErrorMessage .= GetMessage("ERROR_ADD_SMILE").". \n";
		}
	}

	if ($strErrorMessage <> '') $bInitVars = True;

	if ($save <> '' && $strErrorMessage == '')
		LocalRedirect("socnet_smile.php?lang=".LANG."&".GetFilterParams("filter_", false));
}

ClearVars("f_");
ClearVars("str_");

$str_SORT = 150;

if ($ID > 0)
{
	$db_smile = CSocNetSmile::GetList(array(), array("ID" => $ID));
	$db_smile->ExtractFields("str_", True);
	$f_IMAGE = $str_IMAGE;
	$f_IMAGE_WIDTH = $str_IMAGE_WIDTH;
	$f_IMAGE_HEIGHT = $str_IMAGE_HEIGHT;
	$f_SMILE_TYPE = $str_SMILE_TYPE;
}

if ($bInitVars)
	$DB->InitTableVarsForEdit("b_sonet_smile", "", "str_");

$sDocTitle = ($ID>0) ? GetMessage("SONET_EDIT_RECORD", array("#ID#" => $ID)) : GetMessage("SONET_NEW_RECORD");
$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>

<?
$aMenu = array(
		array(
			"TEXT" => GetMessage("FSN_2FLIST"),
			"ICON" => "btn_list",
			"LINK" => "/bitrix/admin/socnet_smile.php?lang=".LANG."&".GetFilterParams("filter_", false)
		)
	);

if ($ID > 0 && $sonetPermissions == "W")
{
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
		"TEXT" => GetMessage("FSN_NEW_SMILE"),
		"LINK" => "/bitrix/admin/socnet_smile_edit.php?lang=".LANG."&".GetFilterParams("filter_", false)
	);

	$aMenu[] = array(
		"TEXT" => GetMessage("FSN_DELETE_SMILE"), 
		"LINK" => "javascript:if(confirm('".GetMessage("FSN_DELETE_SMILE_CONFIRM")."')) window.location='/bitrix/admin/socnet_smile.php?action=delete&ID[]=".$ID."&lang=".LANG."&".bitrix_sessid_get()."#tb';",
		"WARNING" => "Y"
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?CAdminMessage::ShowMessage($strErrorMessage);?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="fform" enctype="multipart/form-data">
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="lang" value="<?echo LANG ?>">
<input type="hidden" name="ID" value="<?echo $ID ?>">
<?=bitrix_sessid_post()?>

<?
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("FSN_TAB_SMILE"), "ICON" => "sonet", "TITLE" => GetMessage("FSN_TAB_SMILE_DESCR"))
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();

$tabControl->BeginNextTab();

if ($ID > 0):
?><tr>
	<td width="40%"><?echo GetMessage("SONET_CODE")?>:</td>
	<td width="60%"><?echo $ID ?></td>
</tr><?
endif;
?><tr>
	<td width="40%"><?echo GetMessage("SONET_SORT")?>:</td>
	<td width="60%">
		<input type="text" name="SORT" value="<?echo $str_SORT ?>" size="10">
	</td>
</tr>
<tr>
	<td><?echo GetMessage("SONET_TYPE")?>:</td>
	<td>
		<select name="SMILE_TYPE">
			<option value="S" <?if ($str_SMILE_TYPE=="S") echo "selected";?>><?echo GetMessage("FSE_SMILE");?></option>
			<option value="I" <?if ($str_SMILE_TYPE=="I") echo "selected";?>><?echo GetMessage("FSE_ICON");?></option>
		</select>
	</td>
</tr>
<tr>
	<td valign="top"><?echo GetMessage("SONET_TYPING")?>:<br><small><?echo GetMessage("SONET_TYPING_NOTE")?></small></td>
	<td valign="top">
		<input type="text" name="TYPING" value="<?echo $str_TYPING ?>" size="50">
	</td>
</tr>

<tr class="adm-detail-required-field">
	<td class="adm-detail-valign-top"><?echo GetMessage("SONET_IMAGE")?>:<br><small><?echo GetMessage("SONET_IMAGE_NOTE")?></small></td>
	<td>
		<input type="file" name="IMAGE1" size="30"><?
		if ($f_IMAGE <> '')
		{
			?><div style="padding-top: 10px;"><img src="/bitrix/images/socialnetwork/<?echo ($f_SMILE_TYPE=="I")?"icon":"smile" ?>/<?echo $f_IMAGE?>" border="0" <?echo (intval($f_IMAGE_WIDTH)>0) ? "width=\"".$f_IMAGE_WIDTH."\"" : "" ?> <?echo (intval($f_IMAGE_WIDTH)>0) ? "height=\"".$f_IMAGE_HEIGHT."\"" : "" ?>></div><?
		}
	?></td>
</tr><?

for ($i = 0; $i < count($arSysLangs); $i++):
	$arSmileLang = CSocNetSmile::GetLangByID($ID, $arSysLangs[$i]);
	$str_NAME = htmlspecialcharsbx($arSmileLang["NAME"]);
	$str_DESCRIPTION = htmlspecialcharsbx($arSmileLang["DESCRIPTION"]);
	if ($bInitVars)
	{
		$str_NAME = htmlspecialcharsbx(${"NAME_".$arSysLangs[$i]});
		$str_DESCRIPTION = htmlspecialcharsbx(${"DESCRIPTION_".$arSysLangs[$i]});
	}
	?><tr class="heading">
		<td colspan="2">[<?echo $arSysLangs[$i];?>] <?echo $arSysLangNames[$i];?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td>
			<?echo GetMessage("SONET_NAME")?>:
		</td>
		<td>
			<input type="text" name="NAME_<?echo $arSysLangs[$i] ?>" value="<?echo $str_NAME ?>" size="40">
		</td>
	</tr><?
endfor;

$tabControl->EndTab();

$tabControl->Buttons(
		array(
				"disabled" => ($sonetPermissions < "W"),
				"back_url" => "/bitrix/admin/socnet_smile.php?lang=".LANG."&".GetFilterParams("filter_", false)
			)
	);

$tabControl->End();

?></form>
<?require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php");?>
