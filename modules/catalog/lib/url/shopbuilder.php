<?php
namespace Bitrix\Catalog\Url;

use Bitrix\Catalog;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

class ShopBuilder extends AdminPage\CatalogBuilder
{
	public const TYPE_ID = 'SHOP';

	public const OPEN_SETTINGS_PARAM = 'open_settings_page';

	public const PAGE_CSV_IMPORT = 'csvImport';

	protected const TYPE_WEIGHT = 300;

	protected const PATH_PREFIX = '/shop/settings/';

	protected const PATH_DETAIL_CARD_PREFIX = '/shop/catalog/';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Returns true if the current product's grid is open in the inventory section.
	 *
	 * @return bool
	 */
	public function use(): bool
	{
		if (defined('CATALOG_PRODUCT') && defined('SELF_FOLDER_URL'))
		{
			return true;
		}
		if (!$this->request->isAdminSection())
		{
			if ($this->checkCurrentPage([
				self::PATH_PREFIX,
				self::PATH_DETAIL_CARD_PREFIX
			]))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns grid context menu for public inventory section.
	 *
	 * @param string $pageType
	 * @param array $items
	 * @param array $options
	 * @return array|null
	 */
	public function getContextMenuItems(string $pageType, array $items = [], array $options = []): ?array
	{
		if ($pageType !== self::PAGE_ELEMENT_LIST && $pageType !== self::PAGE_SECTION_LIST)
		{
			return null;
		}

		if (!Loader::includeModule('crm'))
		{
			return null;
		}

		$result = [];

		if (AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS))
		{
			if (!\CCrmSaleHelper::isWithOrdersMode())
			{
				Extension::load(['crm.config.catalog']);

				$result[] = [
					'TEXT' => Loc::getMessage('CATALOG_SHOP_BUILDER_CONTEXT_MENU_ITEM_INVENTORY_MANAGEMENT_SETTINGS'),
					'TITLE' => Loc::getMessage('CATALOG_SHOP_BUILDER_CONTEXT_MENU_ITEM_INVENTORY_MANAGEMENT_SETTINGS'),
					'ONCLICK' => 'BX.Crm.Config.Catalog.Slider.open(\'shop\')',
				];
			}

			if (
				Catalog\Config\Feature::isInventoryManagementEnabled()
				&& !Catalog\Component\UseStore::isUsed()
			)
			{
				Extension::load(['catalog.store-use']);

				$sliderPath = \CComponentEngine::makeComponentPath('bitrix:catalog.warehouse.master.clear');
				$sliderPath = getLocalPath('components' . $sliderPath . '/slider.php');

				$result[] = [
					'TEXT' => Loc::getMessage('CATALOG_SHOP_BUILDER_CONTEXT_MENU_ITEM_WAREHOUSE_Y'),
					'TITLE' => Loc::getMessage('CATALOG_SHOP_BUILDER_CONTEXT_MENU_ITEM_WAREHOUSE_Y'),
					'ONCLICK' => "BX.Catalog.StoreUse.ProductGridMenu.openWarehousePanel('" . $sliderPath . "')"
				];

				unset($sliderPath);
			}
		}

		if (Catalog\Config\Feature::isAccessControllerCheckingEnabled())
		{
			Extension::load('sidepanel');

			$result[] = [
				'TEXT' => Loc::getMessage('CATALOG_SHOP_BUILDER_CONTEXT_MENU_ITEM_ACCESS_RIGHTS'),
				'TITLE' => Loc::getMessage('CATALOG_SHOP_BUILDER_CONTEXT_MENU_ITEM_ACCESS_RIGHTS'),
				'ONCLICK' => "BX.SidePanel.Instance.open('" . \CUtil::JSEscape('/shop/settings/permissions/') . "')"
			];
		}
		else
		{
			$helpLink = Catalog\Config\Feature::getAccessControllerHelpLink();
			if (!empty($helpLink))
			{
				Catalog\Config\Feature::initUiHelpScope();
				$result[] = [
					'TEXT' => Loc::getMessage('CATALOG_SHOP_BUILDER_CONTEXT_MENU_ITEM_ACCESS_RIGHTS'),
					'TITLE' => Loc::getMessage('CATALOG_SHOP_BUILDER_CONTEXT_MENU_ITEM_ACCESS_RIGHTS'),
					$helpLink['TYPE'] => $helpLink['LINK'],
				];
			}
			unset($helpLink);
		}

		$result[] = [
			'TEXT' => Loc::getMessage('CATALOG_SHOP_BUILDER_CONTEXT_MENU_ITEM_SEO'),
			'TITLE' => Loc::getMessage('CATALOG_SHOP_BUILDER_CONTEXT_MENU_ITEM_SEO'),
			'ONCLICK' => "BX.SidePanel.Instance.open('"
				. \CUtil::JSEscape($this->getCatalogSeoUrl())
				. "', {cacheable: false, allowChangeHistory: false, width: 1000})"
			,
		];

		if (!empty($items))
		{
			$result = array_merge($result, $items);
		}

		return (!empty($result) ? $result: null);
	}

	/**
	 * Url builder config initialization.
	 *
	 * @return void
	 */
	protected function initConfig(): void
	{
		parent::initConfig();
		$this->config['UI_CATALOG'] = Catalog\Config\State::isProductCardSliderEnabled();
	}

	/**
	 * Returns true, if enabled new product card.
	 *
	 * @return bool
	 */
	protected function isUiCatalog(): bool
	{
		return (isset($this->config['UI_CATALOG']) && $this->config['UI_CATALOG']);
	}

	/**
	 * Fill url templates list.
	 *
	 * @return void
	 */
	protected function initUrlTemplates(): void
	{
		$this->urlTemplates[self::PAGE_SECTION_LIST] = '#PATH_PREFIX#'
			.($this->iblockListMixed ? 'menu_catalog_goods_#IBLOCK_ID#/' : 'menu_catalog_category_#IBLOCK_ID#/')
			.'?#BASE_PARAMS#'
			.'#PARENT_FILTER#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_SECTION_DETAIL] = '#PATH_PREFIX#'
			.'cat_section_edit/'
			.'?#BASE_PARAMS#'
			.'&ID=#ENTITY_ID#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_SECTION_COPY] = $this->urlTemplates[self::PAGE_SECTION_DETAIL]
			.$this->getCopyAction();
		$this->urlTemplates[self::PAGE_SECTION_SAVE] = '#PATH_PREFIX#'
			.'cat_section_edit.php'
			.'?#BASE_PARAMS#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_SECTION_SEARCH] = '/bitrix/tools/iblock/section_search.php'
			.'?#LANGUAGE#'
			.'#ADDITIONAL_PARAMETERS#';

		$this->urlTemplates[self::PAGE_ELEMENT_LIST] = '#PATH_PREFIX#'
			.($this->iblockListMixed ? 'menu_catalog_goods_#IBLOCK_ID#/' : 'menu_catalog_#IBLOCK_ID#/')
			.'?#BASE_PARAMS#'
			.'#PARENT_FILTER#'
			.'#ADDITIONAL_PARAMETERS#';
		if ($this->isUiCatalog())
		{
			$this->urlTemplates[self::PAGE_ELEMENT_DETAIL] = self::PATH_DETAIL_CARD_PREFIX
				.'#IBLOCK_ID#/product/#ENTITY_ID#/'
				.'?#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_ELEMENT_COPY] = self::PATH_DETAIL_CARD_PREFIX
				.'#IBLOCK_ID#/product/0/copy/#ENTITY_ID#/';
			$this->urlTemplates[self::PAGE_ELEMENT_SAVE] = $this->urlTemplates[self::PAGE_ELEMENT_DETAIL];
			$this->urlTemplates[self::PAGE_OFFER_DETAIL] = '/shop/catalog/'
				.'#PRODUCT_IBLOCK_ID#/product/#PRODUCT_ID#/'
				.'variation/#ENTITY_ID#/';
		}
		else
		{
			$this->urlTemplates[self::PAGE_ELEMENT_DETAIL] = '#PATH_PREFIX#'
				.'cat_product_edit/'
				.'?#BASE_PARAMS#'
				.'&ID=#ENTITY_ID#'
				.'#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_ELEMENT_COPY] = $this->urlTemplates[self::PAGE_ELEMENT_DETAIL]
				.$this->getCopyAction();
			$this->urlTemplates[self::PAGE_ELEMENT_SAVE] = '#PATH_PREFIX#'
				.'cat_product_edit.php'
				.'?#BASE_PARAMS#'
				.'#ADDITIONAL_PARAMETERS#';
			$this->urlTemplates[self::PAGE_OFFER_DETAIL] = $this->urlTemplates[self::PAGE_ELEMENT_DETAIL];
		}
		$this->urlTemplates[self::PAGE_ELEMENT_SEARCH] = '/bitrix/tools/iblock/element_search.php'
			.'?#LANGUAGE#'
			.'#ADDITIONAL_PARAMETERS#';
		$this->urlTemplates[self::PAGE_CATALOG_SEO] = self::PATH_DETAIL_CARD_PREFIX . '#IBLOCK_ID#/seo/';
		$this->urlTemplates[self::PAGE_ELEMENT_SEO] = self::PATH_DETAIL_CARD_PREFIX . '#IBLOCK_ID#/seo/product/#PRODUCT_ID#/';
		$this->urlTemplates[self::PAGE_SECTION_SEO] = self::PATH_DETAIL_CARD_PREFIX . '#IBLOCK_ID#/seo/section/#SECTION_ID#/';
	}

	/**
	 * Returns regular expression's list for check urls.
	 *
	 * @return string[]
	 */
	protected function getSliderPathTemplates(): array
	{
		return [
			'/^\/shop\/catalog\/[0-9]+\/product\/[0-9]+\/$/',
			'/^\/shop\/catalog\/[0-9]+\/product\/[0-9]+\/variation\/[0-9]+\/$/',
		];
	}

	public function openSettingsPage(): void
	{
		if
		(
			$this->request->get('open_settings_page')
			&& (int)$this->request->get('open_settings_page') === 1
		)
		{
			echo $this->getSettingsSlider();
		}
	}

	protected function getSettingsSlider(): string
	{
		\Bitrix\Main\UI\Extension::load(['crm.config.catalog']);

		return '<script>'
			. 'BX.ready(function() {' . "\n"
			. ' BX.Crm.Config.Catalog.Slider.open(\'shop\');' . "\n"
			. '});' . "\n"
			. '</script>'
			;
	}

	public function subscribeOnAfterSettingsSave(): void
	{
		$saveEventName = static::getOnSaveEventName();

		if ($saveEventName !== '')
		{
			$saveEventName = \CUtil::JSEscape($saveEventName);

			echo '<script>'
				. 'BX.addCustomEvent(\'' . $saveEventName . '\', function() {' . "\n"
				. ' var href = window.top.location.href;' . "\n"
				. ' window.top.location.replace(href.replace(/' . \CUtil::JSEscape(static::OPEN_SETTINGS_PARAM) . '.*&?/, \'\'));' . "\n"
				. '});' . "\n"
				. '</script>'
			;
		}
	}

	protected static function getOnSaveEventName(): string
	{
		return 'onCatalogSettingsSave';
	}
}
