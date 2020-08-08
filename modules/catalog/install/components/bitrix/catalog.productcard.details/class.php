<?php

use Bitrix\Catalog\Component\BaseForm;
use Bitrix\Catalog\Component\GridVariationForm;
use Bitrix\Catalog\Component\ProductForm;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\IoC\Dependency;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Currency\Integration\IblockMoneyProperty;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CatalogProductDetailsComponent
	extends \CBitrixComponent
	implements Controllerable, Errorable
{
	use ErrorableImplementation;

	private $iblockId;
	private $productId;
	/** @var \Bitrix\Catalog\Component\ProductForm */
	private $form;
	/** @var \Bitrix\Catalog\v2\Product\BaseProduct */
	private $product;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->errorCollection = new ErrorCollection();
	}

	protected function showErrors()
	{
		foreach ($this->getErrors() as $error)
		{
			ShowError($error);
		}
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
			'PATH_TO',
		];
	}

	public function onPrepareComponentParams($params)
	{
		if (isset($params['PRODUCT_ID']))
		{
			$this->setProductId($params['PRODUCT_ID']);
		}

		if (isset($params['IBLOCK_ID']))
		{
			$this->setIblockId($params['IBLOCK_ID']);
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
		if ($this->checkModules() && $this->checkPermissions() && $this->checkRequiredParameters())
		{
			$product = $this->getProduct();

			if ($product)
			{
				$this->initializeExternalProductFields($product);

				$this->initializeProductFields($product);
				$this->placePageTitle($product);

				$this->errorCollection->clear();
				$this->includeComponentTemplate();
			}
		}

		$this->showErrors();
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

	protected function checkPermissions(): bool
	{
		if (!\Bitrix\Catalog\Config\State::isProductCardSliderEnabled())
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('New product card feature disabled.');

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

	protected function placePageTitle(BaseProduct $product): void
	{
		$title = $product->isNew() ? Loc::getMessage('CPD_NEW_PRODUCT_TITLE') : HtmlFilter::encode($product->getName());
		$this->getApplication()->setTitle($title);
	}

	protected function createProduct()
	{
		$product = null;
		$productFactory = ServiceContainer::getProductFactory($this->getIblockId());

		if ($productFactory)
		{
			/** @var BaseProduct $product */
			$product = $productFactory
				->createEntity()
				->setActive(true)
			;
		}

		if ($product === null)
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(sprintf(
				'Could not create product for iblock {%s}.', $this->getIblockId()
			));
		}

		$copyProductId = (int)($this->arParams['COPY_PRODUCT_ID'] ?? 0);
		if ($copyProductId > 0)
		{
			/** @var BaseProduct $copyProduct */
			$copyProduct = $this->loadProduct($copyProductId);
			if ($copyProduct)
			{
				$fields = $copyProduct->getFields();
				unset($fields['ID'], $fields['IBLOCK_ID'], $fields['PREVIEW_PICTURE'], $fields['DETAIL_PICTURE']);
				$product->setFields($fields);
				$product->getSectionCollection()->setValues(
					$copyProduct->getSectionCollection()->getValues()
				)
				;

				$propertyValues = [];
				foreach ($copyProduct->getPropertyCollection() as $property)
				{
					if ($property->isFileType())
					{
						$propertyValues[$property->getIndex()] = [];
					}
					else
					{
						$propertyValues[$property->getIndex()] = $property->getPropertyValueCollection()->toArray();
					}
				}
				$product->getPropertyCollection()->setValues($propertyValues);
			}
		}

		return $product;
	}

	protected function loadProduct($productId)
	{
		$product = null;

		$productRepository = ServiceContainer::getProductRepository($this->getIblockId());

		if ($productRepository)
		{
			$product = $productRepository->getEntityById($productId);
		}

		if ($product === null)
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(sprintf(
				'Product {%s} not found.', $productId
			));
		}

		return $product;
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
		$this->arResult['SIMPLE_PRODUCT'] = $product->isSimple();
		$this->arResult['IS_NEW_PRODUCT'] = $product->isNew();

		$this->arResult['UI_ENTITY_FIELDS'] = $this->getForm()->getDescriptions();
		$this->arResult['UI_ENTITY_CONFIG'] = $this->getForm()->getConfig();
		$this->arResult['UI_ENTITY_DATA'] = $this->getForm()->getValues();
		$this->arResult['UI_ENTITY_CONTROLLERS'] = $this->getForm()->getControllers();
		$this->arResult['UI_CREATION_PROPERTY_URL'] = $this->getCreationPropertyUrl();
		$this->arResult['UI_CREATION_SKU_PROPERTY_URL'] = $this->getCreationSkuPropertyLink();

		$this->arResult['VARIATION_GRID_ID'] = $this->getForm()->getVariationGridId();
		$this->arResult['CARD_SETTINGS'] = $this->getForm()->getCardSettings();
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
		return "/shop/settings/iblock_edit_property/?lang=".LANGUAGE_ID."&IBLOCK_ID=".urlencode($this->getIblockId())."&ID=n0&publicSidePanel=Y&newProductCard=Y";
	}

	protected function getCreationSkuPropertyLink()
	{
		return str_replace(
			'#IBLOCK_ID#',
			$this->getForm()->getVariationIblockId(),
			$this->arParams['PATH_TO']['PROPERTY_CREATOR']
		);
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

	private function parsePropertyFields(&$fields)
	{
		$propertyFields = [];
		$prefixLength = mb_strlen(BaseForm::PROPERTY_FIELD_PREFIX);

		foreach ($fields as $name => $field)
		{
			if (
				mb_strpos($name, BaseForm::PROPERTY_FIELD_PREFIX) === 0
				&& mb_substr($name, -7) !== '_custom'
			)
			{
				// grid file properties
				if (!empty($fields[$name.'_custom']['isFile']))
				{
					unset($fields[$name.'_custom']['isFile']);
					$field = $this->prepareFilePropertyFromGrid($fields[$name.'_custom']);
					unset($fields[$name.'_custom']);
				}
				// editor file properties
				elseif (isset($fields[$name.'_descr']) || isset($fields[$name.'_del']))
				{
					$descriptions = $fields[$name.'_descr'] ?? [];
					$deleted = $fields[$name.'_del'] ?? [];
					$field = $this->prepareFilePropertyFromEditor($fields[$name], $descriptions, $deleted);
					unset($fields[$name.'_descr']);
				}
				elseif (isset($field['AMOUNT'], $field['CURRENCY']) && Loader::includeModule('currency'))
				{
					$field = $field['AMOUNT'].IblockMoneyProperty::SEPARATOR.$field['CURRENCY'];
				}

				$index = mb_substr($name, $prefixLength);
				$propertyFields[$index] = $field;

				unset($fields[$name]);
			}
		}

		return $propertyFields;
	}

	private function prepareDescriptionFields(&$fields): void
	{
		$descriptionFieldNames = ['DETAIL_TEXT', 'PREVIEW_TEXT'];

		foreach ($descriptionFieldNames as $name)
		{
			if (isset($fields[$name]))
			{
				$fields[$name.'_TYPE'] = 'html';
			}
		}
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

	private function prepareFilePropertyFromGrid($propertyFields)
	{
		$fileProp = [];

		foreach ($propertyFields as $key => $value)
		{
			if (is_array($value))
			{
				$description = $propertyFields[$key.'_descr'] ?? null;
				$fileProp[] = \CIBlock::makeFilePropArray($value, false, $description);
			}
			elseif (is_numeric($value) && isset($propertyFields[$key.'_descr']))
			{
				$fileProp[] = [
					'VALUE' => $value,
					'DESCRIPTION' => $propertyFields[$key.'_descr'],
				];
			}
		}

		return $fileProp;
	}

	private function prepareFilePropertyFromEditor($propertyFields, $descriptions, $deleted)
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

		if ($deleted)
		{
			foreach ($deleted as $key => $value)
			{
				unset($propertyFields[$key], $descriptions[$key]);
			}
		}

		foreach ($propertyFields as $key => $value)
		{
			$propertyFields[$key.'_descr'] = $descriptions[$key] ?? '';
		}

		return $this->prepareFilePropertyFromGrid($propertyFields);
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

		if (empty($fields))
		{
			return null;
		}

		if ($this->checkModules() && $this->checkPermissions() && $this->checkRequiredParameters())
		{
			$product = $this->getProduct();

			if ($product)
			{
				$isSkuProduct = $this->parseIsSkuProduct($fields, $product);
				$skuFields = $this->parseSkuFields($fields);
				$propertyFields = $this->parsePropertyFields($fields);
				$this->checkCompatiblePictureFields($product, $propertyFields);
				$sectionFields = $this->parseSectionFields($fields);

				$convertedSku = null;

				if ($isSkuProduct && $product->isSimple())
				{
					/** @var \Bitrix\Catalog\v2\Converter\ProductConverter $converter */
					$converter = ServiceContainer::get(Dependency::PRODUCT_CONVERTER);
					$result = $converter->convert($product, $converter::SKU_PRODUCT);
					if ($result->isSuccess())
					{
						$convertedSku = $result->getData()['CONVERTED_SKU'] ?? null;
					}
				}

				if (!empty($fields))
				{
					$this->prepareDescriptionFields($fields);
					$this->preparePictureFields($fields);
					$product->setFields($fields);
				}

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
					if (!$isSkuProduct && $product->isNew())
					{
						$product->setType(ProductTable::TYPE_PRODUCT);
					}

					// to save new variations in exactly same grid order
					$skuFields = array_reverse($skuFields, true);

					foreach ($skuFields as $skuId => $skuField)
					{
						$sku = null;

						if (is_numeric($skuId))
						{
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
						elseif ($product->isNew() && $product->isSimple())
						{
							$sku = $product->getSkuCollection()->getIterator()[0] ?? null;
						}

						if ($sku === null)
						{
							$sku = $product->getSkuCollection()->create();
							$notifyAboutNewVariation = true;
						}

						if ($sku === null)
						{
							continue;
						}

						$this->prepareSkuPictureFields($skuField);
						$skuPropertyFields = $this->parsePropertyFields($skuField);
						$this->checkCompatiblePictureFields($sku, $skuPropertyFields);
						$skuPriceFields = $this->parsePriceFields($skuField);
						$skuMeasureRatioField = $this->parseMeasureRatioFields($skuField);

						if (!empty($skuField))
						{
							$sku->setFields($skuField);
						}

						if (!empty($skuPropertyFields))
						{
							$sku->getPropertyCollection()->setValues($skuPropertyFields);
						}

						if (!empty($skuPriceFields))
						{
							$sku->getPriceCollection()->setValues($skuPriceFields);
						}

						if (!empty($skuMeasureRatioField))
						{
							$sku->getMeasureRatioCollection()->setDefault($skuMeasureRatioField);
						}
					}
				}

				$result = $product->save();

				if ($result->isSuccess())
				{
					$redirect = !$this->hasProductId();
					$this->setProductId($product->getId());

					$response = [
						'ENTITY_ID' => $product->getId(),
						'ENTITY_DATA' => $this->getForm()->getValues(),
						'NOTIFY_ABOUT_NEW_VARIATION' => $redirect ? false : $notifyAboutNewVariation,
						'IS_SIMPLE_PRODUCT' => $product->isSimple(),
					];

					if ($redirect)
					{
						$response['REDIRECT_URL'] = $this->getProductDetailUrl();
					}

					return $response;
				}

				$this->errorCollection->add($result->getErrors());
			}
		}

		return null;
	}

	public function refreshLinkedPropertiesAction(array $sectionIds = []): ?array
	{
		if ($this->checkModules() && $this->checkPermissions() && $this->checkRequiredParameters())
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
		if ($this->checkModules() && $this->checkPermissions() && $this->checkRequiredParameters())
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
		if ($this->checkModules() && $this->checkPermissions() && $this->checkRequiredParameters())
		{
			$id = (int)str_replace(\Bitrix\Catalog\Component\ProductForm::PROPERTY_FIELD_PREFIX, '', $fields['CODE']);
			$result = self::updateProperty($id, $fields);
			if (!$result->isSuccess())
			{
				$this->errorCollection->add($result->getErrors());

				return [];
			}

			$descriptions = $this->getForm()->getIblockPropertiesDescriptions();
			foreach ($descriptions as $property)
			{
				if ((int)$property['propertyId'] === $id)
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

	public static function updateProperty($id, array $fields = []): \Bitrix\Main\Result
	{
		$result = new \Bitrix\Main\Result();

		$propertyRaw = \CIBlockProperty::GetByID($id);
		if (!($property = $propertyRaw->Fetch()))
		{
			$result->addError(new \Bitrix\Main\Error('Property not found.'));

			return $result;
		}

		if (!CIBlockRights::UserHasRightTo($property['IBLOCK_ID'], $property['IBLOCK_ID'], 'iblock_edit'))
		{
			$result->addError(new \Bitrix\Main\Error('User has no rights to edit property.'));

			return $result;
		}

		$updateFields = [
			'NAME' => $fields['NAME'],
		];

		if (!empty($fields['MULTIPLE']))
		{
			$updateFields['MULTIPLE'] = ($fields['MULTIPLE'] === 'Y') ? 'Y' : 'N';
		}

		if (!empty($fields['IS_REQUIRED']))
		{
			$updateFields['IS_REQUIRED'] = ($fields['IS_REQUIRED'] === 'Y') ? 'Y' : 'N';
		}

		if ($fields['USER_TYPE'] === 'Date' || $fields['USER_TYPE'] === 'DateTime')
		{
			$updateFields['USER_TYPE'] = $fields['USER_TYPE'];
		}

		if ($fields['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST)
		{
			$updateFields['VALUES'] = is_array($fields['VALUES']) ? $fields['VALUES'] : [];
			if (empty($updateFields['VALUES']))
			{
				return $result->addError(
					new \Bitrix\Main\Error(Loc::getMessage('CPD_ERROR_EMPTY_LIST_ITEMS'))
				);
			}
			$updateFields['VALUES'] = array_column($updateFields['VALUES'], null, 'ID');
		}

		$iblockProperty = new \CIBlockProperty();
		$resultId = $iblockProperty->Update($id, $updateFields);
		if (!$resultId)
		{
			$result->addError(new \Bitrix\Main\Error($iblockProperty->LAST_ERROR));

			return $result;
		}

		$tableName = $property['USER_TYPE_SETTINGS']['TABLE_NAME'];
		if ($property['USER_TYPE'] === 'directory'
			&& !empty($tableName)
			&& Loader::includeModule('highloadblock')
		)
		{
			self::updateDirectoryValues($tableName, $fields['VALUES'] ?? []);
		}

		return $result;
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
						$addFields['UF_NAME'] = $addFields['UF_XML_ID'];
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

		if ($fields['USER_TYPE'] === 'directory')
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

		$propertyFields = [
			'IBLOCK_ID' => $fields['IBLOCK_ID'],
			'NAME' => $fields['NAME'],
			'SORT' => $fields['SORT'] ?? 500,
			'CODE' => $fields['CODE'] ?? '',
			'MULTIPLE' => ($fields['MULTIPLE'] === 'Y') ? 'Y' : 'N',
			'IS_REQUIRED' => ($fields['IS_REQUIRED'] === 'Y') ? 'Y' : 'N',
			'PROPERTY_TYPE' => $fields['PROPERTY_TYPE'],
			'USER_TYPE' => $fields['USER_TYPE'] ?? '',
			'LIST_TYPE' => ($fields['LIST_TYPE'] === PropertyTable::CHECKBOX) ? PropertyTable::CHECKBOX : PropertyTable::LISTBOX,
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
			static function ($string) {
				return mb_strtoupper(mb_substr($string, 0, 1)).mb_substr($string, 1);
			},
			['Property', $translitName, $tableId]
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
				$newFields['UF_NAME'] = $newFields['UF_XML_ID'];
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

			$arUserField = [
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

			$obUserField->Add($arUserField);
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

	public function setCardSettingAction(string $settingId, $selected): Bitrix\Main\Engine\Response\AjaxJson
	{
		if (!$this->checkModules() || !$this->checkPermissions() || !$this->checkRequiredParameters())
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
		if (!$this->checkModules() || !$this->checkPermissions() || !$this->checkRequiredParameters())
		{
			return Bitrix\Main\Engine\Response\AjaxJson::createError($this->errorCollection);
		}

		$headers = [];

		if ($settingId === 'MEASUREMENTS')
		{
			$headers = ['WEIGHT', 'WIDTH', 'LENGTH', 'HEIGHT'];
		}
		elseif ($settingId === 'PURCHASING_PRICE_FIELD')
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

	private function getForm()
	{
		if ($this->form === null)
		{
			$this->form = new ProductForm($this->getProduct(), $this->arParams);
		}

		return $this->form;
	}
}