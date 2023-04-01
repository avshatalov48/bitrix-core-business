<?php
/**
 * @global \CMain $APPLICATION
 * @var $component \CatalogProductDetailsComponent
 * @var $this \CBitrixComponentTemplate
 * @var array $arResult
 * @var array $arParams
 *
 * @var string $templateFolder
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
use Bitrix\UI\Buttons\SettingsButton;
use Bitrix\UI\Toolbar\Facade\Toolbar;

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-background');

Loader::includeModule('ui');

$createDocumentButtonId = null;
if (isset($arResult['CREATE_DOCUMENT_BUTTON']))
{
	$createDocumentButton = new \Bitrix\UI\Buttons\Split\Button($arResult['CREATE_DOCUMENT_BUTTON']['PARAMS']);
	Toolbar::addButton($createDocumentButton);
	$createDocumentButtonId = $createDocumentButton->getUniqId();
}

$settingsButton = new SettingsButton([
	'className' => $arResult['IS_NEW_PRODUCT'] ? 'ui-btn-highlighted' : '',
]);
Toolbar::addButton($settingsButton);

$feedbackButton = new Button([
	'color' => Color::LIGHT_BORDER,
	'text' => Loc::getMessage('CPD_FEEDBACK_BUTTON'),
	'className' => $arResult['IS_NEW_PRODUCT'] ? 'ui-btn-highlighted' : '',
	'onclick' => new JsHandler(
		'BX.Catalog.ProductCard.Instance.openFeedbackPanel',
		'BX.Catalog.ProductCard.Instance'
	),
]);
Toolbar::addButton($feedbackButton);

Toolbar::deleteFavoriteStar();

Extension::load([
	'catalog.entity-card',
	'admin_interface',
	'sidepanel',
	'ui.hint',
]);

$tabs = [];

if ($arResult['TAB_LIST']['MAIN'])
{
	$tabs[] = [
		'id' => 'main',
		'name' => Loc::getMessage('CPD_TAB_GENERAL_TITLE'),
		'enabled' => true,
		'active' => true,
	];
}
if ($arResult['TAB_LIST']['BALANCE'])
{
	$tabs[] = [
		'id' => 'balance',
		'name' => Loc::getMessage('CPD_TAB_BALANCE_TITLE'),
		'enabled' => !$arResult['IS_NEW_PRODUCT'],
		'active' => false,
	];
}
if ($arResult['TAB_LIST']['SEO'])
{
	$tabs[] = [
		'id' => 'seo',
		'name' => 'SEO',
		'enabled' => false,
		'active' => false,
	];
}

$guid = 'product-details';
$containerId = "{$guid}_container";
$tabMenuContainerId = "{$guid}_tabs_menu";
$tabContainerId = "{$guid}_tabs";

$cardParameters = [
	'entityId' => $arResult['PRODUCT_FIELDS']['ID'],
	'componentName' => $component->getName(),
	'componentSignedParams' => $component->getSignedParameters(),
	'variationGridComponentName' => $arResult['VARIATION_GRID_COMPONENT_NAME'],
	'isSimpleProduct' => $arResult['SIMPLE_PRODUCT'],
	'tabs' => $tabs,
	'settingsButtonId' => $settingsButton->getUniqId(),
	'cardSettings' => $arResult['CARD_SETTINGS'],
	'hiddenFields' => $arResult['HIDDEN_FIELDS'],
	'isWithOrdersMode' => $arResult['IS_WITH_ORDERS_MODE'],
	'isInventoryManagementUsed' => $arResult['IS_INVENTORY_MANAGEMENT_USED'],
	'createDocumentButtonId' => $createDocumentButtonId,
	'createDocumentButtonMenuPopupItems' => $arResult['CREATE_DOCUMENT_BUTTON']['POPUP_ITEMS'] ?? [],
	'feedbackUrl' => $arParams['PATH_TO']['FEEDBACK'] ?? '',
	'containerId' => $containerId,
	'tabContainerId' => $tabContainerId,
	'tabMenuContainerId' => $tabMenuContainerId,
	'creationPropertyUrl' => $arResult['UI_CREATION_PROPERTY_URL'],
	'creationVariationPropertyUrl' => $arResult['UI_CREATION_SKU_PROPERTY_URL'],
	'variationGridId' => $arResult['VARIATION_GRID_ID'],
	'productStoreGridId' => $arResult['STORE_AMOUNT_GRID_ID'],
	'productTypeSelector' => 'catalog-productcard-product-type-selector',
	'productTypeSelectorTypes' => $arResult['DROPDOWN_TYPES'],
];
?>
<script>
	BX.message(<?=Json::encode(Loc::loadLanguageFile(__FILE__))?>);
	BX(function() {
		let topWindow = BX.PageObject.getRootWindow().window
		if (!topWindow.adminSidePanel || !BX.is_subclass_of(topWindow.adminSidePanel, BX.adminSidePanel))
		{
			topWindow.adminSidePanel = new BX.adminSidePanel({
				publicMode: true
			});
		}

		BX.Catalog.ProductCard.Instance = new BX.Catalog.ProductCard(
			'<?=CUtil::JSEscape($guid)?>',
			<?= CUtil::PhpToJSObject($cardParameters) ?>
		);
	});
</script>
<?php
if (!empty($arResult['DROPDOWN_TYPES']))
{
	$dropDownTypes = '<div id="catalog-productcard-product-type-selector" class="catalog-productcard-product-type-selector">'
		. '<span class="catalog-productcard-product-type-selector-text" data-hint="" data-hint-no-icon>'
		. Loc::getMessage('CPD_PRODUCT_TYPE_SELECTOR', ['#PRODUCT_TYPE_NAME#' => $arResult['PRODUCT_TYPE_NAME']])
		. '</span>'
		. '</div>'
	;
	Toolbar::addUnderTitleHtml($dropDownTypes);
	unset($dropDownTypes);
}
?>
<div id="<?=htmlspecialcharsbx($containerId)?>" class="catalog-entity-wrap catalog-wrapper">
	<?php
	$tabContainerClassName = 'catalog-entity-section catalog-entity-section-tabs';
	$tabContainerClassName .= ' ui-entity-stream-section-planned-above-overlay';
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

			if ($arResult['IS_NEW_PRODUCT'])
			{
				$className .= ' catalog-entity-section-new';
			}

			if (!$tab['active'])
			{
				$className .= ' catalog-entity-section-tab-content-hide catalog-entity-section-above-overlay';
				$style = 'style="display: none;"';
			}
			?>
			<div data-tab-id="<?=htmlspecialcharsbx($tabId)?>" class="<?=$className?>" <?=$style?>>
				<?php
				$tabFolderPath = Application::getDocumentRoot() . $templateFolder . '/tabs/';
				$file = new File($tabFolderPath.$tabId . '.php');

				if ($file->isExists())
				{
					include $file->getPath();
				}
				else
				{
					echo 'Unknown tab {' . $tabId . '}.';
				}
				?>
			</div>
			<?php
		}
		?>
	</div>
</div>
