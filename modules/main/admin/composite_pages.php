<?

use Bitrix\Main\Composite;
use Bitrix\Main\Composite\Internals\Model\PageTable;
use Bitrix\Main\Composite\Page;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\AdminPageNavigation;
use Bitrix\Main\Type;

/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 */
require_once(__DIR__."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/composite_pages.php");

if (!$USER->canDoOperation("view_other_settings") || Composite\Engine::isSelfHostedPortal())
{
	$APPLICATION->authForm(Loc::getMessage("ACCESS_DENIED"));
}

Loc::loadMessages(__FILE__);

$APPLICATION->setTitle(Loc::getMessage("MAIN_COMPOSITE_PAGES_TITLE"));

$tableID = "tbl_composite_pages";
$sorting = new CAdminSorting($tableID, "ID", "DESC");
$adminList = new CAdminList($tableID, $sorting);

$request = Context::getCurrent()->getRequest();
if ($request->get("action_button") === "delete" && ($ids = $adminList->groupAction()))
{
	$ids = array_map("intval", $ids);

	$pageList = PageTable::getList(
		array(
			"select" => array("ID", "CACHE_KEY"),
			"filter" => array("ID" => $ids),
		)
	);

	while ($record = $pageList->fetch())
	{
		$page = Page::createFromCacheKey($record["CACHE_KEY"]);
		$page->delete();
	}
}

//Filter
$filterFields = array(
	"find_id",
	"find_cache_key",
	"find_host",
	"find_uri",
	"find_title",
	"find_created_start",
	"find_created_end",
	"find_changed_start",
	"find_changed_end",
	"find_last_viewed_start",
	"find_last_viewed_end",
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
	"?CACHE_KEY" => $find_cache_key,
	"=HOST" => $find_host,
	"?URI" => $find_uri,
	"?TITLE" => $find_title,
	">=CREATED" => getFilterDate($find_created_start),
	"<=CREATED" => getFilterDate($find_created_end),
	">=CHANGED" => getFilterDate($find_changed_start),
	"<=CHANGED" => getFilterDate($find_changed_end),
	">=LAST_VIEWED" => getFilterDate($find_last_viewed_start),
	"<=LAST_VIEWED" => getFilterDate($find_last_viewed_end),
);

foreach ($filter as $key => $value)
{
	if (trim($value) == '')
	{
		unset($filter[$key]);
	}
}

$pageEntity = PageTable::getEntity();

//Sorting
$sortBy = mb_strtoupper($sorting->getField());
$sortBy = $pageEntity->hasField($sortBy) ? $sortBy : "ID";
$sortOrder = mb_strtoupper($sorting->getOrder());
$sortOrder = $sortOrder !== "DESC" ? "ASC" : "DESC";

//Navigation
$nav = new AdminPageNavigation("nav");

$pageList = PageTable::getList(array(
	"filter" => $filter,
	"order" => array($sortBy => $sortOrder),
	"count_total" => true,
	"offset" => $nav->getOffset(),
	"limit" => $nav->getLimit(),
));

$nav->setRecordCount($pageList->getCount());

$adminList->setNavigation($nav, Loc::getMessage("MAIN_COMPOSITE_PAGES_PAGES"));
$adminList->addHeaders(array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"default" => true
	),
	array(
		"id" => "PAGE",
		"content" => Loc::getMessage("MAIN_COMPOSITE_PAGES_PAGE"),
		"sort" => "TITLE",
		"default" => true
	),
	array(
		"id" => "CACHE_KEY",
		"content" => $pageEntity->getField("CACHE_KEY")->getTitle(),
	),
	array(
		"id" => "HOST",
		"content" => $pageEntity->getField("HOST")->getTitle(),
		"sort" => "HOST",
	),
	array(
		"id" => "URI", 
		"content" => $pageEntity->getField("URI")->getTitle(), 
		"sort" => "URI",
	),
	array(
		"id" => "TITLE", 
		"content" => $pageEntity->getField("TITLE")->getTitle(),
		"sort" => "TITLE",
	),
	array(
		"id" => "CREATED",
		"content" => $pageEntity->getField("CREATED")->getTitle(),
		"sort" => "CREATED",
		"default" => true
	),
	array(
		"id" => "CHANGED",
		"content" => $pageEntity->getField("CHANGED")->getTitle(),
		"sort" => "CHANGED",
		"default" => true
	),
	array(
		"id" => "LAST_VIEWED",
		"content" => $pageEntity->getField("LAST_VIEWED")->getTitle(),
		"sort" => "LAST_VIEWED",
		"default" => true
	),
	array(
		"id" => "VIEWS",
		"content" => $pageEntity->getField("VIEWS")->getTitle(),
		"sort" => "VIEWS",
		"default" => true
	),
	array(
		"id" => "REWRITES",
		"content" => $pageEntity->getField("REWRITES")->getTitle(),
		"sort" => "REWRITES",
		"default" => true
	),
	array(
		"id" => "SIZE",
		"content" => $pageEntity->getField("SIZE")->getTitle(),
		"sort" => "SIZE",
		"default" => true
	),

));

while ($record = $pageList->fetch())
{
	$row = &$adminList->addRow($record["ID"], $record);

	$pageCell = '<a href="//%s" target="_blank">%s</a><br><span>%s</span>';
	$pageLink = htmlspecialcharsbx($record["HOST"].$record["URI"]);
	$title = trim($record["TITLE"]) <> ''? $record["TITLE"] : $pageLink;
	$title = htmlspecialcharsbx($title, ENT_COMPAT, false);

	$row->addViewField("PAGE", sprintf($pageCell, $pageLink, $title, $pageLink));
	$row->addViewField("SIZE", \CFile::formatSize($record["SIZE"]));

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
		"TEXT" => Loc::getMessage("MAIN_COMPOSITE_DELETE_ALL_CACHE"),
		"LINK" => "/bitrix/admin/cache.php?lang=".LANGUAGE_ID."&cachetype=html&tabControl_active_tab=fedit2",
		"TITLE" => Loc::getMessage("MAIN_COMPOSITE_DELETE_ALL_CACHE"),
	),
);

$adminList->addAdminContextMenu($toolbar);
$adminList->checkListMode();
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>


<form method="GET" action="<?=$APPLICATION->getCurPage()?>" name="find_form">
<?
$filterControl = new CAdminFilter(
	$tableID."_filter",
	array(
		$pageEntity->getField("CACHE_KEY")->getTitle(),
		$pageEntity->getField("HOST")->getTitle(),
		$pageEntity->getField("URI")->getTitle(),
		$pageEntity->getField("TITLE")->getTitle(),
		$pageEntity->getField("CREATED")->getTitle(),
		$pageEntity->getField("CHANGED")->getTitle(),
		$pageEntity->getField("LAST_VIEWED")->getTitle(),
	)
);

$filterControl->begin();
?>
	<tr>
		<td>ID:</td>
		<td><input type="text" name="find_id" value="<?=htmlspecialcharsbx($find_id)?>" size="40"></td>
	</tr>
	<tr>
		<td><?=$pageEntity->getField("CACHE_KEY")->getTitle()?>:</td>
		<td><input type="text" name="find_cache_key" value="<?=htmlspecialcharsbx($find_cache_key)?>" size="40"></td>
	</tr>
	<tr>
		<td><?=$pageEntity->getField("HOST")->getTitle()?>:</td>
		<td><input type="text" name="find_host" value="<?=htmlspecialcharsbx($find_host)?>" size="40"></td>
	</tr>
	<tr>
		<td><?=$pageEntity->getField("URI")->getTitle()?>:</td>
		<td><input type="text" name="find_uri" value="<?=htmlspecialcharsbx($find_uri)?>" size="40"></td>
	</tr>
	<tr>
		<td><?=$pageEntity->getField("TITLE")->getTitle()?>:</td>
		<td><input type="text" name="find_title" value="<?=htmlspecialcharsbx($find_title)?>" size="40"></td>
	</tr>
	<tr>
		<td><?=$pageEntity->getField("CREATED")->getTitle()?>:</td>
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
		<td><?=$pageEntity->getField("CHANGED")->getTitle()?>:</td>
		<td><?=calendarPeriod(
			"find_changed_start",
			htmlspecialcharsbx($find_changed_start),
			"find_changed_end",
			htmlspecialcharsbx($find_changed_end),
			"find_form",
			"Y",
			"class=\"typeselect\"",
			"class=\"typeinput\"",
			"20" //important
		)?></td>
	</tr>
	<tr>
		<td><?=$pageEntity->getField("LAST_VIEWED")->getTitle()?>:</td>
		<td><?=calendarPeriod(
			"find_last_viewed_start",
			htmlspecialcharsbx($find_last_viewed_start),
			"find_last_viewed_end",
			htmlspecialcharsbx($find_last_viewed_end),
			"find_form",
			"Y",
			"class=\"typeselect\"",
			"class=\"typeinput\"",
			"20" //important
		)?></td>

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