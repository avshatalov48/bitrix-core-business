<?php

namespace Bitrix\Main\UserField\Types;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\BooleanField;
use CUserTypeManager;

Loc::loadMessages(__FILE__);

/**
 * Class BooleanType
 * @package Bitrix\Main\UserField\Types
 */
class BooleanType extends BaseType
{
	public const
		USER_TYPE_ID = 'boolean',
		RENDER_COMPONENT = 'bitrix:main.field.boolean';

	public const
		DISPLAY_DROPDOWN = 'DROPDOWN',
		DISPLAY_RADIO = 'RADIO',
		DISPLAY_CHECKBOX = 'CHECKBOX';

	/**
	 * @return array
	 */
	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => Loc::getMessage('USER_TYPE_BOOL_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_INT,
		];
	}

	/**
	 * @return string
	 */
	public static function getDbColumnType(): string
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		return $helper->getColumnTypeByField(new \Bitrix\Main\ORM\Fields\IntegerField('x'));
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function prepareSettings(array $userField): array
	{
		$label = ($userField['SETTINGS']['LABEL'] ?? ['', '']);

		if($label[0] === Loc::getMessage('MAIN_NO'))
		{
			$label[0] = '';
		}
		if($label[1] === Loc::getMessage('MAIN_YES'))
		{
			$label[1] = '';
		}

		$labelCheckbox = ($userField['SETTINGS']['LABEL_CHECKBOX'] ?? '');
		if($labelCheckbox === Loc::getMessage('MAIN_YES'))
		{
			$labelCheckbox = '';
		}


		$def = (int)($userField['SETTINGS']['DEFAULT_VALUE'] ?? 0);
		if($def !== 1)
		{
			$def = 0;
		}

		$disp = ($userField['SETTINGS']['DISPLAY'] ?? '');
		if($disp !== 'CHECKBOX' && $disp !== 'RADIO' && $disp !== 'DROPDOWN')
		{
			$disp = 'CHECKBOX';
		}

		return [
			'DEFAULT_VALUE' => $def,
			'DISPLAY' => $disp,
			'LABEL' => [
				$label[0], $label[1]
			],
			'LABEL_CHECKBOX' => $labelCheckbox,
		];
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function getLabels(array $userField): array
	{
		$label = [Loc::getMessage('MAIN_NO'), GetMessage('MAIN_YES')];
		if(isset($userField['SETTINGS']['LABEL']) && is_array($userField['SETTINGS']['LABEL']))
		{
			foreach($label as $key => $value)
			{
				if($userField['SETTINGS']['LABEL'][$key] <> '')
				{
					$label[$key] = $userField['SETTINGS']['LABEL'][$key];
				}
			}
		}

		return $label;
	}

	/**
	 * @param array $userField
	 * @param array $additionalParameters
	 * @return array
	 */
	public static function getFilterData(array $userField, array $additionalParameters): array
	{
		return [
			'id' => $additionalParameters['ID'],
			'name' => $additionalParameters['NAME'],
			'type' => 'list',
			'items' => [
				'Y' => Loc::getMessage('MAIN_YES'),
				'N' => Loc::getMessage('MAIN_NO')
			],
			'filterable' => ''
		];
	}

	/**
	 * @param $userField
	 * @param $value
	 * @return int
	 */
	public static function onBeforeSave($userField, $value): int
	{
		$result = 0;
		if($value)
		{
			$result = 1;
		}
		return $result;
	}

	/**
	 * Return all display types
	 * @return array
	 */
	final public static function getAllDisplays(): array
	{
		$reflection = new \ReflectionClass(__CLASS__);
		$constants = $reflection->getConstants();
		$result = [];
		foreach($constants as $name => $value)
		{
			if(mb_strpos($name, 'DISPLAY_') === 0)
			{
				$result[$name] = $value;
			}
		}
		return $result;
	}

	public static function getAdminListViewHtmlMulty(array $userField, ?array $additionalParameters): string
	{
		return parent::renderAdminListView($userField, $additionalParameters);
	}

	public static function isMandatorySupported(): bool
	{
		return false;
	}

	public static function isMultiplicitySupported(): bool
	{
		return false;
	}

	public static function checkFields(array $userField, $value): array
	{
		return [];
	}

	public static function getEntityField($fieldName, $fieldParameters)
	{
		$fieldParameters['values'] = [0, 1];

		$field = (new BooleanField($fieldName, $fieldParameters))
			->configureNullable();

		return $field;
	}
}
