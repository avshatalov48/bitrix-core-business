<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Iblock\Helpers\Arrays\ArrayFlatterator;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\Integration\UI\EntityEditor\FriendlyPropertyProvider;
use Bitrix\Iblock\Integration\UI\EntityEditor\PropertyProvider;
use Bitrix\Iblock\Model\PropertyFeature;
use Bitrix\Iblock\PropertyFeatureTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionPropertyTable;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\Response\ContentArea\ContentAreaInterface;
use Bitrix\Main\Engine\Response\ContentArea\DataSectionInterface;
use Bitrix\Main\Engine\Response\HtmlContent;
use Bitrix\Main\Localization\Loc;

class IblockPropertyDetails extends CBitrixComponent implements Controllerable
{
	private int $iblockId;
	private int $propertyId;
	private array $entityFields;

	/**
	 * @inheritDoc
	 *
	 * @param array $arParams
	 *
	 * @return array
	 */
	public function onPrepareComponentParams($arParams)
	{
		$arParams['ID'] ??= null;
		$arParams['IBLOCK_ID'] ??= null;

		$arParams['PROPERTY_TYPE'] =
			isset($arParams['PROPERTY_TYPE'])
				? (string)$arParams['PROPERTY_TYPE']
				: null
		;

		$arParams['PROPERTY_USER_TYPE'] =
			isset($arParams['PROPERTY_USER_TYPE'])
				? (string)$arParams['PROPERTY_USER_TYPE']
				: null
		;

		return $arParams;
	}

	public function executeComponent()
	{
		$this->init();

		if (!$this->checkReadPermissions() || !$this->checkParams())
		{
			$this->arResult['ERROR'] = Loc::getMessage('IBLOCK_PROPERTY_DETAILS_COMPONENT_ERROR_ACCESS_DENIED');
		}
		else
		{
			$this->loadEntity();
		}

		if (isset($this->arResult['ERROR']))
		{
			$this->includeComponentTemplate('error');

			return;
		}

		$this->initResult();

		$this->includeComponentTemplate();
	}

	private function init(): void
	{
		$this->iblockId = (int)$this->arParams['IBLOCK_ID'];
		$this->propertyId = (int)$this->arParams['ID'];
	}

	protected function listKeysSignedParameters()
	{
		return [
			'ID',
			'IBLOCK_ID',
		];
	}

	private function checkReadPermissions(): bool
	{
		return true;
	}

	private function isNew(): bool
	{
		return $this->propertyId === 0;
	}

	private function getEntityFields(): array
	{
		if (!isset($this->entityFields))
		{
			if (!$this->loadEntity())
			{
				return [];
			}
		}

		return $this->entityFields;
	}

	private function loadEntity(): bool
	{
		if ($this->isNew())
		{
			$this->entityFields = [
				'ACTIVE' => 'Y',
				'SORT' => 500,
				'IBLOCK_ID' => $this->iblockId,
			];

			return true;
		}

		$entity = PropertyTable::getRow([
			'filter' => [
				'=ID' => $this->propertyId,
				'=IBLOCK_ID' => $this->iblockId,
			],
		]);
		if (!$entity)
		{
			$this->arResult['ERROR'] = Loc::getMessage('IBLOCK_PROPERTY_DETAILS_COMPONENT_ERROR_NOT_FOUND');

			return false;
		}

		// arrayable user settings
		$entity['USER_TYPE_SETTINGS'] = $entity['USER_TYPE_SETTINGS_LIST'];
		unset($entity['USER_TYPE_SETTINGS_LIST']);

		// append section link
		$sectionProperty = SectionPropertyTable::getRow([
			'select' => [
				'SMART_FILTER',
				'DISPLAY_TYPE',
				'DISPLAY_EXPANDED',
				'FILTER_HINT',
			],
			'filter' => [
				'=IBLOCK_ID' => $this->iblockId,
				'=PROPERTY_ID' => $this->propertyId,
			],
		]);
		if (is_array($sectionProperty))
		{
			$entity += $sectionProperty;
		}

		// append features
		$entity['FEATURES'] = [];
		$rows = PropertyFeatureTable::getList([
			'select' => [
				'ID',
				'MODULE_ID',
				'FEATURE_ID',
				'IS_ENABLED',
			],
			'filter' => [
				'=PROPERTY_ID' => $this->propertyId,
			]
		]);
		foreach ($rows as $row)
		{
			$index = PropertyFeature::getIndex($row);
			$entity["FEATURES"][$index] = $row['IS_ENABLED']; // for other
		}

		// flatten fields for editor
		$faltterator = new ArrayFlatterator([
			'FEATURES',
			'USER_TYPE_SETTINGS',
		]);
		$entity = $faltterator->flatten($entity);

		$entity = $this->fillPublicFeature($entity);

		$this->entityFields = $entity;

		return true;
	}

	private function checkParams(): bool
	{
		if ($this->iblockId > 0)
		{
			return !empty(
				IblockTable::getRowById($this->iblockId)
			);
		}

		return false;
	}

	private function initResult(): void
	{
		$provider = $this->getEditorProvider();

		$this->arResult['ID'] = $this->propertyId;
		$this->arResult['IBLOCK_ID'] = $this->iblockId;
		$this->arResult['VALUES'] = $this->getResultValues($provider->getEntityData());
		$this->arResult['FIELDS'] = $provider->getEntityFields();
		$this->arResult['ADDITIONAL_FIELDS'] = $provider->getAdditionalFields();
		$this->arResult['SETTINGS_HTML'] = $provider->getSettingsHtml();
		$this->arResult['BUTTONS'] = $this->getButtons();
	}

	private function getResultValues(array $values): array
	{
		// merge for correct detect <select> value for `PROPERTY_TYPE`.
		$fields = $this->mergeUserType($values);

		return $fields;
	}

	private function getButtons(): array
	{
		$buttons = [
			[
				'TYPE' => 'save',
			],
			[
				'TYPE' => 'cancel',
			],
		];

		$id = (int)($this->entityFields['ID'] ?? 0);
		if ($id > 0)
		{
			$buttons[] = [
				'TYPE' => 'remove',
			];
		}

		return $buttons;
	}

	private function getPropertyType(): string
	{
		return $this->entityFields['PROPERTY_TYPE'] ?? $this->arParams['PROPERTY_TYPE'] ?? PropertyTable::TYPE_STRING;
	}

	private function getPropertyUserType(): ?string
	{
		return $this->entityFields['USER_TYPE'] ?? $this->arParams['PROPERTY_USER_TYPE'];
	}

	private function getEditorProvider(): PropertyProvider
	{
		return new FriendlyPropertyProvider(
			$this->getPropertyType(),
			$this->getPropertyUserType(),
			$this->getEntityFields()
		);
	}

	private function mergeUserType(array $fields): array
	{
		if (!empty($fields['PROPERTY_TYPE']) && !empty($fields['USER_TYPE']))
		{
			$fields['PROPERTY_TYPE'] .= ":{$fields['USER_TYPE']}";
		}

		return $fields;
	}

	/**
	 * @param string $propertyFullType
	 *
	 * @return array in format `[propertyType, userType]`
	 */
	private function parseUserType(string $propertyFullType): array
	{
		$parts = explode(':', $propertyFullType);
		if (count($parts) === 2)
		{
			return $parts;
		}

		return [$propertyFullType, null];
	}

	/**
	 * @param array $entity Entity fields.
	 *
	 * @return array
	 */
	private function fillPublicFeature(array $entity): array
	{
		if ($this->isNew())
		{
			return $entity;
		}

		$value = 'N';

		$listIndex = PropertyFeature::getIndex([
			'MODULE_ID' => 'iblock',
			'FEATURE_ID' => PropertyFeature::FEATURE_ID_LIST_PAGE_SHOW,
		]);
		$detailIndex = PropertyFeature::getIndex([
			'MODULE_ID' => 'iblock',
			'FEATURE_ID' => PropertyFeature::FEATURE_ID_DETAIL_PAGE_SHOW,
		]);
		if (
			isset($entity['FEATURES'][$listIndex])
			|| isset($entity['FEATURES'][$detailIndex])
		)
		{
			$listShow = ($entity['FEATURES'][$listIndex] ?? 'N') === 'Y';
			$detailShow = ($entity['FEATURES'][$detailIndex] ?? 'N') === 'Y';

			if ($listShow || $detailShow)
			{
				$value = 'Y';
			}
		}
		$entity[FriendlyPropertyProvider::FEATURE_PUBLIC_PROPERTY] = $value;

		return $entity;
	}

	//
	// AJAX
	//

	public function configureActions()
	{
		return [
			'save' => [
				'class' => \Bitrix\Iblock\Controller\Property\Action\SaveAction::class,
			],
			'delete' => [
				'class' => \Bitrix\Iblock\Controller\Property\Action\DeleteAction::class,
			],
		];
	}

	/**
	 * Property settings HTML.
	 *
	 * @param string $propertyFullType in format `S`, `S:HTML`, ...
	 *
	 * @return HtmlContent
	 */
	public function getSettingsAction(string $propertyFullType): HtmlContent
	{
		$this->init();

		$parts = $this->parseUserType($propertyFullType);

		$this->entityFields = array_merge(
			[
				'IBLOCK_ID' => $this->iblockId,
				'ID' => $this->propertyId,
				'PROPERTY_TYPE' => $parts[0],
				'USER_TYPE' => $parts[1],
			],
			$this->getAdditionalEntityFields($parts[0], $parts[1])
		);

		$this->initResult();

		try
		{
			ob_start();

			$this->includeComponentTemplate('additional_fields');

			$html = ob_get_contents();
		}
		finally
		{
			ob_end_clean();
		}

		$content = new class($html) implements ContentAreaInterface, DataSectionInterface
		{
			private string $html;
			public array $info = [];

			public function __construct(string $html)
			{
				$this->html = $html;
			}

			public function getHtml()
			{
				return $this->html;
			}

			public function getSectionName(): string
			{
				return 'info';
			}

			public function getSectionData()
			{
				return $this->info;
			}
		};

		$content->info = [
			'showedFields' => array_column($this->arResult['FIELDS'], 'name'),
		];

		return new HtmlContent($content);
	}

	private function getAdditionalEntityFields(string $propertyType, ?string $userType): array
	{
		$defaultValues = [
			'NAME' => '',
			'ACTIVE' => 'Y',
			'SORT' => '500',
			'CODE' => '',
			'DEFAULT_VALUE' => '',
			'ROW_COUNT' => '1',
			'COL_COUNT' => '30',
			'LIST_TYPE' => PropertyTable::LISTBOX,
			'MULTIPLE' => 'N',
			'XML_ID' => '',
			'FILE_TYPE' => '',
			'MULTIPLE_CNT' => PropertyTable::DEFAULT_MULTIPLE_CNT,
			'TMP_ID' => '',
			'LINK_IBLOCK_ID' => '0',
			'WITH_DESCRIPTION' => 'N',
			'SEARCHABLE' => 'N',
			'FILTRABLE' => 'N',
			'IS_REQUIRED' => 'N',
			'USER_TYPE_SETTINGS' => '',
			'HINT' => '',
		];

		$result = null;
		if ($this->propertyId > 0)
		{
			$row = PropertyTable::getRow([
				'filter' => [
					'=ID' => $this->propertyId,
				]
			]);
			if (is_array($row))
			{
				$row['USER_TYPE'] = (string)$row['USER_TYPE'];
				if ($row['USER_TYPE'] === '')
				{
					$row['USER_TYPE'] = null;
				}
				if (
					$row['PROPERTY_TYPE'] === $propertyType
					&& $row['USER_TYPE'] === $userType
				)
				{
					$result = array_intersect_key($row, $defaultValues);
					$result['USER_TYPE_SETTINGS'] = (string)$result['USER_TYPE_SETTINGS'];
					if (
						$result['USER_TYPE_SETTINGS'] !== ''
						&& CheckSerializedData($result['USER_TYPE_SETTINGS'])
					)
					{
						$result['USER_TYPE_SETTINGS'] = unserialize($result['USER_TYPE_SETTINGS'], ['allowed_classes' => false]);
					}
					if (!is_array($result['USER_TYPE_SETTINGS']))
					{
						$result['USER_TYPE_SETTINGS'] = [];
					}
				}
			}
		}

		return
			is_array($result)
				? $result
				: $defaultValues
		;
	}
}
