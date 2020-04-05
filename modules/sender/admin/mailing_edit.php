<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!\Bitrix\Main\Loader::includeModule("sender"))
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));


$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("sender_mailing_edit_tab_main"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("sender_mailing_edit_tab_main_title")),
	array("DIV" => "edit2", "TAB" => GetMessage("sender_mailing_edit_tab_grp"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("sender_mailing_edit_tab_grp_title")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);


$ID = intval($ID);		// Id of the edited record
$message = null;
$bVarsFromForm = false;

if($REQUEST_METHOD == "POST" && ($save!="" || $apply!="") && $POST_RIGHT=="W" && check_bitrix_sessid())
{
	$arError = array();
	$NAME = trim($NAME);
	$arFields = Array(
		"ACTIVE"	=> ($ACTIVE <> "Y"? "N":"Y"),
		"SORT"		=> $SORT,
		"IS_PUBLIC"	=> ($IS_PUBLIC <> "Y"? "N":"Y"),
		"NAME"		=> $NAME,
		"DESCRIPTION"	=> $DESCRIPTION,
		"SITE_ID" => $SITE_ID,
	);

	if($ID > 0)
	{
		$mailingUpdateDb = \Bitrix\Sender\MailingTable::update($ID, $arFields);
		$res = $mailingUpdateDb->isSuccess();
		if(!$res)
			$arError = $mailingUpdateDb->getErrorMessages();
	}
	else
	{
		$mailingAddDb = \Bitrix\Sender\MailingTable::add($arFields);
		if($mailingAddDb->isSuccess())
		{
			$ID = $mailingAddDb->getId();
			$res = ($ID > 0);
		}
		else
		{
			$arError = $mailingAddDb->getErrorMessages();
		}
	}

	$GROUP = array();
	if(isset($GROUP_INCLUDE))
	{
		$GROUP_INCLUDE = explode(',', $GROUP_INCLUDE);
		trimArr($GROUP_INCLUDE);
	}
	else
		$GROUP_INCLUDE = array();

	if(isset($GROUP_EXCLUDE))
	{
		$GROUP_EXCLUDE = explode(',', $GROUP_EXCLUDE);
		trimArr($GROUP_EXCLUDE);
	}
	else
		$GROUP_EXCLUDE = array();

	if($res)
	{
		foreach($GROUP_INCLUDE as $groupId)
		{
			if (is_numeric($groupId))
			{
				$GROUP[] = array('MAILING_ID' => $ID, 'GROUP_ID' => $groupId, 'INCLUDE' => true);
			}
		}

		foreach($GROUP_EXCLUDE as $groupId)
		{
			if (is_numeric($groupId))
			{
				$GROUP[] = array('MAILING_ID' => $ID, 'GROUP_ID' => $groupId, 'INCLUDE' => false);
			}
		}

		\Bitrix\Sender\MailingGroupTable::delete(array('MAILING_ID' => $ID));
		foreach($GROUP as $arGroup)
		{
			\Bitrix\Sender\MailingGroupTable::add($arGroup);
		}
	}

	if($res)
	{
		if($apply!="")
			LocalRedirect("/bitrix/admin/sender_mailing_edit.php?ID=".$ID."&lang=".LANG."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect("/bitrix/admin/sender_mailing_admin.php?lang=".LANG);
	}
	else
	{
		if(!empty($arError))
			$message = new CAdminMessage(implode("<br>", $arError));
		$bVarsFromForm = true;
	}

}
else
{
	$GROUP_EXCLUDE = $GROUP_INCLUDE = array();
	$groupDb = \Bitrix\Sender\MailingGroupTable::getList(array(
		'select' => array('ID' => 'GROUP_ID', 'INCLUDE'),
		'filter' => array('MAILING_ID' => $ID),
	));
	while($arGroup = $groupDb->fetch())
	{
		if($arGroup['INCLUDE'])
			$GROUP_INCLUDE[] = $arGroup['ID'];
		else
			$GROUP_EXCLUDE[] = $arGroup['ID'];
	}
}

//Edit/Add part
ClearVars();
$str_SORT = 100;
$str_ACTIVE = "Y";
$str_VISIBLE = "Y";

if($ID>0)
{
	$rubric = new CDBResult(\Bitrix\Sender\MailingTable::getById($ID));
	if(!$rubric->ExtractFields("str_"))
		$ID=0;
}

$GROUP_EXIST = array();
$groupDb = \Bitrix\Sender\GroupTable::getList(array(
	'select' => array('NAME', 'ID', 'ADDRESS_COUNT'),
	'filter' => array('ACTIVE' => 'Y'),
	'order' => array('SORT' => 'ASC', 'NAME' => 'ASC'),
));
while($arGroup = $groupDb->fetch())
{
	$GROUP_EXIST[] = $arGroup;
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sender_mailing", "", "str_");

\CJSCore::Init(array("sender_admin"));
$APPLICATION->SetTitle(($ID>0? GetMessage("sender_mailing_edit_title_edit").$ID : GetMessage("sender_mailing_edit_title_new")));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


$aMenu = array(
	array(
		"TEXT"=>GetMessage("sender_mailing_edit_list"),
		"TITLE"=>GetMessage("sender_mailing_edit_list_title"),
		"LINK"=>"sender_mailing_admin.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("sender_mailing_edit_add"),
		"TITLE"=>GetMessage("sender_mailing_edit_add_title"),
		"LINK"=>"sender_mailing_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("sender_mailing_edit_del"),
		"TITLE"=>GetMessage("sender_mailing_edit_del_title"),
		"LINK"=>"javascript:if(confirm('".GetMessage("sender_mailing_edit_del_confirm")."'))window.location='sender_mailing_admin.php?ID=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
	$aMenu[] = array("SEPARATOR"=>"Y");
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
if($_REQUEST["mess"] == "ok" && $ID>0)
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("sender_mailing_edit_saved"), "TYPE"=>"OK"));

if($message)
	echo $message->Show();
elseif($rubric->LAST_ERROR!="")
	CAdminMessage::ShowMessage($rubric->LAST_ERROR);
?>

<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" name="post_form">
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2">
			<div class="adm-info-message"><?=GetMessage("sender_mailing_edit_main");?></div>
		</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("sender_mailing_edit_field_active")?></td>
		<td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE == "Y") echo " checked"?>></td>
	</tr>
	<tr>
		<td><?echo GetMessage("sender_mailing_edit_field_site")?></td>
		<td><?echo CLang::SelectBox("SITE_ID", $str_SITE_ID);?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("sender_mailing_edit_field_name")?>
			<br/>
			<?echo GetMessage("sender_mailing_edit_field_name_desc")?>
		</td>
		<td><input type="text" name="NAME" value="<?echo $str_NAME;?>" size="45" maxlength="100"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("sender_mailing_edit_field_sort")?></td>
		<td><input type="text" name="SORT" value="<?echo $str_SORT;?>" size="6"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("sender_mailing_edit_field_desc")?></td>
		<td><textarea class="typearea" name="DESCRIPTION" cols="45" rows="5" wrap="VIRTUAL" style="width:100%"><?echo $str_DESCRIPTION; ?></textarea></td>
	</tr>
	<tr>
		<td><?echo GetMessage("sender_mailing_edit_field_is_public")?></td>
		<td><input type="checkbox" name="IS_PUBLIC" value="Y"<?if($str_IS_PUBLIC != "N") echo " checked"?>></td>
	</tr>
<?
//********************
//Auto params
//********************
$tabControl->BeginNextTab();
?>

	<?
	function ShowGroupControl($controlName, $controlValues, $controlSelectedValues)
	{
		$controlName = htmlspecialcharsbx($controlName);
		?>
		<td>
			<select multiple style="width:350px; height:300px;" id="<?=$controlName?>_EXISTS" ondblclick="GroupManager(true, '<?=$controlName?>');">
				<?
				foreach($controlValues as $arGroup)
				{
					?><option value="<?=htmlspecialcharsbx($arGroup['ID'])?>"><?=htmlspecialcharsbx($arGroup['NAME'].' ('.$arGroup['ADDRESS_COUNT'].')')?></option><?
				}
				?>
			</select>
		</td>
		<td class="sender-mailing-group-block-sect-delim">
			<input type="button" value=">" onClick="GroupManager(true, '<?=$controlName?>');">
			<br>
			<br>
			<input type="button" value="<" onClick="GroupManager(false, '<?=$controlName?>');">
		</td>
		<td>
			<select id="<?=$controlName?>" multiple="multiple" style="width:350px; height:300px;" ondblclick="GroupManager(false, '<?=$controlName?>');">
				<?
				$arGroupId = array();
				foreach($controlValues as $arGroup)
				{
					if(!in_array($arGroup['ID'], $controlSelectedValues))
						continue;

					$arGroupId[] = $arGroup['ID'];
					?><option value="<?=htmlspecialcharsbx($arGroup['ID'])?>"><?=htmlspecialcharsbx($arGroup['NAME'].' ('.$arGroup['ADDRESS_COUNT'].')')?></option><?
				}
				?>
			</select>
			<input type="hidden" name="<?=$controlName?>" id="<?=$controlName?>_HIDDEN" value="<?=implode(',', $arGroupId)?>">
		</td>
	<?
	}
	?>

	<tr>
		<td colspan="2">
			<div class="sender-mailing-group-container sender-mailing-group-add">
				<span class="sender-mailing-group-container-title"><span><?=GetMessage("sender_mailing_edit_grp_add");?></span></span>
				<span class="adm-white-container-p"><span><?=GetMessage("sender_mailing_edit_grp_add_desc");?></span></span>
			</div>

		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2">
			<table class="sender-mailing-group">
				<tr>
					<td><span class="sender-mailing-group-block-all"><?=GetMessage("sender_mailing_edit_grp_all");?></td>
					<td class="sender-mailing-group-block-sect-delim"></td>
					<td><span class="sender-mailing-group-block-sel"><?=GetMessage("sender_mailing_edit_grp_sel");?></td>
				</tr>
				<tr>
					<?ShowGroupControl('GROUP_INCLUDE', $GROUP_EXIST, $GROUP_INCLUDE)?>
				</tr>
			</table>
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2">
			<div class="sender-mailing-group-container sender-mailing-group-del">
				<span class="sender-mailing-group-container-title"><span><?=GetMessage("sender_mailing_edit_grp_del");?></span></span>
				<span class="adm-white-container-p"><span><?=GetMessage("sender_mailing_edit_grp_del_desc");?></span></span>
			</div>

		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2">
			<table class="sender-mailing-group">
				<tr>
					<td><span class="sender-mailing-group-block-all"><?=GetMessage("sender_mailing_edit_grp_all");?></td>
					<td class="sender-mailing-group-block-sect-delim"></td>
					<td><span class="sender-mailing-group-block-sel"><?=GetMessage("sender_mailing_edit_grp_sel");?></td>
				</tr>
				<tr>
					<?ShowGroupControl('GROUP_EXCLUDE', $GROUP_EXIST, $GROUP_EXCLUDE)?>
				</tr>
			</table>
		</td>
	</tr>

<?
$tabControl->Buttons(
	array(
		"disabled"=>($POST_RIGHT<"W"),
		"back_url"=>"sender_mailing_admin.php?lang=".LANG,
		"btnSave"=>true,

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

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>