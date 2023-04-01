<?php
/**
 * @var $component \CatalogProductDetailsComponent
 * @var $this \CBitrixComponentTemplate
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\UI\Buttons\Button;
use Bitrix\UI\Buttons\Color;
use Bitrix\UI\Buttons\JsHandler;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\UI\Buttons\SettingsButton;

Extension::load([
	'catalog.entity-card',
	'admin_interface',
	'sidepanel',
]);

Loader::includeModule('ui');

$settingsButton = new SettingsButton([
	'className' => $arResult['IS_NEW_PRODUCT'] ? 'ui-btn-highlighted' : '',
]);
Toolbar::addButton($settingsButton);

$feedbackButton = new Button([
	'color' => Color::LIGHT_BORDER,
	'text' => Loc::getMessage('CPVD_FEEDBACK_BUTTON'),
	'className' => $arResult['IS_NEW_PRODUCT'] ? 'ui-btn-highlighted' : '',
	'onclick' => new JsHandler(
		'BX.Catalog.VariationCard.Instance.openFeedbackPanel',
		'BX.Catalog.VariationCard.Instance'
	),
]);
Toolbar::addButton($feedbackButton);

Toolbar::deleteFavoriteStar();

$tabs = [
	[
		'id' => 'main',
		'name' => Loc::getMessage('CPVD_TAB_GENERAL_TITLE'),
		'enabled' => true,
		'active' => true,
	],
	[
		'id' => 'balance',
		'name' => Loc::getMessage('CPVD_TAB_BALANCE_TITLE'),
		'enabled' => true,
		'active' => false,
	]
	// [
	// 	'id' => 'seo',
	// 	'name' => 'SEO',
	// 	'enabled' => false,
	// 	'active' => false,
	// ],
];

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."no-background");

$guid = 'product-variation-details';
$containerId = "{$guid}_container";
$tabMenuContainerId = "{$guid}_tabs_menu";
$tabContainerId = "{$guid}_tabs";

$cardParameters = [
	'entityId' => $arResult['VARIATION_FIELDS']['ID'],
	'componentName' => $component->getName(),
	'componentSignedParams' => $component->getSignedParameters(),
	'tabs' => $tabs,
	'cardSettings' => $arResult['CARD_SETTINGS'],
	'hiddenFields' => $arResult['HIDDEN_FIELDS'],
	'isWithOrdersMode' => $arResult['IS_WITH_ORDERS_MODE'],
	'isInventoryManagementUsed' => $arResult['IS_INVENTORY_MANAGEMENT_USED'],
	'feedbackUrl' => $arParams['PATH_TO']['FEEDBACK'] ?? '',
	'containerId' => $containerId,
	'settingsButtonId' => $settingsButton->getUniqId(),
	'tabContainerId' => $tabContainerId,
	'tabMenuContainerId' => $tabMenuContainerId,
	'creationPropertyUrl' => $arResult['UI_CREATION_PROPERTY_URL'],
	'variationGridId' => $arResult['VARIATION_GRID_ID'],
	'productStoreGridId' => $arResult['STORE_AMOUNT_GRID_ID'],
	'creationVariationPropertyUrl' => $arResult['UI_CREATION_SKU_PROPERTY_URL'],
];
?>
<script>
	BX.message(<?=Json::encode(Loc::loadLanguageFile(__FILE__))?>);
	BX(function() {
		var topWindow = BX.PageObject.getRootWindow().window;
		if (!topWindow.adminSidePanel || !BX.is_subclass_of(topWindow.adminSidePanel, BX.adminSidePanel))
		{
			topWindow.adminSidePanel = new BX.adminSidePanel({
				publicMode: true
			});
		}

		BX.Catalog.VariationCard.Instance = new BX.Catalog.VariationCard(
			'<?=CUtil::JSEscape($guid)?>',
			<?= CUtil::PhpToJSObject($cardParameters) ?>
		);
	});
</script>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="catalog-entity-wrap catalog-wrapper">
	<?php
	$tabContainerClassName = 'catalog-entity-section catalog-entity-section-tabs';
	$tabContainerClassName .= ' catalog-entity-stream-section-planned-above-overlay';
	?>
	<div class="<?=$tabContainerClassName?>">
		<ul id="<?=htmlspecialcharsbx($tabMenuContainerId)?>" class="catalog-entity-section-tabs-container">
			<?php
			foreach ($tabs as $tab)
			{
				$classNames = ['catalog-entity-section-tab'];

				if (isset($tab['active']) && $tab['active'])
				{
					$classNames[] = 'catalog-entity-section-tab-current';
				}
				elseif (isset($tab['enabled']) && !$tab['enabled'])
				{
					$classNames[] = 'catalog-entity-section-tab-disabled';
				}
				?>
				<li data-tab-id="<?=htmlspecialcharsbx($tab['id'])?>" class="<?=implode(' ', $classNames)?>">
					<a class="catalog-entity-section-tab-link" href="#"><?=htmlspecialcharsbx($tab['name'])?></a>
				</li>
				<?php
			}
			?>
		</ul>
	</div>
	<div id="<?=htmlspecialcharsbx($tabContainerId)?>" style="position: relative;">
		<?php
		foreach ($tabs as $tab)
		{
			$tabId = $tab['id'];
			$className = 'catalog-entity-section catalog-entity-section-info';
			$style = '';

			if ($tab['active'] !== true)
			{
				$className .= ' catalog-entity-section-tab-content-hide catalog-entity-section-above-overlay';
				$style = 'style="display: none;"';
			}
			?>
			<div data-tab-id="<?=htmlspecialcharsbx($tabId)?>" class="<?=$className?>" <?=$style?>>
				<?php
				$tabFolderPath = Application::getDocumentRoot().$templateFolder.'/tabs/';
				$file = new File($tabFolderPath.$tabId.'.php');

				if ($file->isExists())
				{
					include $file->getPath();
				}
				else
				{
					echo "Unknown tab {{$tabId}}.";
				}
				?>
			</div>
			<?php
		}
		?>
	</div>
</div>