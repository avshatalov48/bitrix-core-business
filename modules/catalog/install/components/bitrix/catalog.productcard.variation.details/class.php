<?php

use Bitrix\Catalog\Component\BaseForm;
use Bitrix\Catalog\Component\VariationForm;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Currency\Integration\IblockMoneyProperty;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

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
	/** @var \Bitrix\Catalog\v2\Sku\BaseSku */
	private $variation;

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
			'IBLOCK_ID',
			'PRODUCT_ID',
			'VARIATION_ID',
			'PATH_TO',
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
		if ($this->checkModules() && $this->checkPermissions() && $this->checkRequiredParameters())
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
				$fields[$name] = \CAllIBlock::makeFileArray($fields[$name], $delete, $description);
				unset($fields[$name.'_descr'], $fields[$name.'_del']);
			}
		}
	}

	private function parsePropertyFields(&$fields): array
	{
		$propertyFields = [];
		$prefixLength = mb_strlen(BaseForm::PROPERTY_FIELD_PREFIX);

		foreach ($fields as $name => $field)
		{
			if (mb_strpos($name, BaseForm::PROPERTY_FIELD_PREFIX) === 0)
			{
				if (isset($fields[$name.'_descr']) || isset($fields[$name.'_del']))
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

	private function prepareFilePropertyFromEditor($propertyFields, $descriptions, $deleted): array
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

	private function prepareFilePropertyFromGrid($propertyFields): array
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
			$variation = $this->getVariation();

			if ($variation)
			{
				$propertyFields = $this->parsePropertyFields($fields);
				$this->checkCompatiblePictureFields($variation, $propertyFields);
				$priceFields = $this->parsePriceFields($fields);
				$measureRatioField = $this->parseMeasureRatioFields($fields);

				if (!empty($fields))
				{
					$this->prepareDescriptionFields($fields);
					$this->preparePictureFields($fields);
					$variation->setFields($fields);
				}

				if (!empty($propertyFields))
				{
					$variation->getPropertyCollection()->setValues($propertyFields);
				}

				if (!empty($priceFields))
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
						$response['ENTITY_DATA']['MEASURE'] = (string) $response['ENTITY_DATA']['MEASURE'];
					}

					if (isset($response['ENTITY_DATA']['VAT_ID']))
					{
						$response['ENTITY_DATA']['VAT_ID'] = (string) $response['ENTITY_DATA']['VAT_ID'];
					}

					if ($redirect)
					{
						$response['REDIRECT_URL'] = $this->getVariationDetailUrl();
					}

					return $response;
				}
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
			$this->errorCollection[] = new \Bitrix\Main\Error(sprintf(
				'Variation {%s} for product {%s} not found.',
				$this->getVariationId(), $this->getProductId()
			));
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

		$this->arResult['UI_ENTITY_FIELDS'] = $this->getForm()->getDescriptions();
		$this->arResult['UI_ENTITY_CONFIG'] = $this->getForm()->getConfig();
		$this->arResult['UI_ENTITY_DATA'] = $this->getForm()->getValues();
		$this->arResult['UI_ENTITY_CONTROLLERS'] = $this->getForm()->getControllers();
		$this->arResult['UI_CREATION_PROPERTY_URL'] = $this->getCreationPropertyUrl();
		$this->arResult['CARD_SETTINGS'] = $this->getForm()->getCardSettings();
	}

	protected function getCreationPropertyUrl(): string
	{
		$iblockInfo = ServiceContainer::getIblockInfo($this->getIblockId());

		if ($iblockInfo)
		{
			return "/shop/settings/iblock_edit_property/?lang=".LANGUAGE_ID
				."&IBLOCK_ID=".urlencode($iblockInfo->getSkuIblockId())
				."&ID=n0&publicSidePanel=Y&newProductCard=Y";
		}

		return '';
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

	private function getForm()
	{
		if ($this->form === null)
		{
			$this->form = new VariationForm($this->getVariation(), $this->arParams);
		}

		return $this->form;
	}

	public function updatePropertyAction(array $fields): array
	{
		$resultFields = [];
		if ($this->checkModules() && $this->checkPermissions() && $this->checkRequiredParameters())
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
		if ($this->checkModules() && $this->checkPermissions() && $this->checkRequiredParameters())
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