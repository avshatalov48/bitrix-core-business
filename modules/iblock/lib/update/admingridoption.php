<?php
namespace Bitrix\Iblock\Update;

use Bitrix\Main\Update\AdminGridOption as BaseAdminGridOption;
use Bitrix\Catalog;
use Bitrix\Main\Loader;


/**
 * Class AdminGridOption
 * The class is designed to convert the settings of the old administrative grid into a new one.
 *
 * @package Bitrix\Iblock\Update
 */
class AdminGridOption extends BaseAdminGridOption
{
	private const COMMON = 'C';
	private const PERSONAL = 'P';

	protected static $moduleId = "iblock";

	public static function showNewProductNameFieldsAgent(): string
	{
		if (
			!Loader::includeModule('crm')
			|| !Loader::includeModule('catalog')
			|| !Loader::includeModule('bitrix24')
		)
		{
			return '';
		}

		self::showProductField();
		self::showNameCodeField('CATALOG_PRODUCT_CARD', self::COMMON);
		self::showNameCodeField('CATALOG_PRODUCT_CARD', self::PERSONAL);
		self::showNameCodeField('CATALOG_VARIATION_CARD', self::COMMON);
		self::showNameCodeField('CATALOG_VARIATION_CARD', self::PERSONAL);

		return '';
	}

	private static function showProductField(): void
	{
		$crmCatalogId = \CCrmCatalog::GetDefaultID();

		if ($crmCatalogId === 0)
		{
			return;
		}

		$tablePrefix = 'tbl_product_list_';
		$listId = 'CRM_PRODUCT_CATALOG.' . $crmCatalogId;

		if (Catalog\Config\State::isProductCardSliderEnabled())
		{
			$listId .= '.NEW';
		}

		$tableId = $tablePrefix . md5($listId);

		$productGridOptions = new \Bitrix\Main\Grid\Options($tableId);
		$visibleColumns = $productGridOptions->GetVisibleColumns();

		if (empty($visibleColumns))
		{
			return;
		}

		$hasProductField = in_array('CATALOG_PRODUCT', $visibleColumns, true);
		$hasMorePhotoField = in_array('MORE_PHOTO', $visibleColumns, true);

		$oldNameFieldIndex = array_search('NAME', $visibleColumns, true);

		if ($hasProductField)
		{
			if ($oldNameFieldIndex !== false)
			{
				unset($visibleColumns[$oldNameFieldIndex]);
			}
			elseif ($hasMorePhotoField)
			{
				return;
			}
		}
		else
		{
			if ($oldNameFieldIndex !== false)
			{
				$visibleColumns[$oldNameFieldIndex] = 'CATALOG_PRODUCT';
			}
			else
			{
				array_unshift($visibleColumns, 'CATALOG_PRODUCT');
			}
		}

		if (!$hasMorePhotoField)
		{
			$catalogProductFieldIndex = array_search('CATALOG_PRODUCT', $visibleColumns, true);
			$visibleColumnsBefore = array_slice($visibleColumns, 0, $catalogProductFieldIndex + 1);
			$visibleColumnsAfter = array_slice($visibleColumns, $catalogProductFieldIndex + 1);
			$visibleColumns = array_merge($visibleColumnsBefore, ['MORE_PHOTO'], $visibleColumnsAfter);
		}

		$productGridOptions->SetVisibleColumns($visibleColumns);
	}

	private static function showNameCodeField(string $configId, string $scope): void
	{
		if (!Loader::includeModule('ui'))
		{
			return;
		}

		$entityEditorConfiguration = new \Bitrix\UI\Form\EntityEditorConfiguration('ui.form.editor');
		$cardSettings = $entityEditorConfiguration->get($configId, $scope);

		if (!is_array($cardSettings))
		{
			return;
		}

		$hasOldNameField = self::hasField($cardSettings, 'NAME');
		$hasOldCodeField = self::hasField($cardSettings, 'CODE');
		$hasNameCodeField = self::hasField($cardSettings, 'NAME-CODE');

		if ($hasNameCodeField)
		{
			if (!$hasOldNameField && !$hasOldCodeField)
			{
				return;
			}

			if ($hasOldNameField)
			{
				$cardSettings = self::unsetField($cardSettings, 'NAME');
			}
		}
		else
		{
			$nameCodeField = [
				'name' => 'NAME-CODE',
				'optionFlags' => '1',
				'options' => [
					'showCode' => $hasOldCodeField ? 'true' : 'false',
				]
			];

			if (!$hasOldNameField)
			{
				$cardSettings = self::setField($cardSettings, $nameCodeField);
			}
			else
			{
				$cardSettings = self::replaceField($cardSettings, 'NAME', $nameCodeField);
			}
		}

		if ($hasOldCodeField)
		{
			$cardSettings = self::unsetField($cardSettings, 'CODE');
		}

		$entityEditorConfiguration->set($configId, $cardSettings, ['scope' => $scope]);
	}

	private static function hasField(array $cardSettings, string $fieldName): bool
	{
		foreach ($cardSettings as $column)
		{
			foreach ($column['elements'] as $columnElement)
			{
				if ($columnElement['type'] !== 'section')
				{
					continue;
				}

				foreach ($columnElement['elements'] as $element)
				{
					if ($element['name'] === $fieldName)
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	private static function unsetField(array $cardSettings, string $fieldName): array
	{
		foreach ($cardSettings as $columnKey => $column)
		{
			foreach ($column['elements'] as $columnElementKey => $columnElement)
			{
				if ($columnElement['type'] !== 'section')
				{
					continue;
				}

				foreach ($columnElement['elements'] as $elementKey => $element)
				{
					if ($element['name'] === $fieldName)
					{
						unset ($cardSettings[$columnKey]['elements'][$columnElementKey]['elements'][$elementKey]);
						return $cardSettings;
					}
				}
			}
		}

		return $cardSettings;
	}

	private static function replaceField(array $cardSettings, string $oldFieldName, array $newField): array
	{
		foreach ($cardSettings as $columnKey => $column)
		{
			foreach ($column['elements'] as $columnElementKey => $columnElement)
			{
				if ($columnElement['type'] !== 'section')
				{
					continue;
				}

				foreach ($columnElement['elements'] as $elementKey => $element)
				{
					if ($element['name'] === $oldFieldName)
					{
						$cardSettings[$columnKey]['elements'][$columnElementKey]['elements'][$elementKey] = $newField;
						return $cardSettings;
					}
				}
			}
		}

		return $cardSettings;
	}

	private static function setField(array $cardSettings, array $newField): array
	{
		foreach ($cardSettings as $columnKey => $column)
		{
			foreach ($column['elements'] as $columnElementKey => $columnElement)
			{
				if ($columnElement['name'] === 'main')
				{
					array_unshift(
						$cardSettings[$columnKey]['elements'][$columnElementKey]['elements'],
						$newField
					);
					return $cardSettings;
				}
			}
		}

		return $cardSettings;
	}
}