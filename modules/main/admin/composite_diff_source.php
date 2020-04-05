<?
use Bitrix\Main\Composite\Debug\Model\LogTable;
use Bitrix\Main\Composite\Internals\Model\PageTable;
use Bitrix\Main\Composite\Page;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 */

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

if (!$USER->canDoOperation("view_other_settings"))
{
	$APPLICATION->authForm(Loc::getMessage("ACCESS_DENIED"));
}

Loc::loadMessages(dirname(__FILE__)."/composite_diff.php");
$request = Context::getCurrent()->getRequest();

$logId = intval($request->get("log_id"));
$pageId = intval($request->get("page_id"));


$error = null;
$sourceContent = false;
$sourceTitle = "";

if ($logId > 0)
{
	$logRecord = LogTable::getList(array(
		"filter" => array(
			"ID" => $logId,
			"TYPE" => \Bitrix\Main\Composite\Debug\Logger::TYPE_CACHE_REWRITING
		)
	))->fetch();

	if ($logRecord && count($logRecord["MESSAGE"]))
	{
		$sourceContent = $logRecord["MESSAGE"];
		$sourceTitle = $logRecord["TITLE"];
		$sourceTitle .= " (". $logRecord["CREATED"].")";
	}
	else
	{
		$error = Loc::getMessage("MAIN_COMPOSITE_DIFF_LOG_NOT_FOUND");
	}
}
else if ($pageId > 0)
{
	$page = PageTable::getRowById($pageId);
	if ($page)
	{
		$cache = Page::createFromCacheKey($page["CACHE_KEY"]);
		$sourceContent = $cache->read();
		$sourceTitle = $page["TITLE"]." (".Loc::getMessage("MAIN_COMPOSITE_DIFF_CURRENT_VERSION").")";
		if (!$sourceContent)
		{
			$error = Loc::getMessage("MAIN_COMPOSITE_DIFF_CONTENT_NOT_FOUND");
		}
	}
	else
	{
		$error = Loc::getMessage("MAIN_COMPOSITE_DIFF_PAGE_NOT_FOUND");
	}
}

if (!$sourceContent)
{
	echo $error;
	return;
}
?>
<!doctype html>
<html>
<head>
	<link rel="stylesheet" href="<?=CUtil::getAdditionalFileURL("/bitrix/panel/main/composite.css")?>">
	<meta http-equiv="Content-Type" content="text/html; charset=<?=htmlspecialcharsbx(LANG_CHARSET)?>">
	<meta name="viewport" content="initial-scale=1.0, width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?=htmlspecialcharsbx($sourceTitle)?></title>
</head>
<body>
	<pre><?=htmlspecialcharsbx($sourceContent)?></pre>
</body>
</html>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_after.php");