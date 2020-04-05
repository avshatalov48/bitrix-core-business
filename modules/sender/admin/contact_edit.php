<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!\Bitrix\Main\Loader::includeModule("sender"))
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sender/admin/template_edit.php');
IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("sender_contact_edit_tab_main"), "TITLE"=>GetMessage("sender_contact_edit_tab_main_title")),
	array("DIV" => "edit2", "TAB" => GetMessage("sender_contact_edit_tab_lists"), "TITLE"=>GetMessage("sender_contact_edit_tab_lists_title")),
	array("DIV" => "edit3", "TAB" => GetMessage("sender_contact_edit_tab_subs"), "TITLE"=>GetMessage("sender_contact_edit_tab_subs_title")),
	array("DIV" => "edit4", "TAB" => GetMessage("sender_contact_edit_tab_unsubs"), "TITLE"=>GetMessage("sender_contact_edit_tab_unsubs_title")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);		// Id of the edited record
$message = null;
$bVarsFromForm = false;

$listsSelected = array();
$subSelected = array();
$unsubSelected = array();

if($REQUEST_METHOD == "POST" && ($save!="" || $apply!="") && $POST_RIGHT=="W" && check_bitrix_sessid())
{
	$arError = array();
	$NAME = trim($_POST['NAME']);
	$EMAIL = trim($_POST['EMAIL']);

	$arFields = Array(
		"EMAIL"	=> $EMAIL,
		"NAME"		=> $NAME,
	);

	if($ID > 0)
	{
		$contactUpdateDb = \Bitrix\Sender\ContactTable::update($ID, $arFields);
		$res = $contactUpdateDb->isSuccess();
		if(!$res)
			$arError = $contactUpdateDb->getErrorMessages();
	}
	else
	{
		$contactAddDb = \Bitrix\Sender\ContactTable::add($arFields);
		if($contactAddDb->isSuccess())
		{
			$ID = $contactAddDb->getId();
			$res = ($ID > 0);
		}
		else
		{
			$arError = $contactAddDb->getErrorMessages();
		}
	}

	if(isset($LIST))
	{
		$listsSelected = explode(',', $LIST);
		trimArr($listsSelected);
	}
	else
	{
		$listsSelected = array();
	}

	if(isset($SUB_LIST))
	{
		$subSelected = explode(',', $SUB_LIST);
		trimArr($subSelected);
	}
	else
	{
		$subSelected = array();
	}

	if(isset($UNSUB_LIST))
	{
		$unsubSelected = explode(',', $UNSUB_LIST);
		trimArr($unsubSelected);
	}
	else
	{
		$unsubSelected = array();
	}


	if($res)
	{
		\Bitrix\Sender\ContactListTable::delete(array('CONTACT_ID' => $ID));
		foreach($listsSelected as $listId)
		{
			if (is_numeric($listId))
			{
				\Bitrix\Sender\ContactListTable::add(array('CONTACT_ID' => $ID, 'LIST_ID' => $listId));
			}
		}

		foreach($subSelected as $mailingId)
		{
			if (is_numeric($mailingId))
			{
				\Bitrix\Sender\MailingSubscriptionTable::addSubscription(array('CONTACT_ID' => $ID, 'MAILING_ID' => $mailingId));
			}
		}

		foreach($unsubSelected as $mailingId)
		{
			if (is_numeric($mailingId))
			{
				\Bitrix\Sender\MailingSubscriptionTable::addUnSubscription(array('CONTACT_ID' => $ID, 'MAILING_ID' => $mailingId));
			}
		}

		$mailingDeleteList = array();
		$mailingDeleteDb = \Bitrix\Sender\MailingSubscriptionTable::getList(array(
			'select' => array('MAILING_ID'),
			'filter' => array('=CONTACT_ID' => $ID),
		));
		while($mailingDelete = $mailingDeleteDb->fetch())
		{
			$mailingDeleteList[] = $mailingDelete['MAILING_ID'];
		}
		$mailingDeleteList = array_diff($mailingDeleteList, $subSelected, $unsubSelected);
		foreach($mailingDeleteList as $mailingId)
		{
			\Bitrix\Sender\MailingSubscriptionTable::delete(array('CONTACT_ID' => $ID, 'MAILING_ID' => $mailingId));
		}
	}

	if($res)
	{
		if($apply!="")
			LocalRedirect("/bitrix/admin/sender_contact_edit.php?ID=".$ID."&lang=".LANG."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect("/bitrix/admin/sender_contact_admin.php?lang=".LANG);
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

if($ID > 0)
{
	$rubric = new CDBResult(\Bitrix\Sender\ContactTable::getById($ID));
	if(!$rubric->ExtractFields("str_"))
		$ID=0;
}

$mailingSubList = \Bitrix\Sender\MailingTable::getList(array('filter' => array('IS_TRIGGER' => 'N'),'order' => array('SITE_ID' => 'ASC', 'NAME' => 'ASC')))->fetchAll();
$mailingUnSubList = \Bitrix\Sender\MailingTable::getList(array('order' => array('SITE_ID' => 'ASC', 'NAME' => 'ASC')))->fetchAll();
$lists = \Bitrix\Sender\ListTable::getList(array('order' => array('NAME' => 'ASC')))->fetchAll();
if($ID > 0 )
{
	$listSelectedDb = \Bitrix\Sender\ContactListTable::getList(array(
		'select' => array('LIST_ID'),
		'filter' => array('=CONTACT_ID' => $ID),
	));
	while($listSelected = $listSelectedDb->fetch())
	{
		$listsSelected[] = $listSelected['LIST_ID'];
	}
	$subSelectedDb = \Bitrix\Sender\MailingSubscriptionTable::getSubscriptionList(array(
		'select' => array('MAILING_ID'),
		'filter' => array('=CONTACT_ID' => $ID),
	));
	while($sub = $subSelectedDb->fetch())
	{
		$subSelected[] = $sub['MAILING_ID'];
	}
	$unsubSelectedDb = \Bitrix\Sender\MailingSubscriptionTable::getUnSubscriptionList(array(
		'select' => array('MAILING_ID'),
		'filter' => array('=CONTACT_ID' => $ID),
	));
	while($unsub = $unsubSelectedDb->fetch())
	{
		$unsubSelected[] = $unsub['MAILING_ID'];
	}
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sender_contact", "", "str_");

\CJSCore::Init(array("sender_admin"));
$APPLICATION->SetTitle(($ID>0? GetMessage("sender_contact_edit_title_edit").$ID : GetMessage("sender_contact_edit_title_add")));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");



$aMenu = array(
	array(
		"TEXT"=>GetMessage("sender_contact_edit_btn_list"),
		"TITLE"=>GetMessage("sender_contact_edit_btn_list_title"),
		"LINK"=>"sender_contact_admin.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("sender_contact_edit_bnt_add"),
		"TITLE"=>GetMessage("sender_contact_edit_bnt_add_title"),
		"LINK"=>"sender_contact_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("sender_contact_edit_bnt_del"),
		"TITLE"=>GetMessage("sender_contact_edit_bnt_del_title"),
		"LINK"=>"javascript:if(confirm('".GetMessage("sender_contact_edit_bnt_del_confirm")."'))window.location='sender_contact_admin.php?ID=".$ID."&action=delete&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."';",
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

<?
function ShowGroupControl($controlName, $controlValues, $controlSelectedValues)
{
	$controlName = htmlspecialcharsbx($controlName);
	?>
	<td>
		<select multiple style="width:350px; height:300px;" id="<?=$controlName?>_EXISTS" ondblclick="GroupManager(true, '<?=$controlName?>');">
			<?
			$lastSite = '';
			foreach($controlValues as $arGroup)
			{
				if(isset($arGroup['SITE_ID']))
				{
					if($lastSite <> $arGroup['SITE_ID'])
					{
						if($lastSite != '')
						{
							echo '</optgroup>';
						}

						echo '<optgroup label="' . GetMessage('sender_contact_edit_field_sub_site') . ' ' . htmlspecialcharsbx($arGroup['SITE_ID']) .'">';

						$lastSite = $arGroup['SITE_ID'];
					}
				}

				?><option value="<?=htmlspecialcharsbx($arGroup['ID'])?>"><?=htmlspecialcharsbx($arGroup['NAME'] . ' [' . $arGroup['ID'] . ']')?></option><?
			}

			if(isset($arGroup['SITE_ID']))
			{
				echo '</optgroup>';
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
				?><option value="<?=htmlspecialcharsbx($arGroup['ID'])?>"><?=htmlspecialcharsbx($arGroup['NAME'] . ' [' . $arGroup['ID'] . ']')?></option><?
			}
			?>
		</select>
		<input type="hidden" name="<?=$controlName?>" id="<?=$controlName?>_HIDDEN" value="<?=implode(',', $arGroupId)?>">
	</td>
<?
}
?>

<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" name="post_form">
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%"><?echo GetMessage("sender_contact_edit_field_email")?></td>
		<td width="60%"><input type="text" name="EMAIL" value="<?echo $str_EMAIL;?>" size="45" maxlength="100"></td>
	</tr>

	<tr>
		<td width="40%"><?echo GetMessage("sender_contact_edit_field_name")?></td>
		<td width="60%"><input type="text" name="NAME" value="<?echo $str_NAME;?>" size="45" maxlength="100"></td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2">
			<table class="sender-mailing-group">
				<tr>
					<td><span class="sender-mailing-group-block-all"><?=GetMessage("sender_contact_edit_field_lists_all");?></td>
					<td class="sender-mailing-group-block-sect-delim"></td>
					<td><span class="sender-mailing-group-block-sel"><?=GetMessage("sender_contact_edit_field_lists_sel");?></td>
				</tr>
				<tr>
					<?ShowGroupControl('LIST', $lists, $listsSelected)?>
				</tr>
			</table>
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2">
			<table class="sender-mailing-group">
				<tr>
					<td><span class="sender-mailing-group-block-all"><?=GetMessage("sender_contact_edit_field_sub_all");?></td>
					<td class="sender-mailing-group-block-sect-delim"></td>
					<td><span class="sender-mailing-group-block-sel"><?=GetMessage("sender_contact_edit_field_sub_sel");?></td>
				</tr>
				<tr>
					<?ShowGroupControl('SUB_LIST', $mailingSubList, $subSelected)?>
				</tr>
			</table>
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2">
			<table class="sender-mailing-group">
				<tr>
					<td><span class="sender-mailing-group-block-all"><?=GetMessage("sender_contact_edit_field_sub_all");?></td>
					<td class="sender-mailing-group-block-sect-delim"></td>
					<td><span class="sender-mailing-group-block-sel"><?=GetMessage("sender_contact_edit_field_unsub_sel");?></td>
				</tr>
				<tr>
					<?ShowGroupControl('UNSUB_LIST', $mailingUnSubList, $unsubSelected)?>
				</tr>
			</table>
		</td>
	</tr>
<?
$tabControl->Buttons(
	array(
		"disabled"=>($POST_RIGHT<"W"),
		"back_url"=>"sender_contact_admin.php?lang=".LANG,

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