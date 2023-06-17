<?php

use Bitrix\Catalog\Component\BaseForm;
use Bitrix\Catalog\Component\GridVariationForm;
use Bitrix\Catalog\Component\GridServiceForm;
use Bitrix\Catalog\Component\ProductForm;
use Bitrix\Catalog\Component\ServiceForm;
use Bitrix\Catalog\Component\StoreAmount;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Component\UseStore;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\Model\StoreDocument;
use Bitrix\Catalog\Config\Feature;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\IoC\Dependency;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Currency\Integration\IblockMoneyProperty;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\Model\PropertyFeature;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\UI\FileInputUtility;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\Catalog\v2\Barcode\Barcode;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Iblock\Component\Property\ComponentLinksBuilder;
use Bitrix\Main\Web\Uri;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogProductDetailsComponent
	extends \CBitrixComponent
	implements Controllerable, Errorable
{
	use ErrorableImplementation;

	private int $iblockId = 0;
	private int $productId = 0;
	private int $productTypeId = 0;
	private int $copyProductId = 0;
	private bool $isCopy = false;

	/** @var ProductForm|ServiceForm */
	private $form;
	/** @var StoreAmount */
	private $storeAmount;
	/** @var BaseProduct */
	private $product;
	/** @var BaseProduct */
	private $copyProduct;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
	}

	protected function showErrors()
	{
		foreach ($this->getErrors() as $error)
		{
			$this->includeErrorComponent($error->getMessage());
		}
	}

	protected function includeErrorComponent(string $errorMessage, string $description = null): void
	{
		Toolbar::deleteFavoriteStar();

		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			"bitrix:ui.info.error",
			"",
			[
				'TITLE' => $errorMessage,
				'DESCRIPTION' => $description,
				'IS_HTML' => 'Y',
			]
		);
	}

	public function configureActions()
	{
		return [];
	}

	protected function listKeysSignedParameters()
	{
		return [
			'PRODUCT_ID',
			'IBLOCK_ID',
			'PRODUCT_TYPE_ID',
			'PATH_TO',
			'COPY_PRODUCT_ID',
			'BUILDER_CONTEXT',
			'SCOPE',
		];
	}

	public function onPrepareComponentParams($params)
	{
		if (
			!$this->checkModules()
		)
		{
			$this->showErrors();
			return null;
		}

		if (isset($params['IBLOCK_ID']))
		{
			$this->setIblockId((int)$params['IBLOCK_ID']);
		}

		if (isset($params['PRODUCT_ID']))
		{
			$this->setProductId((int)$params['PRODUCT_ID']);
			if ($this->hasProductId())
			{
				unset($params['PRODUCT_TYPE_ID']);
			}
		}

		if (isset($params['COPY_PRODUCT_ID']))
		{
			$this->setCopyProductId((int)$params['COPY_PRODUCT_ID']);
			if (!$this->hasCopyProductId())
			{
				unset($params['COPY_PRODUCT_ID']);
			}
		}

		if (isset($params['PRODUCT_TYPE_ID']))
		{
			$this->setProductTypeId((int)$params['PRODUCT_TYPE_ID']);
			$currentTypeId = $this->getProductTypeId();
			if ($currentTypeId > 0)
			{
				$params['PRODUCT_TYPE_ID'] = $currentTypeId;
			}
			else
			{
				unset($params['PRODUCT_TYPE_ID']);
			}
		}

		$externalFields = $this->request->get('external_fields') ?? [];
		if (!empty($externalFields) && is_array($externalFields))
		{
			$params['EXTERNAL_FIELDS'] = $externalFields;
		}

		return parent::onPrepareComponentParams($params);
	}

	public function executeComponent()
	{
		if (
			!$this->checkModules()
		)
		{
			$this->showErrors();
			return;
		}

		if (!$this->isProductTypeSupported())
		{
			return;
		}

		if (
			$this->checkBasePermissions()
			&& $this->checkRequiredParameters()
		)
		{
			$product = $this->getProduct();

			if ($product)
			{
				if (!$product->isNew() || $this->checkAddPermissions())
				{
					$this->initializeExternalProductFields($product);

					$this->initializeProductFields($product);

					$this->placePageTitle($product);

					$this->errorCollection->clear();
					$this->includeComponentTemplate();
				}
			}
		}

		$this->showErrors();
	}

	protected function checkModules(): bool
	{
		if (!Loader::includeModule('catalog'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Module "catalog" is not installed.');

			return false;
		}

		return true;
	}

	/**
	 * Returns a list of product types that the component can display.
	 *
	 * @return array
	 */
	protected function getAllowedProductTypes(): array
	{
		$iblockInfo = ServiceContainer::getIblockInfo($this->getIblockId());
		if (!$iblockInfo)
		{
			return [];
		}

		if ($iblockInfo->canHaveSku())
		{
			return [
				ProductTable::TYPE_PRODUCT,
				ProductTable::TYPE_SKU,
				ProductTable::TYPE_OFFER,
				ProductTable::TYPE_SERVICE,
			];
		}

		return [
			ProductTable::TYPE_PRODUCT,
			ProductTable::TYPE_SERVICE,
			ProductTable::TYPE_EMPTY_SKU,
		];
	}

	protected function checkLimitedProductTypes(int $productTypeId): bool
	{
		if ($productTypeId !== ProductTable::TYPE_SERVICE)
		{
			return true;
		}
		if (Feature::isCatalogServicesEnabled())
		{
			return true;
		}

		if (Loader::includeModule('bitrix24'))
		{
			Extension::load('ui.info-helper');
			$this->includeErrorComponent(Loc::getMessage(
				'CPD_ERROR_CATALOG_SERVICES_LIMIT_TARIFF',
				['#LINK#' => Feature::getCatalogServicesHelpLink()['LINK']]
			));
		}
		else
		{
			$this->includeErrorComponent(Loc::getMessage('CPD_ERROR_CATALOG_SERVICES_LIMIT_EDITION'));
		}

		return false;
	}

	protected function checkAllowedProductTypes(int $productTypeId): bool
	{
		if (in_array($productTypeId, $this->getAllowedProductTypes(), true))
		{
			return true;
		}

		$row = $this->getElementRow($this->getProductId());
		if ($row === null)
		{
			return true;
		}
		$row['ID'] = (int)$row['ID'];
		$row['TYPE'] = (int)$row['TYPE'];
		$row['IBLOCK_ID'] = (int)$row['IBLOCK_ID'];

		$this->showProductTypeNotSupportedError($row);

		return false;
	}

	protected function isProductTypeSupported(): bool
	{
		if (!$this->hasProductId())
		{
			return true;
		}

		$productTypeId = $this->getProductTypeId();
		if ($productTypeId === 0)
		{
			return true;
		}

		if (!$this->checkAllowedProductTypes($productTypeId))
		{
			return false;
		}
		if (!$this->checkLimitedProductTypes($productTypeId))
		{
			return false;
		}

		return true;
	}

	protected function showProductTypeNotSupportedError(array $row): void
	{
		$productTypes = ProductTable::getProductTypes(true);
		if (isset($productTypes[$row['TYPE']]))
		{
			$errorMessage = $this->getFormedErrorMessage($row, $productTypes[$row['TYPE']]);
			if (!empty($errorMessage))
			{
				$this->includeErrorComponent($errorMessage);
			}
		}
		else
		{
			$this->includeErrorComponent(Loc::getMessage('CPD_ERROR_UNKNOWN_PRODUCT_TYPE'));
		}
	}

	private function getFormedErrorMessage(array $row, string $productType): string
	{
		$title = $this->formatProductTypeName($productType);

		if (
			Loader::includeModule('crm')
			&& !Loader::includeModule('bitrix24')
			&& \Bitrix\Main\Config\Option::get('catalog', 'product_card_slider_enabled') === 'Y'
		)
		{
			$builder = new \Bitrix\Catalog\Url\ShopBuilder();
			$builder->setIblockId($row['IBLOCK_ID']);
			$builder->setSliderMode(false);
			$builder->setUrlParams([
				\Bitrix\Catalog\Url\ShopBuilder::OPEN_SETTINGS_PARAM => 1,
			]);
			$url = $builder->getElementListUrl(0);

			if ($url && $this->checkProductSliderEnablePermissions())
			{
				$link = '<a href="'
					. $url
					. '" target="_top" class="ui-link-solid">'
					. Loc::getMessage('CPD_SETS_ENABLE_PRODUCT_SLIDER_LINK')
					. '</a>'
				;
			}
			else
			{
				$link = '';
			}

			return Loc::getMessage('CPD_ERROR_NOT_SUPPORTED_PRODUCT_TYPE_BUS', ['#TYPE#' => $title, '#LINK#' => $link]);
		}
		else
		{
			$builder = new \Bitrix\Catalog\Url\ShopBuilder();
			$builder->setIblockId($row['IBLOCK_ID']);
			$url = $builder->getElementListUrl(0);
			if ($url)
			{
				$link = '<a href="'
					. $url
					. '" target="_top" class="ui-link-solid">'
					. Loc::getMessage('CPD_SETS_NOT_SUPPORTED_LINK')
					. '</a>'
				;

				return Loc::getMessage('CPD_ERROR_NOT_SUPPORTED_PRODUCT_TYPE', ['#TYPE#' => $title, '#LINK#' => $link]);
			}
		}

		return '';
	}

	private function checkProductSliderEnablePermissions(): bool
	{
		$accessController = \Bitrix\Catalog\Access\AccessController::getCurrent();

		return
			(
				$accessController->check(ActionDictionary::ACTION_CATALOG_READ)
				&& $accessController->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS)
			)
		;
	}

	protected function checkBasePermissions(): bool
	{
		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('CPD_ACCESS_DENIED_ERROR_TITLE'));

			return false;
		}

		$form = $this->getForm();
		if ($form === null)
		{
			return false;
		}
		if (!$form->isCardAllowed())
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('New product card feature disabled.');

			return false;
		}

		return true;
	}

	protected function checkEditPermissions(): bool
	{
		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_EDIT))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('CPD_EDIT_PRODUCT_DENIED_ERROR_TITLE'));

			return false;
		}

		return true;
	}

	public function checkStoreViewPermissions(): bool
	{
		return AccessController::getCurrent()->check(ActionDictionary::ACTION_STORE_VIEW);
	}

	protected function checkAddPermissions(): bool
	{
		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_ADD))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('CPD_ADD_PRODUCT_DENIED_ERROR_TITLE'));

			return false;
		}

		return true;
	}

	protected function checkDeletePermissions(): bool
	{
		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_DELETE))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('CPD_ACCESS_DENIED_ERROR_TITLE'));

			return false;
		}

		return true;
	}

	protected function checkRequiredParameters(): bool
	{
		if (!$this->hasIblockId())
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Iblock id not found.');

			return false;
		}

		return true;
	}

	private function getApplication()
	{
		global $APPLICATION;

		return $APPLICATION;
	}

	protected function setIblockId(int $iblockId): self
	{
		$this->iblockId = $iblockId;

		return $this;
	}

	protected function getIblockId(): int
	{
		return $this->iblockId;
	}

	private function hasIblockId(): bool
	{
		return $this->getIblockId() > 0;
	}

	protected function setProductId(int $productId): self
	{
		$productTypeId = 0;
		if ($productId > 0)
		{
			$row = $this->getElementRow($productId);
			if ($row !== null)
			{
				$productTypeId = (int)($row['TYPE'] ?? 0);
			}
			else
			{
				$productId = 0;
			}
		}

		$this->productId = $productId;
		$this->productTypeId = $productTypeId;

		return $this;
	}

	protected function getProductId(): int
	{
		return $this->productId;
	}

	private function hasProductId(): bool
	{
		return $this->getProductId() > 0;
	}

	protected function getProductTypeId(): int
	{
		return $this->productTypeId;
	}

	protected function setProductTypeId(int $productTypeId): self
	{
		if ($this->productTypeId > 0)
		{
			return $this;
		}

		if (!in_array($productTypeId, $this->getAllowedProductTypes(), true))
		{
			return $this;
		}

		$this->productTypeId = $productTypeId;

		return $this;
	}

	protected function hasProductTypeid(): bool
	{
		return $this->getProductTypeId() > 0;
	}

	protected function isServiceType(): bool
	{
		return $this->getProductTypeId() === ProductTable::TYPE_SERVICE;
	}

	protected function isProductType(): bool
	{
		$productTypeId = $this->getProductTypeId();

		return (
			$productTypeId === ProductTable::TYPE_PRODUCT
			|| $productTypeId === ProductTable::TYPE_SET
			|| $productTypeId === ProductTable::TYPE_SKU
		);
	}

	protected function isVariationType(): bool
	{
		return $this->getProductTypeId() === ProductTable::TYPE_OFFER;
	}

	protected function setCopyProductId(int $copyProductId): self
	{
		if ($this->hasProductId())
		{
			$copyProductId = 0;
		}
		if ($copyProductId > 0)
		{
			$row = $this->getElementRow($copyProductId);
			if ($row !== null)
			{
				$productTypeId = (int)($row['TYPE'] ?? 0);
				if ($productTypeId > 0)
				{
					$this->setProductTypeId($productTypeId);
				}
			}
			else
			{
				$copyProductId = 0;
			}
		}

		$this->copyProductId = $copyProductId;
		$this->isCopy = $this->copyProductId > 0;

		return $this;
	}

	protected function getCopyProductId(): int
	{
		return $this->copyProductId;
	}

	protected function hasCopyProductId(): bool
	{
		return $this->getCopyProductId() > 0;
	}

	protected function isCopy(): bool
	{
		return $this->isCopy;
	}

	protected function placePageTitle(BaseProduct $product): void
	{
		if ($product->isNew())
		{
			$title = $this->isServiceType()
				? Loc::getMessage('CPD_NEW_SERVICE_TITLE')
				: Loc::getMessage('CPD_NEW_PRODUCT_TITLE')
			;
		}
		else
		{
			$title = HtmlFilter::encode($product->getName());
		}

		$this->getApplication()->setTitle($title);
	}

	protected function createProduct(): ?BaseProduct
	{
		$isCopy = $this->isCopy();
		$existsProductType = !$isCopy && $this->hasProductTypeid();

		$product = null;
		$productFactory = ServiceContainer::getProductFactory($this->getIblockId());

		if ($productFactory)
		{
			/** @var BaseProduct $product */
			$product = $productFactory
				->createEntity()
				->setActive(true)
			;
			if ($existsProductType)
			{
				$product->setType($this->getProductTypeId());
			}
		}

		if ($product === null)
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(sprintf(
				'Could not create product for iblock {%s}.', $this->getIblockId()
			));
		}

		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_PUBLIC_VISIBILITY_SET))
		{
			$product->setField('UF_PRODUCT_MAPPING', []);
		}

		if (!$existsProductType)
		{
			$skuRepository = ServiceContainer::getSkuRepository($this->getIblockId());
			$productTypeId = $skuRepository ? ProductTable::TYPE_SKU : ProductTable::TYPE_PRODUCT;
			$this->setProductTypeId($productTypeId);
		}

		if ($isCopy)
		{
			$copyProductId = $this->getCopyProductId();
			$this->copyProduct = $this->loadProduct($copyProductId);
			if ($this->copyProduct)
			{
				$fields = $this->copyProduct->getFields();
				unset(
					$fields['ID'],
					$fields['IBLOCK_ID'],
					$fields['XML_ID'],
					$fields['PREVIEW_PICTURE'],
					$fields['DETAIL_PICTURE'],
					$fields['QUANTITY'],
					$fields['QUANTITY_RESERVED']
				);
				if ($fields['TYPE'] === ProductTable::TYPE_EMPTY_SKU)
				{
					unset($fields['TYPE']);
				}
				$product->setFields($fields);
				$product->getSectionCollection()->setValues(
					$this->copyProduct->getSectionCollection()->getValues()
				);

				$propertyValues = [];
				foreach ($this->copyProduct->getPropertyCollection() as $property)
				{
					$propertyValues[$property->getId()] = $property->getPropertyValueCollection()->toArray();
				}
				$product->getPropertyCollection()->setValues($propertyValues);
			}
		}
		else
		{
			$iblockSectionId = $this->request->get('IBLOCK_SECTION_ID');
			if (!empty($iblockSectionId))
			{
				$product->getSectionCollection()->setValues([$iblockSectionId]);
			}
		}

		return $product;
	}

	protected function loadProduct($productId): ?BaseProduct
	{
		$repositoryFacade = ServiceContainer::getRepositoryFacade();

		$product = $repositoryFacade->loadProduct($productId);
		if ($product)
		{
			return $product;
		}

		$variation = $repositoryFacade->loadVariation($productId);

		if ($variation === null)
		{
			$this->includeErrorComponent(Loc::getMessage('CPD_NOT_FOUND_ERROR_TITLE'), '');

			return null;
		}

		return $variation->getParent();
	}

	protected function getProduct(): ?BaseProduct
	{
		if ($this->product === null)
		{
			if ($this->hasProductId())
			{
				$this->product = $this->loadProduct($this->getProductId());
			}
			else
			{
				$this->product = $this->createProduct();
			}
		}

		return $this->product;
	}

	protected function initializeExternalProductFields(BaseProduct $product): void
	{
		$fields = $this->arParams['EXTERNAL_FIELDS'] ?? [];

		if (empty($fields))
		{
			return;
		}

		$product->setFields($fields);

		if ($product->getSkuCollection()->isEmpty())
		{
			$product->getSkuCollection()->create();
		}

		foreach ($product->getSkuCollection() as $sku)
		{
			$sku->setFields($fields);

			if (isset($fields['PRICE']) || isset($fields['CURRENCY']))
			{
				$sku->getPriceCollection()->setValues([
					'BASE' => [
						'PRICE' => $fields['PRICE'] ?? null,
						'CURRENCY' => $fields['CURRENCY'] ?? null,
					],
				])
				;
				break;
			}
		}
	}

	protected function initializeProductFields(BaseProduct $product): void
	{
		$this->arResult['PRODUCT_ENTITY'] = $product;
		$this->arResult['PRODUCT_FIELDS'] = $product->getFields();
		$this->arResult['PRODUCT_FIELDS']['ID'] = $this->arResult['PRODUCT_FIELDS']['ID'] ?? null;
		$this->arResult['SIMPLE_PRODUCT'] = $product->isSimple();
		$this->arResult['IS_NEW_PRODUCT'] = $product->isNew();

		$this->arResult['UI_GUID'] = $this->isServiceType()
			? 'CATALOG_SERVICE_CARD'
			: 'CATALOG_PRODUCT_CARD'
		;

		$this->arResult['UI_ENTITY_FIELDS'] = $this->getForm()->getDescriptions();
		$this->arResult['UI_ENTITY_CONFIG'] = $this->getForm()->getConfig();
		$this->arResult['UI_ENTITY_READ_ONLY'] = $this->getForm()->isReadOnly();
		$this->arResult['UI_ENTITY_CARD_SETTINGS_EDITABLE'] = $this->getForm()->isCardSettingsEditable();
		$this->arResult['UI_ENTITY_ENABLE_SETTINGS_FOR_ALL'] = $this->getForm()->isEnabledSetSettingsForAll();

		$this->arResult['UI_ENTITY_DATA'] = $this->getForm()->getValues($product->isNew());

		$this->arResult['UI_ENTITY_CONTROLLERS'] = $this->getForm()->getControllers();
		$this->arResult['UI_CREATION_PROPERTY_URL'] = $this->getCreationPropertyUrl();
		$this->arResult['UI_CREATION_SKU_PROPERTY_URL'] = $this->getCreationSkuPropertyLink();

		$this->arResult['TAB_LIST'] = $this->getTabList();
		$this->arResult['VARIATION_GRID_ID'] = $this->getForm()->getVariationGridId();
		$this->arResult['VARIATION_GRID_COMPONENT_NAME'] = $this->getForm()->getVariationGridJsComponentName();
		$this->arResult['STORE_AMOUNT_GRID_ID'] = $this->getStoreAmount()->getStoreAmountGridId();
		$this->arResult['CARD_SETTINGS'] = $this->getForm()->getCardSettings();
		$this->arResult['HIDDEN_FIELDS'] = $this->getForm()->getHiddenFields();
		$this->arResult['IS_WITH_ORDERS_MODE'] = Loader::includeModule('crm') && \CCrmSaleHelper::isWithOrdersMode();
		$this->arResult['IS_INVENTORY_MANAGEMENT_USED'] = UseStore::isUsed();

		$this->arResult['CREATE_DOCUMENT_BUTTON'] = $this->getCreateDocumentButton();

		$this->arResult['PRODUCT_TYPE_NAME'] = $this->getProductTypeName($product);
		$this->arResult['DROPDOWN_TYPES'] = $this->getDropdownTypes($product);
		$this->arResult['DISABLED_HTML_CONTROLS'] = $this->getDisabledHtmlControls();
	}

	/**
	 * Returns tab list for entity card
	 *
	 * @return array
	 */
	protected function getTabList(): array
	{
		return [
			'MAIN' => true,
			'BALANCE' => !$this->isServiceType(),
			'SEO' => false,
		];
	}

	/**
	 * The button for creating a document with rights.
	 *
	 * @return array|null
	 */
	private function getCreateDocumentButton(): ?array
	{
		if (
			!State::isUsedInventoryManagement()
			|| !AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ)
			|| !AccessController::getCurrent()->check(ActionDictionary::ACTION_INVENTORY_MANAGEMENT_ACCESS)
		)
		{
			return null;
		}

		$popupItems = $this->getCreateDocumentButtonPopupItems();
		if (empty($popupItems))
		{
			return null;
		}

		$row = reset($popupItems);

		return [
			'PARAMS' => [
				'className' => 'ui-btn-primary',
				'mainButton' => [
					'text' => Loc::getMessage('CPD_CREATE_DOCUMENT_BUTTON'),
					'link' => $row['link'],
				],
			],
			'POPUP_ITEMS' => $popupItems,
		];
	}

	protected function getCreateDocumentButtonPopupItems(): array
	{
		$productId = $this->getProductId();
		$isService = $this->isServiceType();

		$baseLink = new Uri('/shop/documents/details/0/');
		$baseLink->addParams([
			'preselectedProductId' => $productId,
			'inventoryManagementSource' => 'product',
			'focusedTab' => 'tab_products',
		]);

		$result = [];

		if (!$isService)
		{
			$documents = [
				[
					'text' => Loc::getMessage('CPD_CREATE_DOCUMENT_BUTTON_POPUP_ADJUSTMENT'),
					'type' => StoreDocumentTable::TYPE_ARRIVAL,
				],
				[
					'text' => Loc::getMessage('CPD_CREATE_DOCUMENT_BUTTON_POPUP_SHIPMENT'),
					'type' => StoreDocumentTable::TYPE_SALES_ORDERS,
				],
				[
					'text' => Loc::getMessage('CPD_CREATE_DOCUMENT_BUTTON_POPUP_MOVING'),
					'type' => StoreDocumentTable::TYPE_MOVING,
				],
				[
					'text' => Loc::getMessage('CPD_CREATE_DOCUMENT_BUTTON_POPUP_DEDUCT'),
					'type' => StoreDocumentTable::TYPE_DEDUCT,
				],
			];
		}
		else
		{
			$documents = [
				[
					'text' => Loc::getMessage('CPD_CREATE_DOCUMENT_BUTTON_POPUP_SHIPMENT'),
					'type' => StoreDocumentTable::TYPE_SALES_ORDERS,
				],
			];
		}

		foreach ($documents as $item)
		{
			$type = $item['type'];

			$can = AccessController::getCurrent()->check(
				ActionDictionary::ACTION_STORE_DOCUMENT_MODIFY,
				StoreDocument::createFromArray([
					'DOC_TYPE' => $type,
				])
			);
			if (!$can)
			{
				continue;
			}

			if ($type === StoreDocumentTable::TYPE_SALES_ORDERS)
			{
				$link = new Uri('/shop/documents/details/sales_order/0/?DOCUMENT_TYPE=W&inventoryManagementSource=product');
				$link->addParams([
					'preselectedProductId' => $productId,
					'focusedTab' => 'tab_products',
				]);
			}
			else
			{
				$link = (clone $baseLink)->addParams([
					'DOCUMENT_TYPE' => $type,
				]);
			}

			$result[] = [
				'text' => $item['text'],
				'link' => (string)$link,
			];
		}

		return $result;
	}

	protected function getProductDetailUrl(): string
	{
		$productDetailsTemplate = (string)($this->arParams['PATH_TO']['PRODUCT_DETAILS'] ?? '');

		if ($productDetailsTemplate === '')
		{
			return '';
		}

		return str_replace(
			['#IBLOCK_ID#', '#PRODUCT_ID#'],
			[$this->getIblockId(), $this->getProductId()],
			$this->arParams['PATH_TO']['PRODUCT_DETAILS']
		);
	}

	protected function getCreationPropertyUrl(): string
	{
		if (Loader::includeModule('iblock'))
		{
			return (new ComponentLinksBuilder)->getActionCreateUrl($this->getIblockId());
		}

		return '';
	}

	protected function getCreationSkuPropertyLink()
	{
		return str_replace(
			'#IBLOCK_ID#',
			$this->getForm()->getVariationIblockId(),
			$this->arParams['PATH_TO']['PROPERTY_CREATOR']
		);
	}

	private static function isNumericId($id): bool
	{
		if (is_int($id))
		{
			return true;
		}

		if (is_string($id))
		{
			$typedId = (int)$id;
			if ((string)$typedId === $id)
			{
				return true;
			}
		}

		return false;
	}

	private function parseIsSkuProduct(array $fields, BaseProduct $product): bool
	{
		$skuGridId = $this->getForm()->getVariationGridId();
		$skuFields = $fields[$skuGridId] ?? [];

		if (count($skuFields) > 1)
		{
			return true;
		}

		foreach ($skuFields as $id => $sku)
		{
			$isNumeric = static::isNumericId($id);
			if ($isNumeric && $this->getProductId() !== $id)
			{
				return true;
			}

			if (!$isNumeric && !$product->isNew())
			{
				return true;
			}

			$propertyPrefix = GridVariationForm::preparePropertyName();
			$morePhotoName = GridVariationForm::preparePropertyName(BaseForm::MORE_PHOTO);

			foreach ($sku as $name => $value)
			{
				if (
					!empty($value)
					&& $name !== $morePhotoName
					&& mb_substr($name, -7) !== '_custom'
					&& mb_strpos($name, $propertyPrefix) === 0
				)
				{
					return true;
				}
			}
		}

		return false;
	}

	private function parseSkuFields(&$fields): array
	{
		$skuGridId = $this->getForm()->getVariationGridId();

		$skuFields = $fields[$skuGridId] ?? [];
		unset($fields['ID'], $fields[$skuGridId]);

		$prefixLength = mb_strlen(BaseForm::GRID_FIELD_PREFIX);
		$propertyPrefixLength = mb_strlen(BaseForm::PROPERTY_FIELD_PREFIX);

		$oldProductName = isset($fields['NAME']) ? $this->product->getName() : null;
		foreach ($skuFields as $id => $sku)
		{
			foreach ($sku as $name => $value)
			{
				if (mb_strpos($name, BaseForm::GRID_FIELD_PREFIX) === 0)
				{
					unset($skuFields[$id][$name]);
					$originalName = mb_substr($name, $prefixLength);

					if ($originalName === 'BARCODE_custom')
					{
						$originalName = 'BARCODE';
					}
					// It is necessary that the unchanged default name of variation does not remove new one,
					// which will be setted in BaseProduct::setField
					if ($originalName === 'NAME' && $oldProductName === $value)
					{
						continue;
					}

					$propertyId = (int)mb_substr($originalName, $propertyPrefixLength);
					if ($propertyId > 0)
					{
						$propertySettings = CIBlockProperty::GetByID($propertyId)->Fetch();

						if ($propertySettings && $propertySettings['PROPERTY_TYPE'] === 'F')
						{
							$gridImages = false;
							if (isset($sku[$name . '_custom']))
							{
								$gridImages = true;
								$customValue = [];
								if (isset($sku[$name . '_custom'][$name]))
								{
									$customValue = $this->prepareFilePropertyFromGrid($sku[$name . '_custom']);
								}
								$sku[$name] = [$customValue[0]['VALUE'] ?? ''];
								$value = [$customValue[0]['VALUE'] ?? ''];
								unset($sku[$name . '_custom']);
							}

							if (!isset($fields[$name]))
							{
								$value = '';
								$skuFields[$id][$originalName] = $value;

								continue;
							}

							if (static::isNumericId($id) && !$gridImages)
							{
								$prefix = BaseForm::GRID_FIELD_PREFIX . BaseForm::PROPERTY_FIELD_PREFIX . $propertyId;
								$controlId = $prefix . '_uploader_' . $id;
								if (isset($sku[$name]) && is_array($sku[$name]))
								{
									$checkedField = \Bitrix\Main\UI\FileInputUtility::instance()->checkFiles(
										$controlId,
										$sku[$name]
									);
								}
								else
								{
									$checkedField = \Bitrix\Main\UI\FileInputUtility::instance()->checkFiles(
										$controlId,
										[$sku[$name] ?? 0]
									);
									$checkedField = reset($checkedField);
								}
								$value = $checkedField;
							}
						}
					}

					if (!isset($sku[$name]))
					{
						continue;
					}

					$skuFields[$id][$originalName] = $value;
				}
			}
		}

		foreach ($fields as $name => $field)
		{
			if (mb_strpos($name, BaseForm::GRID_FIELD_PREFIX) === 0)
			{
				unset($fields[$name]);
			}
		}

		return $skuFields;
	}

	private function parseSectionFields(&$fields)
	{
		$sectionFields = $fields['IBLOCK_SECTION'] ?? null;
		unset($fields['IBLOCK_SECTION']);

		return $sectionFields;
	}

	private function prepareSkuPictureFields(&$fields)
	{
		$pictureFieldNames = ['DETAIL_PICTURE', 'PREVIEW_PICTURE'];

		foreach ($pictureFieldNames as $name)
		{
			$customName = $name.'_custom';

			if (!empty($fields[$name.'_custom']['isFile']))
			{
				unset($fields[$name.'_custom']['isFile']);

				$fileProps = $this->prepareDetailPictureFromGrid($fields[$customName]);

				if ($fileProps)
				{
					$fields[$name] = $fileProps;
				}

				unset($fields[$customName]);
			}
		}
	}

	private function parsePropertyFields(&$fields): array
	{
		$propertyFields = [];
		$prefixLength = mb_strlen(BaseForm::PROPERTY_FIELD_PREFIX);
		$propertyCollection = $this->product->getPropertyCollection();
		$sku = $this->product->getSkuCollection()->getFirst();
		$skuPropertyCollection = null;
		if ($sku)
		{
			$skuPropertyCollection = $sku->getPropertyCollection();
		}

		$this->prepareFieldKeys($fields);

		foreach ($fields as $name => $field)
		{
			if (
				mb_strpos($name, BaseForm::PROPERTY_FIELD_PREFIX) === 0
				&& mb_substr($name, -7) !== '_custom'
			)
			{
				$index = mb_substr($name, $prefixLength);

				$isSkuProperty = false;
				$property = $propertyCollection->findById((int)$index);
				if ($property === null)
				{
					$property = $propertyCollection->findByCode($index);
				}

				if ($property === null && $skuPropertyCollection)
				{
					$isSkuProperty = true;
					$property = $skuPropertyCollection->findById((int)$index);
				}

				$propertyType = null;
				if ($property !== null)
				{
					$propertyType = $property->getPropertyType();
				}

				// grid file properties
				if (!empty($fields[$name.'_custom']['isFile']))
				{
					$field = $this->prepareFilePropertyFromGrid($fields[$name.'_custom']);
					if (empty($field))
					{
						$field = '';
					}
					unset($fields[$name.'_custom']);
				}
				// editor file properties
				elseif ($propertyType === PropertyTable::TYPE_FILE)
				{
					$descriptions = $fields[$name.'_descr'] ?? [];
					$deleted = $fields[$name.'_del'] ?? [];
					if (!$isSkuProperty)
					{
						$entityId = $this->product->getId();
						$controlId = BaseForm::PROPERTY_FIELD_PREFIX . $index . '_uploader_' . $entityId;

						$editorFiles = $this->prepareFilePropertyFromEditor($fields[$name] ?? [], $descriptions, $deleted);
						if (!$this->product->isNew())
						{
							$editorFiles = array_column($editorFiles ?? [], 'VALUE');
						}
						$checkedField = [];

						if ($this->form->isImageProperty($property->getSettings()) || $this->product->isNew())
						{
							$actualFilesCollection = $this->product->getPropertyCollection()->getValues()[$property->getId()];
							$actualFiles = [];
							foreach ($actualFilesCollection as $item)
							{
								$actualFiles[] = $item['VALUE']; // already saved images
							}

							foreach ($editorFiles as $editorFile)
							{
								if (is_numeric($editorFile))
								{
									if (in_array($editorFile, $actualFiles))
									{
										$checkedField[] = $editorFile; // already recorded image
									}
								}
								elseif (is_array($editorFile))
								{
									$checkedField[] = $editorFile; // array file ['tmp_name', 'size', ...], no need to check
								}
							}
						}
						else
						{
							$checkedField = \Bitrix\Main\UI\FileInputUtility::instance()->checkFiles(
								$controlId,
								$editorFiles
							);
						}
					}
					else
					{
						$checkedField = $fields[$name];
					}
					$field = $checkedField;
					if (empty($field))
					{
						$field = '';
					}
					unset($fields[$name.'_descr']);
				}
				elseif (isset($property) && $property->getListType() === PropertyTable::CHECKBOX)
				{
					$variant = \Bitrix\Iblock\PropertyEnumerationTable::getRow([
						'select' => ['ID', 'PROPERTY_ID', 'VALUE'],
						'filter' => [
							'=PROPERTY_ID' => $index,
						],
					]);
					if ($variant && $field === $variant['VALUE'])
					{
						$field = $variant['ID'];
					}
				}
				elseif (Loader::includeModule('currency'))
				{
					if (isset($field['AMOUNT'], $field['CURRENCY']))
					{
						if (is_array($field['AMOUNT']) && is_array($field['CURRENCY']))
						{
							$moneyValues = [];
							$valuesCount = count($field['AMOUNT']);
							for ($valueIndex = 0; $valueIndex < $valuesCount; $valueIndex++)
							{
								$moneyValues[] = IblockMoneyProperty::getUnitedValue(
									$field['AMOUNT'][$valueIndex],
									$field['CURRENCY'][$valueIndex]
								);
							}
							$field = $moneyValues;
						}
						else
						{
							$field = IblockMoneyProperty::getUnitedValue($field['AMOUNT'], $field['CURRENCY']);
						}
					}
					elseif (isset($field['PRICE']['VALUE'], $field['CURRENCY']['VALUE']))
					{
						$field = IblockMoneyProperty::getUnitedValue(
							$field['PRICE']['VALUE'],
							$field['CURRENCY']['VALUE']
						);
					}
				}

				$propertyFields[$index] = $field;

				unset($fields[$name]);
			}
		}

		return $propertyFields;
	}

	private function prepareFieldKeys(&$fields)
	{
		foreach ($fields as $name => $field)
		{
			if (mb_substr($name, -8) === '_deleted')
			{
				$explodedName = explode('_', $name);
				$propertyId = $explodedName[count($explodedName) - 2];
				$propertyName = BaseForm::PROPERTY_FIELD_PREFIX . $propertyId;
				$propertyNameDel = BaseForm::PROPERTY_FIELD_PREFIX . $propertyId . '_del';
				if (!isset($fields[$propertyName]))
				{
					$fields[$propertyName] = '';
				}
				$fields[$propertyNameDel] = $field;
			}
		}
	}

	private function prepareDescriptionFields(&$fields): void
	{
		$descriptionFieldNames = ['DETAIL_TEXT', 'PREVIEW_TEXT'];

		foreach ($descriptionFieldNames as $name)
		{
			if (isset($fields[$name]) && is_string($fields[$name]))
			{
				$fields[$name] = $this->sanitize(htmlspecialchars_decode($fields[$name]));
				$fields[$name.'_TYPE'] = 'html';
			}
		}
	}

	private function sanitize(string $html): string
	{
		static $sanitizer = null;

		if ($sanitizer === null)
		{
			$sanitizer = new \CBXSanitizer;

			$sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
			$sanitizer->ApplyDoubleEncode(false);
		}

		return $sanitizer->sanitizeHtml($html);
	}

	private function preparePictureFields(&$fields): void
	{
		$pictureFieldNames = ['DETAIL_PICTURE', 'PREVIEW_PICTURE'];

		foreach ($pictureFieldNames as $name)
		{
			if (isset($fields[$name]))
			{
				$description = $fields[$name.'_descr'] ?? null;
				$delete = $fields[$name.'_del'] ?? false;
				$fields[$name] = \CIBlock::makeFileArray($fields[$name], $delete, $description);
				unset($fields[$name.'_descr'], $fields[$name.'_del']);
			}
		}
	}

	private function prepareCatalogFields(&$fields): void
	{
		if ($this->form->isQuantityTraceSettingDisabled())
		{
			unset($fields['QUANTITY_TRACE']);
		}
	}

	private function prepareDateFields(&$fields): void
	{
		if (isset($fields['ACTIVE_FROM']) && $fields['ACTIVE_FROM'] !== '')
		{
			$date = \Bitrix\Main\Type\DateTime::createFromUserTime($fields['ACTIVE_FROM']);
			$date->disableUserTime();
			$fields['ACTIVE_FROM'] = $date;
		}

		if (isset($fields['ACTIVE_TO']) && $fields['ACTIVE_TO'] !== '')
		{
			$date = \Bitrix\Main\Type\DateTime::createFromUserTime($fields['ACTIVE_TO']);
			$date->disableUserTime();
			$fields['ACTIVE_TO'] = $date;
		}
	}

	private function checkCompatiblePictureFields(BaseIblockElementEntity $entity, array &$propertyFields): void
	{
		if (!isset($propertyFields[BaseForm::MORE_PHOTO]) || !is_array($propertyFields[BaseForm::MORE_PHOTO]))
		{
			return;
		}

		$previewPicture = $entity->getField('PREVIEW_PICTURE');
		$detailPicture = $entity->getField('DETAIL_PICTURE');

		if ($previewPicture || $detailPicture)
		{
			$previewFound = false;
			$detailFound = false;

			foreach ($propertyFields[BaseForm::MORE_PHOTO] as $key => $propertyField)
			{
				if (is_numeric($propertyField['VALUE']))
				{
					$value = (int)$propertyField['VALUE'];

					if ($value === $previewPicture)
					{
						$previewFound = true;
						unset($propertyFields[BaseForm::MORE_PHOTO][$key]);
					}

					if ($value === $detailPicture)
					{
						$detailFound = true;
						unset($propertyFields[BaseForm::MORE_PHOTO][$key]);
					}

					if ($previewFound && $detailFound)
					{
						break;
					}
				}
			}

			if ($previewPicture && !$previewFound)
			{
				$entity->setField('PREVIEW_PICTURE', \CIBlock::makeFileArray(null, true));
			}

			if ($detailPicture && !$detailFound)
			{
				$entity->setField('DETAIL_PICTURE', \CIBlock::makeFileArray(null, true));
			}
		}
	}

	/**
	 * Only for new products.
	 *
	 * @param $fields
	 * @return void
	 */
	private function prepareProductCode(&$fields): void
	{
		$productName = $fields['NAME'] ?? '';
		$productCode = $fields['CODE'] ?? '';

		$config = [
			'CHECK_UNIQUE' => 'Y',
			'CHECK_SIMILAR' => 'Y',
		];

		if ($productCode === '')
		{
			if ($productName !== '')
			{
				$fields['CODE'] = (new CIBlockElement())->createMnemonicCode(
					[
						'NAME' => $productName,
						'IBLOCK_ID' => $this->getIblockId()
					],
					$config
				);
			}
		}
		else
		{
			$fields['CODE'] = (new CIBlockElement())->getUniqueMnemonicCode(
				$productCode,
				null,
				$this->getIblockId(),
				$config
			);
		}
	}

	private function prepareDetailPictureFromGrid($propertyFields)
	{
		$fileProp = [];

		foreach ($propertyFields as $key => $value)
		{
			if (isset($propertyFields[$key.'_descr']) && (is_array($value) || is_numeric($value)))
			{
				$description = $propertyFields[$key.'_descr'] ?? null;
				$delete = $propertyFields[$key.'_del'] ?? false;
				$fileProp[] = \CIBlock::makeFilePropArray($value, $delete, $description);
			}
		}

		if (empty($fileProp))
		{
			$fileProp[] = \CIBlock::makeFilePropArray([], true);
		}

		return reset($fileProp)['VALUE'] ?? null;
	}

	private function prepareFilePropertyFromGrid($propertyFields): array
	{
		$fileProp = [];
		$counter = 0;

		foreach ($propertyFields as $key => $value)
		{
			if (
				mb_substr($key, -9) === '_deleted['
				|| mb_substr($key, -4) === '_del'
				|| isset($propertyFields[$key . '_del'])
			)
			{
				continue;
			}

			$description = $propertyFields[$key.'_descr'] ?? null;

			if (is_array($value))
			{
				$fileProp[] = \CIBlock::makeFilePropArray($value, false, $description);
			}
			elseif (is_numeric($value))
			{
				if ($this->product->isNew())
				{
					$fileArray = CIBlock::makeFileArray(
						$value,
						false,
						$description,
						['allow_file_id' => true]
					);
					$fileArray['COPY_FILE'] = 'Y';
					$fileProp['n'.$counter++] = $fileArray;
				}
				else
				{
					$fileProp[] = [
						'VALUE' => $value,
						'DESCRIPTION' => $description ?? '',
					];
				}
			}
		}

		return $fileProp;
	}

	private function prepareFilePropertyFromEditor($propertyFields, $descriptions, $deleted): ?array
	{
		if ($deleted !== null && !is_array($deleted))
		{
			$deleted = [$deleted];
			$propertyFields = [$propertyFields];
			$descriptions = [$descriptions];
		}

		if ($descriptions !== null && !is_array($descriptions))
		{
			$descriptions = [$descriptions];
			$propertyFields = [$propertyFields];
		}

		if (!is_array($propertyFields))
		{
			$propertyFields = [$propertyFields];
		}

		if ($deleted)
		{
			foreach ($deleted as $key => $value)
			{
				if ($value === 'Y')
				{
					unset($propertyFields[$key], $descriptions[$key]);
				}
				else
				{
					$propertyValueKey = array_search($value, $propertyFields, true);
					if ($propertyValueKey !== false)
					{
						unset($propertyFields[$propertyValueKey]);
					}

					$propertyDescriptionKey = array_search($value, $descriptions, true);
					if ($propertyDescriptionKey !== false)
					{
						unset($descriptions[$propertyDescriptionKey]);
					}
				}
			}
		}

		if (empty($propertyFields))
		{
			return null;
		}

		foreach ($propertyFields as $key => $value)
		{
			$propertyFields[$key.'_descr'] = $descriptions[$key] ?? '';
		}

		return $this->prepareFilePropertyFromGrid($propertyFields);
	}

	private function parsePriceFields(&$fields): array
	{
		$priceFields = [];

		foreach ($fields as $name => $value)
		{
			if (mb_strpos($name, BaseForm::PRICE_FIELD_PREFIX) === 0)
			{
				$index = str_replace(BaseForm::PRICE_FIELD_PREFIX, '', $name);
				if (!empty($index))
				{
					$priceFields[$index]['PRICE'] = $value;
				}

				unset($fields[$name]);
			}

			if (mb_strpos($name, BaseForm::CURRENCY_FIELD_PREFIX) === 0)
			{
				$index = str_replace(BaseForm::CURRENCY_FIELD_PREFIX, '', $name);
				if (!empty($index))
				{
					$priceFields[$index]['CURRENCY'] = $value;
				}

				unset($fields[$name]);
			}
		}

		return $priceFields;
	}

	private function parseMeasureRatioFields(&$fields)
	{
		$measureRatio = $fields['MEASURE_RATIO'] ?? null;
		unset($fields['MEASURE_RATIO']);

		return $measureRatio;
	}

	private function prepareFileFields(&$fields)
	{
		$files = $_FILES['data'] ?? [];
		if (!empty($files))
		{
			CFile::ConvertFilesToPost($files, $fields);
			foreach ($fields as $key => $field)
			{
				if (is_array($field) && array_key_exists('FILE', $field))
				{
					$fields[$key] = [
						$field,
					];
				}
			}
		}
	}

	public function saveAction()
	{
		$fields = $this->request->get('data') ?: [];

		$this->prepareFileFields($fields);

		if (
			empty($fields)
			|| !$this->checkModules()
			|| !$this->checkBasePermissions()
			|| !$this->checkRequiredParameters()
		)
		{
			return null;
		}

		$product = $this->getProduct();
		if ($product === null)
		{
			return null;
		}

		if ($product->isNew())
		{
			if (!$this->checkAddPermissions())
			{
				return null;
			}
		}
		elseif (!$this->checkEditPermissions())
		{
			return null;
		}

		$isSkuProduct = $this->parseIsSkuProduct($fields, $product);

		$skuFields = $this->parseSkuFields($fields);
		$propertyFields = $this->parsePropertyFields($fields);
		$this->checkCompatiblePictureFields($product, $propertyFields);
		$sectionFields = $this->parseSectionFields($fields);

		$convertedSku = null;
		if ($isSkuProduct && $product->allowConvertToSku())
		{
			$convertedSku = $this->convertSimpleProductToSku($product);
		}

		$this->prepareDescriptionFields($fields);
		$this->preparePictureFields($fields);
		$this->prepareCatalogFields($fields);
		$this->prepareDateFields($fields);

		if ($product->isNew())
		{
			$this->prepareProductCode($fields);
		}

		if (!$this->getForm()->isVisibilityEditable())
		{
			unset($fields['UF_PRODUCT_MAPPING']);
		}

		$product->setFields($fields);

		if ($sectionFields !== null)
		{
			$product->getSectionCollection()->setValues($sectionFields);
		}

		if (!empty($propertyFields))
		{
			$product->getPropertyCollection()->setValues($propertyFields);
		}

		$notifyAboutNewVariation = false;

		if (!empty($skuFields))
		{
			$skuFields = array_reverse($skuFields, true);

			foreach ($skuFields as $skuId => $skuField)
			{
				$sku = null;

				if (static::isNumericId($skuId))
				{
					$skuId = (int)$skuId;

					// probably simple sku came with product id
					if ($convertedSku)
					{
						$sku = $convertedSku;
					}
					else
					{
						/** @var \Bitrix\Catalog\v2\Sku\BaseSku $sku */
						$sku = $product->getSkuCollection()->findById($skuId);
					}
				}
				elseif ($product->isNew())
				{
					$sku =
						$product->isSimple()
							? $product->getSkuCollection()->getFirst()
							: $product->getSkuCollection()->create()
					;
				}

				if ($sku === null)
				{
					$notifyAboutNewVariation = true;

					$sku = $this->createSkuItem($product, (int)($skuField['COPY_SKU_ID'] ?? 0));
				}

				$this->fillSku($sku, $skuField);
			}
		}

		return $this->saveInternal($product, $notifyAboutNewVariation);
	}

	private function convertSimpleProductToSku(BaseProduct $product): ?BaseSku
	{
		$converter = ServiceContainer::get(Dependency::PRODUCT_CONVERTER);
		$result = $converter->convert($product, $converter::SKU_PRODUCT);
		if (!$result->isSuccess())
		{
			return null;
		}

		return $result->getData()['CONVERTED_SKU'] ?? null;
	}

	private function fillSku(BaseSku $sku, array $fields = []): void
	{
		/** @var BaseProduct $product */
		$product = $sku->getParent();
		$this->prepareSkuPictureFields($fields);
		$skuPropertyFields = $this->parsePropertyFields($fields);
		$this->checkCompatiblePictureFields($sku, $skuPropertyFields);
		$skuPriceFields = $this->parsePriceFields($fields);
		$skuMeasureRatioField = $this->parseMeasureRatioFields($fields);

		if (!$this->getForm()->isPurchasingPriceAllowed())
		{
			unset($fields['PURCHASING_PRICE']);
		}

		if (!empty($fields))
		{
			if (isset($fields['NAME']) && $fields['NAME'] === '' && $product)
			{
				$fields['NAME'] = $product->getName();
			}

			if (isset($fields['PURCHASING_PRICE']) && $fields['PURCHASING_PRICE'] === '')
			{
				$fields['PURCHASING_PRICE'] = null;
			}

			$sku->setFields($fields);

			if (isset($fields['BARCODE']))
			{
				$barcodeCollection = $sku->getBarcodeCollection();
				$barcodeCollection->remove(...$barcodeCollection);
				if (is_array($fields['BARCODE']))
				{
					foreach ($fields['BARCODE'] as $barcode)
					{
						if ($barcode === '')
						{
							continue;
						}

						$exist = false;
						/** @var Barcode $deleteItem */
						foreach ($barcodeCollection->getRemovedItems() as $deleteItem)
						{
							if ($deleteItem->getBarcode() === $barcode)
							{
								$barcodeCollection->clearRemoved($deleteItem);
								$exist = true;
								break;
							}
						}

						if (!$exist)
						{
							$barcodeItem = $barcodeCollection->create();
							$barcodeItem->setBarcode($barcode);
							$barcodeCollection->add($barcodeItem);
						}
					}
				}
				else
				{
					$barcodeCollection->setSimpleBarcodeValue($fields['BARCODE']);
				}
			}
		}

		if (!empty($skuPropertyFields))
		{
			// fix: two MORE_PHOTO fields overwrite each other (editor and grid)
			if (
				isset($propertyFields[BaseForm::MORE_PHOTO], $skuPropertyFields[BaseForm::MORE_PHOTO])
				&& $product
				&& $product->isSimple()
			)
			{
				$skuPropertyFields[BaseForm::MORE_PHOTO] = array_merge(
					$propertyFields[BaseForm::MORE_PHOTO],
					$skuPropertyFields[BaseForm::MORE_PHOTO]
				);
			}

			$sku->getPropertyCollection()->setValues($skuPropertyFields);
		}

		if (!empty($skuPriceFields) && $this->getForm()->isPricesEditable())
		{
			$sku->getPriceCollection()->setValues($skuPriceFields);
		}

		if (!empty($skuMeasureRatioField))
		{
			$sku->getMeasureRatioCollection()->setDefault($skuMeasureRatioField);
		}
	}

	private function createSkuItem(BaseProduct $product, int $copySkuId = null): BaseSku
	{
		/** @var BaseSku $sku */
		$sku = $product->getSkuCollection()
			->create()
			->setActive(true)
		;

		if ($this->copyProduct && $copySkuId > 0)
		{
			/** @var BaseSku $copySku */
			$copySku = $this->copyProduct->getSkuCollection()->findById($copySkuId);
			if ($copySku)
			{
				$fields = $copySku->getFields();
				unset(
					$fields['ID'],
					$fields['IBLOCK_ID'],
					$fields['XML_ID'],
					$fields['PREVIEW_PICTURE'],
					$fields['DETAIL_PICTURE']
				);
				$sku->setFields($fields);

				$propertyValues = [];
				foreach ($copySku->getPropertyCollection() as $property)
				{
					$propertyValues[$property->getId()] = $property->getPropertyValueCollection()->toArray();
				}

				if (!empty($propertyValues))
				{
					$sku->getPropertyCollection()->setValues($propertyValues);
				}

				$sku->getPriceCollection()->setValues($copySku->getPriceCollection()->getValues());

				$measureRatio = $copySku->getMeasureRatioCollection()->findDefault();
				if ($measureRatio)
				{
					$sku->getMeasureRatioCollection()->setDefault($measureRatio->getRatio());
				}
			}
		}

		return $sku;
	}

	private function saveInternal(BaseProduct $product, bool $notifyAboutNewVariation = false): ?array
	{
		$result = $product->save();

		if (!$result->isSuccess())
		{
			$this->errorCollection->add($result->getErrors());

			return null;
		}

		$redirect = !$this->hasProductId();
		$this->setProductId($product->getId());

		$response = [
			'ENTITY_ID' => $product->getId(),
			'ENTITY_DATA' => $this->getEntityDataForResponse(),
			'NOTIFY_ABOUT_NEW_VARIATION' => $redirect ? false : $notifyAboutNewVariation,
			'IS_SIMPLE_PRODUCT' => $product->isSimple(),
		];

		if ($redirect)
		{
			$response['REDIRECT_URL'] = $this->getProductDetailUrl();
		}

		return $response;
	}

	private function getEntityDataForResponse(): array
	{
		$entityData = $this->getForm()->getValues(false);

		foreach ($entityData as $key => $field)
		{
			if ($field instanceof \Bitrix\Main\Type\DateTime)
			{
				$entityData[$key] = $field->toString();
			}
		}

		return $entityData;
	}

	public function refreshLinkedPropertiesAction(array $sectionIds = []): ?array
	{
		if (
			$this->checkModules()
			&& $this->checkBasePermissions()
			&& $this->checkEditPermissions()
			&& $this->checkRequiredParameters()
		)
		{
			$product = $this->getProduct();

			if ($product)
			{
				$product->getSectionCollection()->setValues($sectionIds);
			}

			return [
				'ENTITY_FIELDS' => $this->getForm()->getIblockPropertiesDescriptions(),
				'ENTITY_VALUES' => $this->getForm()->getValues(),
			];
		}

		return null;
	}

	public function addPropertyAction(array $fields = []): ?array
	{
		if (
			$this->checkModules()
			&& $this->checkBasePermissions()
			&& $this->checkEditPermissions()
			&& $this->checkRequiredParameters()
		)
		{
			$fields['IBLOCK_ID'] = $this->getIblockId();
			$result = self::addProperty($fields);
			if (!$result->isSuccess())
			{
				$this->errorCollection->add($result->getErrors());

				return null;
			}

			$newID = $result->getId();
			$descriptions = $this->getForm()->getIblockPropertiesDescriptions();
			foreach ($descriptions as $property)
			{
				if ((int)$property['propertyId'] === $newID)
				{
					break;
				}
			}

			$additionalValues = [];
			if (!empty($property))
			{
				if (!empty($property['defaultValue']))
				{
					$additionalValues[$property['name']] = $property['defaultValue'];
				}

				if ($property['multiple'] === true && !is_array($additionalValues[$property['name']]))
				{
					$additionalValues[$property['name']] = [];
				}

				if ($property['type'] === 'custom' && is_array($property['data']))
				{
					$values = $this->getForm()->getValues();
					foreach (['edit', 'view'] as $keyType)
					{
						$customDataName = $property['data'][$keyType];
						if (!empty($customDataName))
						{
							$additionalValues[$customDataName] = $values[$customDataName];
						}
					}
				}
			}

			return [
				'PROPERTY_FIELDS' => $property ?? null,
				'ADDITIONAL_VALUES' => $additionalValues,
			];
		}

		return null;
	}

	public function updatePropertyAction(array $fields = []): array
	{
		$resultFields = [];
		if (
			$this->checkModules()
			&& $this->checkBasePermissions()
			&& $this->checkEditPermissions()
			&& $this->checkRequiredParameters()
		)
		{
			$id = str_replace(BaseForm::PROPERTY_FIELD_PREFIX, '', $fields['CODE']);
			$result = self::updateProperty($id, $fields);
			if (!$result->isSuccess())
			{
				$this->errorCollection->add($result->getErrors());

				return [];
			}

			$id = (int)$result->getData()['ID'];
			$descriptions = $this->getForm()->getIblockPropertiesDescriptions();
			foreach ($descriptions as $property)
			{
				if ($property['propertyId'] === $id)
				{
					$resultFields = $property;
					break;
				}
			}
		}

		return [
			'PROPERTY_FIELDS' => $resultFields,
		];
	}

	public static function updateProperty($code, array $fields = []): \Bitrix\Main\Result
	{
		$result = new \Bitrix\Main\Result();

		$property = self::getPropertyByCode($code);
		if (empty($property))
		{
			$result->addError(new \Bitrix\Main\Error('Property not found.'));

			return $result;
		}

		$result->setData($property);
		$id = $property['ID'];

		if (!CIBlockRights::UserHasRightTo($property['IBLOCK_ID'], $property['IBLOCK_ID'], 'iblock_edit'))
		{
			$result->addError(new \Bitrix\Main\Error('User has no rights to edit property.'));

			return $result;
		}

		$updateFields = [
			'IBLOCK_ID' => $property['IBLOCK_ID'],
			'NAME' => $fields['NAME'],
			'USER_TYPE_SETTINGS' => null,
			'SMART_FILTER' => null,
			'DEFAULT_VALUE' => null,
		];

		if (!empty($fields['MULTIPLE']))
		{
			$updateFields['MULTIPLE'] = ($fields['MULTIPLE'] === 'Y') ? 'Y' : 'N';
		}

		if (!empty($fields['IS_REQUIRED']))
		{
			$updateFields['IS_REQUIRED'] = ($fields['IS_REQUIRED'] === 'Y') ? 'Y' : 'N';
		}

		if (!empty($fields['IS_PUBLIC']))
		{
			$features = [
				[
					'MODULE_ID' => 'iblock',
					'FEATURE_ID' => PropertyFeature::FEATURE_ID_LIST_PAGE_SHOW,
					'IS_ENABLED' => ($fields['IS_PUBLIC'] === 'Y') ? 'Y' : 'N',
				],
				[
					'MODULE_ID' => 'iblock',
					'FEATURE_ID' => PropertyFeature::FEATURE_ID_DETAIL_PAGE_SHOW,
					'IS_ENABLED' => ($fields['IS_PUBLIC'] === 'Y') ? 'Y' : 'N',
				],
			];

			\Bitrix\Iblock\Model\PropertyFeature::updateFeatures($id, $features);
		}

		if (isset($fields['USER_TYPE']))
		{
			if (
				$fields['USER_TYPE'] === CIBlockPropertyDate::USER_TYPE
				|| $fields['USER_TYPE'] === CIBlockPropertyDateTime::USER_TYPE
				|| $fields['USER_TYPE'] === CIBlockPropertySKU::USER_TYPE
			)
			{
				$updateFields['USER_TYPE'] = $fields['USER_TYPE'];
			}
		}

		if (isset($fields['PROPERTY_TYPE']) && $fields['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST)
		{
			$updateFields['VALUES'] = is_array($fields['VALUES']) ? $fields['VALUES'] : [];
			if (empty($updateFields['VALUES']))
			{
				return $result->addError(
					new \Bitrix\Main\Error(Loc::getMessage('CPD_ERROR_EMPTY_LIST_ITEMS'))
				);
			}

			if (count($updateFields['VALUES']) === 1 && $updateFields['VALUES'][0]['VALUE'] === 'Y')
			{
				$variant = \Bitrix\Iblock\PropertyEnumerationTable::getRow([
					'select' => ['ID', 'PROPERTY_ID', 'VALUE'],
					'filter' => [
						'=PROPERTY_ID' => $id,
					],
				]);
				unset($updateFields['VALUES'][0]);
				$updateFields['VALUES'][$variant['ID']] = ['VALUE' => 'Y'];
			}
			else
			{
				$updateFields['VALUES'] = array_column($updateFields['VALUES'], null, 'ID');
			}
		}

		$iblockProperty = new \CIBlockProperty();
		$resultId = $iblockProperty->Update($id, $updateFields);
		if (!$resultId)
		{
			$result->addError(new \Bitrix\Main\Error($iblockProperty->LAST_ERROR));

			return $result;
		}

		$tableName = $property['USER_TYPE_SETTINGS']['TABLE_NAME'] ?? null;
		if ($property['USER_TYPE'] === 'directory'
			&& !empty($tableName)
			&& Loader::includeModule('highloadblock')
		)
		{
			self::updateDirectoryValues($tableName, $fields['VALUES'] ?? []);
		}

		return $result;
	}

	private static function getPropertyByCode($code): array
	{
		if (is_numeric($code))
		{
			$propertyRaw = \CIBlockProperty::GetByID($code);
			if ($property = $propertyRaw->Fetch())
			{
				return $property;
			}
		}

		$propertyRaw = \CIBlockProperty::GetList([], ['CODE' => $code]);
		if ($property = $propertyRaw->Fetch())
		{
			return $property;
		}

		return [];
	}

	private static function updateDirectoryValues($tableName, array $values = [])
	{
		$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getRow([
			'filter' => [
				'=TABLE_NAME' => $tableName,
			],
		]);

		if ($hlblock)
		{
			$files = [];
			$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
			$entityClass = $entity->getDataClass();

			$recordRaws = $entityClass::getList();
			$recordsByXmlId = [];
			foreach ($recordRaws as $record)
			{
				$recordsByXmlId[$record['UF_XML_ID']] = $record;
			}

			$activeItems = array_column($values, 'XML_ID');
			$activeItems = array_diff($activeItems, ['']);
			$itemsForRemoving = array_diff_key($recordsByXmlId, array_flip($activeItems));

			foreach ($itemsForRemoving as $removeItem)
			{
				if ((int)($removeItem['UF_FILE']) > 0)
				{
					\CFile::Delete((int)$removeItem['UF_FILE']);
				}
				if ((int)($removeItem['ID']) > 0)
				{
					$entityClass::delete((int)$removeItem['ID']);
				}
			}

			if (!empty($_FILES['FILES']))
			{
				CFile::ConvertFilesToPost($_FILES['FILES'], $files);
			}

			foreach ($values as $value)
			{
				$xmlId = $value['XML_ID'];
				$sortValue = $value['SORT'];
				if (empty($xmlId))
				{
					$addFields = [
						'UF_NAME' => $value['VALUE'],
						'UF_SORT' => $value['SORT'],
					];

					if (isset($files[$sortValue]))
					{
						$addFields['UF_FILE'] = $files[$sortValue];
					}

					if (empty($addFields['UF_NAME']) && empty($addFields['UF_FILE']))
					{
						continue;
					}

					$addFields['UF_XML_ID'] = !empty($item['XML_ID']) ? $item['XML_ID'] : md5(mt_rand());
					if (empty($addFields['UF_NAME']))
					{
						$addFields['UF_NAME'] = Loc::getMessage('CPD_NEW_LIST_ELEMENT_EMPTY_NAME');
					}
					$entityClass::add($addFields);
				}
				else
				{
					$updateFields = [];
					if (!empty($files[$sortValue]))
					{
						$updateFields['UF_FILE'] = $files[$sortValue];
						\CFile::Delete($recordsByXmlId[$xmlId]['UF_FILE']);
					}

					if (!empty($value['VALUE']) && $value['VALUE'] !== $recordsByXmlId[$xmlId]['UF_NAME'])
					{
						$updateFields['UF_NAME'] = $value['VALUE'];
					}

					if (!empty($updateFields))
					{
						$entityClass::update($recordsByXmlId[$xmlId]['ID'], $updateFields);
					}
				}
			}
		}
	}

	public static function addProperty(array $fields): AddResult
	{
		$result = new AddResult();
		$iblockProperty = new \CIBlockProperty();

		$iblockId = (int)$fields['IBLOCK_ID'];
		if ($iblockId <= 0)
		{
			return $result->addError(new \Bitrix\Main\Error('Empty iblock ID'));
		}

		if (!CIBlockRights::UserHasRightTo($iblockId, $iblockId, 'iblock_edit'))
		{
			return $result->addError(new \Bitrix\Main\Error('User has no rights to edit property.'));
		}

		if (isset($fields['USER_TYPE']) && $fields['USER_TYPE'] === 'directory')
		{
			$addDictionaryResult = self::addDictionary($fields);
			if (!$addDictionaryResult->isSuccess())
			{
				return $addDictionaryResult;
			}

			$addDictionaryData = $addDictionaryResult->getData();
			$fields['LIST_TYPE'] = 'L';
			$fields['USER_TYPE_SETTINGS'] = [
				'size' => '1',
				'width' => '0',
				'group' => 'N',
				'multiple' => 'N',
				'TABLE_NAME' => $addDictionaryData['TABLE_NAME'],
			];
		}

		if (!isset($fields['CODE']) || $fields['CODE'] === '')
		{
			$fields['CODE'] = CUtil::translit(
				$fields['NAME'],
				LANGUAGE_ID,
				[
					'replace_space' => '_',
					'replace_other' => '',
				]
			);

			if (isset($fields['CODE'][0]) && is_numeric($fields['CODE'][0]))
			{
				$fields['CODE'] = 'PROP_'.$fields['CODE'];
			}

			$fields['CODE'] .= '_'.\Bitrix\Main\Security\Random::getString(6);
			$fields['CODE'] = mb_strtoupper($fields['CODE']);
		}

		$listType = (isset($fields['LIST_TYPE']) && $fields['LIST_TYPE'] === PropertyTable::CHECKBOX)
			? PropertyTable::CHECKBOX
			: PropertyTable::LISTBOX;

		$propertyFields = [
			'IBLOCK_ID' => $fields['IBLOCK_ID'],
			'NAME' => $fields['NAME'],
			'SORT' => $fields['SORT'] ?? 500,
			'CODE' => $fields['CODE'],
			'MULTIPLE' => ($fields['MULTIPLE'] === 'Y') ? 'Y' : 'N',
			'IS_REQUIRED' => ($fields['IS_REQUIRED'] === 'Y') ? 'Y' : 'N',
			'PROPERTY_TYPE' => $fields['PROPERTY_TYPE'],
			'USER_TYPE' => $fields['USER_TYPE'] ?? '',
			'LIST_TYPE' => $listType,
			'SMART_FILTER' => null,
			'USER_TYPE_SETTINGS' => null,
			'DEFAULT_VALUE' => null,
		];

		if ($fields['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST)
		{
			if (empty($fields['VALUES']) || !is_array($fields['VALUES']))
			{
				return $result->addError(
					new \Bitrix\Main\Error(Loc::getMessage('CPD_ERROR_EMPTY_LIST_ITEMS'))
				);
			}

			$i = 1;
			foreach ($fields['VALUES'] as $value)
			{
				unset($value['ID']);
				$propertyFields['VALUES'][$i++] = $value;
			}
		}

		if (isset($fields['USER_TYPE_SETTINGS']))
		{
			$propertyFields['USER_TYPE_SETTINGS'] = $fields['USER_TYPE_SETTINGS'];
		}

		if (isset($fields['FEATURES']))
		{
			$propertyFields['FEATURES'] = $fields['FEATURES'];
		}

		if (!empty($fields['IS_PUBLIC']))
		{
			$propertyFields['FEATURES'] = $propertyFields['FEATURES'] ?? [];
			$propertyFields['FEATURES'][] = [
				'MODULE_ID' => 'iblock',
				'FEATURE_ID' => PropertyFeature::FEATURE_ID_DETAIL_PAGE_SHOW,
				'IS_ENABLED' => ($fields['IS_PUBLIC'] === 'Y') ? 'Y' : 'N',
			];
			$propertyFields['FEATURES'][] = [
				'MODULE_ID' => 'iblock',
				'FEATURE_ID' => PropertyFeature::FEATURE_ID_LIST_PAGE_SHOW,
				'IS_ENABLED' => ($fields['IS_PUBLIC'] === 'Y') ? 'Y' : 'N',
			];
		}

		$newId = (int)($iblockProperty->Add($propertyFields));
		if ($newId === 0)
		{
			return $result->addError(new Error($iblockProperty->LAST_ERROR));
		}

		$result->setId($newId);

		return $result;
	}

	private static function addDictionary(array $fields): AddResult
	{
		$result = new AddResult();
		if (!Loader::includeModule('highloadblock'))
		{
			return $result->addError(
				new \Bitrix\Main\Error('Module "highloadblock" is not installed.')
			);
		}

		$tableId = uniqid();
		$translitName = CUtil::translit(
			$fields['NAME'],
			LANGUAGE_ID,
			[
				'replace_space' => '',
				'replace_other' => '',
			]
		);
		$chunks = array_map(
			static function ($string)
			{
				return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
			},
			[
				'Property',
				$translitName,
				$tableId,
			]
		);
		$dictionaryName = implode('', $chunks);
		$tableName = CIBlockPropertyDirectory::createHighloadTableName($tableId);
		$data = [
			'NAME' => $dictionaryName,
			'TABLE_NAME' => $tableName,
		];

		$dictionaryItems = [];
		$values = $fields['VALUES'] ?? [];

		$files = [];
		if (!empty($_FILES['FILES']))
		{
			CFile::ConvertFilesToPost($_FILES['FILES'], $files);
		}

		foreach ($values as $item)
		{
			$newFields = [
				'UF_NAME' => $item['VALUE'],
				'UF_SORT' => $item['SORT'],
			];

			if (isset($files[$item['SORT']]))
			{
				$newFields['UF_FILE'] = $files[$item['SORT']];
			}

			if (empty($newFields['UF_NAME']) && empty($newFields['UF_FILE']))
			{
				continue;
			}

			$newFields['UF_XML_ID'] = !empty($item['XML_ID']) ? $item['XML_ID'] : md5(mt_rand());
			if (empty($newFields['UF_NAME']))
			{
				$newFields['UF_NAME'] = Loc::getMessage('CPD_NEW_LIST_ELEMENT_EMPTY_NAME');
			}

			$dictionaryItems[] = $newFields;
		}

		if (empty($dictionaryItems))
		{
			return $result->addError(
				new \Bitrix\Main\Error(Loc::getMessage('CPD_ERROR_EMPTY_DIRECTORY_ITEMS'))
			);
		}

		$addResult = Bitrix\Highloadblock\HighloadBlockTable::add($data);
		if (!$addResult->isSuccess())
		{
			return $addResult->addError(
				new \Bitrix\Main\Error(Loc::getMessage('CPD_ERROR_ADD_HIGHLOAD_BLOCK'))
			);
		}

		$highloadBlockID = $addResult->getId();
		$obUserField = new CUserTypeEntity();
		$columnSorting = 100;
		$highloadColumns = ['UF_NAME', 'UF_XML_ID', 'UF_SORT', 'UF_FILE', 'UF_DEF'];
		foreach ($highloadColumns as $column)
		{
			$fieldMandatory = 'N';
			switch ($column)
			{
				case 'UF_NAME':
				case 'UF_XML_ID':
					$fieldType = 'string';
					$fieldMandatory = 'Y';
					break;
				case 'UF_SORT':
					$fieldType = 'integer';
					break;
				case 'UF_FILE':
					$fieldType = 'file';
					break;
				case 'UF_DEF':
					$fieldType = 'boolean';
					break;
				default:
					$fieldType = 'string';
			}

			$userField = [
				"ENTITY_ID" => "HLBLOCK_".$highloadBlockID,
				"FIELD_NAME" => $column,
				"USER_TYPE_ID" => $fieldType,
				"XML_ID" => "",
				"SORT" => $columnSorting,
				"MULTIPLE" => "N",
				"MANDATORY" => $fieldMandatory,
				"SHOW_FILTER" => "N",
				"SHOW_IN_LIST" => "Y",
				"EDIT_IN_LIST" => "Y",
				"IS_SEARCHABLE" => "N",
				"SETTINGS" => [],
			];

			$obUserField->Add($userField);
			$columnSorting += 100;
		}

		$entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($highloadBlockID);
		$entityDataClass = $entity->getDataClass();

		foreach ($dictionaryItems as $item)
		{
			$entityDataClass::add($item);
		}

		return $result->setData([
			'TABLE_NAME' => $tableName,
		]);
	}

	public function setCardSettingAction(string $settingId, $selected): AjaxJson
	{
		if (
			!$this->checkModules()
			|| !$this->checkBasePermissions()
			|| !$this->checkRequiredParameters()
		)
		{
			return AjaxJson::createError($this->errorCollection);
		}

		$selected = $selected === 'true';
		$settings = $this->getForm()->getCardSettings();

		foreach ($settings as $item)
		{
			if ($item['id'] === $settingId && $item['action'] === 'card' && $item['checked'] !== $selected)
			{
				$config = $this->getForm()->getCardUserConfig();
				$config[$item['id']] = $selected;
				$this->getForm()->saveCardUserConfig($config);
			}
		}

		return AjaxJson::createSuccess();
	}

	public function setGridSettingAction(string $settingId, $selected, array $currentHeaders = []): AjaxJson
	{
		if (
			!$this->checkModules()
			|| !$this->checkBasePermissions()
			|| !$this->checkRequiredParameters()
		)
		{
			return AjaxJson::createError($this->errorCollection);
		}

		$gridVariationForm = null;
		$productFactory = ServiceContainer::getProductFactory($this->getIblockId());
		if ($productFactory)
		{
			$newProduct = $productFactory->createEntity();
			$emptyVariation = $newProduct->getSkuCollection()->create();
			/** @var GridVariationForm|GridServiceForm $gridVariationClassName */
			$gridVariationClassName = $this->getForm()->getVariationGridClassName();
			$gridVariationForm = new $gridVariationClassName($emptyVariation);
		}

		if (!$gridVariationForm)
		{
			$this->errorCollection[] = new Error('Grid variation form not found');

			return AjaxJson::createError($this->errorCollection);
		}

		return $gridVariationForm->setGridSettings($settingId, $selected, $currentHeaders);
	}

	/**
	 * @return null|ProductForm|ServiceForm
	 */
	private function getForm()
	{
		if ($this->form === null)
		{
			$product = $this->getProduct();
			if ($product !== null)
			{
				switch ($product->getType())
				{
					case ProductTable::TYPE_SERVICE:
						$this->form = new ServiceForm($product, $this->arParams);
						break;
					default:
						$this->form = new ProductForm($product, $this->arParams);
						break;
				}
			}
		}

		return $this->form;
	}

	private function getStoreAmount(): StoreAmount
	{
		if ($this->storeAmount === null)
		{
			$this->storeAmount = new StoreAmount($this->getProductId());
		}
		return $this->storeAmount;
	}

	private function getElementRow(int $id): ?array
	{
		return ElementTable::getRow([
			'runtime' => [
				new ORM\Fields\Relations\Reference(
					'TMP_PRODUCT',
					'Bitrix\Catalog\ProductTable',
					['=this.ID' => 'ref.ID']
				),
			],
			'select' => [
				'ID',
				'IBLOCK_ID',
				'TYPE' => 'TMP_PRODUCT.TYPE',
			],
			'filter' => [
				'=ID' => $id,
			],
		]);
	}

	private function getProductTypeName(BaseProduct $product): string
	{
		$entityName = ProductTable::getTradingEntityNameByType($product->getType());
		return ($entityName === null
			? ''
			: $this->formatProductTypeName($entityName)
		);
	}

	private function getDropdownTypes(BaseProduct $product): array
	{
		if (!Feature::isCatalogServicesEnabled())
		{
			return [];
		}
		$iblockInfo = ServiceContainer::getIblockInfo($this->getIblockId());
		if (!$iblockInfo)
		{
			return [];
		}


		$result = [];

		$dropdownTypes = [
			$iblockInfo->canHaveSku() ? ProductTable::TYPE_SKU : ProductTable::TYPE_PRODUCT,
			ProductTable::TYPE_SERVICE,
		];

		if (
			$product->isNew()
			&& !$this->isCopy()
			&& in_array($product->getType(), $dropdownTypes, true)
		)
		{
			foreach ($dropdownTypes as $dropdownType)
			{
				$title = ProductTable::getTradingEntityNameByType($dropdownType);
				if ($title !== null)
				{
					$result[$dropdownType] = $this->formatProductTypeName($title);
				}
			}
		}

		return $result;
	}

	private function getDisabledHtmlControls(): array
	{
		return [
			'Code',
			'Quote',
		];
	}

	private function formatProductTypeName(string $productTypeName): string
	{
		return mb_strtoupper(mb_substr($productTypeName, 0, 1)) . mb_substr($productTypeName, 1);
	}
}
