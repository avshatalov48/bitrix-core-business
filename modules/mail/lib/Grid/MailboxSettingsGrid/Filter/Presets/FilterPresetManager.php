<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Filter\Presets;

use Bitrix\Mail\Grid\MailboxSettingsGrid\Filter\MailboxFilterSettings;

class FilterPresetManager
{
	/**
	 * @var FilterPreset[]
	 */
	private array $availablePresetList;
	private array $disabledPresetList;
	private ?MailboxFilterSettings $filterSettings;

	/**
	 * @param FilterPreset[] $additionalPresets
	 */
	public function __construct(MailboxFilterSettings $filterSettings, array $additionalPresets = [])
	{
		$this->filterSettings = $filterSettings;

		$presetList = array_filter($additionalPresets, static fn ($preset) => $preset instanceof FilterPreset);

		$this->availablePresetList = array_filter($presetList, fn ($preset) => $this->isPresetAvailable($preset));
		$this->disabledPresetList = array_diff_key($presetList, $this->availablePresetList);
	}

	public function getPresetsArrayData(array $defaultFields = []): array
	{
		$result = [];

		foreach ($this->availablePresetList as $preset)
		{
			$result[$preset->getId()] = $preset->toArray($defaultFields);
		}

		return $result;
	}

	/**
	 * @return FilterPreset[]
	 */
	public function getPresets(): array
	{
		return $this->availablePresetList;
	}

	/**
	 * @return FilterPreset[]
	 */
	public function getDisabledPresets(): array
	{
		return $this->disabledPresetList;
	}

	private function isPresetAvailable(FilterPreset $preset): bool
	{
		if (isset($this->filterSettings))
		{
			foreach ($preset->getFilterFields() as $field => $value)
			{
				if ($value === 'Y' && !$this->filterSettings->isFilterAvailable($field))
				{
					return false;
				}
			}
		}

		return true;
	}
}
