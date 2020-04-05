<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/sites/template_import.php");

if(!$USER->CanDoOperation('edit_php') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_php');

IncludeModuleLangFile(__FILE__);

$strError="";
$strOK="";
$bVarsFromForm = false;

if($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["action"]=="import" && $isAdmin && check_bitrix_sessid())
{
	$ID = $_POST["ID"];
	if(!is_uploaded_file($_FILES["tpath_file"]["tmp_name"]))
	{
		$strError .= GetMessage("MAIN_TEMPLATE_LOAD_ERR_LOAD");
	}
	else
	{
		if(strlen($ID)<=0)
		{
			$ID = basename($_FILES['tpath_file']['name']);
			if($p = bxstrrpos($ID, ".gz"))
				$ID = substr($ID, 0, $p);
			if($p = bxstrrpos($ID, ".tar"))
				$ID = substr($ID, 0, $p);
			$ID = str_replace("\\", "", $ID);
			$ID = str_replace("/", "", $ID);
		}

		if(strlen($ID)<=0)
		{
			$strError .= GetMessage("MAIN_TEMPLATE_LOAD_ERR_ID");
		}
		else
		{
			if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ID))
			{
				$strError .= str_replace("#TEMPLATE_NAME#", $ID, GetMessage("MAIN_TEMPLATE_LOAD_ERR_EX"));
			}
			else
			{
				require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/tar_gz.php");
				$oArchiver = new CArchiver($_FILES["tpath_file"]["tmp_name"]);
				if($oArchiver->extractFiles($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ID))
				{
					$strOK .= str_replace("#TEMPLATE_NAME#", $ID, GetMessage("MAIN_TEMPLATE_LOAD_OK"));

					$SITE_ID = $_POST["SITE_ID"];
					if(strlen($SITE_ID)>0 && $SITE_ID!="NOT_REF")
					{
						$db_site = CSite::GetByID($SITE_ID);
						if($ar_site = $db_site->Fetch())
						{
							$arTemplates = array();
							$dbSiteRes = CSite::GetTemplateList($SITE_ID);
							$bW = false;
							while($v = $dbSiteRes->Fetch())
							{
								if(!$bW && strlen(Trim($v["CONDITION"]))<=0)
								{
									$v["TEMPLATE"] = $ID;
									$bW = true;
								}
								$arTemplates[]= $v;
							}

							if(!$bW)
								$arTemplates[]= array('CONDITION' => '', 'SORT' => 150, 'TEMPLATE' => $ID);

							$arFields = array(
								"TEMPLATE" => $arTemplates,
								"NAME" => $ar_site["NAME"],
							);
							$ob_site = new CSite();
							$ob_site->Update($SITE_ID, $arFields);
						}
					}
				}
				else
				{
					$strError .= GetMessage("MAIN_T_EDIT_IMP_ERR");
					$arErrors = &$oArchiver->GetErrors();
					if(count($arErrors)>0)
					{
						$strError .= ":<br>";
						foreach ($arErrors as $value)
							$strError .= "[".$value[0]."] ".$value[1]."<br>";
					}
					else
						$strError .= ".<br>";
				}
			}
		}
	}

	if(strlen($strError)>0)
		$bVarsFromForm = true;
	elseif($_POST["goto_edit"] == "Y")
		LocalRedirect(BX_ROOT."/admin/template_edit.php?lang=".LANGUAGE_ID."&ID=".$ID);
	else
		LocalRedirect(BX_ROOT."/admin/template_admin.php?lang=".LANGUAGE_ID);
}

if($bVarsFromForm)
{
	$str_ID = htmlspecialcharsbx($_POST["ID"]);
	$str_SITE_ID = htmlspecialcharsbx($_POST["SITE_ID"]);
}
else
{
	$str_ID = $str_SITE_ID = "";
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TEMPLATE_LOAD_TITLE"), "ICON" => "template_load", "TITLE" => GetMessage("MAIN_TEMPLATE_LOAD_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$APPLICATION->SetTitle(GetMessage("MAIN_TEMPLATE_LOAD_TITLE"));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

CAdminMessage::ShowMessage($strError);
CAdminMessage::ShowNote($strOK);
?>
<script language="JavaScript">
<!--
function NewFileName(ob)
{
	var str_file = ob.value;
	var filename = str_file.substr(str_file.lastIndexOf("\\")+1);
	if(filename.lastIndexOf(".gz")>0)
		filename = filename.substr(0, filename.lastIndexOf(".gz"));
	if(filename.lastIndexOf(".tar")>0)
		filename = filename.substr(0, filename.lastIndexOf(".tar"));
	document.getElementById("ID").value = filename;
}
//-->
</script>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?" name="bform2" enctype="multipart/form-data">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
<?
$tabControl->Begin();

$tabControl->BeginNextTab();
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("MAIN_TEMPLATE_LOAD_FILE")?></td>
		<td width="60%"><input type="file" size="35" name="tpath_file" onChange="NewFileName(this)"></td>
	</tr>
	<tr>
		<td></td>
		<td><?
echo BeginNote();
if(defined("BX_UTF"))
	echo GetMessage("MAIN_TEMPLATE_LOAD_WARN_UTF");
else
	echo GetMessage("MAIN_TEMPLATE_LOAD_WARN_NON_UTF");
echo EndNote();
?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_TEMPLATE_LOAD_ID")?></td>
		<td><input type="text" name="ID" Id="ID" size="20" maxlength="255" value="<? echo $str_ID?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_TEMPLATE_LOAD_SITE_ID")?></td>
		<td><?=CSite::SelectBox("SITE_ID", $str_SITE_ID, GetMessage("MAIN_TEMPLATE_LOAD_SITE_ID_N"))?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_TEMPLATE_LOAD_GOTO_EDIT")?></td>
		<td><input type="checkbox" name="goto_edit" value="Y"></td>
	</tr>
<?
$tabControl->Buttons();
?>
	<input type="hidden" name="action" value="import">
	<input <?if(!$isAdmin) echo "disabled" ?> type="submit" name="import" value="<?echo GetMessage("MAIN_TEMPLATE_LOAD_SUBMIT")?>" class="adm-btn-save">
<?
$tabControl->End();
?>
</form>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
