<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Main;
use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField;
use Bitrix\Catalog;
use Bitrix\Highloadblock as Highload;
use Bitrix\UI\EntityForm\Control;

class ProductForm extends BaseForm
{
	/** @var \Bitrix\Catalog\v2\Product\BaseProduct */
	protected $entity;

	public function getControllers(): array
	{
		$controllers = parent::getControllers();

		$controllers[] = [
			'name' => 'VARIATION_GRID_CONTROLLER',
			'type' => 'variation_grid',
			'config' => [
				'reloadUrl' => '/bitrix/components/bitrix/catalog.productcard.variation.grid/list.ajax.php',
				'signedParameters' => $this->getVariationGridSignedParameters(),
				'gridId' => $this->getVariationGridId(),
			],
		];
		$controllers[] = [
			'name' => 'IBLOCK_SECTION_CONTROLLER',
			'type' => 'iblock_section',
			'config' => [],
		];

		return $controllers;
	}

	public function collectFieldConfigs(): array
	{
		$config = parent::collectFieldConfigs();

		$config['right']['elements'][] = [
			'name' => 'variation_grid',
			'title' => 'Variation grid',
			'type' => 'included_area',
			'data' => [
				'isRemovable' => false,
				'type' => 'component',
				'componentName' => $this->getVariationGridComponentName(),
				'action' => 'getProductGrid',
				'mode' => 'ajax',
				'signedParametersName' => 'VARIATION_GRID_SIGNED_PARAMETERS',
			],
			'sort' => 100,
		];

		return $config;
	}

	protected function getVariationGridComponentName(): string
	{
		return 'bitrix:catalog.productcard.variation.grid';
	}

	protected function getVariationGridParameters(): array
	{
		return [
			'IBLOCK_ID' => $this->entity->getIblockId(),
			'PRODUCT_ID' => $this->entity->getId(),
			'COPY_PRODUCT_ID' => $this->params['COPY_PRODUCT_ID'] ?? null,
			'EXTERNAL_FIELDS' => $this->params['EXTERNAL_FIELDS'] ?? null,
			'PATH_TO' => $this->params['PATH_TO'] ?? [],
		];
	}

	protected function getVariationGridSignedParameters(): string
	{
		return ParameterSigner::signParameters(
			$this->getVariationGridComponentName(),
			$this->getVariationGridParameters()
		);
	}

	protected function buildDescriptions(): array
	{
		return array_merge(
			parent::buildDescriptions(),
			$this->getSectionDescriptions(),
			$this->getNameCodeDescription(),
			$this->getUserFieldDescriptions()
		);
	}

	protected function getHiddenPropertyCodes(): array
	{
		return [self::MORE_PHOTO];
	}

	protected function getPropertiesConfigElements(): array
	{
		return array_merge(
			[
				['name' => 'IBLOCK_SECTION'],
			],
			parent::getPropertiesConfigElements()
		);
	}

	private function getSectionDescriptions(): array
	{
		return [
			[
				'entity' => 'section',
				'name' => 'IBLOCK_SECTION',
				'title' => Loc::getMessage('CATALOG_C_F_SECTION_SELECTOR_TITLE'),
				'type' => 'iblock_section',
				'editable' => true,
				'required' => false,
				'defaultValue' => null,
			],
		];
	}

	protected function getUserFieldDescriptions(): array
	{
		$result = [];
		$iterator = Main\UserFieldTable::getList([
			'select' => array_merge(
				['*'],
				Main\UserFieldTable::getLabelsSelect()
			),
			'filter' => [
				'=ENTITY_ID' => Catalog\ProductTable::getUfId(),
			],
			'order' => ['SORT' => 'ASC', 'ID' => 'ASC'],
			'runtime' => [
				Main\UserFieldTable::getLabelsReference('', Loc::getCurrentLang()),
			],
		]);
		while ($row = $iterator->fetch())
		{
			$description = [
				'entity' => 'product',
				'name' => $row['FIELD_NAME'],
				'originalName' => $row['FIELD_NAME'],
				'title' => $row['EDIT_FORM_LABEL'] ?? $row['FIELD_NAME'],
				'hint' => $row['HELP_MESSAGE'],
				'type' => $this->getUserFieldType($row),
				'editable' => true,
				'required' => $row['MANDATORY'] === 'Y',
				'multiple' => $row['MULTIPLE'] === 'Y',
				'placeholders' => null,
				'defaultValue' => $row['SETTINGS']['DEFAULT_VALUE'] ?? '',
				'optionFlags' => 1, // showAlways */
				'options' => [
					'showCode' => 'true',
				],
				'data' => [],
			];

			switch ($description['type'])
			{
				case Control\Type::CUSTOM:
					$description['data'] += $this->getCustomControlParameters($description['name']);
					break;
				case Control\Type::MULTI_LIST:
				case Control\Type::LIST:
					$description['data'] += $this->getUserFieldListItems($row);
					break;
			}

			$result[] = $description;
		}

		return $result;
	}

	protected function getUserFieldType(array $userField): string
	{
		$isMultiple = $userField['MULTIPLE'] === 'Y';
		switch ($userField['USER_TYPE_ID'])
		{
			case UserField\Types\BooleanType::USER_TYPE_ID:
				$result = Control\Type::BOOLEAN;
				break;
			case UserField\Types\DateTimeType::USER_TYPE_ID:
			case UserField\Types\DateType::USER_TYPE_ID:
				$result = $isMultiple ? Control\Type::MULTI_DATETIME : Control\Type::DATETIME;
				break;
			case UserField\Types\DoubleType::USER_TYPE_ID:
			case UserField\Types\IntegerType::USER_TYPE_ID:
				$result = $isMultiple ? Control\Type::MULTI_NUMBER : Control\Type::NUMBER;
				break;
			case UserField\Types\EnumType::USER_TYPE_ID:
				$result = $isMultiple ? Control\Type::MULTI_LIST : Control\Type::LIST;
				break;
			case UserField\Types\FileType::USER_TYPE_ID:
				$result = Control\Type::CUSTOM;
				break;
			case UserField\Types\StringFormattedType::USER_TYPE_ID:
				$result = Control\Type::TEXTAREA; // TODO: need replace
				break;
			case UserField\Types\StringType::USER_TYPE_ID:
				$result = $isMultiple ? Control\Type::MULTI_TEXT : Control\Type::TEXT;
				break;
			case UserField\Types\UrlType::USER_TYPE_ID:
				$result = Control\Type::LINK;
				break;
			default:
				if (
					Loader::includeModule('highloadblock')
					&& $userField['USER_TYPE_ID'] === \CUserTypeHlblock::USER_TYPE_ID
				)
				{
					$result = $isMultiple ? Control\Type::MULTI_LIST : Control\Type::LIST;
				}
				else
				{
					$result = Control\Type::TEXT;
				}
		}

		return $result;
	}

	private function getUserFieldListItems(array $userField): array
	{
		if (
			Loader::includeModule('highloadblock')
			&& $userField['USER_TYPE_ID'] === \CUserTypeHlblock::USER_TYPE_ID
		)
		{
			return $this->getUserFieldHighloadblockItems($userField);
		}

		return [];
	}

	private function getUserFieldHighloadblockItems(array $userField): array
	{
		$list = [];
		if (
			$userField['MANDATORY'] === 'N'
			&& $userField['MULTIPLE'] === 'N'
		)
		{
			$list[] = [
				'ID' => '0',
				'VALUE' => '0',
				'NAME' => Loc::getMessage('BX_CATALOG_PRODUCT_USERFIELD_MESS_EMPTY_VALUE')
			];
		}

		$entity = Highload\HighloadBlockTable::compileEntity($userField['SETTINGS']['HLBLOCK_ID']);
		$entityDataClass = $entity->getDataClass();
		$iterator = $entityDataClass::getList([
			'select' => [
				'ID',
				'UF_NAME',
			],
			'order' => ['UF_NAME' => 'ASC'],
		]);
		while ($value = $iterator->fetch())
		{
			$list[] = [
				'ID' =>  $value['ID'],
				'VALUE' => $value['ID'],
				'NAME' => $value['UF_NAME'],
			];
		}
		unset($value, $iterator);
		unset($entityDataClass, $entity);

		return (!empty($list) ? ['items' => $list] : []);
	}

	protected function showSpecificCatalogParameters(): bool
	{
		return $this->entity->isSimple();
	}

	protected function getFieldValue(array $field)
	{
		if ($field['entity'] === 'section')
		{
			return $this->getIblockSectionFieldValue();
		}

		return parent::getFieldValue($field);
	}

	private function getIblockSectionFieldValue(): array
	{
		$sectionIds = $this->entity->getSectionCollection()->getValues();

		if (empty($sectionIds))
		{
			$sectionIds[] = 0;
		}

		return $sectionIds;
	}

	protected function getAdditionalValues(array $values, array $descriptions = []): array
	{
		$additionalValues = parent::getAdditionalValues($values, $descriptions);

		$additionalValues['IBLOCK_SECTION_DATA'] = $this->getIblockSectionServiceFieldValue($values);
		$additionalValues['VARIATION_GRID_SIGNED_PARAMETERS'] = $this->getVariationGridSignedParameters();

		return $additionalValues;
	}

	private function getIblockSectionServiceFieldValue(array $values): array
	{
		$sectionData = [];

		$sections = $values['IBLOCK_SECTION'] ?? [];
		$sections = array_diff($sections, [0]);

		if (!empty($sections))
		{
			$sectionList = \CIBlockSection::GetList(
				[],
				['ID' => $sections],
				false,
				['ID', 'NAME', 'PICTURE']
			);
			while ($section = $sectionList->Fetch())
			{
				$section['PICTURE'] = \CFile::resizeImageGet(
					$section['PICTURE'],
					['width' => 100, 'height' => 100],
					BX_RESIZE_IMAGE_EXACT,
					false
				)['src'];
				$sectionData[] = $section;
			}
		}

		return $sectionData;
	}

	protected function getMainConfigElements(): array
	{
		return array_merge(
			parent::getMainConfigElements(),
			Catalog\Product\SystemField::getFieldsByRestrictions(
				[
					'TYPE' => $this->entity->getType(),
					'IBLOCK_ID' => $this->entity->getIblockId(),
				],
				[
					'RESULT_MODE' => Catalog\Product\SystemField::DESCRIPTION_MODE_UI_FORM_EDITOR,
				]
			)
		);
	}
}
