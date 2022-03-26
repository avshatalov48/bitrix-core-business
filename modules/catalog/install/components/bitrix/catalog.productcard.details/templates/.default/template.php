<?php
/**
 * @var $component \CatalogProductDetailsComponent
 * @var $this \CBitrixComponentTemplate
 * @var $arResult
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
if (\Bitrix\Catalog\Config\State::isUsedInventoryManagement())
{
	$createDocumentButton = new \Bitrix\UI\Buttons\Split\Button($arResult['CREATE_DOCUMENT_BUTTON_PARAMS']);
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

$tabs = [
	[
		'id' => 'main',
		'name' => Loc::getMessage('CPD_TAB_GENERAL_TITLE'),
		'enabled' => true,
		'active' => true,
	],
	[
		'id' => 'balance',
		'name' => Loc::getMessage('CPD_TAB_BALANCE_TITLE'),
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

$guid = 'product-details';
$containerId = "{$guid}_container";
$tabMenuContainerId = "{$guid}_tabs_menu";
$tabContainerId = "{$guid}_tabs";
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

		BX.Catalog.ProductCard.Instance = new BX.Catalog.ProductCard(
			'<?=CUtil::JSEscape($guid)?>',
			{
				entityId: '<?=CUtil::JSEscape($arResult['PRODUCT_FIELDS']['ID'])?>',
				componentName: '<?=CUtil::JSEscape($component->getName())?>',
				componentSignedParams: '<?=CUtil::JSEscape($component->getSignedParameters())?>',
				isSimpleProduct: !!'<?=CUtil::JSEscape($arResult['SIMPLE_PRODUCT'])?>',
				tabs: <?=CUtil::PhpToJSObject($tabs)?>,
				settingsButtonId: '<?=$settingsButton->getUniqId()?>',
				cardSettings: <?=CUtil::PhpToJSObject($arResult['CARD_SETTINGS'])?>,
				createDocumentButtonId: '<?=CUtil::JSEscape($createDocumentButtonId)?>',
				createDocumentButtonMenuPopupItems: <?=CUtil::PhpToJSObject($arResult['CREATE_DOCUMENT_BUTTON_POPUP_ITEMS'])?>,
				feedbackUrl: '<?=CUtil::JSEscape($arParams['PATH_TO']['FEEDBACK'] ?? '')?>',
				containerId: '<?=CUtil::JSEscape($containerId)?>',
				tabContainerId: '<?=CUtil::JSEscape($tabContainerId)?>',
				tabMenuContainerId: '<?=CUtil::JSEscape($tabMenuContainerId)?>',
				serviceUrl: '<?=CUtil::JSEscape($arResult['SERVICE_URL'])?>',
				creationPropertyUrl: '<?=CUtil::JSEscape($arResult['UI_CREATION_PROPERTY_URL'])?>',
				creationVariationPropertyUrl: '<?=CUtil::JSEscape($arResult['UI_CREATION_SKU_PROPERTY_URL'])?>',
				variationGridId: '<?=CUtil::JSEscape($arResult['VARIATION_GRID_ID'])?>',
				productStoreGridId: '<?=CUtil::JSEscape($arResult['STORE_AMOUNT_GRID_ID'])?>',
			}
		);
	});
</script>
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