<?php

namespace Bitrix\Bizproc\Integration\Intranet\Settings;

use Bitrix\Intranet\Settings\Search\SearchEngine;
use Bitrix\Intranet\Settings\AbstractSettings;
use Bitrix\Main\Result;
use Bitrix\Intranet;
use Bitrix\Main\Localization\Loc;

class AutomationSettings extends AbstractSettings
{
	public const TYPE = 'automation';

	public function getType(): string
	{
		return 'automation';
	}

	public function save(): Result
	{
		$manager = new Manager();

		foreach ($this->data as $code => $value)
		{
			$item = $manager->getControl($code);
			if ($item)
			{
				$manager->saveControlValue($code, $value);
			}
		}

		return new Result();
	}

	public function get(): Intranet\Settings\SettingsInterface
	{
		$manager = new Manager();

		$data = [];
		foreach ($manager->getList() as $code => $control)
		{
			$data[$code] = $control;
		}

		return new static($data);
	}

	public function find(string $query): array
	{
		$manager = new Manager();

		$fields = [];
		foreach ($manager->getList() as $control)
		{
			$data = $control->jsonSerialize();
			$fields[$control->getId()] = $data['label'] ?? $data['title'];
		}

		return SearchEngine::initWithDefaultFormatter($fields)->find($query);
	}
}
