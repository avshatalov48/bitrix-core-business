<?php

namespace Bitrix\Main\UI\Filter;

use Bitrix\Main\Localization\Loc;

class FieldAdapter
{
	public const STRING = 'string';
	public const TEXTAREA = 'textarea';
	public const NUMBER = 'number';
	public const DATE = 'date';
	public const LIST = 'list';
	public const DEST_SELECTOR = 'dest_selector';
	public const ENTITY_SELECTOR = 'entity_selector';
	public const CUSTOM = 'custom';
	public const CUSTOM_ENTITY = 'custom_entity';
	public const CUSTOM_DATE = 'custom_date';
	public const CHECKBOX = 'checkbox';

	/**
	 * @param array $sourceField
	 * @param mixed $filterId
	 * @return array
	 */
	public static function adapt(array $sourceField, $filterId = ''): array
	{
		$sourceField = static::normalize($sourceField);
		switch ($sourceField['type'])
		{
			case self::LIST:
				$items = [];

				if (isset($sourceField['items']) && !empty($sourceField['items']) && is_array($sourceField['items']))
				{
					foreach ($sourceField['items'] as $selectItemValue => $selectItem)
					{
						if (is_array($selectItem))
						{
							$selectItem['VALUE'] = (string)$selectItemValue;
							$listItem = $selectItem;
						}
						else
						{
							$listItem = [
								'NAME' => $selectItem,
								'VALUE' => (string)$selectItemValue,
							];
						}

						$items[] = $listItem;
					}
				}

				if ($sourceField['params']['multiple'])
				{
					$field = Field::multiSelect(
						$sourceField['id'],
						$items,
						[],
						$sourceField['name'],
						$sourceField['placeholder']
					);
				}
				else
				{
					if (empty($items[0]['VALUE']) && empty($items[0]['NAME']))
					{
						$items[0]['NAME'] = Loc::getMessage('MAIN_UI_FILTER__NOT_SET');
					}

					if (!empty($items[0]['VALUE']) && !empty($items[0]['NAME']))
					{
						array_unshift(
							$items,
							[
								'NAME' => Loc::getMessage('MAIN_UI_FILTER__NOT_SET'),
								'VALUE' => '',
							]
						);
					}

					$field = Field::select(
						$sourceField['id'],
						$items,
						[],
						$sourceField['name'],
						$sourceField['placeholder']
					);
				}

				break;

			case self::DATE:
				$field = Field::date(
					$sourceField['id'],
					DateType::NONE,
					[],
					$sourceField['name'],
					$sourceField['placeholder'],
					($sourceField['time'] ?? false),
					($sourceField['exclude'] ?? []),
					($sourceField['include'] ?? []),
					($sourceField['allow_years_switcher'] ?? false),
					($sourceField['messages'] ?? [])
				);
				break;

			case self::NUMBER:
				$field = Field::number(
					$sourceField['id'],
					NumberType::SINGLE,
					[],
					$sourceField['name'],
					$sourceField['placeholder'],
					($sourceField['exclude'] ?? []),
					($sourceField['include'] ?? []),
					($sourceField['messages'] ?? [])
				);

				break;

			case self::CUSTOM:
				$field = Field::custom(
					$sourceField['id'],
					$sourceField['value'],
					$sourceField['name'],
					$sourceField['placeholder'],
					($sourceField['style'] ?? false)
				);
				break;

			case self::CUSTOM_ENTITY:
				$field = Field::customEntity(
					$sourceField['id'],
					$sourceField['name'],
					$sourceField['placeholder'],
					$sourceField['params']['multiple']
				);
				break;

			case self::CHECKBOX:
				$values = isset($sourceField['valueType']) && $sourceField['valueType'] === 'numeric'
					? [
						'1',
						'0',
					]
					: [
						'Y',
						'N',
					]
				;

				$items = [
					[
						'NAME' => Loc::getMessage('MAIN_UI_FILTER__NOT_SET'),
						'VALUE' => '',
					],
					[
						'NAME' => Loc::getMessage('MAIN_UI_FILTER__YES'),
						'VALUE' => $values[0],
					],
					[
						'NAME' => Loc::getMessage('MAIN_UI_FILTER__NO'),
						'VALUE' => $values[1],
					]
				];

				$field = Field::select(
					$sourceField['id'],
					$items,
					$items[0],
					$sourceField['name'],
					$sourceField['placeholder']
				);

				break;

			case self::CUSTOM_DATE:
				$field = Field::customDate($sourceField);
				break;

			case self::DEST_SELECTOR:
				$field = Field::destSelector(
					$sourceField['id'],
					$sourceField['name'],
					$sourceField['placeholder'],
					$sourceField['params']['multiple'],
					$sourceField['params'],
					($sourceField['lightweight'] ?? false),
					$filterId
				);
				break;

			case self::ENTITY_SELECTOR:
				$field = Field::entitySelector(
					(string)($sourceField['id'] ?? ''),
					(string)($sourceField['name'] ?? ''),
					(string)($sourceField['placeholder'] ?? ''),
					(isset($sourceField['params']) && is_array($sourceField['params'])) ? $sourceField['params'] : [],
					(string)$filterId
				);
				break;

			case self::TEXTAREA:
				$field = Field::textarea(
					$sourceField['id'],
					'',
					$sourceField['name'],
					$sourceField['placeholder']
				);
				break;

			case self::STRING:
			default:
				$field = Field::string(
					$sourceField['id'],
					'',
					$sourceField['name'],
					$sourceField['placeholder']
				);
				break;
		}

		if (!empty($sourceField['html']))
		{
			$field['HTML'] = $sourceField['html'];
		}
		if (!empty($sourceField['additionalFilter']))
		{
			$field['ADDITIONAL_FILTER_ALLOWED'] = $sourceField['additionalFilter'];
		}

		if (isset($sourceField['sectionId']) && $sourceField['sectionId'] !== '')
		{
			$field['SECTION_ID'] = $sourceField['sectionId'];
		}
		if (!empty($sourceField['icon']))
		{
			$field['ICON'] = $sourceField['icon'];
		}

		return $field;
	}

	/**
	 * @param array $sourceField
	 * @return array
	 */
	public static function normalize(array $sourceField): array
	{
		if (!isset($sourceField['type']))
		{
			$sourceField['type'] = self::STRING;
		}
		if (!isset($sourceField['placeholder']))
		{
			$sourceField['placeholder'] = '';
		}
		if (!isset($sourceField['params']) || !is_array($sourceField['params']))
		{
			$sourceField['params'] = [];
		}
		if (!isset($sourceField['params']['multiple']))
		{
			$sourceField['params']['multiple'] = false;
		}
		else
		{
			$sourceField['params']['multiple'] = (
				$sourceField['params']['multiple'] === 'Y'
				|| $sourceField['params']['multiple'] === true
			);
		}

		return $sourceField;
	}
}
