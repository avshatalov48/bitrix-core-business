<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!\Bitrix\Main\Loader::includeModule("sender"))
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isUserHavePhpAccess = $USER->CanDoOperation('edit_php');


$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("sender_tmpl_edit_tab_main"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("sender_tmpl_edit_tab_main_title")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);		// Id of the edited record
$message = null;
$bVarsFromForm = false;
$blockTemplateId = $ID;

if($REQUEST_METHOD == "POST" && ($save!="" || $apply!="") && $POST_RIGHT=="W" && check_bitrix_sessid())
{
	$arError = array();
	$NAME = trim($NAME);
	if(!$isUserHavePhpAccess)
	{
		$MESSAGE_OLD = false;
		if($ID>0)
		{
			$templateOld = \Bitrix\Sender\TemplateTable::getRowById(array('ID' => $ID));
			if($templateOld)
			{
				$MESSAGE_OLD = $templateOld['MESSAGE'];
			}
		}

		$MESSAGE = CMain::ProcessLPA($MESSAGE, $MESSAGE_OLD);
	}

	$CONTENT = '';
	if(!empty($MESSAGE)) $CONTENT = $MESSAGE;
	$arFields = Array(
		"ACTIVE"	=> ($ACTIVE <> "Y"? "N":"Y"),
		"CONTENT"	=> $CONTENT,
		"NAME"		=> $NAME,
	);

	if($ID > 0)
	{
		$mailingUpdateDb = \Bitrix\Sender\TemplateTable::update($ID, $arFields);
		$res = $mailingUpdateDb->isSuccess();
		if(!$res)
		{
			$arError = $mailingUpdateDb->getErrorMessages();
			$_SESSION['bx_sender_template_tmp'] = base64_encode($CONTENT);
			$blockTemplateId = '';
		}
	}
	else
	{
		$mailingAddDb = \Bitrix\Sender\TemplateTable::add($arFields);
		if($mailingAddDb->isSuccess())
		{
			$ID = $mailingAddDb->getId();
			$res = ($ID > 0);
			$_SESSION['bx_sender_template_tmp'] = '';
			$blockTemplateId = '';
		}
		else
		{
			$arError = $mailingAddDb->getErrorMessages();
			$_SESSION['bx_sender_template_tmp'] = base64_encode($CONTENT);
			$blockTemplateId = '';
		}
	}

	if($res)
	{
		if($apply!="")
			LocalRedirect("/bitrix/admin/sender_template_edit.php?ID=".$ID."&lang=".LANG."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect("/bitrix/admin/sender_template_admin.php?lang=".LANG);
	}
	else
	{
		if(!empty($arError))
			$message = new CAdminMessage(implode("<br>", $arError));
		$bVarsFromForm = true;
	}

}

//Edit/Add part
ClearVars();
$str_SORT = 100;
$str_ACTIVE = "Y";
$str_VISIBLE = "Y";

if($ID>0)
{
	$rubric = new CDBResult(\Bitrix\Sender\TemplateTable::getById($ID));
	if(!$rubric->ExtractFields("str_"))
		$ID=0;
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sender_preset_template", "", "str_");

$APPLICATION->SetTitle(($ID>0? GetMessage("sender_tmpl_edit_title_edit").$ID : GetMessage("sender_tmpl_edit_title_add")));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

\CJSCore::Init(array("sender_admin"));
$templateListHtml = \Bitrix\Sender\Preset\Template::getTemplateListHtml('tabControl_layout');

$aMenu = array(
	array(
		"TEXT"=>GetMessage("sender_tmpl_edit_list"),
		"TITLE"=>GetMessage("sender_tmpl_edit_list_title"),
		"LINK"=>"sender_template_admin.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("sender_tmpl_edit_action_add"),
		"TITLE"=>GetMessage("sender_tmpl_edit_action_add_title"),
		"LINK"=>"sender_template_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("sender_tmpl_edit_action_del"),
		"TITLE"=>GetMessage("sender_tmpl_edit_action_del_title"),
		"LINK"=>"javascript:if(confirm('".GetMessage("sender_tmpl_edit_action_del_confirm")."'))window.location='sender_template_admin.php?ID=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
	$aMenu[] = array("SEPARATOR"=>"Y");

}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
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
		<td width="40%"><?echo GetMessage("sender_tmpl_edit_field_active")?></td>
		<td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE == "Y") echo " checked"?>></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("sender_tmpl_edit_field_name")?></td>
		<td><input type="text" name="NAME" value="<?echo $str_NAME;?>" size="45" maxlength="100"></td>
	</tr>
	<tr><td colspan="2">&nbsp</td></tr>
	<tr><td colspan="2">&nbsp</td></tr>

	<?if(!empty($templateListHtml)):?>
		<tr class="adm-detail-required-field  show-when-show-template-list" <?=(!empty($str_CONTENT) ? 'style="display: none;"' : '')?>>
			<td colspan="2">
				<?=$templateListHtml;?>
			</td>
		</tr>
		<tr class="hidden-when-show-template-list" <?=(empty($str_CONTENT) ? 'style="display: none;"' : '')?>>
			<td><?echo GetMessage("sender_tmpl_edit_field_sel_templ")?></td>
			<td>
				<span class="sender-template-message-caption-container"></span> <a class="sender-link-email sender-template-message-caption-container-btn" href="javascript: void(0);"><?echo GetMessage("sender_tmpl_edit_field_sel_templ_another")?></a>
			</td>
		</tr>
	<?endif;?>

	<tr class="adm-detail-required-field hidden-when-show-template-list" <?=(empty($str_CONTENT) ? 'style="display: none;"' : '')?>>
		<td colspan="2" align="left">
			<b><?=GetMessage("sender_tmpl_edit_field_message")?></b>
			<?=\Bitrix\Sender\TemplateTable::initEditor(array(
				'FIELD_NAME' => 'MESSAGE',
				'FIELD_VALUE' => $str_CONTENT,
				'CONTENT_URL' => '/bitrix/admin/sender_template_admin.php?action=get_template&template_type=USER&template_id=' . $blockTemplateId . '&lang=' . LANGUAGE_ID . '&' . bitrix_sessid_get(),
				'HAVE_USER_ACCESS' => $isUserHavePhpAccess,
				'SHOW_SAVE_TEMPLATE' => false,
				'IS_TEMPLATE_MODE' => false,
			));?>
			<input type="hidden" name="IS_TEMPLATE_LIST_SHOWN" id="IS_TEMPLATE_LIST_SHOWN" value="<?=(empty($str_CONTENT) ?"Y":"N")?>">
		</td>
	</tr>
<?

$tabControl->Buttons(
	array(
		"disabled"=>($POST_RIGHT<"W"),
		"back_url"=>"sender_template_admin.php?lang=".LANG,

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
	<script>
		BX.message({"SENDER_SHOW_TEMPLATE_LIST" : "<?=GetMessage('SENDER_SHOW_TEMPLATE_LIST')?>"});
		function ShowTemplateListL(bShow)
		{
			var i, displayShow, displayHide, listShown;
			if(bShow)
			{
				displayShow = 'none';
				displayHide = 'table-row';
				listShown = 'Y';
			}
			else
			{
				displayShow = '';
				displayHide = 'none';
				listShown = 'N';
			}

			var tmplTypeContList = BX.findChildren(BX('tabControl_layout'), {'className': 'hidden-when-show-template-list'}, true);
			for (i in tmplTypeContList)
				tmplTypeContList[i].style.display = displayShow;

			tmplTypeContList = BX.findChildren(BX('tabControl_layout'), {'className': 'show-when-show-template-list'}, true);
			for (i in tmplTypeContList)
				tmplTypeContList[i].style.display = displayHide;

			BX('IS_TEMPLATE_LIST_SHOWN').value = listShown;
		}

		var letterManager = new SenderLetterManager;
		letterManager.onSetTemplate(function()
		{
			ShowTemplateListL(false);
		});

		letterManager.onShowTemplateList(function(){ ShowTemplateListL(true); });
		letterManager.onHideTemplateList(function(){ ShowTemplateListL(false); });

	</script>
<?
$tabControl->ShowWarnings("post_form", $message);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>