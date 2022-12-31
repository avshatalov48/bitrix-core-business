<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Property\Property;
use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Localization\Loc;

class ServiceForm extends ProductForm
{
	protected const GRID_SIGNED_PARAMETERS_NAME = 'SERVICE_GRID_SIGNED_PARAMETERS';

	/** @var \Bitrix\Catalog\v2\Product\BaseProduct */
	protected $entity;

	protected function getVariationGridShortComponentName(): string
	{
		return 'catalog.productcard.service.grid';
	}

	protected function getGridController(): array
	{
		return [
			'name' => 'SERVICE_GRID_CONTROLLER',
			'type' => 'service_grid',
			'config' => [
				'reloadUrl' => '/bitrix/components/bitrix/' . $this->getVariationGridShortComponentName() . '/list.ajax.php',
				'signedParameters' => $this->getVariationGridSignedParameters(),
				'gridId' => $this->getVariationGridId(),
			],
		];
	}

	protected function getGridFieldConfig(): array
	{
		return [
			'name' => 'service_grid',
			'title' => 'service_grid',
			'type' => 'included_area',
			'data' => [
				'isRemovable' => false,
				'type' => 'component',
				'componentName' => $this->getVariationGridComponentName(),
				'action' => 'getProductGrid',
				'mode' => 'ajax',
				'signedParametersName' => static::GRID_SIGNED_PARAMETERS_NAME,
			],
			'sort' => 100,
		];
	}

	protected function getCatalogParametersSectionConfig(): array
	{
		return [];
	}

	protected function getVariationGridParameters(): array
	{
		return [
			'IBLOCK_ID' => $this->entity->getIblockId(),
			'PRODUCT_ID' => $this->entity->getId(),
			'PRODUCT_TYPE_ID' => $this->entity->getType(),
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

	public function getVariationGridId(): string
	{
		$iblockInfo = ServiceContainer::getIblockInfo($this->entity->getIblockId());

		if ($iblockInfo)
		{
			return 'catalog-product-service-grid-' . $iblockInfo->getProductIblockId();
		}

		return 'catalog-product-service-grid';
	}

	public function getVariationGridClassName(): string
	{
		return GridServiceForm::class;
	}

	protected function getCardSettingsItems(): array
	{
		return GridServiceForm::getGridCardSettingsItems();
	}

	protected function getPropertyDescription(Property $property): array
	{
		$description = parent::getPropertyDescription($property);
		if ($property->getCode() === BaseForm::MORE_PHOTO)
		{
			$description['title'] = Loc::getMessage('CATALOG_SERVICE_FORM_PROPERTY_NAME_MORE_PHOTO');
		}

		return $description;
	}

	public function getCardConfigId(): string
	{
		$iblockInfo = ServiceContainer::getIblockInfo($this->entity->getIblockId());

		if ($iblockInfo)
		{
			return 'catalog-service-card-config-' . $iblockInfo->getProductIblockId();
		}

		return 'catalog-service-card-config';
	}

	public function getVariationGridJsComponentName(): string
	{
		return 'BX.Catalog.ProductServiceGrid';
	}

	protected function showSpecificCatalogParameters(): bool
	{
		return false;
	}

	protected function getAdditionalValues(array $values, array $descriptions = []): array
	{
		$result = parent::getAdditionalValues($values, $descriptions);

		$result[static::GRID_SIGNED_PARAMETERS_NAME] = $this->getVariationGridSignedParameters();
		unset($result['VARIATION_GRID_SIGNED_PARAMETERS']);

		return $result;
	}
}
