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

require_once(__DIR__."/../include/prolog_admin_before.php");

if (!$USER->canDoOperation("view_other_settings"))
{
	$APPLICATION->authForm(Loc::getMessage("ACCESS_DENIED"));
}

Loc::loadMessages(__FILE__);
$request = Context::getCurrent()->getRequest();

$logId = intval($request->get("log_id"));
$logRecord = LogTable::getList(array(
	"filter" => array(
		"ID" => $logId,
		"TYPE" => \Bitrix\Main\Composite\Debug\Logger::TYPE_CACHE_REWRITING
	)
))->fetch();


$page  = null;
$cacheContent = false;
$error = null;

if ($logRecord)
{
	$page = PageTable::getRowById($logRecord["PAGE_ID"]);
	if ($page)
	{
		$cache = Page::createFromCacheKey($page["CACHE_KEY"]);
		$cacheContent = $cache->read();
		if (!$cacheContent)
		{
			$error = Loc::getMessage("MAIN_COMPOSITE_DIFF_CONTENT_NOT_FOUND");
		}
	}
	else
	{
		$error = Loc::getMessage("MAIN_COMPOSITE_DIFF_PAGE_NOT_FOUND");
	}
}
else
{
	$error = Loc::getMessage("MAIN_COMPOSITE_DIFF_LOG_NOT_FOUND");
}

if (!$logRecord || !$page || !$cacheContent)
{
	echo $error;
	return;
}

$diff = new \Bitrix\Main\Text\Diff();

$linesA = preg_split('/\r\n|\n|\r/', $logRecord["MESSAGE"]);
$linesB = preg_split('/\r\n|\n|\r/', $cacheContent);
$diffScript = $diff->getDiffScript($linesA, $linesB);

$deletedFromA = array();
$insertedToB = array();
foreach ($diffScript as $scriptRecord)
{
	$deletedFromA[$scriptRecord["startA"]] = $scriptRecord["deletedA"];
	$insertedToB[$scriptRecord["startB"]] = $scriptRecord["insertedB"];
}

?>
<!doctype html>
<html>
<head>
	<link rel="stylesheet" href="<?=CUtil::getAdditionalFileURL("/bitrix/panel/main/composite.css")?>">
	<meta http-equiv="Content-Type" content="text/html; charset=<?=htmlspecialcharsbx(LANG_CHARSET)?>">
	<meta name="viewport" content="initial-scale=1.0, width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?=htmlspecialcharsbx($page["TITLE"])?>: <?=Loc::getMessage("MAIN_COMPOSITE_DIFF_VERSION_COMPARISON")?></title>
</head>
<body class="adm-composite-diff-body">

<div class="adm-composite-diff-header">
	<div class="adm-composite-left-header">
		<?=Loc::getMessage("MAIN_COMPOSITE_DIFF_PREV_VERSION", array("#DATE_TIME#" => $logRecord["CREATED"]))?>
		(<a href="composite_diff_source.php?lang=<?=LANGUAGE_ID?>&log_id=<?=$logRecord["ID"]?>" target="_blank"
		class="adm-composite-source-link"><?=Loc::getMessage("MAIN_COMPOSITE_DIFF_SOURCE_CODE")?></a>)
	</div>
	<div class="adm-composite-right-header" id="right-header">
		<?=Loc::getMessage("MAIN_COMPOSITE_DIFF_CURRENT_VERSION")?>
		(<a href="composite_diff_source.php?lang=<?=LANGUAGE_ID?>&page_id=<?=$page["ID"]?>" target="_blank"
		class="adm-composite-source-link"><?=Loc::getMessage("MAIN_COMPOSITE_DIFF_SOURCE_CODE")?></a>)
	</div>

	<? if (empty($diffScript)):?>
		<div class="adm-composite-diff-notice" id="right-header">
			<?=Loc::getMessage("MAIN_COMPOSITE_DIFF_VERSIONS_IDENTICAL")?>
		</div>
	<? endif ?>
</div>

<div class="adm-composite-diff">
	<div class="adm-composite-diff-file" id="left-window">
		<?
			$deletedLines = 0;
			for ($line = 0, $length = count($linesA); $line < $length; $line++):
				$deletedLines = $deletedFromA[$line] ?? $deletedLines;
		?>
				<div class="adm-composite-diff-line
					<?if ($deletedLines): $deletedLines--?> adm-composite-deleted-line<?endif?>
					"><span class="adm-composite-diff-line-content"><?=htmlspecialcharsbx($linesA[$line])?></span></div>
		<?
			endfor;
		?>
	</div>
	<div class="adm-composite-diff-gutter" id="gutter">
		<?
		$numberOfLines = max(count($linesA), count($linesB));

		?>
		<div class="adm-composite-line-numbers adm-composite-left-numbers">
			<?for ($line = 0; $line < $numberOfLines; $line++):
				?><div class="adm-composite-line-number"><?=($line + 1)?></div><?
			endfor?>
		</div><?
		?><div class="adm-composite-line-numbers adm-composite-right-numbers">
			<?for ($line = 0; $line < $numberOfLines; $line++):
				?><div class="adm-composite-line-number"><?=($line + 1)?></div><?endfor?>
		</div>
	</div>
	<div class="adm-composite-diff-file" id="right-window">

		<?
		$insertedLines = 0;
		for ($line = 0, $length = count($linesB); $line < $length; $line++):
			$insertedLines = $insertedToB[$line] ?? $insertedLines;
			?>
			<div class="adm-composite-diff-line
					<?if ($insertedLines): $insertedLines--?> adm-composite-inserted-line<?endif?>
					"><span class="adm-composite-diff-line-content"><?=htmlspecialcharsbx($linesB[$line])?></span></div>
			<?
		endfor;
		?>

	</div>
</div>
<script>
	(function() {
		var leftWindow = document.getElementById("left-window");
		var rightWindow = document.getElementById("right-window");
		var gutter = document.getElementById("gutter");
		var leftWindowScroll = true;
		var rightWindowScroll = true;

		function debounce(fn, timeout, ctx)
		{
			var timer = 0;

			return function()
			{
				ctx = ctx || this;
				var args = arguments;

				clearTimeout(timer);

				timer = setTimeout(function()
				{
					fn.apply(ctx, args);
				}, timeout);
			}
		}

		var enableLeftWindowScroll = debounce(function() {
			leftWindowScroll = true;
			console.log("enableLeftWindowScroll");
		}, 400);


		var enableRightWindowScroll = debounce(function() {
			rightWindowScroll = true;
			console.log("enableRightWindowScroll");
		}, 400);

		leftWindow.addEventListener("scroll", function() {

			if (!leftWindowScroll)
			{
				return;
			}

			rightWindowScroll = false;

			gutter.style.transform = "translateY(-" + leftWindow.scrollTop + "px)";
			rightWindow.scrollLeft = leftWindow.scrollLeft;
			rightWindow.scrollTop = leftWindow.scrollTop;

			enableRightWindowScroll();

		}, false);

		rightWindow.addEventListener("scroll", function() {

			if (!rightWindowScroll)
			{
				return;
			}

			leftWindowScroll = false;

			gutter.style.transform = "translateY(-" + rightWindow.scrollTop + "px)";
			leftWindow.scrollLeft = rightWindow.scrollLeft;
			leftWindow.scrollTop = rightWindow.scrollTop;

			enableLeftWindowScroll();

		}, false);

		document.addEventListener("DOMContentLoaded", function() {

			var rightHeader = document.getElementById("right-header");
			rightHeader.style.marginLeft = gutter.offsetWidth / 2 + 20 + "px";
		}, false);

	})();

</script>
</body>
</html>


<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_after.php");
