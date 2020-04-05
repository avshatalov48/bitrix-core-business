<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/prolog.php");

IncludeModuleLangFile(__FILE__);
define("HELP_FILE", "add_newsletter.php");

$POST_RIGHT = $APPLICATION->GetGroupRight("subscribe");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("rub_tab_rubric"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("rub_tab_rubric_title")),
	array("DIV" => "edit2", "TAB" => GetMessage("rub_tab_generation"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("rub_tab_generation_title")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);		// Id of the edited record
$message = null;
$bVarsFromForm = false;

if($REQUEST_METHOD == "POST" && ($save!="" || $apply!="") && $POST_RIGHT=="W" && check_bitrix_sessid())
{
	$rubric = new CRubric;
	$arFields = Array(
		"ACTIVE"	=> ($ACTIVE <> "Y"? "N":"Y"),
		"NAME"		=> $NAME,
		"CODE"		=> $CODE,
		"SORT"		=> $SORT,
		"DESCRIPTION"	=> $DESCRIPTION,
		"LID"		=> $LID,
		"AUTO"		=>($AUTO <> "Y"? "N":"Y"),
		"DAYS_OF_MONTH"	=> $DAYS_OF_MONTH,
		"DAYS_OF_WEEK"	=> (is_array($DAYS_OF_WEEK)?implode(",", $DAYS_OF_WEEK):""),
		"TIMES_OF_DAY"	=> $TIMES_OF_DAY,
		"TEMPLATE"	=> $TEMPLATE,
		"VISIBLE"	=> ($VISIBLE <> "Y"? "N":"Y"),
		"FROM_FIELD"	=> $FROM_FIELD,
		"LAST_EXECUTED"	=> $LAST_EXECUTED
	);

	if($ID > 0)
	{
		$res = $rubric->Update($ID, $arFields);
	}
	else
	{
		$ID = $rubric->Add($arFields);
		$res = ($ID>0);
	}

	if($res)
	{
		if($apply!="")
			LocalRedirect("/bitrix/admin/rubric_edit.php?ID=".$ID."&mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect("/bitrix/admin/rubric_admin.php?lang=".LANG);
	}
	else
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("rub_save_error"), $e);
		$bVarsFromForm = true;
	}

}

//Edit/Add part
ClearVars();
$str_SORT = 100;
$str_ACTIVE = "Y";
$str_AUTO = "N";
$str_DAYS_OF_MONTH = "";
$str_DAYS_OF_WEEK = "";
$str_TIMES_OF_DAY = "";
$str_VISIBLE = "Y";
$str_LAST_EXECUTED = ConvertTimeStamp(time()+CTimeZone::GetOffset(), "FULL");
$str_FROM_FIELD = COption::GetOptionString("subscribe", "default_from");

if($ID>0)
{
	$rubric = CRubric::GetByID($ID);
	if(!$rubric->ExtractFields("str_"))
		$ID=0;
}
if($ID>0 && !$message)
	$DAYS_OF_WEEK = explode(",", $str_DAYS_OF_WEEK);
if(!is_array($DAYS_OF_WEEK))
	$DAYS_OF_WEEK = array();

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_list_rubric", "", "str_");

$APPLICATION->SetTitle(($ID>0? GetMessage("rub_title_edit").$ID : GetMessage("rub_title_add")));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("rub_list"),
		"TITLE"=>GetMessage("rub_list_title"),
		"LINK"=>"rubric_admin.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("rub_add"),
		"TITLE"=>GetMessage("rubric_mnu_add"),
		"LINK"=>"rubric_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("rub_delete"),
		"TITLE"=>GetMessage("rubric_mnu_del"),
		"LINK"=>"javascript:if(confirm('".GetMessage("rubric_mnu_del_conf")."'))window.location='rubric_admin.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("rub_check"),
		"TITLE"=>GetMessage("rubric_mnu_check"),
		"LINK"=>"template_test.php?lang=".LANG."&ID=".$ID
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
if($_REQUEST["mess"] == "ok" && $ID>0)
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("rub_saved"), "TYPE"=>"OK"));

if($message)
	echo $message->Show();
elseif($rubric->LAST_ERROR!="")
	CAdminMessage::ShowMessage($rubric->LAST_ERROR);
?>

<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
<?
$tabControl->Begin();
?>
<?
//********************
//Rubric
//********************
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%"><?echo GetMessage("rub_act")?></td>
		<td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE == "Y") echo " checked"?>></td>
	</tr>
	<tr>
		<td><?echo GetMessage("rub_visible")?></td>
		<td><input type="checkbox" name="VISIBLE" value="Y"<?if($str_VISIBLE == "Y") echo " checked"?>></td>
	</tr>
	<tr>
		<td><?echo GetMessage("rub_site")?></td>
		<td><?echo CLang::SelectBox("LID", $str_LID);?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("rub_name")?></td>
		<td><input type="text" name="NAME" value="<?echo $str_NAME;?>" size="45" maxlength="100"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("rub_sort")?></td>
		<td><input type="text" name="SORT" value="<?echo $str_SORT;?>" size="6"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("rub_code")?></td>
		<td><input type="text" name="CODE" value="<?echo $str_CODE;?>" size="45"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("rub_desc")?></td>
		<td><textarea class="typearea" name="DESCRIPTION" cols="45" rows="5" wrap="VIRTUAL" style="width:100%"><?echo $str_DESCRIPTION; ?></textarea></td>
	</tr>
	<tr>
		<td><?echo GetMessage("rub_auto")?></td>
		<td><input type="checkbox" name="AUTO" value="Y"<?if($str_AUTO == "Y") echo " checked"?> OnClick="if(this.checked) tabControl.EnableTab('edit2'); else tabControl.DisableTab('edit2');"></td>
	</tr>
<?
//********************
//Auto params
//********************
$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("rub_schedule")?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td width="40%"><?echo GetMessage("rub_last_executed"). ":"?></td>
		<td width="60%"><?echo CalendarDate("LAST_EXECUTED", $str_LAST_EXECUTED, "post_form", "20")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("rub_dom")?></td>
		<td><input class="typeinput" type="text" name="DAYS_OF_MONTH" value="<?echo $str_DAYS_OF_MONTH;?>" size="30" maxlength="100"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("rub_dow")?></td>
		<td>
		<table cellspacing=1 cellpadding=0 border=0 class="internal">
		<?	$arDoW = array(
				"1"	=> GetMessage("rubric_mon"),
				"2"	=> GetMessage("rubric_tue"),
				"3"	=> GetMessage("rubric_wed"),
				"4"	=> GetMessage("rubric_thu"),
				"5"	=> GetMessage("rubric_fri"),
				"6"	=> GetMessage("rubric_sat"),
				"7"	=> GetMessage("rubric_sun")
			);
		?>
			<tr class="heading"><?foreach($arDoW as $strVal=>$strDoW):?>
				<td><?=$strDoW?></td>
				<?endforeach;?>
			</tr>
			<tr>
			<?foreach($arDoW as $strVal=>$strDoW):?>
				<td style="text-align:center"><input type="checkbox" name="DAYS_OF_WEEK[]" value="<?=$strVal?>"<?if(array_search($strVal, $DAYS_OF_WEEK) !== false) echo " checked"?>></td>
			<?endforeach;?>
			</tr>
		</table>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("rub_tod")?></td>
		<td><input type="text" name="TIMES_OF_DAY" value="<?echo $str_TIMES_OF_DAY;?>" size="30" maxlength="255"></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("rub_template")?></td>
	</tr>
<?
$arTemplates=CPostingTemplate::GetList();
if(count($arTemplates)>0):
?>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><?echo GetMessage("rub_templates")?></td>
		<td><table>
<?
	$i=0;
	foreach($arTemplates as $strTemplate):
		$arTemplate = CPostingTemplate::GetByID($strTemplate);
?>
		<tr>
			<td class="adm-detail-valign-top"><input type="radio" id="TEMPLATE<?=$i?>" name="TEMPLATE" value="<?=$arTemplate["PATH"]?>"<?if($str_TEMPLATE==$arTemplate["PATH"]) echo "checked"?>></td>
			<td>
				<label for="TEMPLATE<?=$i?>" title="<?=$arTemplate["DESCRIPTION"]?>"><?=(strlen($arTemplate["NAME"])>0?$arTemplate["NAME"]:GetMessage("rub_no_name"))?></label><br>
				<?if(IsModuleInstalled("fileman")):?>
					<a title="<?=GetMessage("rub_manage")?>" href="/bitrix/admin/fileman_admin.php?path=<?=urlencode("/".$arTemplate["PATH"])?>"><?=$arTemplate["PATH"]?></a>
				<?else:?>
					<?=$arTemplate["PATH"]?>
				<?endif?>
			</td>
		<?$i++?>
		</tr>
	<?endforeach;?>
		</table></td>
	</tr>
<?else:?>
	<tr>
		<td colspan="2"><?=GetMessage("rub_no_templates")?></td>
	</tr>
<?endif?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("rub_post_fields")?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("rub_post_fields_from")?></td>
		<td><input type="text" name="FROM_FIELD" value="<?echo $str_FROM_FIELD;?>" size="30" maxlength="255"></td>
	</tr>
<?
$tabControl->Buttons(
	array(
		"disabled"=>($POST_RIGHT<"W"),
		"back_url"=>"rubric_admin.php?lang=".LANG,

	)
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANG?>">
<?if($ID>0 && !$bCopy):?>
	<input type="hidden" name="ID" value="<?=$ID?>">
<?endif;?>
<?
$tabControl->End();
?>

<?
$tabControl->ShowWarnings("post_form", $message);
?>

<script language="JavaScript">
<!--
	if(document.post_form.AUTO.checked)
		tabControl.EnableTab('edit2');
	else
		tabControl.DisableTab('edit2');
//-->
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>