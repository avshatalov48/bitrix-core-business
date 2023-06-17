<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Component\BaseForm;
use Bitrix\Catalog\Component\GridVariationForm;
use Bitrix\Catalog\Component\UseStore;
use Bitrix\Catalog\Component\VariationForm;
use Bitrix\Catalog\Component\StoreAmount;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Currency\Integration\IblockMoneyProperty;
use Bitrix\Iblock\Component\Property\ComponentLinksBuilder;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Toolbar\Facade\Toolbar;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogProductVariationDetailsComponent
	extends \CBitrixComponent
	implements Controllerable, Errorable
{
	use ErrorableImplementation;

	private $iblockId;
	private $productId;
	private $variationId;
	/** @var \Bitrix\Catalog\Component\VariationForm */
	private $form;
	/** @var \Bitrix\Catalog\Component\StoreAmount */
	private $storeAmount;
	/** @var \Bitrix\Catalog\v2\Sku\BaseSku */
	private $variation;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
	}

	protected function showErrors()
	{
		Toolbar::deleteFavoriteStar();
		foreach ($this->getErrors() as $error)
		{
			$this->includeErrorComponent($error->getMessage());
		}
	}

	protected function includeErrorComponent(string $errorMessage, string $description = null): void
	{
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			"bitrix:ui.info.error",
			"",
			[
				'TITLE' => $errorMessage,
				'DESCRIPTION' => $description,
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
			'IBLOCK_ID',
			'PRODUCT_ID',
			'VARIATION_ID',
			'PATH_TO',
			'BUILDER_CONTEXT',
			'SCOPE',
		];
	}

	public function onPrepareComponentParams($params)
	{
		if (isset($params['IBLOCK_ID']))
		{
			$this->setIblockId($params['IBLOCK_ID']);
		}

		if (isset($params['PRODUCT_ID']))
		{
			$this->setProductId($params['PRODUCT_ID']);
		}

		if (isset($params['VARIATION_ID']))
		{
			$this->setVariationId($params['VARIATION_ID']);
		}

		return parent::onPrepareComponentParams($params);
	}

	public function executeComponent()
	{
		if ($this->checkModules() && $this->checkBasePermissions() && $this->checkRequiredParameters())
		{
			$variation = $this->getVariation();

			if ($variation)
			{
				$this->initializeVariationFields($variation);
				$this->placePageTitle($variation);

				$this->errorCollection->clear();
				$this->includeComponentTemplate();
			}
		}

		$this->showErrors();
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

	private function prepareFileFields(&$fields): void
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
						$fields[$key],
					];
				}
			}
		}
	}

	private function parsePropertyFields(&$fields): array
	{
		$propertyFields = [];
		$prefixLength = mb_strlen(BaseForm::PROPERTY_FIELD_PREFIX);
		$propertyCollection = $this->variation->getPropertyCollection();

		$this->prepareFieldKeys($fields);

		foreach ($fields as $name => $field)
		{
			$index = mb_substr($name, $prefixLength);

			$property = $propertyCollection->findById((int)$index);
			if ($property === null)
			{
				$property = $propertyCollection->findByCode($index);
			}

			if (
				mb_strpos($name, BaseForm::PROPERTY_FIELD_PREFIX) === 0
				&& mb_substr($name, -7) !== '_custom'
			)
			{
				$index = mb_substr($name, $prefixLength);

				$property = $propertyCollection->findById((int)$index);
				if ($property === null)
				{
					$property = $propertyCollection->findByCode($index);
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
					$entityId = $this->variation->getId();
					$controlId = BaseForm::PROPERTY_FIELD_PREFIX . $index . '_uploader_' . $entityId;

					$editorFiles = $this->prepareFilePropertyFromEditor($fields[$name] ?? [], $descriptions, $deleted);
					$editorFiles = array_column($editorFiles ?? [], 'VALUE');
					$checkedField = [];

					if ($this->form->isImageProperty($property->getSettings()))
					{
						$actualFilesCollection = $this->variation->getPropertyCollection()->getValues()[$property->getId()];
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
						$field = IblockMoneyProperty::getUnitedValue($field['AMOUNT'], $field['CURRENCY']);
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

	private function prepareFilePropertyFromGrid($propertyFields): array
	{
		$fileProp = [];

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
				$fileProp[] = [
					'VALUE' => $value,
					'DESCRIPTION' => $description ?? '',
				];
			}
		}

		return $fileProp;
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

	private function parseGridFields(&$fields)
	{
		$skuGridId = $this->getForm()->getVariationGridId();

		$skuField = $fields[$skuGridId][$this->variationId] ?? [];
		unset($fields['ID'], $fields[$skuGridId]);

		$prefixLength = mb_strlen(BaseForm::GRID_FIELD_PREFIX);
		$propertyPrefixLength = mb_strlen(BaseForm::PROPERTY_FIELD_PREFIX);

		foreach ($skuField as $name => $value)
		{
			if (mb_strpos($name, BaseForm::GRID_FIELD_PREFIX) === 0)
			{
				$originalName = mb_substr($name, $prefixLength);

				$propertyId = mb_substr($originalName, $propertyPrefixLength);
				if (is_numeric($propertyId))
				{
					$propertySettings = $this->variation->getPropertyCollection()->findById($propertyId)->getSettings();

					if ($propertySettings['PROPERTY_TYPE'] === 'F')
					{
						if ($propertySettings['MULTIPLE'] === 'Y')
						{
							if (isset($skuField[$name . '_custom']))
							{
								unset($skuField[$name . '_custom']);
							}
						}
						elseif (!isset($fields[$name]))
						{
							$value = '';
							$skuField[$originalName] = $value;

							continue;
						}

						if (isset($fields[$name . '_del']))
						{
							if (!is_array($fields[$name]))
							{
								$fields[$name] = [$fields[$name]];
							}

							if (!is_array($fields[$name.'_del']))
							{
								$fields[$name.'_del'] = [$fields[$name.'_del']];
							}
							$value = array_diff($fields[$name], $fields[$name.'_del']);
							if (empty($value))
							{
								$value = '';
							}
						}
						else
						{
							$value = $fields[$name];
						}
					}
				}

				if (!isset($skuField[$name]))
				{
					continue;
				}

				unset($skuField[$name]);
				$skuField[$originalName] = $value;
			}
		}

		if (!$this->getForm()->isPricesEditable())
		{
			unset($fields['VAT_ID'], $fields['VAT_INCLUDED'], $fields['PURCHASING_PRICE']);
		}

		if (State::isUsedInventoryManagement() || !$this->getForm()->isPurchasingPriceAllowed())
		{
			unset($fields['PURCHASING_PRICE']);
		}

		foreach ($fields as $name => $field)
		{
			if (mb_strpos($name, BaseForm::GRID_FIELD_PREFIX) === 0)
			{
				unset($fields[$name]);
			}
		}

		return $fields = array_merge($fields, $skuField);
	}

	private function parsePriceFields(&$fields)
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

	public function saveAction()
	{
		$fields = $this->request->get('data') ?: [];

		$this->prepareFileFields($fields);

		if (empty($fields))
		{
			return null;
		}

		if (
			$this->checkModules()
			&& $this->checkBasePermissions()
			&& $this->checkRequiredParameters()
			&& $this->checkProductEditPermissions()
		)
		{
			$variation = $this->getVariation();

			if ($variation)
			{
				$this->parseGridFields($fields);
				$propertyFields = $this->parsePropertyFields($fields);
				$this->checkCompatiblePictureFields($variation, $propertyFields);
				$priceFields = $this->parsePriceFields($fields);
				$measureRatioField = $this->parseMeasureRatioFields($fields);

				if (!empty($fields))
				{
					$this->prepareDescriptionFields($fields);
					$this->preparePictureFields($fields);
					$this->prepareCatalogFields($fields);
					$this->prepareDateFields($fields);

					if (isset($fields['PURCHASING_PRICE']) && $fields['PURCHASING_PRICE'] === '')
					{
						$fields['PURCHASING_PRICE'] = null;
					}

					$variation->setFields($fields);

					if (isset($fields['BARCODE']))
					{
						$variation
							->getBarcodeCollection()
							->setSimpleBarcodeValue($fields['BARCODE'])
						;
					}
				}

				if (!empty($propertyFields))
				{
					$variation->getPropertyCollection()->setValues($propertyFields);
				}

				if (!empty($priceFields) && $this->getForm()->isPricesEditable())
				{
					$variation->getPriceCollection()->setValues($priceFields);
				}

				if (!empty($measureRatioField))
				{
					$variation->getMeasureRatioCollection()->setDefault($measureRatioField);
				}

				$result = $variation->save();

				if ($result->isSuccess())
				{
					$redirect = !$this->hasVariationId();
					$this->setVariationId($variation->getId());

					$response = [
						'ENTITY_ID' => $variation->getId(),
						'ENTITY_DATA' => $this->getForm()->getValues(false),
						'IS_SIMPLE_PRODUCT' => $variation->isSimple(),
					];

					if (isset($response['ENTITY_DATA']['MEASURE']))
					{
						$response['ENTITY_DATA']['MEASURE'] = (string)$response['ENTITY_DATA']['MEASURE'];
					}

					if (isset($response['ENTITY_DATA']['VAT_ID']))
					{
						$response['ENTITY_DATA']['VAT_ID'] = (string)$response['ENTITY_DATA']['VAT_ID'];
					}

					if ($redirect)
					{
						$response['REDIRECT_URL'] = $this->getVariationDetailUrl();
					}

					return $response;
				}

				$this->errorCollection->add($result->getErrors());
			}
		}

		return null;
	}

	protected function checkModules()
	{
		if (!Loader::includeModule('catalog'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Module "catalog" is not installed.');

			return false;
		}

		return true;
	}

	protected function checkBasePermissions(): bool
	{
		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('CPVD_ACCESS_DENIED_TITLE'));

			return false;
		}

		if (!$this->getForm()->isCardAllowed())
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('New product card feature disabled.');

			return false;
		}

		return true;
	}

	protected function checkProductEditPermissions(): bool
	{
		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_EDIT))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('CPD_ACCESS_DENIED_ERROR_TITLE'));

			return false;
		}

		return true;
	}

	protected function checkRequiredParameters()
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
		$this->productId = $productId;

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

	protected function setVariationId(int $variationId): self
	{
		$this->variationId = $variationId;

		return $this;
	}

	protected function getVariationId(): int
	{
		return $this->variationId;
	}

	private function hasVariationId(): bool
	{
		return $this->getVariationId() > 0;
	}

	protected function placePageTitle(BaseSku $variation): void
	{
		$title = $variation->isNew() ? Loc::getMessage('CPVD_NEW_VARIATION_TITLE') : Bitrix\Main\Text\HtmlFilter::encode($variation->getName());
		$this->getApplication()->setTitle($title);
	}

	protected function loadProduct()
	{
		$productRepository = ServiceContainer::getProductRepository($this->getIblockId());

		if ($productRepository)
		{
			return $productRepository->getEntityById($this->getProductId());
		}

		return null;
	}

	protected function getVariation()
	{
		if ($this->variation === null)
		{
			if ($this->hasVariationId())
			{
				$this->variation = $this->loadVariation();
			}
			else
			{
				$this->variation = $this->createVariation();
			}
		}

		return $this->variation;
	}

	protected function loadVariation()
	{
		$variation = null;

		$skuRepository = ServiceContainer::getSkuRepository($this->getIblockId());

		if ($skuRepository)
		{
			$variation = $skuRepository->getEntityById($this->getVariationId());
		}

		if ($variation === null)
		{
			Toolbar::deleteFavoriteStar();

			global $APPLICATION;
			$APPLICATION->IncludeComponent(
				"bitrix:ui.info.error",
				'',
				[
					'TITLE' => Loc::getMessage('CPVD_NOT_FOUND_ERROR_TITLE'),
					'DESCRIPTION' => '',
				]
			);
		}

		return $variation;
	}

	protected function createVariation()
	{
		$variation = null;

		// $skuFactory = ServiceContainer::getSkuFactory($this->getIblockId());
		//
		// if ($skuFactory)
		// {
		// 	$variation = $skuFactory
		// 		->createEntity()
		// 		->setActive(true)
		// 	;
		//
		// 	if ($this->hasProductId())
		// 	{
		// 		/** @var \Bitrix\Catalog\v2\Product\BaseProduct $product */
		// 		$product = $this->loadProduct();
		//
		// 		if (!$product)
		// 		{
		// 			$this->errorCollection[] = new \Bitrix\Main\Error(sprintf(
		// 				'Product {%s} not found.',
		// 				$this->getProductId()
		// 			));
		//
		// 			return null;
		// 		}
		//
		// 		$variation->setName($product->getName());
		// 		$product->getSkuCollection()->add($variation);
		// 	}
		// }

		if ($variation === null)
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(sprintf(
				'Could not create variation for product {%s} with iblock {%s}.',
				$this->getProductId(), $this->getIblockId()
			));
		}

		return $variation;
	}

	protected function initializeVariationFields(BaseSku $variation)
	{
		$this->arResult['VARIATION_ENTITY'] = $variation;
		$this->arResult['VARIATION_FIELDS'] = $variation->getFields();
		$this->arResult['IS_NEW_PRODUCT'] = $variation->isNew();

		$this->arResult['UI_ENTITY_FIELDS'] = $this->getForm()->getDescriptions();
		$this->arResult['UI_ENTITY_CONFIG'] = $this->getForm()->getConfig();
		$this->arResult['UI_ENTITY_DATA'] = $this->getForm()->getValues();
		$this->arResult['UI_ENTITY_CONTROLLERS'] = $this->getForm()->getControllers();
		$this->arResult['UI_CREATION_PROPERTY_URL'] = $this->getCreationPropertyUrl();
		$this->arResult['UI_ENTITY_READ_ONLY'] = $this->getForm()->isReadOnly();
		$this->arResult['UI_ENTITY_CARD_SETTINGS_EDITABLE'] = $this->getForm()->isCardSettingsEditable();
		$this->arResult['UI_ENTITY_ENABLE_SETTINGS_FOR_ALL'] = $this->getForm()->isEnabledSetSettingsForAll();
		$this->arResult['UI_CREATION_SKU_PROPERTY_URL'] = $this->getCreationSkuPropertyLink();
		$this->arResult['VARIATION_GRID_ID'] = $this->getForm()->getVariationGridId();
		$this->arResult['STORE_AMOUNT_GRID_ID'] = $this->getStoreAmount()->getStoreAmountGridId();
		$this->arResult['CARD_SETTINGS'] = $this->getForm()->getCardSettings();
		$this->arResult['HIDDEN_FIELDS'] = $this->getForm()->getHiddenFields();
		$this->arResult['IS_WITH_ORDERS_MODE'] = Loader::includeModule('crm') && \CCrmSaleHelper::isWithOrdersMode();
		$this->arResult['IS_INVENTORY_MANAGEMENT_USED'] = UseStore::isUsed();
	}

	public function setCardSettingAction(string $settingId, $selected): Bitrix\Main\Engine\Response\AjaxJson
	{
		if (
			!$this->checkModules()
			|| !$this->checkBasePermissions()
			|| !$this->checkRequiredParameters()
		)
		{
			return Bitrix\Main\Engine\Response\AjaxJson::createError($this->errorCollection);
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

		return Bitrix\Main\Engine\Response\AjaxJson::createSuccess();
	}

	public function setGridSettingAction(string $settingId, $selected, array $currentHeaders = []): Bitrix\Main\Engine\Response\AjaxJson
	{
		if (
			!$this->checkModules()
			|| !$this->checkBasePermissions()
			|| !$this->checkRequiredParameters()
		)
		{
			return Bitrix\Main\Engine\Response\AjaxJson::createError($this->errorCollection);
		}

		$headers = [];

		if ($settingId === 'MEASUREMENTS')
		{
			$headers = ['WEIGHT', 'WIDTH', 'LENGTH', 'HEIGHT'];
		}
		elseif ($settingId === 'PURCHASING_PRICE_FIELD' && $this->getForm()->isPurchasingPriceAllowed())
		{
			$headers = ['PURCHASING_PRICE_FIELD'];
		}
		elseif ($settingId === 'MEASURE_RATIO')
		{
			$headers = ['MEASURE_RATIO'];
		}
		elseif ($settingId === 'VAT_INCLUDED')
		{
			$headers = ['VAT_INCLUDED', 'VAT_ID'];
		}

		if (!empty($headers))
		{
			$gridVariationForm = null;
			$productFactory = ServiceContainer::getProductFactory($this->getIblockId());
			if ($productFactory)
			{
				$newProduct = $productFactory->createEntity();
				$emptyVariation = $newProduct->getSkuCollection()->create();
				$gridVariationForm = new GridVariationForm($emptyVariation);
			}

			if (!$gridVariationForm)
			{
				return Bitrix\Main\Engine\Response\AjaxJson::createError($this->errorCollection);
			}

			foreach ($headers as &$header)
			{
				$header = $gridVariationForm::formatFieldName($header);
			}

			unset($header);

			$options = new \Bitrix\Main\Grid\Options($gridVariationForm->getVariationGridId());
			$allUsedColumns = $options->getUsedColumns();

			if (empty($allUsedColumns))
			{
				$allUsedColumns = $currentHeaders;
			}

			if ($selected === 'true')
			{
				// sort new columns by default grid column sort
				$defaultHeaders = array_column($gridVariationForm->getGridHeaders(), 'id');
				$currentHeadersInDefaultPosition = array_values(
					array_intersect($defaultHeaders, array_merge($allUsedColumns, $headers))
				);
				$headers = array_values(array_intersect($defaultHeaders, $headers));

				foreach ($headers as $header)
				{
					$insertPosition = array_search($header, $currentHeadersInDefaultPosition, true);
					array_splice($allUsedColumns, $insertPosition, 0, $header);
				}
			}
			else
			{
				$allUsedColumns = array_diff($allUsedColumns, $headers);
			}

			$options->setColumns(implode(',', $allUsedColumns));
			$options->save();
		}

		return Bitrix\Main\Engine\Response\AjaxJson::createSuccess();
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
			if (is_numeric($id) && $this->getProductId() !== $id)
			{
				return true;
			}

			if (!is_numeric($id) && !$product->isNew())
			{
				return true;
			}

			$propertyPrefix = GridVariationForm::preparePropertyName();
			$morePhotoName = GridVariationForm::preparePropertyName(BaseForm::MORE_PHOTO);
			$morePhotoNameCustom = "{$morePhotoName}_custom";

			foreach ($sku as $name => $value)
			{
				if (
					$name !== $morePhotoName
					&& $name !== $morePhotoNameCustom
					&& mb_strpos($name, $propertyPrefix) === 0)
				{
					return true;
				}
			}
		}

		return false;
	}

	private function parseSkuFields(&$fields)
	{
		$skuGridId = $this->getForm()->getVariationGridId();

		$skuFields = $fields[$skuGridId] ?? [];
		unset($fields['ID'], $fields[$skuGridId]);

		foreach ($fields as $name => $field)
		{
			if (mb_strpos($name, BaseForm::GRID_FIELD_PREFIX) === 0)
			{
				unset($fields[$name]);
			}
		}

		$prefixLength = mb_strlen(BaseForm::GRID_FIELD_PREFIX);

		foreach ($skuFields as $id => $sku)
		{
			foreach ($sku as $name => $value)
			{
				if (mb_strpos($name, BaseForm::GRID_FIELD_PREFIX) === 0)
				{
					$originalName = mb_substr($name, $prefixLength);
					$skuFields[$id][$originalName] = $value;
					unset($skuFields[$id][$name]);
				}
			}
		}

		return $skuFields;
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

	protected function getCreationPropertyUrl(): string
	{
		$iblockInfo = ServiceContainer::getIblockInfo($this->getIblockId());

		if ($iblockInfo && Loader::includeModule('iblock'))
		{
			return (new ComponentLinksBuilder)->getActionCreateUrl($iblockInfo->getSkuIblockId());
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

	protected function getVariationDetailUrl(): string
	{
		$variationTemplate = (string)($this->arParams['PATH_TO']['VARIATION_DETAILS'] ?? '');

		if ($variationTemplate === '')
		{
			return '';
		}

		return str_replace(
			['#IBLOCK_ID#', '#PRODUCT_ID#', '#VARIATION_ID#'],
			[$this->getIblockId(), $this->getProductId(), $this->getVariationId()],
			$variationTemplate
		);
	}

	private function getForm(): VariationForm
	{
		if ($this->form === null)
		{
			$this->form = new VariationForm($this->getVariation(), $this->arParams);
		}

		return $this->form;
	}

	private function getStoreAmount(): StoreAmount
	{
		if ($this->storeAmount === null)
		{
			$this->storeAmount = new \Bitrix\Catalog\Component\StoreAmount($this->getVariationId());
		}
		return $this->storeAmount;
	}

	public function updatePropertyAction(array $fields): array
	{
		$resultFields = [];
		if (
			$this->checkModules()
			&& $this->checkBasePermissions()
			&& $this->checkRequiredParameters()
			&& $this->checkProductEditPermissions()
		)
		{
			CBitrixComponent::includeComponentClass('bitrix:catalog.productcard.details');
			$id = str_replace(VariationForm::PROPERTY_FIELD_PREFIX, '', $fields['CODE']);
			$result = \CatalogProductDetailsComponent::updateProperty($id, $fields);
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

	public function addPropertyAction(array $fields = []): array
	{
		if (
			$this->checkModules()
			&& $this->checkBasePermissions()
			&& $this->checkRequiredParameters()
			&& $this->checkProductEditPermissions()
		)
		{
			$fields['IBLOCK_ID'] = $this->getForm()->getVariationIblockId();
			CBitrixComponent::includeComponentClass("bitrix:catalog.productcard.details");
			$result = \CatalogProductDetailsComponent::addProperty($fields);
			if (!$result->isSuccess())
			{
				$this->errorCollection->add($result->getErrors());

				return [];
			}

			$newId = $result->getId();

			$code = null;
			$additionalValues = [];
			$productFactory = ServiceContainer::getProductFactory($this->getIblockId());
			if ($productFactory)
			{
				$newProduct = $productFactory->createEntity();
				$emptyVariation = $newProduct->getSkuCollection()->create();
				$form = new VariationForm($emptyVariation);
				$descriptions = $form->getIblockPropertiesDescriptions();
				foreach ($descriptions as $property)
				{
					if ((int)$property['propertyId'] === $newId)
					{
						$code = $property['name'];
						break;
					}
				}

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
			}

			return [
				'PROPERTY_ID' => $newId,
				'PROPERTY_GRID_CODE' => $code,
				'PROPERTY_FIELDS' => $property ?? null,
				'ADDITIONAL_VALUES' => $additionalValues,
			];
		}

		return [];
	}
}
