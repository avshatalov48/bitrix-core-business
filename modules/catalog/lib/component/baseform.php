<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\Url;
use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\BaseIblockElementEntity;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Property\HasPropertyCollection;
use Bitrix\Catalog\v2\Property\Property;
use Bitrix\Crm;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\Url\AdminPage\BuilderManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\FileInputUtility;

abstract class BaseForm
{
	public const GRID_FIELD_PREFIX = 'SKU_GRID_';
	public const PROPERTY_FIELD_PREFIX = 'PROPERTY_';
	public const PRICE_FIELD_PREFIX = 'CATALOG_GROUP_';
	public const CURRENCY_FIELD_PREFIX = 'CATALOG_CURRENCY_';
	public const MORE_PHOTO = 'MORE_PHOTO';

	private const USER_TYPE_METHOD = 'GetUIEntityEditorProperty';
	private const USER_TYPE_GET_VIEW_METHOD = 'GetUIEntityEditorPropertyViewHtml';
	private const USER_TYPE_GET_EDIT_METHOD = 'GetUIEntityEditorPropertyEditHtml';
	private const USER_TYPE_FORMAT_VALUE_METHOD = 'getFormattedValue';

	protected const CONTROL_NAME_WITH_CODE = 'name-code';
	protected const CONTROL_IBLOCK_SECTION = 'iblock_section';

	public const SCOPE_SHOP = 'shop';
	public const SCOPE_CRM = 'crm';

	/** @var \Bitrix\Catalog\v2\BaseIblockElementEntity */
	protected $entity;
	/** @var array */
	protected $params;

	/** @var array|null */
	protected $descriptions;
	/** @var array|null */
	protected $propertyDescriptions;

	/** @var null|Url\ShopBuilder|Url\InventoryBuilder|Crm\Product\Url\ProductBuilder */
	protected $urlBuilder;

	protected $crmIncluded;

	public function __construct(BaseIblockElementEntity $entity, array $params = [])
	{
		$this->crmIncluded = Loader::includeModule('crm');

		$this->entity = $entity;
		$this->params = $this->getPreparedParams($params);

		$this->initUrlBuilder();
	}

	protected function getPreparedParams(array $params): array
	{
		$allowedBuilderTypes = [
			Url\ShopBuilder::TYPE_ID,
			Url\InventoryBuilder::TYPE_ID,
		];
		$allowedScopeList = [
			self::SCOPE_SHOP,
		];
		if ($this->crmIncluded)
		{
			$allowedBuilderTypes[] = Crm\Product\Url\ProductBuilder::TYPE_ID;
			$allowedScopeList[] = self::SCOPE_CRM;
		}

		$params['BUILDER_CONTEXT'] = (string)($params['BUILDER_CONTEXT'] ?? '');
		if (!in_array($params['BUILDER_CONTEXT'], $allowedBuilderTypes, true))
		{
			$params['BUILDER_CONTEXT'] = Url\ShopBuilder::TYPE_ID;
		}

		$params['SCOPE'] = (string)($params['SCOPE'] ?? '');
		if ($params['SCOPE'] === '')
		{
			$params['SCOPE'] = $this->getScopeByUrl();
		}

		if (!in_array($params['SCOPE'], $allowedScopeList))
		{
			$params['SCOPE'] = self::SCOPE_SHOP;
		}

		return $params;
	}

	protected function getScopeByUrl(): string
	{
		$result = '';

		$currentPath = Context::getCurrent()->getRequest()->getRequestUri();
		if (strncmp($currentPath, '/shop/', 6) === 0)
		{
			$result = self::SCOPE_SHOP;
		}
		elseif ($this->crmIncluded)
		{
			if (strncmp($currentPath, '/crm/', 5) === 0)
			{
				$result = self::SCOPE_CRM;
			}
		}

		return $result;
	}

	protected function initUrlBuilder(): void
	{
		$this->urlBuilder = BuilderManager::getInstance()->getBuilder($this->params['BUILDER_CONTEXT']);
	}

	public function isCardAllowed(): bool
	{
		switch ($this->params['SCOPE'])
		{
			case self::SCOPE_SHOP:
				$result = \Bitrix\Catalog\Config\State::isProductCardSliderEnabled();
				break;
			case self::SCOPE_CRM:
				$result = false;
				if ($this->crmIncluded)
				{
					$result = Crm\Settings\LayoutSettings::getCurrent()->isFullCatalogEnabled();
				}
				break;
			default:
				$result = false;
				break;
		}

		return $result;
	}

	protected function prepareFieldName(string $name): string
	{
		return $name;
	}

	public function getControllers(): array
	{
		return [
			[
				'name' => 'FIELD_CONFIGURATOR_CONTROLLER',
				'type' => 'field_configurator',
				'config' => [],
			],
			[
				'name' => 'GOOGLE_MAP_CONTROLLER',
				'type' => 'google_map',
				'config' => [],
			],
			[
				'name' => 'EMPLOYEE_CONTROLLER',
				'type' => 'employee',
				'config' => [],
			],
			[
				'name' => 'CRM_CONTROLLER',
				'type' => 'binding_to_crm_element',
				'config' => [],
			],
		];
	}

	public function getValues(bool $allowDefaultValues = true, array $descriptions = null): array
	{
		$values = [];
		if ($descriptions === null)
		{
			$descriptions = $this->getDescriptions();
		}

		if ($allowDefaultValues)
		{
			foreach ($descriptions as $field)
			{
				$values[$field['name']] = $this->getFieldValue($field)
					?? $field['defaultValue']
					?? '';
			}
		}
		else
		{
			foreach ($descriptions as $field)
			{
				$values[$field['name']] = $this->getFieldValue($field) ?? '';
			}
		}

		$additionalValues = $this->getAdditionalValues($values, $descriptions);

		if (!empty($additionalValues))
		{
			$values = array_merge($values, $additionalValues);
		}

		return $values;
	}

	public function getVariationGridId(): string
	{
		$iblockInfo = ServiceContainer::getIblockInfo($this->entity->getIblockId());

		if ($iblockInfo)
		{
			return 'catalog-product-variation-grid-' . $iblockInfo->getProductIblockId();
		}

		return 'catalog-product-variation-grid';
	}

	public function getCardSettings(): array
	{
		$gridColumnSettings = [
			'VAT_INCLUDED' => [
				GridVariationForm::formatFieldName('VAT_ID'),
				GridVariationForm::formatFieldName('VAT_INCLUDED'),
			],
			'PURCHASING_PRICE_FIELD' => [
				GridVariationForm::formatFieldName('PURCHASING_PRICE_FIELD'),
			],
			'MEASUREMENTS' => [
				GridVariationForm::formatFieldName('HEIGHT'),
				GridVariationForm::formatFieldName('LENGTH'),
				GridVariationForm::formatFieldName('WIDTH'),
				GridVariationForm::formatFieldName('WEIGHT'),
			],
			'MEASURE_RATIO' => [
				GridVariationForm::formatFieldName('MEASURE_RATIO'),
			],
		];

		$activeSettings = [];
		$options = new \Bitrix\Main\Grid\Options($this->getVariationGridId());
		$allUsedColumns = $options->getUsedColumns();
		if (!empty($allUsedColumns))
		{
			foreach ($gridColumnSettings as $setting => $columns)
			{
				if (empty(array_diff($columns, $allUsedColumns)))
				{
					$activeSettings[] = $setting;
				}
			}
		}

		$config = $this->getCardUserConfig();
		if (!empty($config['CATALOG_PARAMETERS']))
		{
			$activeSettings[] = 'CATALOG_PARAMETERS';
		}

		$items = [];
		$settingList = array_merge(array_keys($gridColumnSettings), ['CATALOG_PARAMETERS']);
		foreach ($settingList as $setting)
		{
			$items[] = [
				'id' => $setting,
				'checked' => in_array($setting, $activeSettings, true),
				'title' => Loc::getMessage('CATALOG_C_F_VARIATION_SETTINGS_' . $setting . '_TITLE'),
				'desc' => Loc::getMessage('CATALOG_C_F_VARIATION_SETTINGS_' . $setting . '_DESC'),
				'action' => isset($gridColumnSettings[$setting]) ? 'grid' : 'card',
				'columns' => $gridColumnSettings[$setting] ?? null,
			];
		}

		$isInventoryControlEnabled = UseStore::isUsed();
		$sliderPath = \CComponentEngine::makeComponentPath('bitrix:catalog.warehouse.master.clear');
		$sliderPath = getLocalPath('components' . $sliderPath . '/slider.php');

		$items[] = [
			'id' => 'SLIDER',
			'checked' => $isInventoryControlEnabled,
			'disabled' => $isInventoryControlEnabled,
			'title' => Loc::getMessage('CATALOG_C_F_VARIATION_SETTINGS_WAREHOUSE_TITLE'),
			'desc' => '',
			'hint' => $isInventoryControlEnabled ? Loc::getMessage('CATALOG_C_F_VARIATION_SETTINGS_WAREHOUSE_HINT') : '',
			'url' => $sliderPath,
			'action' => 'slider',
		];

		return $items;
	}

	public function getCardConfigId(): string
	{
		$iblockInfo = ServiceContainer::getIblockInfo($this->entity->getIblockId());

		if ($iblockInfo)
		{
			return 'catalog-entity-card-config-' . $iblockInfo->getProductIblockId();
		}

		return 'catalog-entity-card-config';
	}

	public function getCardUserConfig(): array
	{
		return \CUserOptions::getOption('catalog', $this->getCardConfigId(), []);
	}

	public function saveCardUserConfig(array $config): bool
	{
		return \CUserOptions::setOption('catalog', $this->getCardConfigId(), $config);
	}

	public function getVariationIblockId(): ?int
	{
		$iblockInfo = ServiceContainer::getIblockInfo($this->entity->getIblockId());

		if ($iblockInfo)
		{
			return (int)$iblockInfo->getSkuIblockId() ?: $iblockInfo->getProductIblockId();
		}

		return null;
	}

	protected function getAdditionalValues(array $values,  array $descriptions = []): array
	{
		$additionalValues = [];

		foreach ($descriptions as $description)
		{
			if (!in_array($description['type'], ['custom', 'money', 'multimoney', 'user'], true))
			{
				continue;
			}

			$value = $values[$description['name']] ?? null;
			$descriptionData = $description['data'] ?? [];

			if (!empty($description['settings']['USER_TYPE']))
			{
				$description['settings']['PROPERTY_USER_TYPE'] = \CIBlockProperty::GetUserType(
					$description['settings']['USER_TYPE']
				);
			}

			$propertySettings = $description['settings'];

			if ($description['type'] === 'custom')
			{
				if ($propertySettings['PROPERTY_TYPE'] === PropertyTable::TYPE_ELEMENT)
				{
					$elementData = ElementTable::getList([
						'select' => ['NAME'],
						'filter' => ['ID' => $value],
					]);
					$namesList = [];
					while ($element = $elementData->fetch())
					{
						$namesList[] = $element['NAME'];
					}
					$viewValue = implode(', ', $namesList);
					$additionalValues[$descriptionData['view']] = HtmlFilter::encode($viewValue);
					$additionalValues[$descriptionData['edit']] = $this->getElementPropertyEditHtml(
						$description['name'],
						$propertySettings,
						$value
					);
				}
				elseif ($propertySettings['PROPERTY_TYPE'] === PropertyTable::TYPE_FILE)
				{
					if ($description['propertyCode'] === self::MORE_PHOTO)
					{
						$value = $this->getEntityViewPictureValues($this->entity);
						$editValue = $this->getEntityEditPictureValues($this->entity);

						if (!$description['multiple'])
						{
							$value = $value[0];
							$editValue = $editValue[0];
						}
					}
					else
					{
						$editValue = $value;
					}

					$imageExtensions = explode(',', \CFile::GetImageExtensions());
					$fileExtensions = explode(',', $description['settings']['FILE_TYPE']);
					$fileExtensions = array_map('trim', $fileExtensions);

					$diffExtensions = array_diff($fileExtensions, $imageExtensions);
					$isImageInput = empty($diffExtensions);

					$descriptionSingle = $description;
					$descriptionSingle['settings']['MULTIPLE'] = false;
					$descriptionMultiple = $description;
					$descriptionMultiple['settings']['MULTIPLE'] = true;

					if ($isImageInput)
					{
						$additionalValues[$descriptionData['view']] = $this->getImagePropertyViewHtml($value);
						$additionalValues[$descriptionData['viewList']]['SINGLE'] = $this->getImagePropertyViewHtml(is_array($value) ? $value[0] : $value);
						$additionalValues[$descriptionData['viewList']]['MULTIPLE'] = $this->getImagePropertyViewHtml(is_array($value) ? $value : [$value]);
						$additionalValues[$descriptionData['edit']] = $this->getImagePropertyEditHtml($description, $editValue);
						$additionalValues[$descriptionData['editList']]['SINGLE'] = $this->getImagePropertyEditHtml($descriptionSingle, is_array($editValue) ? $editValue[0] : $editValue);
						$additionalValues[$descriptionData['editList']]['MULTIPLE'] = $this->getImagePropertyEditHtml($descriptionMultiple, is_array($editValue) ? $editValue : [$editValue]);
					}
					else
					{
						$controlId = FileInputUtility::instance()->getUserFieldCid([
							'ID' => $description['settings']['ID'],
							'ENTITY_ID' => $description['propertyId'],
							'MULTIPLE' => $description['settings']['MULTIPLE'],
							'FIELD_NAME' => $description['name'],
						]);

						$additionalValues[$descriptionData['view']] = '';
						$additionalValues[$descriptionData['viewList']]['SINGLE'] = '';
						$additionalValues[$descriptionData['viewList']]['MULTIPLE'] = '';

						if (!empty($value))
						{
							$additionalValues[$descriptionData['view']] = $this->getFilePropertyViewHtml($description, $value, $controlId);
							$additionalValues[$descriptionData['viewList']]['SINGLE'] = $this->getFilePropertyViewHtml($description, is_array($value) ? $value[0] : $value, $controlId, false);
							$additionalValues[$descriptionData['viewList']]['MULTIPLE'] = $this->getFilePropertyViewHtml($description, is_array($value) ? $value : [$value], $controlId, true);
						}

						$additionalValues[$descriptionData['edit']] = $this->getFilePropertyEditHtml($description, $value, $controlId);
						$additionalValues[$descriptionData['editList']]['SINGLE'] = $this->getFilePropertyEditHtml($description, is_array($value) ? $value[0] : $value, $controlId, false);
						$additionalValues[$descriptionData['editList']]['MULTIPLE'] = $this->getFilePropertyEditHtml($description, is_array($value) ? $value : [$value], $controlId, true);
					}
				}
				else
				{
					if (
						$propertySettings['USER_TYPE'] === 'FileMan'
						|| $propertySettings['USER_TYPE'] === 'DiskFile'
					)
					{
						$value = [
							'VALUE' => null,
							'DESCRIPTION' => '',
						];
					}

					$params = [
						'SETTINGS' => $propertySettings,
						'VALUE' => $value,
						'FIELD_NAME' => $description['name'],
						'ELEMENT_ID' => $this->entity->getId() ?? 'n' . mt_rand(),
					];

					if ($propertySettings['USER_TYPE'] === 'map_google')
					{
						$params['WIDTH'] = '95%';
						$params['HEIGHT'] = '400px';
					}

					$paramsSingle = $params;
					$paramsSingle['VALUE'] = $description['multiple'] ? $value[0] : $value;
					$paramsSingle['SETTINGS']['MULTIPLE'] = 'N';
					if ($value === '')
					{
						$singleValueToMultiple = [];
					}
					else
					{
						$singleValueToMultiple = [$value];
					}
					$paramsMultiple = $params;
					$paramsMultiple['VALUE'] = $description['multiple'] ? $value : $singleValueToMultiple;
					$paramsMultiple['SETTINGS']['MULTIPLE'] = 'Y';

					$viewMethod = $propertySettings['PROPERTY_USER_TYPE'][self::USER_TYPE_GET_VIEW_METHOD] ?? null;
					if ($viewMethod && is_callable($viewMethod))
					{
						$additionalValues[$descriptionData['viewList']]['SINGLE'] = $viewMethod($paramsSingle);
						$additionalValues[$descriptionData['viewList']]['MULTIPLE'] = $viewMethod($paramsMultiple);
						$additionalValues[$descriptionData['view']] = $viewMethod($params);
					}

					$editMethod = $propertySettings['PROPERTY_USER_TYPE'][self::USER_TYPE_GET_EDIT_METHOD] ?? null;
					if ($editMethod && is_callable($editMethod))
					{
						$additionalValues[$descriptionData['editList']]['SINGLE'] = $editMethod($paramsSingle);
						$additionalValues[$descriptionData['editList']]['MULTIPLE'] = $editMethod($paramsMultiple);
						$additionalValues[$descriptionData['edit']] = $editMethod($params);
					}
				}
			}
			elseif (in_array($description['type'], ['money', 'multimoney'], true) && Loader::includeModule('currency'))
			{
				$formatMethod = $propertySettings['PROPERTY_USER_TYPE'][self::USER_TYPE_FORMAT_VALUE_METHOD] ?? null;
				if ($formatMethod && is_callable($formatMethod))
				{
					if ($description['type'] === 'money')
					{
						$additionalMoneyValues = $this->getAdditionalMoneyValues($value, $formatMethod);

						$additionalValues[$descriptionData['currencyCode']] = $additionalMoneyValues['currencyCode'];
						$additionalValues[$descriptionData['amount']] = $additionalMoneyValues['amount'];
						$additionalValues[$descriptionData['formatted']] = $additionalMoneyValues['formatted'];
						$additionalValues[$descriptionData['formattedWithCurrency']] = $additionalMoneyValues['formattedWithCurrency'];
					}
					else
					{
						foreach ($value as $currentValueElement)
						{
							$additionalMoneyValues = $this->getAdditionalMoneyValues($currentValueElement, $formatMethod);

							$additionalValues[$descriptionData['currencyCode']][] = $additionalMoneyValues['currencyCode'];
							$additionalValues[$descriptionData['amount']][] = $additionalMoneyValues['amount'];
							$additionalValues[$descriptionData['formatted']][] = $additionalMoneyValues['formatted'];
							$additionalValues[$descriptionData['formattedWithCurrency']][] = $additionalMoneyValues['formattedWithCurrency'];
						}
					}
				}
			}
			elseif ($description['type'] === 'user')
			{
				$userData = \Bitrix\Main\UserTable::getList([
					'filter' => ['=ID' => $value],
					'select' => [
						'ID', 'LOGIN', 'PERSONAL_PHOTO',
						'NAME', 'SECOND_NAME', 'LAST_NAME',
						'WORK_POSITION',
					],
					'limit' => 1,
				]);

				if ($user = $userData->fetch())
				{
					$pathToProfile = $this->params['PATH_TO']['USER_PROFILE'];
					if ($pathToProfile)
					{
						$additionalValues['PATH_TO_USER_PROFILE'] = $pathToProfile;
						$additionalValues['PATH_TO_' . $description['name']] = \CComponentEngine::MakePathFromTemplate(
							$pathToProfile,
							['user_id' => $user['ID']]
						);
					}
					$additionalValues[$description['name'] . '_PERSONAL_PHOTO'] = $user['PERSONAL_PHOTO'];
					$additionalValues[$description['name'] . '_WORK_POSITION'] = $user['WORK_POSITION'];

					$additionalValues[$description['name'] . '_FORMATTED_NAME'] = \CUser::FormatName(
						\CSite::GetNameFormat(false),
						[
							'LOGIN' => $user['LOGIN'],
							'NAME' => $user['NAME'],
							'LAST_NAME' => $user['LAST_NAME'],
							'SECOND_NAME' => $user['SECOND_NAME'],
						],
						true,
						false
					);

					if ((int)$user['PERSONAL_PHOTO'] > 0)
					{
						$file = new \CFile();
						$fileInfo = $file->ResizeImageGet(
							(int)$user['PERSONAL_PHOTO'],
							['width' => 60, 'height' => 60],
							BX_RESIZE_IMAGE_EXACT
						);
						if (is_array($fileInfo) && isset($fileInfo['src']))
						{
							$additionalValues[$description['name'] . '_PHOTO_URL'] = $fileInfo['src'];
						}
					}
				}
			}
		}

		return $additionalValues;
	}

	private function getAdditionalMoneyValues(string $value, callable $formatMethod): array
	{
		$additionalValues = [];

		$formattedValues = $formatMethod($value);
		$amount = $formattedValues['AMOUNT'];
		if ($formattedValues['AMOUNT'] !== '' && $formattedValues['DECIMALS'] !== '')
		{
			$amount .= '.' . $formattedValues['DECIMALS'];
		}
		$currency = $formattedValues['CURRENCY'];

		$additionalValues['currencyCode'] = $currency;
		$additionalValues['amount'] = $amount;
		$additionalValues['formatted'] = \CCurrencyLang::CurrencyFormat($amount, $currency, false);
		$additionalValues['formattedWithCurrency'] = \CCurrencyLang::CurrencyFormat($amount, $currency, true);

		return $additionalValues;
	}

	private function getImageValuesForEntity(BaseIblockElementEntity $entity): array
	{
		$values = [];

		if ($entity instanceof HasPropertyCollection)
		{
			$morePhotoProperty = $entity->getPropertyCollection()->findByCode(self::MORE_PHOTO);
			if ($morePhotoProperty)
			{
				$morePhotoValues = $morePhotoProperty->getPropertyValueCollection()->getValues();
				if (!empty($morePhotoValues))
				{
					if (!is_array($morePhotoValues))
					{
						$morePhotoValues = [$morePhotoValues];
					}
					$values = array_merge($values, $morePhotoValues);
				}
			}
		}

		$previewPicture = $entity->getField('PREVIEW_PICTURE');
		if ($previewPicture)
		{
			$values = array_merge([$previewPicture], $values);
		}

		$detailPicture = $entity->getField('DETAIL_PICTURE');
		if ($detailPicture)
		{
			$values = array_merge([$detailPicture], $values);
		}

		return $values;
	}

	private function getEntityEditPictureValues(BaseIblockElementEntity $entity): array
	{
		return $this->getImageValuesForEntity($entity);
	}

	private function getEntityViewPictureValues(BaseIblockElementEntity $entity): array
	{
		return $this->getImageValuesForEntity($entity);
	}

	protected function getFieldValue(array $field)
	{
		if ($field['entity'] === 'product')
		{
			return $this->getProductFieldValue($field);
		}

		if ($field['entity'] === 'property')
		{
			return $this->getPropertyFieldValue($field);
		}

		return null;
	}

	public function getConfig(): array
	{
		$config = $this->collectFieldConfigs();

		foreach ($config as &$column)
		{
			usort($column['elements'], static function ($a, $b) {
				$sortA = $a['sort'] ?? PHP_INT_MAX;
				$sortB = $b['sort'] ?? PHP_INT_MAX;

				return $sortA <=> $sortB;
			});
		}

		return array_values($config);
	}

	protected function collectFieldConfigs(): array
	{
		$leftWidth = 30;

		return [
			'left' => [
				'name' => 'left',
				'type' => 'column',
				'data' => [
					'width' => $leftWidth,
				],
				'elements' => [
					[
						'name' => 'main',
						'title' => Loc::getMessage('CATALOG_C_F_MAIN_SECTION_TITLE'),
						'type' => 'section',
						'elements' => $this->getMainConfigElements(),
						'data' => [
							'isRemovable' => false,
						],
						'sort' => 100,
					],
					[
						'name' => 'properties',
						'title' => Loc::getMessage('CATALOG_C_F_PROPERTIES_SECTION_TITLE'),
						'type' => 'section',
						'elements' => $this->getPropertiesConfigElements(),
						'data' => [
							'isRemovable' => false,
						],
						'sort' => 200,
					],
				],
			],
			'right' => [
				'name' => 'right',
				'type' => 'column',
				'data' => [
					'width' => 100 - $leftWidth,
				],
				'elements' => [
					[
						'name' => 'catalog_parameters',
						'title' => Loc::getMessage('CATALOG_C_F_STORE_SECTION_TITLE'),
						'type' => 'section',
						'elements' => [
							['name' => 'QUANTITY_TRACE'],
							['name' => 'CAN_BUY_ZERO'],
							['name' => 'SUBSCRIBE'],
						],
						'data' => [
							'isRemovable' => false,
						],
						'sort' => 200,
					],
				],
			],
		];
	}

	protected function getMainConfigElements(): array
	{
		return [
			['name' => 'NAME-CODE'],
			['name' => 'DETAIL_TEXT'],
		];
	}

	public function getDescriptions(): array
	{
		if ($this->descriptions === null)
		{
			$this->descriptions = $this->buildDescriptions();
		}

		return $this->descriptions;
	}

	protected function buildDescriptions(): array
	{
		$fieldBlocks = [];

		$fieldBlocks[] = $this->getTableDescriptions($this->getElementTableMap());
		$fieldBlocks[] = $this->getTableDescriptions(ProductTable::getMap());
		$fieldBlocks[] = $this->getIblockPropertiesDescriptions();

		return array_merge(...$fieldBlocks);
	}

	protected function getElementTableMap(): array
	{
		$elementTableMap = ElementTable::getMap();
		unset($elementTableMap['NAME'], $elementTableMap['CODE']);

		return $elementTableMap;
	}

	protected function getNameCodeDescription(): array
	{
		return [
			[
				'entity' => 'product',
				'name' => 'NAME-CODE',
				'originalName' => 'NAME-CODE',
				'title' => Loc::getMessage('ELEMENT_ENTITY_NAME_FIELD'),
				'type' => 'name-code',
				'editable' => 'true',
				'required' => 'true',
				'placeholders' => [
					'creation' => Loc::getMessage('CATALOG_C_F_NEW_PRODUCT_PLACEHOLDER'),
				],
				'defaultValue' => null,
				'optionFlags' => 1,
			],
		];
	}

	private function getTableDescriptions(array $tableMap): array
	{
		$descriptions = [];

		$allowedFields = $this->getTableElementsWhiteList();

		/** @var \Bitrix\Main\ORM\Fields\ScalarField $field */
		foreach ($tableMap as $name => $field)
		{
			$fieldName = $field->getName();

			if (!isset($allowedFields[$fieldName]))
			{
				continue;
			}

			$description = [
				'entity' => 'product',
				'name' => $this->prepareFieldName($fieldName),
				'originalName' => $fieldName,
				'title' => $field->getTitle(),
				'type' => $this->getFieldTypeByObject($field),
				'editable' => $this->isEditableField($field),
				'required' => $this->isRequiredField($field),
				'placeholders' => $this->getFieldPlaceholders($field),
				'defaultValue' => $field->getDefaultValue(),
				'optionFlags' => 1, // showAlways
			];

			if ($field instanceof EnumField)
			{
				if ($this->isSpecificCatalogField($fieldName))
				{
					$items = $this->getCatalogEnumFields($field->getName());
				}
				else
				{
					$items = $this->getCommonEnumFields($field);
				}

				$description['data']['items'] = $items;
			}

			if ($description['type'] === 'custom')
			{
				$description['data'] += $this->getCustomControlParameters($description['name']);
			}
			elseif ($description['type'] === 'user')
			{
				$description['data'] = [
					'enableEditInView' => false,
					'formated' => $description['name'] . '_FORMATTED_NAME',
					'position' => $description['name'] . '_WORK_POSITION',
					'photoUrl' => $description['name'] . '_PHOTO_URL',
					'showUrl' => 'PATH_TO_' . $description['name'],
					'pathToProfile' => 'PATH_TO_USER_PROFILE',
				];
			}
			elseif ($fieldName === 'MEASURE')
			{
				$measureList = [];

				foreach ($this->getMeasures() as $measure)
				{
					$measureTitle = $measure['MEASURE_TITLE'];

					if (empty($measureTitle))
					{
						$measureTitle = \CCatalogMeasureClassifier::getMeasureTitle($measure['CODE'], 'MEASURE_TITLE');
					}

					$measureList[] = [
						'NAME' => HtmlFilter::encode($measureTitle),
						'VALUE' => $measure['ID'],
					];
				}

				$description['data']['items'] = $measureList;
				$description['type'] = 'list';
			}
			elseif ($fieldName === 'VAT_ID')
			{
				$vatList[] = [
					'VALUE' => 'D',
					'NAME' => Loc::getMessage(
						'CATALOG_C_F_DEFAULT',
						['#VALUE#' => Loc::getMessage('CATALOG_PRODUCT_CARD_VARIATION_GRID_NOT_SELECTED')]
					),
				];

				$iblockId = $this->entity->getIblockId();
				$iblockData = \CCatalog::GetByID($iblockId);

				foreach ($this->getVats() as $vat)
				{
					if ($vat['ID'] === $iblockData['VAT_ID'])
					{
						$vatList[0]['NAME'] = Loc::getMessage(
							'CATALOG_C_F_DEFAULT',
							['#VALUE#' => htmlspecialcharsbx($vat['NAME'])]
						);
					}

					$vatList[] = [
						'VALUE' => $vat['ID'],
						'NAME' => htmlspecialcharsbx($vat['NAME']),
					];
				}

				$description['data']['items'] = $vatList;
				$description['type'] = 'list';
			}

			$descriptions[] = $description;
		}

		return $descriptions;
	}

	private function getTableElementsWhiteList(): array
	{
		static $whiteList = null;

		if ($whiteList === null)
		{
			$whiteList = $this->getIblockElementFieldsList();

			if ($this->showCatalogProductFields())
			{
				$whiteList = array_merge($whiteList, $this->getCatalogProductFieldsList());
			}

			if ($this->showSpecificCatalogParameters())
			{
				$whiteList = array_merge($whiteList, $this->getSpecificCatalogFieldsList());
			}

			if ($this->showSubscribeCatalogParameters())
			{
				$whiteList = array_diff($whiteList, ['WEIGHT', 'WIDTH', 'LENGTH', 'HEIGHT']);
				$whiteList = array_merge($whiteList, $this->getSubscribeCatalogFieldList());
			}

			$whiteList = array_fill_keys($whiteList, true);
		}

		return $whiteList;
	}

	protected function getIblockElementFieldsList(): array
	{
		return [
			'ID',
			'IBLOCK_ID',
			// ToDo
			// 'IBLOCK_SECTION_ID',
			'TIMESTAMP_X',
			'MODIFIED_BY',
			'DATE_CREATE',
			'CREATED_BY',
			'ACTIVE',
			'ACTIVE_FROM',
			'ACTIVE_TO',
			'SORT',
			'NAME',
			'PREVIEW_TEXT',
			// 'PREVIEW_TEXT_TYPE',
			'DETAIL_TEXT',
			// 'DETAIL_TEXT_TYPE',
			'XML_ID',
			'CODE',
		];
	}

	protected function showCatalogProductFields(): bool
	{
		return false;
	}

	protected function getCatalogProductFieldsList(): array
	{
		return [
			'QUANTITY',
			'QUANTITY_RESERVED',
			'VAT_ID',
			'VAT_INCLUDED',
			// 'PURCHASING_PRICE',
			// 'PURCHASING_CURRENCY',
			// 'BARCODE_MULTI',
			// 'QUANTITY_RESERVED',
			'WEIGHT',
			'WIDTH',
			'LENGTH',
			'HEIGHT',
			'MEASURE',
			// 'TYPE',
			// 'AVAILABLE',
			// 'BUNDLE',
		];
	}

	protected function showSpecificCatalogParameters(): bool
	{
		return false;
	}

	private function getSpecificCatalogFieldsList(): array
	{
		return [
			'QUANTITY_TRACE',
			'CAN_BUY_ZERO',
			'SUBSCRIBE',
		];
	}

	private function getFieldTypeByObject(ScalarField $field): string
	{
		$fieldName = $field->getName();

		if ($fieldName === 'PREVIEW_PICTURE' || $fieldName === 'DETAIL_PICTURE')
		{
			return 'custom';
		}

		if ($fieldName === 'PREVIEW_TEXT' || $fieldName === 'DETAIL_TEXT')
		{
			return 'html';
		}

		if ($fieldName === 'MODIFIED_BY' || $fieldName === 'CREATED_BY')
		{
			return 'user';
		}

		switch (get_class($field))
		{
			case IntegerField::class:
			case FloatField::class:
				$fieldType = 'number';
				break;

			case BooleanField::class:
				$fieldType = 'boolean';
				break;

			case EnumField::class:
				$fieldType = 'list';
				break;

			case DateField::class:
			case DatetimeField::class:
				$fieldType = 'datetime';
				break;

			case TextField::class:
				$fieldType = 'textarea';
				break;

			case StringField::class:
			default:
				$fieldType = 'text';
		}

		return $fieldType;
	}

	private function isEditableField(Field $field): bool
	{
		if (in_array(
			$field->getName(),
			[
				'IBLOCK_ID',
				'MODIFIED_BY',
				'CREATED_BY',
				'AVAILABLE',
				'DATE_CREATE',
				'TIMESTAMP_X',
			],
			true
		))
		{
			return false;
		}

		if (in_array($field->getName(), ['QUANTITY', 'QUANTITY_RESERVED'], true) && State::isUsedInventoryManagement())
		{
			return false;
		}

		return !$field->isPrimary() && !$field->isAutocomplete();
	}

	private function isRequiredField(Field $field): bool
	{
		if ($field->getName() === 'IBLOCK_ID')
		{
			return false;
		}

		return $field->isRequired();
	}

	private function getFieldPlaceholders(Field $field): ?array
	{
		if ($field->getName() === 'NAME')
		{
			return [
				'creation' => Loc::getMessage('CATALOG_C_F_NEW_PRODUCT_PLACEHOLDER'),
			];
		}

		return null;
	}

	protected function showSubscribeCatalogParameters(): bool
	{
		$iblockInfo = ServiceContainer::getIblockInfo($this->entity->getIblockId());

		if ($iblockInfo)
		{
			return $iblockInfo->hasSubscription();
		}

		return false;
	}

	private function getSubscribeCatalogFieldList(): array
	{
		return [
			'PRICE_TYPE',
			'RECUR_SCHEME_LENGTH',
			'RECUR_SCHEME_TYPE',
			'TRIAL_PRICE_ID',
			'WITHOUT_ORDER',
		];
	}

	private function isSpecificCatalogField(string $fieldName): bool
	{
		static $catalogEnumFields = null;

		if ($catalogEnumFields === null)
		{
			$catalogEnumFields = array_fill_keys(
				$this->getSpecificCatalogFieldsList(),
				true
			);
		}

		return isset($catalogEnumFields[$fieldName]);
	}

	protected function getCatalogEnumFields(string $fieldName): array
	{
		$defaultValue = null;

		switch ($fieldName)
		{
			case 'QUANTITY_TRACE':
				$defaultValue = Option::get('catalog', 'default_quantity_trace') === 'Y';
				break;

			case 'CAN_BUY_ZERO':
				$defaultValue = Option::get('catalog', 'default_can_buy_zero') === 'Y';
				break;

			case 'SUBSCRIBE':
				$defaultValue = Option::get('catalog', 'default_subscribe') === 'Y';
				break;
		}

		return [
			[
				'NAME' => Loc::getMessage(
					'CATALOG_C_F_DEFAULT',
					[
						'#VALUE#' => $defaultValue
							? Loc::getMessage('CATALOG_C_F_YES')
							: Loc::getMessage('CATALOG_C_F_NO'),
					]
				),
				'VALUE' => ProductTable::STATUS_DEFAULT,
			],
			[
				'NAME' => Loc::getMessage('CATALOG_C_F_YES'),
				'VALUE' => ProductTable::STATUS_YES,
			],
			[
				'NAME' => Loc::getMessage('CATALOG_C_F_NO'),
				'VALUE' => ProductTable::STATUS_NO,
			],
		];
	}

	private function getCommonEnumFields(EnumField $field): array
	{
		$items = [];

		foreach ((array)$field->getValues() as $value)
		{
			$items[] = [
				'NAME' => $value,
				'VALUE' => $value,
			];
		}

		return $items;
	}

	public function getIblockPropertiesDescriptions(): array
	{
		if ($this->propertyDescriptions === null)
		{
			$this->propertyDescriptions = $this->buildIblockPropertiesDescriptions();
		}

		return $this->propertyDescriptions;
	}

	protected function buildIblockPropertiesDescriptions(): array
	{
		$propertyDescriptions = [];

		foreach ($this->entity->getPropertyCollection() as $property)
		{
			if ($property->isActive())
			{
				$propertyDescriptions[] = $this->getPropertyDescription($property);
			}
		}

		return $propertyDescriptions;
	}

	public static function preparePropertyName(string $name = ''): string
	{
		return self::PROPERTY_FIELD_PREFIX . $name;
	}

	public static function preparePropertyNameFromProperty(Property $property): string
	{
		$name = $property->getCode() === self::MORE_PHOTO ? self::MORE_PHOTO : $property->getId();

		return static::preparePropertyName($name);
	}

	protected function getPropertyDescription(Property $property): array
	{
		$description = [
			'entity' => 'property',
			'name' => static::preparePropertyNameFromProperty($property),
			'propertyId' => $property->getId(),
			'propertyCode' => $property->getCode(),
			'title' => $property->getName(),
			'editable' => true,
			'required' => $property->isRequired(),
			'multiple' => $property->isMultiple(),
			'defaultValue' => $property->getDefaultValue(),
			'settings' => $property->getSettings(),
		];

		if ($property->getUserType() === \CIBlockPropertySequence::USER_TYPE)
		{
			$userTypeSettings = $property->getSetting('USER_TYPE_SETTINGS');
			$description['editable'] = $userTypeSettings['write'] === 'Y';
		}

		if ($description['propertyCode'] === self::MORE_PHOTO)
		{
			$description['optionFlags'] = 1; // showAlways
		}

		if ($description['multiple'] && !is_array($description['defaultValue']))
		{
			$description['defaultValue'] = $description['defaultValue'] === null ? [] : [$description['defaultValue']];
		}

		// remove it after PropertyTable::TYPE_ELEMENT refactoring
		if ($property->getPropertyType() === PropertyTable::TYPE_ELEMENT)
		{
			Asset::getInstance()->addJs('/bitrix/js/main/utils.js');
		}

		if ($property->getUserType())
		{
			$specificDescription = $this->getUserTypePropertyDescription($property);
		}
		else
		{
			$specificDescription = $this->getGeneralPropertyDescription($property);
		}

		$specificDescription['data']['isPublic'] = $property->isPublic();

		return array_merge($description, $specificDescription);
	}

	private function getPropertyType(Property $property): string
	{
		switch ($property->getPropertyType())
		{
			case PropertyTable::TYPE_STRING:
				// ToDo no multiple textarea right now
				// if ($property->isMultiple())
				// {
				// 	$fieldType = 'multifield';
				// }
				if ((int)$property->getSetting('ROW_COUNT') > 1)
				{
					$fieldType = 'textarea';
				}
				else
				{
					$fieldType = $property->isMultiple() ? 'multitext' : 'text';
				}

				break;

			case PropertyTable::TYPE_NUMBER:
				// ToDo no multiple number right now
				$fieldType = $property->isMultiple() ? 'multinumber' : 'number';
				break;

			case PropertyTable::TYPE_LIST:
				$fieldType = $property->isMultiple() ? 'multilist' : 'list';
				break;

			// case TextField::class:
			// 	$fieldType = 'textarea';
			// 	break;

			case PropertyTable::TYPE_ELEMENT:
			case PropertyTable::TYPE_FILE:
				$fieldType = 'custom';
				break;

			default:
				$fieldType = 'text';
		}

		return $fieldType;
	}

	protected function getHiddenPropertyCodes(): array
	{
		return [];
	}

	protected function getPropertiesConfigElements(): array
	{
		$elements = [];
		$hiddenCodesMap = array_fill_keys($this->getHiddenPropertyCodes(), true);
		foreach ($this->entity->getPropertyCollection() as $property)
		{
			if (isset($hiddenCodesMap[$property->getCode()]))
			{
				continue;
			}

			$elements[] = [
				'name' => static::preparePropertyNameFromProperty($property),
			];
		}

		return $elements;
	}

	protected function getGeneralPropertyDescription(Property $property): array
	{
		$type = $this->getPropertyType($property);

		$description = [
			'type' => $type,
			'data' => [
				'isProductProperty' => true,
			],
		];

		if ($type === 'custom')
		{
			$name = static::preparePropertyNameFromProperty($property);
			$description['data'] += $this->getCustomControlParameters($name);
		}

		if ($type === 'textarea')
		{
			$description['lineCount'] = (int)($property->getSetting('ROW_COUNT') ?? 1);
		}

		if ($property->getPropertyType() === PropertyTable::TYPE_LIST)
		{
			$description['data']['enableEmptyItem'] = true;
			$description['data']['items'] = [];

			$propertyEnumIterator = \CIBlockProperty::GetPropertyEnum(
				$property->getId(),
				[
					'SORT' => 'ASC',
					'VALUE' => 'ASC',
					'ID' => 'ASC',
				]
			);
			while ($enum = $propertyEnumIterator->fetch())
			{
				$description['data']['items'][] = [
					'NAME' => $enum['VALUE'],
					'VALUE' => $enum['ID'],
					'ID' => $enum['ID'],
				];
			}

			if (count($description['data']['items']) === 1
				&& $description['data']['items'][0]['NAME'] === 'Y')
			{
				$description['type'] = 'boolean';
				$description['data']['value'] = $description['data']['items'][0]['VALUE'];
			}
		}

		return $description;
	}

	protected function getUserTypePropertyDescription(Property $property): array
	{
		$propertySettings = $this->getPropertySettings($property);

		if ($property->getPropertyType() === 'S' && $property->getUserType() === 'HTML')
		{
			$defaultValue = $property->getDefaultValue();

			if ($defaultValue)
			{
				if ($property->isMultiple())
				{
					foreach ($defaultValue as &$item)
					{
						$item = $item['TEXT'] ?? null;
					}
				}
				else
				{
					$defaultValue = $defaultValue['TEXT'] ?? null;
				}
			}

			return [
				'type' => 'html',
				'defaultValue' => $defaultValue,
			];
		}

		$userTypeMethod = $propertySettings['PROPERTY_USER_TYPE'][self::USER_TYPE_METHOD] ?? null;
		if ($userTypeMethod && is_callable($userTypeMethod))
		{
			$values = $property->getPropertyValueCollection()->getValues();
			$description = $userTypeMethod($propertySettings, $values);

			if ($property->getCode() === 'CML2_LINK')
			{
				$description['editable'] = false;
			}

			$specialTypes = ['custom', 'money', 'multimoney'];
			if (in_array($description['type'], $specialTypes, true))
			{
				$name = static::preparePropertyNameFromProperty($property);
				$descriptionData = $description['data'] ?? [];

				if ($description['type'] === 'custom')
				{
					$descriptionData += $this->getCustomControlParameters($name);
				}
				elseif ($description['type'] === 'money' || $description['type'] === 'multimoney')
				{
					$descriptionData['affectedFields'] = [
						$name . '[CURRENCY]',
						$name . '[AMOUNT]',
					];
					$descriptionData['currency'] = [
						'name' => $name . '[CURRENCY]',
						'items' => $this->getCurrencyList(),
					];
					$descriptionData['amount'] = $name . '[AMOUNT]';
					$descriptionData['currencyCode'] = $name . '[CURRENCY]';
					$descriptionData['formatted'] = $name . '[FORMATTED_AMOUNT]';
					$descriptionData['formattedWithCurrency'] = $name . '[FORMATTED_AMOUNT_WITH_CURRENCY]';
				}

				$description['data'] = $descriptionData;
			}

			if (empty($description['data']))
			{
				$description['data'] = [];
			}

			$description['data']['isProductProperty'] = true;

			return $description;
		}

		return [];
	}

	protected function getCurrencyList(): array
	{
		static $currencyList = null;

		if ($currencyList === null)
		{
			$currencyList = [];

			foreach (CurrencyManager::getNameList() as $code => $name)
			{
				$currencyList[] = [
					'VALUE' => $code,
					'NAME' => htmlspecialcharsbx($name),
				];
			}
		}

		return $currencyList;
	}

	protected function getPropertySettings(Property $property): array
	{
		$propertySettings = $property->getSettings();

		if (!empty($propertySettings['USER_TYPE']))
		{
			$propertySettings['PROPERTY_USER_TYPE'] = \CIBlockProperty::GetUserType($propertySettings['USER_TYPE']);
		}

		return $propertySettings;
	}

	protected function getImagePropertyViewHtml($value): string
	{
		$fileCount = 0;

		// single scalar property
		if (!empty($value) && !is_array($value))
		{
			$value = [$value];
		}

		if (is_array($value))
		{
			$fileCount = min(count($value), 3);
			$value = reset($value);
		}

		$imageSrc = null;

		if (!empty($value))
		{
			$image = \CFile::GetFileArray($value);
			if ($image)
			{
				$imageSrc = $image['SRC'];
			}
		}

		switch ($fileCount)
		{
			case 3:
				$multipleClass = ' ui-image-input-img-block-multiple';
				break;

			case 2:
				$multipleClass = ' ui-image-input-img-block-double';
				break;

			case 0:
				$multipleClass = ' ui-image-input-img-block-empty';
				break;

			case 1:
			default:
				$multipleClass = '';
				break;
		}

		if ($imageSrc)
		{
			$imageSrc = " src=\"{$imageSrc}\"";

			return <<<HTML
				<div class="ui-image-input-img-block{$multipleClass}">
					<div class="ui-image-input-img-block-inner">
						<div class="ui-image-input-img-item">
							<img class="ui-image-input-img"{$imageSrc}>
						</div>
					</div>
				</div>
				HTML;
		}

		return '';
	}

	/**
	 * @return \CMain
	 */
	protected function getApplication(): \CMain
	{
		global $APPLICATION;

		return $APPLICATION;
	}

	protected function getImageComponent(array $params): string
	{
		ob_start();

		$this->getApplication()->includeComponent('bitrix:ui.image.input', '', $params);

		return ob_get_clean();
	}

	protected function getFilePropertyEditHtml($description, $value, $controlId, bool $multipleForList = null): string
	{
		if ($multipleForList === null)
		{
			$multiple = $description['settings']['MULTIPLE'];
		}
		else
		{
			$multiple = $multipleForList ? 'Y' : 'N';
		}

		ob_start();

		$this->getApplication()->IncludeComponent(
			'bitrix:main.file.input',
			'.default',
			[
				'INPUT_NAME' => $description['name'],
				'INPUT_NAME_UNSAVED' => $description['name'] . '_tmp',
				'INPUT_VALUE' => $value,
				'MULTIPLE' => $multiple,
				'MODULE_ID' => 'catalog',
				'ALLOW_UPLOAD' => 'F',
				'ALLOW_UPLOAD_EXT' => $description['settings']['FILE_TYPE'],
				'MAX_FILE_SIZE' => \CUtil::Unformat(ini_get('upload_max_filesize')),
				'CONTROL_ID' => $controlId,
			]
		);

		return ob_get_clean();
	}

	protected function getFilePropertyViewHtml($description, $value, $controlId, bool $multipleForList = null)
	{
		$cid = FileInputUtility::instance()->registerControl('', $controlId);
		$signer = new \Bitrix\Main\Security\Sign\Signer();
		$signature = $signer->getSignature($cid, 'main.file.input');
		if (is_array($value))
		{
			foreach ($value as $elementOfValue)
			{
				FileInputUtility::instance()->registerFile($cid, $elementOfValue);
			}
		}
		else
		{
			FileInputUtility::instance()->registerFile($cid, $value);
		}

		if ($multipleForList === null)
		{
			$multiple = $description['settings']['MULTIPLE'];
		}
		else
		{
			$multiple = $multipleForList ? 'Y' : 'N';
		}

		ob_start();

		$this->getApplication()->IncludeComponent(
			'bitrix:main.field.file',
			'',
			[
				'userField' => [
					'ID' => $description['settings']['ID'],
					'VALUE' => $value,
					'USER_TYPE_ID' => 'file',
					'MULTIPLE' => $multiple,
				],
				'additionalParameters' => [
					'mode' => 'main.view',
					'CONTEXT' => 'UI_EDITOR',
					'URL_TEMPLATE' => '/bitrix/components/bitrix/main.file.input/ajax.php?'
						. 'mfi_mode=down'
						. '&fileID=#file_id#'
						. '&cid=' . $cid
						. '&sessid=' . bitrix_sessid()
						. '&s=' . $signature,
				],
			]
		);

		return ob_get_clean();
	}

	protected function getImagePropertyEditHtml(array $property, $value): string
	{
		$inputName = $this->getFilePropertyInputName($property);

		if ($value && !is_array($value))
		{
			$value = [$value];
		}

		$fileValues = [];

		if (!empty($value) && is_array($value))
		{
			foreach ($value as $fileId)
			{
				$propName = str_replace('n#IND#', $fileId, $inputName);
				$fileValues[$propName] = $fileId;
			}
		}

		$fileType = $property['settings']['FILE_TYPE'] ?? null;

		$fileParams = [
			'name' => $inputName,
			'id' => $inputName . '_' . random_int(1, 1000000),
			'description' => $property['settings']['WITH_DESCRIPTION'] ?? 'Y',
			'allowUpload' => $fileType ? 'F' : 'I',
			'allowUploadExt' => $fileType,
			'maxCount' => ($property['settings']['MULTIPLE'] ?? 'N') !== 'Y' ? 1 : null,

			'upload' => true,
			'medialib' => false,
			'fileDialog' => true,
			'cloud' => true,
		];

		return $this->getImageComponent([
			'FILE_SETTINGS' => $fileParams,
			'FILE_VALUES' => $fileValues,
			'LOADER_PREVIEW' => $this->getImagePropertyViewHtml($value),
		]);
	}

	protected function getFilePropertyInputName(array $property): string
	{
		$inputName = $property['name'] ?? '';

		if (isset($property['settings']['MULTIPLE']) && $property['settings']['MULTIPLE'] === 'Y')
		{
			$inputName .= '[n#IND#]';
		}

		return $inputName;
	}

	protected function getElementPropertyEditHtml(string $name, array $propertyFields, $values, bool $bVarsFromForm = false, bool $isCopying = false): string
	{
		$name = htmlspecialcharsbx($name);

		$index = 0;
		$show = true;

		$propertyFields['LINK_IBLOCK_ID'] = (int)$propertyFields['LINK_IBLOCK_ID'];
		$multipleCount = (int)($propertyFields['MULTIPLE_CNT']);
		if ($multipleCount <= 0 || $multipleCount > 30)
		{
			$multipleCount = 5;
		}

		$cnt = ($propertyFields['MULTIPLE'] === 'Y' ? $multipleCount : 1);

		if (!is_array($values))
		{
			$values = [$values];
		}

		$fixIBlock = $propertyFields['LINK_IBLOCK_ID'] > 0;
		$windowTableId = 'iblockprop-' . PropertyTable::TYPE_ELEMENT . '-' . $propertyFields['ID'] . '-' . $propertyFields['LINK_IBLOCK_ID'];

		$searchParams = [
			'IBLOCK_ID' => (string)$propertyFields['LINK_IBLOCK_ID'],
			'n' => $name,
			'tableId' => $windowTableId,
		];
		if ($fixIBlock)
		{
			$searchParams['iblockfix'] = 'y';
		}

		$result = '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb' . md5($name) . '">';
		$key = '';
		foreach ($values as $key => $val)
		{
			$show = false;
			if ($isCopying)
			{
				$key = 'n' . $index;
				$index++;
			}

			if (is_array($val) && array_key_exists('VALUE', $val))
			{
				$val = $val['VALUE'];
			}

			$elementData = ElementTable::getList([
				'filter' => ['ID' => $val],
				'limit' => 1,
				'select' => ['NAME'],
			]);
			$element = $elementData->fetch();

			$currentSearchParams = $searchParams;
			$currentSearchParams['k'] = $key;
			$searchUrl = $this->urlBuilder->getElementSearchUrl($currentSearchParams);

			$result .= '<tr><td>'
				. '<input name="' . $name . '[' . $key . ']" id="' . $name . '[' . $key . ']" value="' . htmlspecialcharsbx($val) . '" size="5" type="text">'
				. '<input type="button" value="..." onClick="jsUtils.OpenWindow(\'' . $searchUrl . '\', 900, 700);">'
				. '&nbsp;<span id="sp_' . md5($name) . '_' . $key . '" >' . htmlspecialcharsbx($element['NAME']) . '</span>'
				. '</td></tr>';
			unset($searchUrl, $currentSearchParams);

			if ($propertyFields['MULTIPLE'] !== 'Y')
			{
				$bVarsFromForm = true;
				break;
			}
		}

		if (!$bVarsFromForm || $show)
		{
			for ($i = 0; $i < $cnt; $i++)
			{
				$val = '';
				$key = 'n' . $index;
				$index++;

				$currentSearchParams = $searchParams;
				$currentSearchParams['k'] = $key;
				$searchUrl = $this->urlBuilder->getElementSearchUrl($currentSearchParams);

				$result .= '<tr><td>'
					. '<input name="' . $name . '[' . $key . ']" id="' . $name . '[' . $key . ']" value="' . htmlspecialcharsbx($val) . '" size="5" type="text">'
					. '<input type="button" value="..." onClick="jsUtils.OpenWindow(\'' . $searchUrl . '\', 900, 700);">'
					. '&nbsp;<span id="sp_' . md5($name) . '_' . $key . '"></span>'
					. '</td></tr>';
			}
		}

		if ($propertyFields['MULTIPLE'] === 'Y')
		{
			$currentSearchParams = $searchParams;
			$currentSearchParams['k'] = $key;
			$currentSearchParams['m'] = 'y';
			$searchUrl = $this->urlBuilder->getElementSearchUrl($currentSearchParams);
			$result .= '<tr><td>'
				. '<input type="button" value="' . GetMessage('IBLOCK_AT_PROP_ADD') . '..." onClick="jsUtils.OpenWindow(\'' . $searchUrl . '\', 900, 700);">'
				. '<span id="sp_' . md5($name) . '_' . $key . '" ></span>'
				. '</td></tr>';
		}

		$searchUrl = $this->urlBuilder->getElementSearchUrl($searchParams, "&k=n'+MV_" . md5($name) . "+'");

		$result .= '</table>';
		$result .= '<script type="text/javascript">' . "\r\n";
		$result .= 'var MV_' . md5($name) . ' = ' . $index . ";\r\n";
		$result .= 'function InS' . md5($name) . "(id, name){ \r\n";
		$result .= "	oTbl=document.getElementById('tb" . md5($name) . "');\r\n";
		$result .= "	oRow=oTbl.insertRow(oTbl.rows.length-1); \r\n";
		$result .= "	oCell=oRow.insertCell(-1); \r\n";
		$result .= '	oCell.innerHTML='
			. "'<input name=\"" . $name . "[n'+MV_" . md5($name) . "+']\" value=\"'+id+'\" id=\"" . $name . "[n'+MV_" . md5($name) . "+']\" size=\"5\" type=\"text\">'+\r\n"
			. "'<input type=\"button\" value=\"...\" '+\r\n"
			. "'onClick=\"jsUtils.OpenWindow(\'" . $searchUrl . "\', '+\r\n"
			. "' 900, 700);\">'+"
			. "'&nbsp;<span id=\"sp_" . md5($name) . "_n'+MV_" . md5($name) . "+'\" >'+name+'</span>"
			. "';";
		$result .= 'MV_' . md5($name) . '++;';
		$result .= '}';
		$result .= "\r\n</script>";

		return $result;
	}

	protected function getProductFieldValue(array $field)
	{
		$value = $this->entity->getField($field['originalName']);

		if ($field['originalName'] === 'PREVIEW_TEXT')
		{
			$detailTextType = $this->entity->getField('PREVIEW_TEXT_TYPE');
			if ($detailTextType !== 'html')
			{
				$value = HtmlFilter::encode($value);
			}
		}

		if ($field['originalName'] === 'DETAIL_TEXT')
		{
			$detailTextType = $this->entity->getField('DETAIL_TEXT_TYPE');
			if ($detailTextType !== 'html')
			{
				$value = HtmlFilter::encode($value);
			}
		}

		if ($field['originalName'] === 'MEASURE' && $value === null)
		{
			$defaultMeasure = \CCatalogMeasure::getDefaultMeasure();
			if ($defaultMeasure)
			{
				$value = $defaultMeasure['ID'];
			}
		}

		if ($field['originalName'] === 'VAT_ID' && $value === null)
		{
			$value = 'D';
		}

		if (($field['originalName'] === 'ACTIVE_FROM' || $field['originalName'] === 'ACTIVE_TO')
			&& !($this instanceof GridVariationForm)
			&& !empty($value))
		{
			$value = $value->format(\Bitrix\Main\Type\DateTime::getFormat());
		}

		if ($field['originalName'] === 'NAME-CODE')
		{
			$value = [
				'NAME' => $this->entity->getField('NAME'),
				'CODE' => $this->entity->getField('CODE'),
			];
		}

		return $value;
	}

	protected function getPropertyFieldValue(array $field)
	{
		$property = $this->entity->getPropertyCollection()->findById($field['propertyId']);
		$value = $property ? $property->getPropertyValueCollection()->getValues() : null;

		if ($field['type'] === 'html')
		{
			if ($field['multiple'])
			{
				foreach ($value as &$item)
				{
					$item = $item['TEXT'] ?? null;
				}
			}
			else
			{
				$value = $value['TEXT'] ?? null;
			}
		}
		elseif ($property && $property->getUserType() === \CIBlockPropertySequence::USER_TYPE)
		{
			if ($field['multiple'])
			{
				foreach ($value as $valueItemKey => $valueItem)
				{
					if ($valueItem > 0)
					{
						$value[$valueItemKey] = (int)$value;
					}
					else
					{
						$value[$valueItemKey] = $this->getSequence(
							$property->getId(),
							$property->getSetting('IBLOCK_ID')
						);
					}
				}
			}
			else
			{
				if ($value > 0)
				{
					$value = (int)$value;
				}
				else
				{
					$value = $this->getSequence(
						$property->getId(),
						$property->getSetting('IBLOCK_ID')
					);
				}
			}
		}

		return $value;
	}

	protected function getSequence(int $propertyId, int $propertyIblockId): int
	{
		static $sequenceList = [];

		if (empty($sequenceList[$propertyId]))
		{
			$sequence = new \CIBlockSequence($propertyIblockId, $propertyId);
			$isAjaxRequest = \Bitrix\Main\Context::getCurrent()->getRequest()->isAjaxRequest();
			$sequenceList[$propertyId] = $isAjaxRequest ? $sequence->getCurrent() : $sequence->getNext();
		}

		return $sequenceList[$propertyId];
	}

	protected function getMeasures(): array
	{
		static $measures = null;

		if ($measures === null)
		{
			$measures = \Bitrix\Catalog\MeasureTable::getList([
				'select' => ['ID', 'CODE', 'MEASURE_TITLE'],
			])->fetchAll();
		}

		return $measures;
	}

	protected function getVats(): array
	{
		static $vats = null;

		if ($vats === null)
		{
			$vats = \Bitrix\Catalog\VatTable::getList([
				'select' => ['ID', 'NAME'],
				'filter' => ['=ACTIVE' => 'Y'],
			])->fetchAll();
		}

		return $vats;
	}

	protected function getCustomControlParameters(string $fieldName): array
	{
		return [
			'view' => $fieldName . '[VIEW_HTML]',
			'edit' => $fieldName . '[EDIT_HTML]',
			'editList' => $fieldName . '[EDIT_HTML_LIST]',
			'viewList' => $fieldName . '[VIEW_HTML_LIST]',
		];
	}
}
