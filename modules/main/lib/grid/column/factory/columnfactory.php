<?php

namespace Bitrix\Main\Grid\Column\Factory;

use Bitrix\Main\Grid\Column\Column;
use Bitrix\Main\Grid\Column\Editable\Factory\ConfigFactory;
use Bitrix\Main\Grid\Column\Type;

class ColumnFactory
{
	private ConfigFactory $editableConfigFactory;

	public function __construct()
	{
		$this->editableConfigFactory = new ConfigFactory;
	}

	public function createFromArray(array $params): ?Column
	{
		if (!isset($params['id']))
		{
			return null;
		}

		$id = $params['id'];

		$editable = $params['editable'] ?? null;
		if (is_array($editable))
		{
			$editable['NAME'] ??= $id;
			$editable['TYPE'] ??= Type::getEditorType(
				(string)($params['type'] ?? '')
			);
			$params['editable'] = $this->editableConfigFactory->createFromArray($editable) ?? false;
		}

		if (isset($params['width']))
		{
			if (is_string($params['width']))
			{
				$re = '/^(\d+)px/';
				if (preg_match($re, $params['width'], $m))
				{
					$params['width'] = (int)$m[1];
				}
				elseif (is_numeric($params['width']))
				{
					$params['width'] = (int)$params['width'];
				}
				else
				{
					$params['width'] = null;
				}
			}
		}

		// empty string and `false` convert to `null`
		if (isset($params['sort']) && empty($params['sort']))
		{
			$params['sort'] = null;
		}

		return new Column($id, $params);
	}
}
