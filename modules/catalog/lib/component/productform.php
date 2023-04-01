<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Main;
use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
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

		$controllers[] = $this->getGridController();
		$controllers[] = [
			'name' => 'IBLOCK_SECTION_CONTROLLER',
			'type' => 'iblock_section',
			'config' => [],
		];

		return $controllers;
	}

	protected function getGridController(): array
	{
		return [
			'name' => 'VARIATION_GRID_CONTROLLER',
			'type' => 'variation_grid',
			'config' => [
				'reloadUrl' => '/bitrix/components/bitrix/' . $this->getVariationGridShortComponentName() . '/list.ajax.php',
				'signedParameters' => $this->getVariationGridSignedParameters(),
				'gridId' => $this->getVariationGridId(),
			],
		];
	}

	public function collectFieldConfigs(): array
	{
		$config = parent::collectFieldConfigs();

		$config['right']['elements'][] = $this->getGridFieldConfig();

		return $config;
	}

	protected function getGridFieldConfig(): array
	{
		return [
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
	}

	protected function getVariationGridShortComponentName(): string
	{
		return 'catalog.productcard.variation.grid';
	}

	protected function getVariationGridComponentName(): string
	{
		return 'bitrix:' . $this->getVariationGridShortComponentName();
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

	protected function getCardSettingsItems(): array
	{
		return GridVariationForm::getGridCardSettingsItems();
	}

	protected function buildDescriptions(): array
	{
		return array_merge(
			parent::buildDescriptions(),
			$this->getSectionDescriptions(),
			$this->getNameCodeDescription()
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
				'editable' => $this->isAllowedEditFields(),
				'required' => false,
				'defaultValue' => null,
			],
		];
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
				$picture = \CFile::resizeImageGet(
					$section['PICTURE'],
					['width' => 100, 'height' => 100],
					BX_RESIZE_IMAGE_EXACT,
					false
				);
				$section['PICTURE'] = $picture['src'] ?? null;
				$sectionData[] = $section;
			}
		}

		return $sectionData;
	}
}
