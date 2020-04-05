<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2004 Bitrix             #
# http://www.bitrixsoft.com                  #
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
$APPLICATION->SetTitle(GetMessage("MAIL_LOG_TITLE"));

$sTableID = "t_mail_log";
$oSort = new CAdminSorting($sTableID, "date_insert", "desc");// инициализация сортировки
$lAdmin = new CAdminList($sTableID, $oSort);// инициализация списка

$filter = new CAdminFilter(
	$sTableID."_f_id", 
	array(
		GetMessage("MAIL_LOG_FILT_MBOX"),
		GetMessage("MAIL_LOG_FILT_RULE")
	)
);

$arFilterFields = Array(
	"find_message_subject",
	"find_show_mess",
	"find_mailbox_id",
	"find_filter_id",
	"find_show_filt"
);

$lAdmin->InitFilter($arFilterFields);//инициализация фильтра

if($find_filter_id>0 && $find_mailbox_id)
{
	$mf = CMailFilter::GetList(Array(), Array("MAILBOX_ID"=>$find_mailbox_id, "FILTER_ID"=>$find_filter_id));
	if(!$mf->Fetch())
	{
		$find_filter_id = "";
	}
}

$arFilter = Array(
	"ID"=>$find_id,
	"MAILBOX_ID"=>$find_mailbox_id,
	"FILTER_ID"=>$find_filter_id,
	"MESSAGE_SUBJECT"=>$find_message_subject,
);

$nav = new Bitrix\Main\UI\AdminPageNavigation('nav-mail-log');

$log = Bitrix\Mail\MailLogTable::getList(array(
	'select'      => array(
		'*', 'MAILBOX_NAME' => 'MAILBOX.NAME', 'FILTER_NAME' => 'FILTER.NAME', 'MESSAGE_SUBJECT' => 'MAIL_MESSAGE.SUBJECT'
	),
	'filter'      => array_filter($arFilter),
	'order'       => array(strtoupper($by) => $order),
	'offset'      => $nav->getOffset(),
	'limit'       => $nav->getLimit(),
	'count_total' => true,
));

$nav->setRecordCount($log->getCount());

$lAdmin->setNavigation($nav, Bitrix\Main\Localization\Loc::getMessage("MAIL_LOG_NAVIGATION"));

$arHeaders = Array();
$arHeaders[] = Array("id"=>"DATE_INSERT", "content"=>GetMessage("MAIL_LOG_TIME"), "default"=>true, "sort" => "date_insert");
$arHeaders[] = Array("id"=>"MESSAGE", "content"=>GetMessage("MAIL_LOG_TEXT"), "default"=>true, "sort" => "message");
$arHeaders[] = Array("id"=>"MAILBOX_NAME", "content"=>GetMessage("MAIL_LOG_MBOX"), "default"=>true, "sort" => "mailbox_name");
if($find_show_filt=="Y")
	$arHeaders[] = Array("id"=>"FILTER_NAME", "content"=>GetMessage("MAIL_LOG_RULE"), "default"=>true, "sort" => "filter_name");

if($find_show_mess=="Y")
	$arHeaders[] = Array("id"=>"MESSAGE_SUBJECT", "content"=>GetMessage("MAIL_LOG_MSG"), "default"=>true, "sort" => "message_subject");

$lAdmin->AddHeaders($arHeaders);

// построение списка
while($arRes = $log->fetch())
{
	$arRes = CMailLog::ConvertRow($arRes);
	$row =& $lAdmin->AddRow($arRes['ID'], $arRes);

	$arRes['MESSAGE_TEXT'] = htmlspecialcharsbx($arRes['MESSAGE_TEXT']);

	if($arRes["STATUS_GOOD"]=="Y"):
		if (strpos($arRes["MESSAGE_TEXT"], "&gt;")===0)
			$str = '<span style="color:green">'.$arRes["MESSAGE_TEXT"].'</span>';
		elseif (strpos($arRes["MESSAGE_TEXT"], "&lt;")===0)
			$str = '<span style="color:blue">'.$arRes["MESSAGE_TEXT"].'</span>';
		else 
			$str = $arRes["MESSAGE_TEXT"];
	else:
		$str = '<span style="color:red">'.$arRes["MESSAGE_TEXT"].'</span>';
	endif;

	$row->AddViewField("MESSAGE", $str);

	if ($find_show_filt=="Y")
		$row->AddViewField("FILTER_NAME", $arRes["FILTER_NAME"]);

	if($find_show_mess=="Y")
		$row->AddViewField("MESSAGE_SUBJECT", $arRes["MESSAGE_SUBJECT"]);
}

$lAdmin->AddAdminContextMenu();
$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>


<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>
<tr>
	<td valign="top" nowrap><?echo GetMessage("MAIL_LOG_FILT_MSG")?></td>
	<td nowrap><input type="text" name="find_message_subject" value="<?echo htmlspecialcharsbx($find_message_subject)?>" size="47"><?=ShowFilterLogicHelp()?><br><input type="hidden" name="find_show_mess" value="N">
	<input type="checkbox" name="find_show_mess" value="Y"<?if($find_show_mess=="Y")echo " checked"?> id="find_show_mess"> <label for="find_show_mess"><?echo GetMessage("MAIL_LOG_FILT_SHOW_COLUMN")?></label>
	
	</td>
</tr>


<tr>
	<td nowrap><?echo GetMessage("MAIL_LOG_FILT_MBOX")?>:</td>
	<td nowrap>
		<select name="find_mailbox_id">
			<option value=""><?echo GetMessage("MAIL_LOG_FILT_ANY")?></option>
			<?
			ClearVars("mb_");
			$l = CMailbox::GetList(array('NAME' => 'ASC', 'ID' => 'ASC'), array('VISIBLE' => 'Y', 'USER_ID' => 0));
			while($l->ExtractFields("mb_")):
				?><option value="<?echo $mb_ID?>"<?if($find_mailbox_id==$mb_ID)echo " selected"?>><?echo $mb_NAME?></option><?
			endwhile;
			?>
		</select>
		</td>
</tr>
<tr>
	<td valign="top" nowrap><?echo GetMessage("MAIL_LOG_FILT_RULE")?>:</td>
	<td nowrap>
		<select name="find_filter_id">
			<option value=""><?echo GetMessage("MAIL_LOG_FILT_ANY")?></option>
			<?
			ClearVars("mf_");
			$arF = Array();
			if($find_mailbox_id>0) $arF["MAILBOX_ID"] = $find_mailbox_id;
			$l = CMailFilter::GetList(Array("NAME"=>"ASC", "ID"=>"ASC"), $arF);
			while($l->ExtractFields("mf_")):
				?><option value="<?echo $mf_ID?>"<?if($find_filter_id==$mf_ID)echo " selected"?>><?echo $mf_NAME?></option><?
			endwhile;
			?>
		</select><br><input type="hidden" name="find_show_filt" value="N"><input type="checkbox" name="find_show_filt" value="Y"<?if($find_show_filt=="Y")echo " checked"?> id="find_show_filt"> <label for="find_show_filt"><?echo GetMessage("MAIL_LOG_FILT_SHOW_COLUMN")?></label>
		
		</td>
</tr>

<?
$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));
$filter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>