<?
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
require_once(__DIR__."/../include/prolog_admin_before.php");
define("HELP_FILE", "utilities/log_notification_edit.php");

if(!$USER->CanDoOperation('view_event_log'))
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\EventLog;

$aTabs = array(
	array("DIV" => "edit1", "TAB" => Loc::getMessage("notification_edit_conditions"), "ICON" => "message_edit", "TITLE" => Loc::getMessage("notification_edit_conditions_title")),
	array("DIV" => "edit2", "TAB" => Loc::getMessage("notification_edit_actions"), "ICON" => "message_edit", "TITLE" => Loc::getMessage("notification_edit_actions_title")),
);
$tabControl = new CAdminTabControl("notifyTabControl", $aTabs);

$request = Main\Context::getCurrent()->getRequest();

$errors = [];
$actions = [];
$ID = intval($request["ID"]);
$COPY_ID = intval($request["COPY_ID"]);

if($request->isPost() && ($request["save"] <> '' || $request["apply"] <> '') && check_bitrix_sessid())
{
	if(is_array($request->getPost("ACTIONS")))
	{
		$actions = $request->getPost("ACTIONS");
	}

	$notification = new EventLog\Notification($ID);

	$notification->setFromArray($request->getPostList()->toArray());
	$notification->setActionsFromArray($actions);

	$result = $notification->save();

	if($result instanceof Main\ORM\Data\AddResult)
	{
		$ID = $result->getId();
	}

	if($result->isSuccess())
	{
		if($request["save"] <> '')
			LocalRedirect(BX_ROOT."/admin/log_notifications.php?lang=".LANGUAGE_ID);
		else
			LocalRedirect(BX_ROOT."/admin/log_notification_edit.php?lang=".LANGUAGE_ID."&ID=".$ID."&".$tabControl->ActiveTabParam());
	}
	else
	{
		$errors = $result->getErrorMessages();
	}
}

$notificationId = ($COPY_ID > 0? $COPY_ID : $ID);
$notification = new EventLog\Notification($notificationId);

$notification->fill();
$notification->fillActions();

if(!empty($errors))
{
	//set values from the form
	$notification->setFromArray($request->getPostList()->toArray());
	$notification->setActionsFromArray($actions);
}

$APPLICATION->SetTitle(($ID > 0? Loc::getMessage("notification_edit_title_edit") : Loc::getMessage("notification_edit_title_add")));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"	=> Loc::getMessage("notification_edit_list"),
		"LINK"	=> "log_notifications.php?lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("notification_edit_list_title"),
		"ICON"	=> "btn_list"
	)
);

if($ID > 0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("notification_edit_add"),
		"LINK"	=> "log_notification_edit.php?lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("notification_edit_add_title"),
		"ICON"	=> "btn_new"
	);
	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("notification_edit_copy"),
		"LINK"	=> "log_notification_edit.php?lang=".LANGUAGE_ID."&amp;COPY_ID=".$ID,
		"TITLE"	=> Loc::getMessage("notification_edit_copy_title"),
		"ICON"	=> "btn_copy"
	);
	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("notification_edit_delete"),
		"LINK"	=> "javascript:if(confirm('".CUtil::JSEscape(Loc::getMessage("notification_edit_delete_conf"))."')) window.location='log_notifications.php?ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&action_button=delete';",
		"TITLE"	=> Loc::getMessage("notification_edit_delete_title"),
		"ICON"	=> "btn_delete"
	);
}

$context = new CAdminContextMenu($aMenu);
$context->Show();

if(!empty($errors))
{
	CAdminMessage::ShowMessage(join("\n", $errors));
}
?>

<form method="POST" action="<?= HtmlFilter::encode($request->getRequestedPage())?>" name="form1">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<input type="hidden" name="ID" value="<?= $ID?>">
<?if($COPY_ID > 0):?><input type="hidden" name="COPY_ID" value="<?= $COPY_ID?>"><?endif?>
<?
$tabControl->Begin();

$tabControl->BeginNextTab();
?>
<?if($ID > 0):?>
	<tr>
		<td>ID:</td>
		<td><?= $ID?></td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("notification_edit_date_check")?></td>
		<td><?= $notification->getDateChecked()?></td>
	</tr>
<?endif?>
	<tr>
		<td><label for="active"><?echo Loc::getMessage("notification_edit_active")?></label></td>
		<td><input type="checkbox" name="ACTIVE" id="active" value="Y"<?if($notification->getActive()) echo " checked"?>></td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("notification_edit_name")?></td>
		<td><input type="text" name="NAME" size="30" value="<?= HtmlFilter::encode($notification->getName())?>"></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo Loc::getMessage("notification_edit_header")?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo Loc::getMessage("notification_edit_event_type")?></td>
		<td>
			<select name="AUDIT_TYPE_ID">
				<option value=""><?echo Loc::getMessage("notification_edit_event_type_choose")?></option>
				<?
				$types = CEventLog::GetEventTypes();
				?>
				<? foreach($types as $typeId => $typeName): ?>
					<option value="<?=HtmlFilter::encode($typeId)?>"<? if($notification->getAuditTypeId() == $typeId) echo " selected" ?>>
						<?= HtmlFilter::encode($typeName)?>
					</option>
				<? endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("notification_edit_object")?></td>
		<td><input type="text" name="ITEM_ID" size="30" value="<?= HtmlFilter::encode($notification->getItemId())?>"></td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("notification_edit_user")?></td>
		<td><input type="text" name="USER_ID" size="30" value="<?= HtmlFilter::encode($notification->getUserId())?>"></td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("notification_edit_ip_addr")?></td>
		<td><input type="text" name="REMOTE_ADDR" size="30" value="<?= HtmlFilter::encode($notification->getRemoteAddr())?>"></td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("notification_edit_browser")?></td>
		<td><input type="text" name="USER_AGENT" size="30" value="<?= HtmlFilter::encode($notification->getUserAgent())?>"></td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("notification_edit_uri")?></td>
		<td><input type="text" name="REQUEST_URI" size="30" value="<?= HtmlFilter::encode($notification->getRequestUri())?>"></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo Loc::getMessage("notification_edit_conditions_header")?></td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("notification_edit_interval")?></td>
		<td><input type="text" name="CHECK_INTERVAL" size="30" value="<?= HtmlFilter::encode($notification->getCheckInterval())?>"></td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("notification_edit_number")?></td>
		<td><input type="text" name="ALERT_COUNT" size="30" value="<?= HtmlFilter::encode($notification->getAlertCount())?>"></td>
	</tr>

<?$tabControl->BeginNextTab();?>

<?
foreach($notification->getActions() as $i => $action):
?>
	<tr class="heading">
		<td colspan="2">
			<?echo Loc::getMessage("notification_edit_action")?>
			<input type="hidden" name="ACTIONS[<?=$i?>][ID]" value="<?= HtmlFilter::encode($i)?>">
		</td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("notification_edit_action_type")?></td>
		<td><select name="ACTIONS[<?=$i?>][NOTIFICATION_TYPE]">
				<option value="<?=EventLog\Action::TYPE_EMAIL?>"<?if($action->getType() == EventLog\Action::TYPE_EMAIL) echo " selected"?>><?echo Loc::getMessage("notification_edit_action_type_email")?></option>
				<option value="<?=EventLog\Action::TYPE_SMS?>"<?if($action->getType() == EventLog\Action::TYPE_SMS) echo " selected"?>><?echo Loc::getMessage("notification_edit_action_type_sms")?></option>
			</select></td>
	</tr>
	<tr>
		<td><?echo Loc::getMessage("notification_edit_receiver")?></td>
		<td><input type="text" name="ACTIONS[<?=$i?>][RECIPIENT]" size="30" value="<?= HtmlFilter::encode($action->getRecipient())?>"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo Loc::getMessage("notification_edit_text")?></td>
		<td><textarea name="ACTIONS[<?=$i?>][ADDITIONAL_TEXT]" cols="40" rows="3"><?= HtmlFilter::encode($action->getText())?></textarea></td>
	</tr>
	<tr>
		<td></td>
		<td><a class="bx-action-href" href="javascript:void(0)" onclick="BxDeleteNotificationAction(this)"><?echo Loc::getMessage("notification_edit_action_delete")?></a></td>
	</tr>
<?endforeach;?>
	<tr id="bx_add_notification_action_row">
		<td colspan="2">
			<a class="bx-action-href" href="javascript:void(0)" onclick="BxAddNotificationAction()"><?echo Loc::getMessage("notification_edit_action_add")?></a>
		</td>
	</tr>

<?
$tabControl->Buttons(array("back_url"=>"log_notifications.php?lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>
<?
$messages = [
	"log_notification_edit_action" => Loc::getMessage("notification_edit_action"),
	"log_notification_edit_type" => Loc::getMessage("notification_edit_action_type"),
	"log_notification_edit_email" => Loc::getMessage("notification_edit_action_type_email"),
	"log_notification_edit_sms" => Loc::getMessage("notification_edit_action_type_sms"),
	"log_notification_edit_receiver" => Loc::getMessage("notification_edit_receiver"),
	"log_notification_edit_text" => Loc::getMessage("notification_edit_text"),
	"log_notification_edit_delete" => Loc::getMessage("notification_edit_action_delete"),
];
?>
<script>
BX.message(<?=CUtil::PhpToJSObject($messages)?>);

function BxAddNotificationAction()
{
	var lastRow = BX("bx_add_notification_action_row");
	var table = BX.findParent(lastRow, {'tag': 'table'});
	var rowIndex = lastRow.rowIndex;

	var tableRows = [
		{
			'className': 'heading',
			'cells': [
				{
					'colSpan': 2,
					'innerHTML': BX.message("log_notification_edit_action") + '\n' + '<input type="hidden" name="ACTIONS[#][ID]" value="">'
				}
			]
		},
		{
			'cells': [
				{
					'innerHTML': BX.message("log_notification_edit_type")
				},
				{
					'innerHTML': '<select name="ACTIONS[#][NOTIFICATION_TYPE]">\n' +
						'<option value="email">'+BX.message("log_notification_edit_email")+'</option>\n' +
						'<option value="sms">'+BX.message("log_notification_edit_sms")+'</option>\n' +
						'</select>'
				}
			]
		},
		{
			'cells': [
				{
					'innerHTML': BX.message("log_notification_edit_receiver")
				},
				{
					'innerHTML': '<input type="text" name="ACTIONS[#][RECIPIENT]" size="30" value="">'
				}
			]
		},
		{
			'cells': [
				{
					'className': "adm-detail-valign-top",
					'innerHTML': BX.message("log_notification_edit_text")
				},
				{
					'innerHTML': '<textarea name="ACTIONS[#][ADDITIONAL_TEXT]" cols="40" rows="3"></textarea>'
				}
			]
		},
		{
			'cells': [
				{
					'innerHTML': ''
				},
				{
					'innerHTML': '<a class="bx-action-href" href="javascript:void(0)" onclick="BxDeleteNotificationAction(this)">'+BX.message("notification_edit_action_delete")+'</a>'
				}
			]
		}
	];

	for(var i = 0; i < tableRows.length; i++)
	{
		var row = table.insertRow(rowIndex + i);
		row.className = tableRows[i].className || "";

		for(var j = 0; j < tableRows[i].cells.length; j++)
		{
			var cell = row.insertCell(j);
			cell.className = (j === 0? "adm-detail-content-cell-l" : "adm-detail-content-cell-r") + ' ' + (tableRows[i].cells[j].className || '');
			cell.colSpan = tableRows[i].cells[j].colSpan || 1;
			cell.innerHTML = tableRows[i].cells[j].innerHTML.replace("#", "new"+rowIndex);
		}
	}
}

function BxDeleteNotificationAction(ob)
{
	var row = BX.findParent(ob, {'tag': 'tr'});
	var rowIndex = row.rowIndex;
	var table = BX.findParent(row, {'tag': 'table'});

	for(var i = 0; i < 5; i++)
	{
		table.deleteRow(rowIndex - 4);
	}
}
</script>

<?
if(\Bitrix\Main\ModuleManager::isModuleInstalled("messageservice"))
{
	$url = "/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&amp;mid=messageservice";
	$smsNote = Loc::getMessage("notification_edit_sms_note1", ["#URL#" => $url]);
}
else
{
	$url = "/bitrix/admin/module_admin.php?lang=".LANGUAGE_ID;
	$smsNote = Loc::getMessage("notification_edit_sms_note2", ["#URL#" => $url]);
}
?>
<?=BeginNote()?>
	<?=$smsNote?><br><br>
	<a href="message_admin.php?PAGEN_1=1&amp;lang=<?=LANGUAGE_ID?>&amp;set_filter=Y&amp;find_type_id=<?=EventLog\ActionEmail::EVENT_TYPE?>"><?echo Loc::getMessage("notification_edit_mail_templates")?></a><br>
	<a href="sms_template_admin.php?lang=<?=LANGUAGE_ID?>&amp;set_filter=Y&amp;find_event_name_id=<?=EventLog\ActionSms::EVENT_TYPE?>&amp;nav-sms-template=page-1"><?echo Loc::getMessage("notification_edit_sms_templates")?></a><br><br>
	<?echo Loc::getMessage("notification_edit_def_site")?>
<?=EndNote()?>

<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
