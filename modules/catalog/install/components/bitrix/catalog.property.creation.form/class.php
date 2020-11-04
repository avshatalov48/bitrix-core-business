<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Catalog\Component\GridVariationForm;
use Bitrix\Catalog\Product\PropertyCatalogFeature;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorableImplementation;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CatalogPropertyCreationFormComponent extends \CBitrixComponent
	implements Controllerable, Errorable
{
	use ErrorableImplementation;

	private $iblockId;
	private $propertyType;
	private $propertyId = 0;

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
			'PROPERTY_TYPE',
			'PROPERTY_ID',
			'IBLOCK_ID',
		];
	}

	public function onPrepareComponentParams($params)
	{
		if (isset($params['PROPERTY_TYPE']))
		{
			$this->setPropertyType($params['PROPERTY_TYPE']);
		}

		if (isset($params['PROPERTY_ID']))
		{
			$this->setPropertyId($params['PROPERTY_ID']);
		}

		if (isset($params['IBLOCK_ID']))
		{
			$this->setIblockId($params['IBLOCK_ID']);
		}

		return parent::onPrepareComponentParams($params);
	}

	public function executeComponent()
	{
		if ($this->checkModules() && $this->checkPermissions() && $this->checkRequiredParameters())
		{
			if ($this->hasPropertyId())
			{
				$productFactory = ServiceContainer::getProductFactory($this->getIblockId());
				if ($productFactory)
				{
					$newProduct = $productFactory->createEntity();
					$emptyVariation = $newProduct->getSkuCollection()->create();
					$form = new GridVariationForm($emptyVariation);
					$descriptions = $form->getIblockPropertiesDescriptions();
					foreach ($descriptions as $description)
					{
						if ($this->getPropertyId() === (int)$description['propertyId'])
						{
							$this->arResult['PROPERTY_SCHEME'] = $description;
							$type = $description['type'];
							$propertySchemeType = $description['type'];
							if ($description['data']['userType'] === 'directory')
							{
								$type = $description['data']['userType'];
								$propertySchemeType = 'list';
							}
							$this->setPropertyType($type);
							$this->arResult['PROPERTY_SCHEME_TYPE'] = $propertySchemeType;
							break;
						}
					}

					if (!$this->hasPropertyType())
					{
						$this->errorCollection[] = new \Bitrix\Main\Error('Property is not exist.');
					}

				}
			}
			elseif (!$this->hasPropertyType())
			{
				$this->errorCollection[] = new \Bitrix\Main\Error('Property is not exist.');
			}

			if ($this->hasPropertyId())
			{
				$title = Loc::getMessage('CATALOG_EDIT_VARIATION_PROPERTY_TITLE');
			}
			else
			{
				$title = Loc::getMessage('CATALOG_CREATE_VARIATION_PROPERTY_TITLE');
			}

			$GLOBALS['APPLICATION']->setTitle($title);

			if ($this->errorCollection->isEmpty())
			{
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

		if (!Loader::includeModule('iblock'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Module "iblock" is not installed.');

			return false;
		}

		return true;
	}

	protected function checkPermissions()
	{
		return true;
	}

	protected function checkRequiredParameters()
	{
		if (!$this->hasIblockId())
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Iblock id not found.');

			return false;
		}

		if (!$this->hasPropertyType() && !$this->hasPropertyId())
		{
			$this->errorCollection[] = new \Bitrix\Main\Error('Wrong property data.');

			return false;
		}

		return true;
	}

	protected function setIblockId(int $iblockId): self
	{
		$this->iblockId = $iblockId;

		return $this;
	}

	protected function getIblockId(): ?int
	{
		return $this->iblockId;
	}

	private function hasIblockId(): bool
	{
		return $this->getIblockId() > 0;
	}

	protected function setPropertyType($type): self
	{
		$this->propertyType = $type;

		return $this;
	}

	public function getPropertyType(): ?string
	{
		return $this->propertyType;
	}

	private function hasPropertyType(): bool
	{
		$availableProperties = [
			'string', 'multilist', 'list', 'datetime', 'address',
			'money', 'boolean', 'double', 'directory',
		];

		return in_array($this->getPropertyType(), $availableProperties, true);
	}

	protected function hasPropertyId(): bool
	{
		return $this->getPropertyId() > 0;
	}

	protected function setPropertyId($id): self
	{
		$this->propertyId = (int)$id;

		return $this;
	}

	public function getPropertyId(): ?int
	{
		return $this->propertyId;
	}

	private function getPropertyVariationFeatureList(): array
	{
		return [
			[
				'MODULE_ID' => 'catalog',
				'FEATURE_ID' => PropertyCatalogFeature::FEATURE_ID_OFFER_TREE_PROPERTY,
				'IS_ENABLED' => 'Y',
			],
			[
				'MODULE_ID' => 'catalog',
				'FEATURE_ID' => PropertyCatalogFeature::FEATURE_ID_BASKET_PROPERTY,
				'IS_ENABLED' => 'Y',
			],
		];
	}

	public function addPropertyAction(array $fields = []): ?array
	{
		if ($this->checkModules() && $this->checkPermissions() && $this->checkRequiredParameters())
		{
			CBitrixComponent::includeComponentClass("bitrix:catalog.productcard.details");
			$fields['IBLOCK_ID'] = $this->getIblockId();
			$fields['FEATURES'] = $this->getPropertyVariationFeatureList();
			$result = \CatalogProductDetailsComponent::addProperty($fields);
			if (!$result->isSuccess())
			{
				$this->errorCollection->add($result->getErrors());
			}

			$newId = $result->getId();
			$code = null;
			$productFactory = ServiceContainer::getProductFactory($this->getIblockId());
			if ($productFactory)
			{
				$newProduct = $productFactory->createEntity();
				$emptyVariation = $newProduct->getSkuCollection()->create();
				$form = new GridVariationForm($emptyVariation);
				$descriptions = $form->getIblockPropertiesDescriptions();
				foreach ($descriptions as $property)
				{
					if ((int)$property['propertyId'] === $newId)
					{
						$code = $property['name'];
						break;
					}
				}
			}

			return [
				'PROPERTY_ID' => $newId,
				'PROPERTY_GRID_CODE' => $code,
			];
		}

		return null;
	}

	public function updatePropertyAction(array $fields): ?int
	{
		if ($this->checkModules() && $this->checkPermissions() && $this->checkRequiredParameters())
		{
			CBitrixComponent::includeComponentClass("bitrix:catalog.productcard.details");
			$id = (int)$fields['ID'];
			if ($id > 0)
			{
				$result = \CatalogProductDetailsComponent::updateProperty($id, $fields);
				if (!$result->isSuccess())
				{
					$this->errorCollection->add($result->getErrors());
				}
			}
		}

		return null;
	}
}