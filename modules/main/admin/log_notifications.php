<?php
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
require_once(__DIR__."/../include/prolog_admin_before.php");
define("HELP_FILE", "utilities/log_notifications.php");

if(!$USER->CanDoOperation('view_event_log'))
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

use Bitrix\Main;
use Bitrix\Main\EventLog\Notification;
use Bitrix\Main\EventLog\Internal\LogNotificationTable;
use Bitrix\Main\Localization\Loc;

$tableID = "tbl_notifications";
$sorting = new CAdminSorting($tableID, "id", "asc");
$adminList = new CAdminList($tableID, $sorting);

$request = Main\Context::getCurrent()->getRequest();

if($adminList->EditAction())
{
	foreach($request["FIELDS"] as $ID => $arFields)
	{
		if(!$adminList->IsUpdated($ID))
			continue;

		$notification = new Notification($ID);
		$notification->setFromArray($arFields);
		$result = $notification->save();

		if(!$result->isSuccess())
		{
			$adminList->AddUpdateError("(ID=".$ID.") ".implode("<br>", $result->getErrorMessages()), $ID);
		}
	}
}

if(($arID = $adminList->GroupAction()))
{
	if($request['action_target'] == 'selected')
	{
		$arID = array();
		$data = LogNotificationTable::getList();
		while($notification = $data->fetch())
			$arID[] = $notification['ID'];
	}

	foreach($arID as $ID)
	{
		if(intval($ID) <= 0)
			continue;

		switch($request['action_button'])
		{
			case "delete":
				$notification = new Notification($ID);
				$result = $notification->delete();

				if(!$result->isSuccess())
				{
					$adminList->AddGroupError("(ID=".$ID.") ".implode("<br>", $result->getErrorMessages()), $ID);
				}
				break;
		}
	}
}

$APPLICATION->SetTitle(Loc::getMessage("log_notifications_title"));

$types = CEventLog::GetEventTypes();

$sortBy = mb_strtoupper($sorting->getField());
if(!LogNotificationTable::getEntity()->hasField($sortBy))
{
	$sortBy = "ID";
}

$sortOrder = mb_strtoupper($sorting->getOrder());
if($sortOrder <> "DESC")
{
	$sortOrder = "ASC";
}

$nav = new \Bitrix\Main\UI\AdminPageNavigation("nav-notify");

$notifyList = LogNotificationTable::getList(array(
	'order' => array($sortBy => $sortOrder),
	'count_total' => true,
	'offset' => $nav->getOffset(),
	'limit' => $nav->getLimit(),
));

$nav->setRecordCount($notifyList->getCount());

$adminList->setNavigation($nav, Loc::getMessage("PAGES"));

$adminList->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"ACTIVE", "content"=>Loc::getMessage("log_notifications_active"), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"NAME", "content"=>Loc::getMessage("log_notifications_name"), "sort"=>"NAME", "default"=>true),
	array("id"=>"AUDIT_TYPE_ID", "content"=>Loc::getMessage("log_notifications_type"), "sort"=>"AUDIT_TYPE_ID", "default"=>true),
	array("id"=>"ITEM_ID", "content"=>Loc::getMessage("log_notifications_object"), "sort"=>"ITEM_ID", "default"=>true),
	array("id"=>"USER_ID", "content"=>Loc::getMessage("log_notifications_user"), "sort"=>"USER_ID", "default"=>true),
	array("id"=>"CHECK_INTERVAL", "content"=>Loc::getMessage("log_notifications_interval"), "sort"=>"CHECK_INTERVAL", "default"=>true),
	array("id"=>"ALERT_COUNT", "content"=>Loc::getMessage("log_notifications_count"), "sort"=>"ALERT_COUNT", "default"=>true),
	array("id"=>"DATE_CHECKED", "content"=>Loc::getMessage("log_notifications_date_last"), "sort"=>"DATE_CHECKED", "default"=>true),
	array("id"=>"REMOTE_ADDR", "content"=>Loc::getMessage("log_notifications_ip"), "sort"=>"REMOTE_ADDR", "default"=>false),
	array("id"=>"USER_AGENT", "content"=>Loc::getMessage("log_notifications_browser"), "sort"=>"USER_AGENT", "default"=>false),
	array("id"=>"REQUEST_URI", "content"=>Loc::getMessage("log_notifications_page"), "sort"=>"REQUEST_URI", "default"=>false),
));

while($notification = $notifyList->fetch())
{
	$id = htmlspecialcharsbx($notification["ID"]);

	$row = &$adminList->AddRow($id, $notification, "log_notification_edit.php?ID=".$id."&lang=".LANGUAGE_ID, Loc::getMessage("log_notifications_edit"));
	$row->AddViewField("ID", $id);
	$row->AddCheckField("ACTIVE");
	$row->AddInputField("NAME");
	$row->AddViewField("AUDIT_TYPE_ID", '<a href="log_notification_edit.php?ID='.$id.'&amp;lang='.LANGUAGE_ID.'" title="'.Loc::getMessage("log_notifications_edit").'">'.htmlspecialcharsbx($types[$notification["AUDIT_TYPE_ID"]]).'</a>');
	$row->AddInputField("ITEM_ID");
	$row->AddInputField("USER_ID");
	$row->AddInputField("CHECK_INTERVAL");
	$row->AddInputField("ALERT_COUNT");
	$row->AddViewField("DATE_CHECKED", htmlspecialcharsbx($notification["DATE_CHECKED"]));
	$row->AddInputField("REMOTE_ADDR");
	$row->AddInputField("USER_AGENT");
	$row->AddInputField("REQUEST_URI");

	$arActions = array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("log_notifications_edit1"), "ACTION"=>$adminList->ActionRedirect("log_notification_edit.php?ID=".$id));
	$arActions[] = array("ICON"=>"copy", "TEXT"=>Loc::getMessage("log_notifications_copy"), "ACTION"=>$adminList->ActionRedirect("log_notification_edit.php?COPY_ID=".$id));
	$arActions[] = array("SEPARATOR"=>true);
	$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("log_notifications_delete"), "ACTION"=>"if(confirm('".Loc::getMessage("log_notifications_delete_conf")."')) ".$adminList->ActionDoGroup($id, "delete"));

	$row->AddActions($arActions);
}

$adminList->AddGroupActionTable(array(
	"delete"=>true,
));

$aContext = array(
	array(
		"TEXT"	=> Loc::getMessage("log_notifications_add"),
		"LINK"	=> "log_notification_edit.php?lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("log_notifications_add_title"),
		"ICON"	=> "btn_new"
	),
);
$adminList->AddAdminContextMenu($aContext);

$adminList->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$adminList->DisplayList();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
