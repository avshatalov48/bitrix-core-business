<?
use Bitrix\Main\Composite;
use Bitrix\Main\Composite\Debug\Logger;
use Bitrix\Main\Composite\Debug\Model\LogTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\AdminPageNavigation;
use Bitrix\Main\Type;

/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 */
require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/composite_pages.php");

if (!$USER->canDoOperation("view_other_settings") || Composite\Engine::isSelfHostedPortal())
{
	$APPLICATION->authForm(Loc::getMessage("ACCESS_DENIED"));
}

Loc::loadMessages(__FILE__);

CJSCore::Init(array("timer"));

$tableID = "tbl_composite_log";
$sorting = new CAdminSorting($tableID, "ID", "DESC");
$adminList = new CAdminList($tableID, $sorting);

$request = Context::getCurrent()->getRequest();
if ($request->getPost("debug_form") && check_bitrix_sessid())
{
	$duration = intval($request->getPost("duration"));
	$duration = max($duration, 300);
	Option::set("main", "composite_debug_duration", $duration);

	if ($request->getPost("enable_debug"))
	{
		Logger::enable(time() + $duration);
	}
	elseif ($request->getPost("disable_debug"))
	{
		Logger::disable();
	}

	localRedirect($APPLICATION->getCurPage()."?lang=".LANGUAGE_ID);
}

if ($request->get("action_button") === "delete" && ($ids = $adminList->groupAction()))
{
	$ids = array_map("intval", $ids);
	foreach ($ids as $id)
	{
		$id = intval($id);
		$result = LogTable::delete($id);
		if (!$result->isSuccess())
		{
			$adminList->addGroupError("(ID=".$id.") ".implode("<br>", $result->getErrorMessages()), $id);
		}
	}
}

if ($request->get("action") === "clear_all" && check_bitrix_sessid())
{
	LogTable::deleteAll();
	localRedirect($APPLICATION->getCurPage()."?lang=".LANGUAGE_ID);
}

$APPLICATION->setTitle(Loc::getMessage("MAIN_COMPOSITE_LOG_TITLE"));

//Filter
$filterFields = array(
	"find_id",
	"find_host",
	"find_uri",
	"find_title",
	"find_created_start",
	"find_created_end",
	"find_type",
	"find_ajax",
	"find_user_id",
);

$adminList->initFilter($filterFields);

function getFilterDate($date)
{
	if (!isset($date) || mb_strlen(trim($date)) < 1)
	{
		return null;
	}

	$date = trim($date);
	return Type\DateTime::isCorrect($date) ? new Type\DateTime($date) : null;
}

$filter = array(
	"=ID" => $find_id,
	"=HOST" => $find_host,
	"?URI" => $find_uri,
	"?TITLE" => $find_title,
	">=CREATED" => getFilterDate($find_created_start),
	"<=CREATED" => getFilterDate($find_created_end),
	"=TYPE" => $find_type,
	"=AJAX" => $find_ajax,
	"=USER_ID" => $find_user_id,
);

foreach ($filter as $key => $value)
{
	if (!is_array($value) && !mb_strlen(trim($value)))
	{
		unset($filter[$key]);
	}
}

$logEntity = LogTable::getEntity();

//Sorting
$sortBy = mb_strtoupper($sorting->getField());
$sortBy = $logEntity->hasField($sortBy) ? $sortBy : "ID";
$sortOrder = mb_strtoupper($sorting->getOrder());
$sortOrder = $sortOrder !== "DESC" ? "ASC" : "DESC";

$nav = new AdminPageNavigation("nav");

$logList = LogTable::getList(array(
	"select" => array(
		"ID", "HOST", "URI", "TITLE", "CREATED", "TYPE", "AJAX", "USER_ID", "PAGE_ID", "MESSAGE_SHORT",
		"USER_NAME" => "USER.NAME",
		"USER_LAST_NAME" => "USER.LAST_NAME",
		"USER_SECOND_NAME" => "USER.SECOND_NAME",
		"USER_LOGIN" => "USER.LOGIN"
	),
	"filter" => $filter,
	"order" => array($sortBy => $sortOrder),
	"count_total" => true,
	"offset" => $nav->getOffset(),
	"limit" => $nav->getLimit(),
));

$nav->setRecordCount($logList->getCount());

$adminList->setNavigation($nav, Loc::getMessage("MAIN_COMPOSITE_LOG_PAGES"));

$adminList->addHeaders(array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"default" => true
	),
	array(
		"id" => "PAGE",
		"content" => Loc::getMessage("MAIN_COMPOSITE_LOG_PAGE_COLUMN"),
		"sort" => "TITLE",
		"default" => true
	),
	array(
		"id" => "HOST",
		"content" => $logEntity->getField("HOST")->getTitle(),
		"sort" => "HOST",
	),
	array(
		"id" => "URI",
		"content" => $logEntity->getField("URI")->getTitle(),
		"sort" => "URI",
	),
	array(
		"id" => "TITLE",
		"content" => $logEntity->getField("TITLE")->getTitle(),
	),
	array(
		"id" => "CREATED",
		"content" => $logEntity->getField("CREATED")->getTitle(),
		"sort" => "CREATED",
		"default" => true
	),
	array(
		"id" => "TYPE",
		"content" => $logEntity->getField("TYPE")->getTitle(),
		"sort" => "TYPE",
		"default" => true
	),
	array(
		"id" => "MESSAGE_SHORT",
		"content" => $logEntity->getField("MESSAGE")->getTitle(),
		"default" => true
	),
	array(
		"id" => "AJAX",
		"content" => $logEntity->getField("AJAX")->getTitle(),
		"sort" => "AJAX",
		"default" => true
	),
	array(
		"id" => "USER_ID",
		"content" => $logEntity->getField("USER_ID")->getTitle(),
		"sort" => "USER_ID",
	),
));

while ($record = $logList->fetch())
{
	$row = &$adminList->addRow($record["ID"], $record);

	$pageCell = '<div style="max-width:250px; word-wrap: break-word"><a href="//%s" target="_blank">%s</a><br>%s</div>';
	$pageLink = htmlspecialcharsbx($record["HOST"].$record["URI"]);
	$title = trim($record["TITLE"]) <> ''? $record["TITLE"] : $pageLink;
	$title = htmlspecialcharsbx($title, ENT_COMPAT, false);

	$row->addViewField("PAGE", sprintf($pageCell, $pageLink, $title, $pageLink));

	$row->addViewField("AJAX", $record["AJAX"] === "Y" ? Loc::getMessage("MAIN_YES") : Loc::getMessage("MAIN_NO"));
	$row->addViewField("TYPE", Logger::getTypeName($record["TYPE"]));

	if ($record["TYPE"] === Logger::TYPE_CACHE_REWRITING)
	{
		$messageCell =
			'<a href="composite_diff.php?lang='.LANGUAGE_ID.'&log_id='.$record["ID"].'" target="_blank">'.
			Loc::getMessage("MAIN_COMPOSITE_LOG_VIEW_DIFF").'</a>';

		$row->addViewField("MESSAGE_SHORT", $messageCell);
	}
	else
	{
		$messageCell = '<div style="max-width:250px; word-wrap: break-word;">%s</div>';
		$message = str_replace("\n", "<br>", htmlspecialcharsbx($record["MESSAGE_SHORT"]));
		$row->addViewField("MESSAGE_SHORT", sprintf($messageCell, $message));
	}

	if ($record["USER_ID"])
	{
		$userCell = '<a href="user_edit.php?lang='.LANGUAGE_ID.'&ID=%s">%s</a>';
		$userName = \CUser::formatName(
			\CSite::getNameFormat(),
			array(
				"NAME" => $record["USER_NAME"],
				"SECOND_NAME" => $record["USER_SECOND_NAME"],
				"LAST_NAME" => $record["USER_LAST_NAME"],
				"LOGIN" => $record["USER_LOGIN"],
			),
			true,
			true
		);

		$row->addViewField("USER_ID", sprintf($userCell, $record["USER_ID"], $userName));
	}
	else
	{
		$row->addViewField("USER_ID", "&ndash;");
	}


	$actions = array(
		array(
			"ICON" => "delete",
			"TEXT" => Loc::getMessage("MAIN_DELETE"),
			"ACTION" =>
				"if(confirm('".Loc::getMessage('admin_lib_list_del_sel')."')) ".
				$adminList->actionDoGroup($record["ID"], "delete")
		)
	);

	$row->addActions($actions);

}

$adminList->addGroupActionTable(
	array("delete" => true),
	array("disable_action_target" => true)
);

$toolbar = array(
	array(
		"TEXT" => Loc::getMessage("MAIN_COMPOSITE_LOG_CLEAR_ALL"),
		"LINK" => $APPLICATION->getCurPage()."?lang=".LANGUAGE_ID."&action=clear_all&".bitrix_sessid_get(),
		"TITLE" => Loc::getMessage("MAIN_COMPOSITE_LOG_CLEAR_ALL_TITLE"),
		"LINK_PARAM" =>
			"onclick=\"if (!confirm('".htmlspecialcharsbx(CUtil::JSEscape(
				Loc::getMessage("MAIN_COMPOSITE_LOG_CLEAR_ALL_CONFIRM")))."')) return false;\"",
		"ICON" => "btn_delete"
	),
);
$adminList->addAdminContextMenu($toolbar);
$adminList->checkListMode();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>

<?=BeginNote()?>
<form method="POST" action="<?=$APPLICATION->getCurPage()?>" name="enable_debug_form">
	<?=bitrix_sessid_post();?>
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<input type="hidden" name="debug_form" value="Y">
	<? if (Logger::isOn()):?>
		<div style="color:red; margin: 0 0 10px 1px;">
			<b><?=Loc::getMessage("MAIN_COMPOSITE_LOG_DEBUG_ENABLED")?></b>
		</div>
		<input type="submit" name="disable_debug"
			   value="<?=Loc::getMessage("MAIN_COMPOSITE_LOG_DISABLE_DEBUG")?>" class="adm-btn-save">&nbsp;&nbsp;

		<?
		$secondsLeft = Logger::getEndTime() - time();
		if ($secondsLeft > 0):
		?>
			<span><?=Loc::getMessage("MAIN_COMPOSITE_LOG_TIME_LEFT")?>: <span id="time-left"></span></span>
			<script>
				BX.timer(BX("time-left"), {
					display: "clock",
					to: new Date((new Date().valueOf() + <?=$secondsLeft?> * 1000)),
					accuracy: 1
				});
			</script>
		<? endif ?>
	<? else: ?>
		<div style="color:green; margin: 0 0 10px 1px;">
			<b><?=Loc::getMessage("MAIN_COMPOSITE_LOG_DEBUG_DISABLED")?></b>
		</div>
		<input type="submit" name="enable_debug" value="<?=Loc::getMessage("MAIN_COMPOSITE_LOG_ENABLE_DEBUG")?>">
		&nbsp;&nbsp;
		<select name="duration">
			<?
			$currentDuration = Option::get("main", "composite_debug_duration", 300);
			?>
			<? foreach (array(300, 600, 1200, 1800, 3600) as $duration): ?>
				<option
					value="<?=$duration?>"
					<?=($currentDuration == $duration ? " selected" : "")?>
				><?=Loc::getMessage("MAIN_COMPOSITE_LOG_INTERVAL_".$duration."_SEC")?></option>
			<? endforeach ?>
		</select>
	<? endif ?>

	<div style="margin: 10px 0 0 1px;">
		<?=Loc::getMessage("MAIN_COMPOSITE_LOG_DEBUG_DESC")?><br>
		<?=Loc::getMessage("MAIN_COMPOSITE_LOG_PLUGIN_AD", array(
			"#LINK_START#" =>
				'<a href="https://chrome.google.com/webstore/detail/bitrix-composite-notifier/'.
				'bhjmmlcdfdcdloebidhnlgoabjpbfjbk?hl=en" target="_blank">',
			"#LINK_END#" => "</a>"
		))?>
	</div>
</form>
<?=EndNote()?>


<form method="GET" action="<?=$APPLICATION->getCurPage()?>" name="find_form">
<?
$filterControl = new CAdminFilter(
	$tableID."_filter",
	array(
		$logEntity->getField("HOST")->getTitle(),
		$logEntity->getField("URI")->getTitle(),
		$logEntity->getField("TITLE")->getTitle(),
		$logEntity->getField("CREATED")->getTitle(),
		$logEntity->getField("TYPE")->getTitle(),
		$logEntity->getField("AJAX")->getTitle(),
		$logEntity->getField("USER_ID")->getTitle(),
	)
);

$filterControl->begin();
?>
	<tr>
		<td>ID:</td>
		<td><input type="text" name="find_id" value="<?=htmlspecialcharsbx($find_id)?>" size="40"></td>
	</tr>

	<tr>
		<td><?=$logEntity->getField("HOST")->getTitle()?>:</td>
		<td><input type="text" name="find_host" value="<?=htmlspecialcharsbx($find_host)?>" size="40"></td>
	</tr>
	<tr>
		<td><?=$logEntity->getField("URI")->getTitle()?>:</td>
		<td><input type="text" name="find_uri" value="<?=htmlspecialcharsbx($find_uri)?>" size="40"></td>
	</tr>
	<tr>
		<td><?=$logEntity->getField("TITLE")->getTitle()?>:</td>
		<td><input type="text" name="find_title" value="<?=htmlspecialcharsbx($find_title)?>" size="40"></td>
	</tr>
	<tr>
		<td><?=$logEntity->getField("CREATED")->getTitle()?>:</td>
		<td><?=calendarPeriod(
			"find_created_start",
			htmlspecialcharsbx($find_created_start),
			"find_created_end",
			htmlspecialcharsbx($find_created_end),
			"find_form",
			"Y",
			"class=\"typeselect\"",
			"class=\"typeinput\"",
			"20" //important
		)?></td>
	</tr>
	<tr>
		<td><?=$logEntity->getField("TYPE")->getTitle()?>:</td>
		<td>
			<?
			$types = array_map(
				function($type) {
					return Logger::getTypeName($type);
				},
				Logger::getTypes()
			);

			echo SelectBoxMFromArray(
				"find_type[]",
				array(
					"reference" => $types,
					"reference_id" => Logger::getTypes()
				),
				$find_type,
				"",
				false,
				"5"
			);
			?>
		</td>
	</tr>
	<tr>
		<td><?=$logEntity->getField("AJAX")->getTitle()?>:</td>
		<td>
			<?=SelectBoxFromArray(
				"find_ajax",
				array(
					"reference" => array(Loc::getMessage("MAIN_YES"), Loc::getMessage("MAIN_NO")),
					"reference_id" => array("Y", "N")
				),
				htmlspecialcharsbx($find_ajax),
				Loc::getMessage("MAIN_ALL")
			);
			?>

		</td>
	</tr>
	<tr>
		<td><?=$logEntity->getField("USER_ID")->getTitle()?>:</td>
		<td><input type="text" name="find_user_id" value="<?=htmlspecialcharsbx($find_user_id)?>" size="40"></td>
	</tr>

<?
$filterControl->buttons(array(
	"table_id" => $tableID,
	"url"=> $APPLICATION->getCurPage(),
	"form"=>"find_form",
));

$filterControl->end();
?>
</form>


<?

$adminList->displayList();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");

