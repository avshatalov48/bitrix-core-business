<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2004 Bitrix             #
# https://www.bitrixsoft.com                 #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/mail/prolog.php");

$MOD_RIGHT = $APPLICATION->GetGroupRight("mail");
if($MOD_RIGHT<"R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);
Bitrix\Main\Loader::includeModule('mail');

$err_mess = "File: ".__FILE__."<br>Line: ";


$sTableID = "t_mailbox_admin";
$oSort = new CAdminSorting($sTableID, "timestamp_x", "desc");// инициализация сортировки
$lAdmin = new CAdminList($sTableID, $oSort);// инициализация списка


$filter = new CAdminFilter(
	$sTableID."_filter_id", 
	array(
		GetMessage("MAIL_MBOX_ADM_TYPE"),
		GetMessage("MAIL_MBOX_ADR"),
		"ID",
		GetMessage("MAIL_MBOX_ADM_USER_TYPE"),
		GetMessage("MAIL_MBOX_ADM_FILT_ACT"),
		GetMessage("MAIL_MBOX_ADM_FILT_LANG"),
	)
);


$arFilterFields = Array(
	"find_name",
	"find_id",
	"find_user_type",
	"find_server",
	"find_server_type",
	"find_active",
	"find_lid",
);

if ($lAdmin->IsDefaultFilter())
{
	$find_user_type = 'admin';
	$set_filter = 'Y';
}

$lAdmin->InitFilter($arFilterFields);//инициализация фильтра


$arFilter = array(
	"ID"          => $find_id,
	"NAME"        => $find_name,
	"LID"         => $find_lid,
	"SERVER_TYPE" => $find_server_type,
	"SERVER"      => $find_server,
	"ACTIVE"      => $find_active
);
if ($find_user_type == 'user')
	$arFilter['!USER_ID'] = 0;
else if ($find_user_type == 'admin')
	$arFilter['USER_ID'] = 0;


if ($MOD_RIGHT=="W" && $lAdmin->EditAction()) //если идет сохранение со списка
{
	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if(!CMailBox::Update($ID, $arFields))
		{
			$e = $APPLICATION->GetException();
			$lAdmin->AddUpdateError(GetMessage("MAIL_SAVE_ERROR")." #".$ID.": ".$e->GetString(), $ID);
			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}


// обработка действий групповых и одиночных
if($MOD_RIGHT=="W" && $arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CMailBox::GetList(Array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;
		$ID = intval($ID);

		switch($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);
				$DB->StartTransaction();
				if(!CMailBox::Delete($ID))
				{
					$DB->Rollback();
					$lAdmin->AddGroupError(GetMessage("MAIL_MBOX_ADM_DELERR"), $ID);
				}
				else
				{
					$DB->Commit();
				}
			break;

			case "activate":
			case "deactivate":

			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!CMailBox::Update($ID, $arFields))
				if($e = $APPLICATION->GetException())
					$lAdmin->AddGroupError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
			break;
		}
	}
}

$rsData = CMailbox::GetList(Array($by=>$order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// установка строки навигации
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("MAIL_MBOX_ADM_NAVIGATION")));


$arHeaders = Array();

$arHeaders[] = Array("id"=>"NAME", "content"=>GetMessage("MAIL_MBOX_ADM_NAME"), "default"=>true, "sort" => "name");
$arHeaders[] = Array("id"=>"ACTIVE", "content"=>GetMessage("MAIL_MBOX_ADM_ACT"), "default"=>true, "sort" => "active");
$arHeaders[] = Array("id"=>"SERVER", "content"=>GetMessage("MAIL_MBOX_ADR"), "default"=>true, "sort" => "server");
$arHeaders[] = Array("id"=>"SERVER_TYPE", "content"=>GetMessage("MAIL_MBOX_ADM_TYPE"), "default"=>true, "sort" => "server_type");
$arHeaders[] = Array("id"=>"LID", "content"=>GetMessage("MAIL_MBOX_ADM_LANG"), "default"=>true, "sort" => "lang");
$arHeaders[] = Array("id"=>"TIMESTAMP_X", "content"=>GetMessage("MAIL_MBOX_ADM_DATECH"), "default"=>true, "sort" => "timestamp_x");
$arHeaders[] = Array("id"=>"ID", "content"=>"ID", "default"=>true, "sort" => "id");

$lAdmin->AddHeaders($arHeaders);

// построение списка
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$str = "mail_filter_admin.php?lang=".LANG."&find_mailbox_id=".$f_ID."&set_filter=Y";
	$row->AddViewField("MAILBOX_NAME", $str);


	$row->AddCheckField("ACTIVE");
	$row->AddInputField("NAME", Array("size"=>"35"));
	$row->AddInputField("SERVER", Array("size"=>"35"));

	$arActions = Array();


	$rules = CMailFilter::GetList(Array(), Array("MAILBOX_ID"=>$f_ID), true);
	$res = $rules->Fetch();

	if ($arRes['USER_ID'] == 0)
	{
		$arActions[] = array(
			"ICON"=>"list",
			"TEXT"=>GetMessage("MAIL_MBOX_ADM_RULES_LINK")." (".intval($res["CNT"]).")",
			"ACTION"=>$lAdmin->ActionRedirect("mail_filter_admin.php?set_filter=Y&find_mailbox_id=".$f_ID."&lang=".LANG)
		);

		$arActions[] = array(
			"ICON"=>"add",
			"TEXT"=>GetMessage("MAIL_MBOX_ADM_NEWRULE"),
			"ACTION"=>$lAdmin->ActionRedirect("mail_filter_edit.php?MAILBOX_ID=".$f_ID."&lang=".LANG)
		);

		$arActions[] = array("SEPARATOR"=>true);

		$arActions[] = array(
			"ICON"=>"list",
			"TEXT"=>GetMessage("MAIL_MBOX_ADM_LOG"),
			"ACTION"=>$lAdmin->ActionRedirect("mail_log.php?set_filter=Y&find_mailbox_id=".$f_ID."&lang=".LANG)
		);

		$msgs = CMailMessage::GetList(Array(), Array("MAILBOX_ID"=>$f_ID), true);
		$res = $msgs->Fetch();

		$arActions[] = array(
			"ICON"=>"list",
			"TEXT"=>GetMessage("MAIL_MBOX_ADM_MESSAGES")." (".intval($res["CNT_NEW"])." / ".intval($res["CNT"]).")",
			"ACTION"=>$lAdmin->ActionRedirect("mail_message_admin.php?set_filter=Y&find_mailbox_id=".$f_ID."&lang=".LANG)
		);

		$arActions[] = array("SEPARATOR"=>true);
	}

	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT" => "Y",
		"TEXT"=>GetMessage("MAIL_MBOX_ADM_CHANGE2"),
		"ACTION"=>$lAdmin->ActionRedirect("mail_mailbox_edit.php?ID=".$f_ID."&lang=".LANG)
	);

	if ($MOD_RIGHT=="W")
	{
		$arActions[] = array("SEPARATOR"=>true);

		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("MAIL_MBOX_ADM_DELETE"),
			"ACTION"=>"if(confirm('".GetMessage('MAIL_MBOX_ADM_DEL_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"),
		);
	}
	$row->AddActions($arActions);

}

// "подвал" списка
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if ($MOD_RIGHT=="W")
{
	// показ добавление формы с кнопками
	$lAdmin->AddGroupActionTable(Array(
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		)
	);
}

$arSiteMenu = array(
	array(
		"TEXT" => GetMessage("MAIL_MBOX_ADM_USER_TYPE_USER"),
		"ACTION" => "window.location = 'mail_mailbox_edit.php?lang=".LANGUAGE_ID."&mailbox_type=user';"
	),
	array(
		"TEXT" => GetMessage("MAIL_MBOX_ADM_USER_TYPE_ADM"),
		"ACTION" => "window.location = 'mail_mailbox_edit.php?lang=".LANGUAGE_ID."&mailbox_type=admin';"
	)
);
$aContext = array(
	array(
		"ICON" => "btn_new",
		"TEXT" => GetMessage("MAIN_ADD"),
		"LINK" => "mail_mailbox_edit.php?lang=".LANGUAGE_ID."&mailbox_type=".($find_user_type == 'user' ? 'user' : 'admin'),
		"TITLE" => GetMessage("MAIN_ADD"),
		"MENU" => $arSiteMenu
	),
);


$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

//$mailboxes = CMailbox::GetList(Array($by=>$order), $arFilter);
//$is_filtered = $mailboxes->is_filtered;
$APPLICATION->SetTitle(GetMessage("MAIL_MBOX_ADM_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>
<tr>
	<td nowrap><?echo GetMessage("MAIL_MBOX_ADM_FILT_NAME")?>:</td>
	<td nowrap><input type="text" name="find_name" value="<?echo htmlspecialcharsbx($find_name)?>" size="47"><?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td nowrap><?echo GetMessage("MAIL_MBOX_ADM_FILT_TYPE")?></td>
	<td nowrap><?
		$arr = array("reference"=>array("POP3", "SMTP"), "reference_id"=>array("pop3","smtp"));
		echo SelectBoxFromArray("find_server_type", $arr, htmlspecialcharsbx($find_server_type), GetMessage("MAIL_MBOX_ADM_FILT_ANY"));
		?></td>
</tr>

<tr>
	<td nowrap><?echo GetMessage("MAIL_MBOX_ADM_FILT_ADR")?></td>
	<td nowrap><input type="text" name="find_server" value="<?echo htmlspecialcharsbx($find_server)?>" size="47"><?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td nowrap>ID:</td>
	<td nowrap><input type="text" name="find_id" value="<?echo htmlspecialcharsbx($find_id)?>" size="47"><?=ShowFilterLogicHelp()?></td>
</tr>

<td nowrap><?=GetMessage("MAIL_MBOX_ADM_FILT_USER_TYPE"); ?></td>
<td nowrap><?
	$arr = array(
		'reference' => array(GetMessage('MAIL_MBOX_ADM_USER_TYPE_USER'), GetMessage('MAIL_MBOX_ADM_USER_TYPE_ADM')),
		'reference_id' => array('user', 'admin')
	);
	echo SelectBoxFromArray('find_user_type', $arr, htmlspecialcharsbx($find_user_type), GetMessage('MAIL_MBOX_ADM_FILT_ANY'));
	?></td>
</tr>

<tr>
	<td nowrap><?echo GetMessage("MAIL_MBOX_ADM_FILT_ACT")?>:</td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage("MAIL_MBOX_ADM_FILT_ANY"));
		?></td>
</tr>

<tr>
	<td nowrap><?echo GetMessage("MAIL_MBOX_ADM_FILT_LANG")?>:</td>
	<td nowrap>
		<select name="find_lid">
			<option value=""><?echo GetMessage("MAIL_MBOX_ADM_FILT_ANY")?></option>
			<?
			ClearVars("l_");
			$l = CLang::GetList('', '', Array("VISIBLE"=>"Y"));
			while($l->ExtractFields("l_")):
				?><option value="<?echo $l_LID?>"<?if($find_lid==$l_LID)echo " selected"?>><?echo $l_NAME?></option><?
			endwhile;
			?>
		</select>
	</td>
</tr>

<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>

</form>

<?$lAdmin->DisplayList();?>




<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
