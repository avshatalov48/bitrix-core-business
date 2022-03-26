<?

/**
 * @var $arParams
 * @var $arResult
 */

use \Bitrix\Main\Text;
use \Bitrix\Main\Grid;
use \Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

CJSCore::Init(array('popup', 'ui', 'resize_observer', 'loader', 'ui.actionpanel',));

\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/main/dd.js');

global $APPLICATION;
$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."grid-mode");

if ($arParams['FLEXIBLE_LAYOUT'])
{
	$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
	$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, 'flexible-layout')));
}

$additionalColumnsCount = 1;

if ($arParams["SHOW_ROW_CHECKBOXES"])
{
	$additionalColumnsCount += 1;
}

if ($arParams["SHOW_GRID_SETTINGS_MENU"] || $arParams["SHOW_ROW_ACTIONS_MENU"])
{
	$additionalColumnsCount += 1;
}

if ($arParams["ALLOW_ROWS_SORT"])
{
	$additionalColumnsCount += 1;
}

$displayedCount = count(
	array_filter(
		$arParams["ROWS"],
		function($val)
		{
			return $val["not_count"] !== true;
		}
	)
);
/** @var \Bitrix\Main\UI\PageNavigation $navigation */
$navigation = $arParams["NAV_OBJECT"];
$navigationData = [
	'urlNextPage' => null,
	'pageSize' => $navigation->getPageSize(),
	'currentPage' => $navigation->getCurrentPage(),
	'hasNextPage' => ($navigation->getRecordCount() - $navigation->getPageSize()*$navigation->getCurrentPage()) > 0
];
if ($navigationData['hasNextPage'])
{
	$uri = new \Bitrix\Main\Web\Uri(\Bitrix\Main\Context::getCurrent()->getRequest()->getRequestUri());
	$uri->deleteParams(\Bitrix\Main\HttpRequest::getSystemParameters());
	$navigation->addParams($uri, $this->arParams["SEF_MODE"], $navigation->getCurrentPage() + 1);
	$navigationData['urlNextPage'] = $uri->getUri();
}

if (\Bitrix\Main\Context::getCurrent()->getRequest()->isAjaxRequest())
{
	$response = new \Bitrix\Main\Engine\Response\AjaxJson([
		'tileGrid' => [
			'id' => $arParams['GRID_ID'],
			'items' => $arResult['TILE_GRID_ITEMS'],
			'itemType' => $arParams['JS_CLASS_TILE_GRID_ITEM'],
		],
		'navigation' => $navigationData,
		'actionsPanel' => '',
	]);
	$application = \Bitrix\Main\Application::getInstance();
	$application->getContext()->setResponse($response);
	$APPLICATION->restartBuffer();
	$application->end();
}

?>
<div id="<?= $arParams["GRID_ID"] ?>_tile_grid_container" class="disk-tile-grid"></div>

<script>
	BX(function() {
		<? if(isset($arParams['TOP_ACTION_PANEL_RENDER_TO'])): ?>
			var actionPanel = new BX.UI.ActionPanel({
				params: {
					tileGridId: '<?=$arParams["GRID_ID"]?>'
				},
				renderTo: document.querySelector("<?= CUtil::JSEscape($arParams['TOP_ACTION_PANEL_RENDER_TO']) ?>"),
				groupActions: <?= \Bitrix\Main\Web\Json::encode($arParams['ACTION_PANEL']) ?>,
				maxHeight: <?= (int)$arParams['ACTION_PANEL_OPTIONS']['MAX_HEIGHT']?>
			});
			actionPanel.draw();
		<? endif; ?>

		var gridTile = new BX.Main.TileGrid(
			{
				id: '<?= $arParams['GRID_ID']?>',
				tileSize: '<?= $arParams['TILE_SIZE']?>',
				container: document.getElementById('<?= $arParams['GRID_ID'] ?>_tile_grid_container'),
				items: <?= \Bitrix\Main\Web\Json::encode($arResult['TILE_GRID_ITEMS']) ?>,
				checkBoxing: <?= $arParams["SHOW_ROW_CHECKBOXES"] ? 'true' : 'false' ?>,
				itemType: '<?= CUtil::JSEscape($arParams['JS_CLASS_TILE_GRID_ITEM']) ?>',
				userOptions: <?=\Bitrix\Main\Web\Json::encode($arResult['OPTIONS'])?>,
				userOptionsActions: <?=\Bitrix\Main\Web\Json::encode($arResult['OPTIONS_ACTIONS'])?>,
				userOptionsHandlerUrl: '<?=$arResult['OPTIONS_HANDLER_URL']?>',
				navigation: <?= \Bitrix\Main\Web\Json::encode($navigationData) ?>,
				generatorEmptyBlock: <?= isset($arParams['~JS_TILE_GRID_GENERATOR_EMPTY_BLOCK'])? $arParams['~JS_TILE_GRID_GENERATOR_EMPTY_BLOCK'] : 'null' ?>
			}
		);
		BX.Main.tileGridManager.push('<?= $arParams['GRID_ID'] ?>', gridTile);

		gridTile.draw();
	});
</script>
