<?php

namespace Bitrix\Main\Grid\Column\Editable\Factory;

use Bitrix\Main\Grid\Column\Column;
use Bitrix\Main\Grid\Column\Editable\Config;
use Bitrix\Main\Grid\Column\Editable\CustomConfig;
use Bitrix\Main\Grid\Column\Editable\ListConfig;
use Bitrix\Main\Grid\Column\Editable\MoneyConfig;
use Bitrix\Main\Grid\Column\Editable\RangeConfig;
use Bitrix\Main\Grid\Column\Type;
use Bitrix\Main\Grid\Editor\Types;

final class ConfigFactory
{
	/**
	 * Create config from array with params.
	 *
	 * ATTENTION: Used only for compatibility!
	 *
	 * @param array $params
	 * @param Column|null $column
	 *
	 * @return Config|null
	 */
	public function createFromArray(array $params): ?Config
	{
		$name = (string)($params['NAME'] ?? '');
		if (empty($name))
		{
			return null;
		}

		$type = $params['TYPE'] ?? null;

		if (Types::RANGE === $type)
		{
			$config = new RangeConfig(
				$name,
				$params['min'] ?? null,
				$params['max'] ?? null,
				$params['step'] ?? null,
			);
		}
		elseif (Types::MONEY === $type)
		{
			$currencyList = null;

			if (isset($params['CURRENCY_LIST']) && is_array($params['CURRENCY_LIST']))
			{
				$currencyList = [];
				foreach ($params['CURRENCY_LIST'] as $key => $value)
				{
					if (is_array($value))
					{
						if (isset($value['NAME'], $value['VALUE']))
						{
							$currencyList[$value['NAME']] = $value['VALUE'];
						}
						else
						{
							trigger_error('Invalid currency list format', E_USER_WARNING);
							continue;
						}
					}
					else
					{
						$currencyList[$key] = $value;
					}
				}
			}

			$config = new MoneyConfig($name, $currencyList);

			if (isset($params['HTML_ENTITY']))
			{
				$config->setHtml($params['HTML_ENTITY'] === true);
			}
		}
		elseif (Types::DROPDOWN === $type || Types::MULTISELECT === $type)
		{
			$items = [];

			if (isset($params['items']) && is_array($params['items']))
			{
				$items = $params['items'];
			}
			elseif (isset($params['DATA']['ITEMS']))
			{
				foreach ($params['DATA']['ITEMS'] as $item)
				{
					$items[$item['VALUE']] = $item['NAME'];
				}
			}

			$config = new ListConfig($name, $items, $type);
		}
		elseif (Types::CUSTOM === $type)
		{
			$config = new CustomConfig($name);

			if (isset($params['HTML']))
			{
				$config->setHtml((string)$params['HTML']);
			}
		}
		else
		{
			$config = new Config($name, $type);
		}

		if (isset($params['PLACEHOLDER']))
		{
			$config->setPlaceholder((string)$params['PLACEHOLDER']);
		}

		if (isset($params['DISABLED']))
		{
			$config->setDisabled(
				is_bool($params['DISABLED']) ? $params['DISABLED'] : $params['DISABLED'] === 'Y'
			);
		}

		return $config;
	}

	/**
	 * Create editor config for column.
	 *
	 * Creates only a simple config, despite the specified type.
	 *
	 * @param Column $column
	 *
	 * @return Config
	 */
	public function createFromColumn(Column $column): Config
	{
		return new Config(
			$column->getId(),
			Type::getEditorType($column->getType())
		);
	}
}
