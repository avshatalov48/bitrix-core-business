<?php
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
use Bitrix\Main;
use Bitrix\Main\Sms\TemplateTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Internal\EventTypeTable;

require_once(__DIR__."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/sms_template_admin.php");

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

$tableID = "tbl_sms_template";
$sorting = new CAdminSorting($tableID, "ID", "DESC");
$adminList = new CAdminList($tableID, $sorting);

/** @var $request Main\HttpRequest */
$request = Main\Context::getCurrent()->getRequest();

/**
 * @global $find
 * @global $find_type
 * @global $find_id
 * @global $find_event_name
 * @global $find_event_name_id
 * @global $find_site
 * @global $find_language_id
 * @global $find_active
 * @global $find_sender
 * @global $find_receiver
 * @global $find_message
*/
$arFilterFields = Array(
	"find",
	"find_type",
	"find_id",
	"find_event_name",
	"find_event_name_id",
	"find_site",
	"find_language_id",
	"find_active",
	"find_sender",
	"find_receiver",
	"find_message",
);

$adminList->InitFilter($arFilterFields);

$filter = array();
if($find_id <> '')
{
	$filter["=ID"] = $find_id;
}
if($find_event_name <> '')
{
	$filter["%EVENT_NAME"] = $find_event_name;
}
if($find_event_name_id <> '')
{
	$filter["=EVENT_NAME"] = $find_event_name_id;
}
if($find_site <> '')
{
	$filter["=SITES.LID"] = $find_site;
}
if($find_language_id <> '')
{
	$filter["=LANGUAGE_ID"] = $find_language_id;
}
if($find_active <> '')
{
	$filter["=ACTIVE"] = $find_active;
}
if($find <> '' && $find_type == "sender" || $find_sender <> '')
{
	$filter["%SENDER"] = ($find <> '' && $find_type == "sender"? $find : $find_sender);
}
if($find <> '' && $find_type == "receiver" || $find_receiver <> '')
{
	$filter["%RECEIVER"] = ($find <> '' && $find_type == "receiver"? $find : $find_receiver);
}
if($find <> '' && $find_type == "message" || $find_message <> '')
{
	$filter["%MESSAGE"] = ($find <> '' && $find_type == "message"? $find : $find_message);
}

if($adminList->EditAction() && $isAdmin)
{
	foreach($request["FIELDS"] as $ID => $arFields)
	{
		if(!$adminList->IsUpdated($ID))
			continue;

		$result = TemplateTable::update($ID, $arFields);
		if(!$result->isSuccess())
		{
			$adminList->AddUpdateError("(ID=".$ID.") ".implode("<br>", $result->getErrorMessages()), $ID);
		}
	}
}

if(($arID = $adminList->GroupAction()) && $isAdmin)
{
	if($request['action_target'] == 'selected')
	{
		$arID = array();
		$data = TemplateTable::getList(["filter" => $filter]);
		while($temlate = $data->fetch())
			$arID[] = $temlate['ID'];
	}

	foreach($arID as $ID)
	{
		if(intval($ID) <= 0)
			continue;

		switch($request['action_button'])
		{
			case "delete":
				$result = TemplateTable::delete($ID);
				if(!$result->isSuccess())
				{
					$adminList->AddGroupError("(ID=".$ID.") ".implode("<br>", $result->getErrorMessages()), $ID);
				}
				break;
		}
	}
}

$APPLICATION->SetTitle(Loc::getMessage("sms_template_admin_title"));

$sortBy = mb_strtoupper($sorting->getField());
if(!TemplateTable::getEntity()->hasField($sortBy))
{
	$sortBy = "ID";
}

$sortOrder = mb_strtoupper($sorting->getOrder());
if($sortOrder <> "ASC")
{
	$sortOrder = "DESC";
}

$nav = new \Bitrix\Main\UI\AdminPageNavigation("nav-sms-template");

$templatesList = TemplateTable::getList([
	'filter' => $filter,
	'order' => [$sortBy => $sortOrder],
	'count_total' => true,
	'offset' => $nav->getOffset(),
	'limit' => $nav->getLimit(),
]);

$nav->setRecordCount($templatesList->getCount());

$adminList->setNavigation($nav, Loc::getMessage("sms_template_admin_nav"));

$entity = TemplateTable::getEntity();
$fields = $entity->getFields();

$adminList->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"EVENT_NAME", "content"=>$fields["EVENT_NAME"]->getTitle(), "sort"=>"EVENT_NAME", "default"=>true),
	array("id"=>"ACTIVE", "content"=>$fields["ACTIVE"]->getTitle(), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"SENDER", "content"=>$fields["SENDER"]->getTitle(), "sort"=>"SENDER", "default"=>true),
	array("id"=>"RECEIVER", "content"=>$fields["RECEIVER"]->getTitle(), "sort"=>"RECEIVER", "default"=>true),
	array("id"=>"SITES", "content"=>Loc::getMessage("sms_template_admin_sites"), "default"=>false),
	array("id"=>"LANGUAGE_ID", "content"=>$fields["LANGUAGE_ID"]->getTitle(), "sort"=>"LANGUAGE_ID", "default"=>false),
	array("id"=>"MESSAGE", "content"=>$fields["MESSAGE"]->getTitle(), "default"=>false),
));

$eventTypes = array();
$eventTypeDb = EventTypeTable::getList(array(
	"select" => array('EVENT_NAME', 'NAME'),
	"filter" => array('=LID' => LANGUAGE_ID, "=EVENT_TYPE" => EventTypeTable::TYPE_SMS),
	"order" => array('NAME' => 'ASC')
));
while($eventType = $eventTypeDb->fetch())
{
	$eventTypes[$eventType["EVENT_NAME"]] = $eventType["NAME"].' ['.$eventType["EVENT_NAME"].']';
}

$langOptions = array("" => "");
$languages = Main\Localization\LanguageTable::getList(array(
	"select" => array('LID', 'NAME'),
	"filter" => array("=ACTIVE" => "Y"),
	"order" => array("SORT" => "ASC", "NAME" => "ASC")
));
while($language = $languages->fetch())
{
	$langOptions[$language["LID"]] = $language["NAME"];
}

while($template = $templatesList->fetchObject())
{
	$id = $template->getId();

	$row = &$adminList->AddRow($id, $template->collectValues(), "sms_template_edit.php?ID=".$id."&lang=".LANGUAGE_ID, Loc::getMessage("sms_template_admin_edit"));

	$row->AddViewField("ID", '<a href="sms_template_edit.php?ID='.$id.'&amp;lang='.LANGUAGE_ID.'" title="'.Loc::getMessage("sms_template_admin_edit").'">'.$id.'</a>');
	$row->AddSelectField("EVENT_NAME", $eventTypes);
	$row->AddCheckField("ACTIVE");
	$row->AddInputField("SENDER");
	$row->AddInputField("RECEIVER");
	$row->AddSelectField("LANGUAGE_ID", $langOptions);
	$row->AddViewField("MESSAGE", Main\Text\HtmlFilter::encode($template->getMessage()));

	$template->fillSites();
	$row->AddViewField("SITES", implode(", ", $template->getSites()->getLidList()));

	$arActions = array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("sms_template_admin_edit1"), "ACTION"=>$adminList->ActionRedirect("sms_template_edit.php?ID=".$id));
	if($isAdmin)
	{
		$arActions[] = array("ICON"=>"copy", "TEXT"=>Loc::getMessage("sms_template_admin_copy"), "ACTION"=>$adminList->ActionRedirect("sms_template_edit.php?COPY_ID=".$id));
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("sms_template_admin_del"), "ACTION"=>"if(confirm('".Loc::getMessage("sms_template_admin_del_conf")."')) ".$adminList->ActionDoGroup($id, "delete"));
	}

	$row->AddActions($arActions);
}

$adminList->AddGroupActionTable(array(
	"delete"=>true,
));

$aContext = array(
	array(
		"TEXT"	=> Loc::getMessage("sms_template_admin_add"),
		"LINK"	=> "sms_template_edit.php?lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("sms_template_admin_add_title"),
		"ICON"	=> "btn_new"
	),
);
$adminList->AddAdminContextMenu($aContext);

$adminList->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$tableID."_filter",
	array(
		"id" => $fields["ID"]->getTitle(),
		"event_name" => $fields["EVENT_NAME"]->getTitle(),
		"site" => Loc::getMessage("sms_template_admin_site"),
		"language_id" => $fields["LANGUAGE_ID"]->getTitle(),
		"active" => $fields["ACTIVE"]->getTitle(),
		"sender" => $fields["SENDER"]->getTitle(),
		"receiver" => $fields["RECEIVER"]->getTitle(),
		"message" => $fields["MESSAGE"]->getTitle(),
	)
);

$oFilter->Begin();
?>
<tr>
	<td><b><?echo Loc::getMessage("sms_template_admin_find")?></b></td>
	<td nowrap>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=Loc::getMessage("F_SEARCH_TITLE")?>">
		<select name="find_type">
			<option value="message"<?if($find_type=="message") echo " selected"?>><?echo Loc::getMessage("sms_template_admin_message")?></option>
			<option value="sender"<?if($find_type=="sender") echo " selected"?>><?echo Loc::getMessage("sms_template_admin_sender")?></option>
			<option value="receiver"<?if($find_type=="receiver") echo " selected"?>><?echo Loc::getMessage("sms_template_admin_receiver")?></option>
		</select>
	</td>
</tr>
<tr>
	<td><?echo $fields["ID"]->getTitle()?>:</td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"></td>
</tr>
<tr>
	<td><?echo $fields["EVENT_NAME"]->getTitle()?>:</td>
	<td><input type="text" name="find_event_name" size="25" value="<?echo htmlspecialcharsbx($find_event_name)?>"><br>
			<select name="find_event_name_id">
				<option value=""><?echo Loc::getMessage("sms_template_admin_all")?></option>
				<?foreach($eventTypes as $eventName => $name): ?>
					<option value="<?=Main\Text\HtmlFilter::encode($eventName)?>"<? if($find_event_name_id == $eventName) echo " selected" ?>>
						<?=Main\Text\HtmlFilter::encode($name)?>
					</option>
				<? endforeach ?>
			</select>
	</td>
</tr>
<tr>
	<td><?=Loc::getMessage("sms_template_admin_site")?>:</td>
	<td><select name="find_site">
			<option value=""><?echo Loc::getMessage("sms_template_admin_all")?></option>
			<?
			$l = CLang::GetList();
			while(($l_arr = $l->Fetch()))
			{
				echo '<option value="'.$l_arr["LID"].'" '.($l_arr["LID"] == $find_site? 'selected':'').'>['.$l_arr["LID"].']&nbsp;'.Main\Text\HtmlFilter::encode($l_arr["NAME"]).'</option>'."\n";
			}
			?>
		</select>
	</td>
</tr>
<tr>
	<td><?echo $fields["LANGUAGE_ID"]->getTitle()?>:</td>
	<td>
			<select name="find_language_id">
				<option value=""><?echo Loc::getMessage("sms_template_admin_all")?></option>
				<?
				unset($langOptions[""]);
				?>
				<? foreach($langOptions as $language_id => $name): ?>
					<option value="<?=$language_id?>"<? if($find_language_id == $language_id) echo " selected" ?>>
						<?=\Bitrix\Main\Text\HtmlFilter::encode($name)?>
					</option>
				<? endforeach ?>
			</select>
	</td>
</tr>
<tr>
	<td><?=$fields["ACTIVE"]->getTitle()?>:</td>
	<td><?
		$arr = array(
			"reference" => [Loc::getMessage("sms_template_admin_yes"), Loc::getMessage("sms_template_admin_no")],
			"reference_id" => ["Y","N"]
		);
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), Loc::getMessage("sms_template_admin_all"));
		?></td>
</tr>
<tr>
	<td><?echo $fields["SENDER"]->getTitle()?>:</td>
	<td><input type="text" name="find_sender" size="47" value="<?echo htmlspecialcharsbx($find_sender)?>"></td>
</tr>
<tr>
	<td><?echo $fields["RECEIVER"]->getTitle()?>:</td>
	<td><input type="text" name="find_receiver" size="47" value="<?echo htmlspecialcharsbx($find_receiver)?>"></td>
</tr>
<tr>
	<td><?echo $fields["MESSAGE"]->getTitle()?>:</td>
	<td><input type="text" name="find_message" size="47" value="<?echo htmlspecialcharsbx($find_message)?>"></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$tableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
?>
</form>
<?
$adminList->DisplayList();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
