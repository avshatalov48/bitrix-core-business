<?
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
require_once(__DIR__."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/sms_template_edit.php");

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Sms\TemplateTable;
use Bitrix\Main\Mail\Internal\EventTypeTable;

function fillTemplateFromPost(Main\Sms\Template $template)
{
	static $formFields = ["EVENT_NAME", "ACTIVE", "SENDER", "RECEIVER", "MESSAGE", "LANGUAGE_ID"];

	$request = Main\Context::getCurrent()->getRequest();

	$fields = $template->entity->getFields();

	//set values from the form
	foreach($formFields as $fieldName)
	{
		$value = $request->getPost($fieldName);
		if($fields[$fieldName] instanceof Main\ORM\Fields\BooleanField)
		{
			$value = ($value == "Y");
		}
		$template->set($fieldName, $value);
	}

	if(is_array($request->getPost("LID")))
	{
		foreach($request->getPost("LID") as $lid)
		{
			$site = Main\SiteTable::getEntity()->wakeUpObject($lid);
			$template->addToSites($site);
		}
	}
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => Loc::getMessage("sms_template_edit_tab"), "ICON" => "message_edit", "TITLE" => Loc::getMessage("sms_template_edit_tab_title")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$request = Main\Context::getCurrent()->getRequest();

$errors = array();
$ID = intval($request["ID"]);
$COPY_ID = intval($request["COPY_ID"]);

$entity = TemplateTable::getEntity();
$fields = $entity->getFields();

if($request->isPost() && ($request["save"] <> '' || $request["apply"] <> '') && $isAdmin && check_bitrix_sessid())
{
	if(empty($request->getPost("LID")))
	{
		$errors[] = GetMessage("sms_template_edit_err_site");
	}

	if(empty($errors))
	{
		if($ID > 0)
		{
			$template = $entity->wakeUpObject($ID);
			$template->removeAllSites();
		}
		else
		{
			$template = $entity->createObject();
		}

		//set values from the form
		fillTemplateFromPost($template);

		$result = $template->save();

		if($result instanceof Main\ORM\Data\AddResult)
		{
			$ID = $result->getId();
		}

		if($result->isSuccess())
		{
			if($request["save"] <> '')
				LocalRedirect(BX_ROOT."/admin/sms_template_admin.php?lang=".LANGUAGE_ID);
			else
				LocalRedirect(BX_ROOT."/admin/sms_template_edit.php?lang=".LANGUAGE_ID."&ID=".$ID."&".$tabControl->ActiveTabParam());
		}
		else
		{
			$errors = $result->getErrorMessages();
		}
	}
}

if($ID > 0 || $COPY_ID > 0)
{
	//existing
	$templateId = ($COPY_ID > 0? $COPY_ID : $ID);
	$template = TemplateTable::getById($templateId)->fetchObject();
	$template->fillSites();
}
else
{
	//new
	$template = $entity->createObject();
	$template->setEventName($request->getQuery("EVENT_NAME"));
}

if(!empty($errors))
{
	//set values from the form
	fillTemplateFromPost($template);
}

$APPLICATION->SetTitle(($ID > 0? Loc::getMessage("sms_template_edit_title") : Loc::getMessage("sms_template_edit_add")));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"	=> Loc::getMessage("sms_template_edit_list"),
		"LINK"	=> "sms_template_admin.php?lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("sms_template_edit_list_title"),
		"ICON"	=> "btn_list"
	)
);

if($ID > 0 && $isAdmin)
{
	$aMenu[] = array("SEPARATOR"=>"Y");

	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("sms_template_edit_add_btn"),
		"LINK"	=> "sms_template_edit.php?lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("sms_template_edit_add_btn_title"),
		"ICON"	=> "btn_new"
	);
	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("sms_template_edit_copy"),
		"LINK"	=> "sms_template_edit.php?lang=".LANGUAGE_ID."&amp;COPY_ID=".$ID,
		"TITLE"	=> Loc::getMessage("sms_template_edit_copy_title"),
		"ICON"	=> "btn_copy"
	);
	$aMenu[] = array(
		"TEXT"	=> Loc::getMessage("sms_template_edit_del"),
		"LINK"	=> "javascript:if(confirm('".CUtil::JSEscape(Loc::getMessage("sms_template_edit_del_conf"))."')) window.location='sms_template_admin.php?ID=".$ID."&lang=".LANGUAGE_ID."&".bitrix_sessid_get()."&action_button=delete';",
		"TITLE"	=> Loc::getMessage("sms_template_edit_del_title"),
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
<script type="text/javascript">
window.bxCurrentControl = null;
function PutString(str)
{
	if(window.bxCurrentControl)
	{
		window.bxCurrentControl.value += str;
	}
}
</script>

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
		<td><?= $fields["ID"]->getTitle()?>:</td>
		<td><?= $ID?></td>
	</tr>
<?endif?>
	<tr class="adm-detail-required-field">
		<td><?= $fields["EVENT_NAME"]->getTitle()?>:</td>
		<td><?
			$eventTypes = array();
			$eventTypesDb = EventTypeTable::getList(array(
				'filter' => array(
					"=LID" => LANGUAGE_ID,
					"=EVENT_TYPE" => EventTypeTable::TYPE_SMS
				),
				'order' => array("NAME" => "ASC"),
			));
			while($eventType = $eventTypesDb->fetch())
			{
				$eventTypes[$eventType["EVENT_NAME"]] = $eventType;
			}

			if($ID > 0 && $COPY_ID <= 0):
			?>
				<input type="hidden" name="EVENT_NAME" value="<?= HtmlFilter::encode($template->getEventName())?>">
				<?
					$type = $eventTypes[$template->getEventName()];
					echo HtmlFilter::encode($type["NAME"]." [".$type["EVENT_NAME"]."]");
				?>
			<?else:?>
				<select name="EVENT_NAME" onchange="window.location='sms_template_edit.php?lang=<?=LANGUAGE_ID?>&EVENT_NAME='+this[this.selectedIndex].value">
				<?foreach($eventTypes as $type):?>
					<option value="<?= HtmlFilter::encode($type["EVENT_NAME"])?>"<?if($type["EVENT_NAME"] == $template->getEventName()) echo " selected";?>>
						<?= HtmlFilter::encode($type["NAME"]." [".$type["EVENT_NAME"]."]")?>
					</option>
				<?endforeach;?>
				</select>
			<?endif;?>
		</td>
	</tr>
	<tr>
		<td><label for="active"><?= $fields["ACTIVE"]->getTitle()?>:</label></td>
		<td><input type="checkbox" name="ACTIVE" id="active" value="Y"<?if($template->getActive()) echo " checked"?>></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td class="adm-detail-valign-top"><?echo Loc::getMessage("sms_template_edit_sites")?></td>
		<td><?
			$sites = $template->getSites();
			echo CLang::SelectBoxMulti("LID", ($sites? $sites->getLidList() : []));
			?></td>
	</tr>
	<tr>
		<td><?= $fields["LANGUAGE_ID"]->getTitle()?>:</td>
		<td>
			<select name="LANGUAGE_ID">
				<option value=""><?echo Loc::getMessage("sms_template_edit_not_set")?></option>
				<?
				$languages = Main\Localization\LanguageTable::getList(array(
					"filter" => array("=ACTIVE" => "Y"),
					"order" => array("SORT" => "ASC", "NAME" => "ASC")
				));
				?>
				<? while($language = $languages->fetch()): ?>
					<option value="<?=$language["LID"]?>"<? if($template->getLanguageId() == $language["LID"]) echo " selected" ?>>
						<?= HtmlFilter::encode($language["NAME"])?>
					</option>
				<? endwhile ?>
			</select>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo Loc::getMessage("sms_template_edit_mess")?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?= $fields["SENDER"]->getTitle()?>:</td>
		<td><input type="text" name="SENDER" size="30" maxlength="50" value="<?= HtmlFilter::encode($template->getSender())?>" onfocus="window.bxCurrentControl=this"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?= $fields["RECEIVER"]->getTitle()?>:</td>
		<td><input type="text" name="RECEIVER" size="30" maxlength="50" value="<?= HtmlFilter::encode($template->getReceiver())?>" onfocus="window.bxCurrentControl=this"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?= $fields["MESSAGE"]->getTitle()?>:</td>
		<td><textarea name="MESSAGE" cols="40" rows="7" onfocus="window.bxCurrentControl=this"><?=HtmlFilter::encode($template->getMessage())?></textarea></td>
	</tr>
<?
	$defaultFields =
		"#DEFAULT_SENDER# - ".Loc::getMessage("sms_template_edit_field_senser")."
		#SITE_NAME# - ".Loc::getMessage("sms_template_edit_field_site")."
		#SERVER_NAME# - ".Loc::getMessage("sms_template_edit_field_server");

	if($template->getEventName())
	{
		$eventFields = $eventTypes[$template->getEventName()]["DESCRIPTION"];
	}
	else
	{
		$eventFields = reset($eventTypes)["DESCRIPTION"];
	}
	$allFields = HtmlFilter::encode(trim($eventFields)."\r\n".$defaultFields);
	$allFields = preg_replace("/(#.+?#)/", '<a title="'.Loc::getMessage("sms_template_edit_insert").'" href="javascript:PutString(\'\\1\')">\\1</a>', $allFields);
?>
	<tr>
		<td align="left" colspan="2"><b><?echo Loc::getMessage("sms_template_edit_fields")?></b><br><br>
			<?= nl2br($allFields);?></td>
	</tr>
<?
$tabControl->Buttons(array("disabled"=>!$isAdmin, "back_url"=>"sms_template_admin.php?lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>

<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
