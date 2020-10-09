<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var \CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if ($arParams['SHOW_MENU'] == 'Y' && isset($_REQUEST['IFRAME']) && $_REQUEST['IFRAME'] === 'Y')
{
	$this->setViewTarget('above_pagetitle');
	$APPLICATION->IncludeComponent(
		'bitrix:menu',
		'top_horizontal',
		array(
			'ROOT_MENU_TYPE' => 'left',
			'MENU_CACHE_TYPE' => 'N',
			'MENU_CACHE_TIME' => '604800',
			'MENU_CACHE_USE_GROUPS' => 'N',
			'MENU_CACHE_USE_USERS' => 'Y',
			'CACHE_SELECTED_ITEMS' => 'N',
			'MENU_CACHE_GET_VARS' => array(),
			'MAX_LEVEL' => '1',
			'USE_EXT' => 'Y',
			'DELAY' => 'N',
			'ALLOW_MULTI_SELECT' => 'N'
		),
		false
	);
	$this->endViewTarget();
}
foreach ($arResult['ERRORS'] as $error)
{
	ShowError($error);
}
$sectionsTileManagerId = 'rest-integrators-sections-'.$arParams['CODE'];
?>
<div class="rest-integration-list-wrapper">
	<div id="<?=$sectionsTileManagerId?>" class="rest-integration-tile-grid"></div>
	<script type="text/javascript">
		BX.message(<?=Json::encode(
			[
				'REST_INTEGRATION_LIST_ERROR_OPEN_URL' => Loc::getMessage('REST_INTEGRATION_LIST_ERROR_OPEN_URL'),
				'REST_INTEGRATION_LIST_OPEN_PROCESS' => Loc::getMessage('REST_INTEGRATION_LIST_OPEN_PROCESS'),
			]
		);?>);
		BX.ready(function () {
			var RestIntegrationTileGrid = new BX.TileGrid.Grid(
				{
					id: '<?=$sectionsTileManagerId?>',
					container: document.getElementById('<?=$sectionsTileManagerId?>'),
					items: <?=Json::encode($arResult['ITEMS'])?>,
					itemHeight: 160,
					itemMinWidth: 400,
					itemMaxWidth: 400,
					itemType: 'BX.RestIntegrationList.Start.TileGridItem'
				}
			);
			RestIntegrationTileGrid.draw();
		});
	</script>
</div>