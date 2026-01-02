<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Filter;

use Bitrix\Mail\Grid\MailboxSettingsGrid\Filter\Presets\FilterPresetManager;
use Bitrix\Main\Filter\DataProvider;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Filter\Field;
use Bitrix\Main\UI\Filter\Options;

class MailboxFilter extends Filter
{
	private Options $filterOptions;
	private array $filterPresets;
	private ?MailboxFilterSettings $filterSettings = null;
	protected $uiFilterServiceFields = [
		'EMAIL',
		'OWNER',
		'LAST_SYNC',
		'CRM_INTEGRATION',
		'ACCESS_USERS',
		'CRM_QUEUE',
		'DISK_SIZE',
		'SENDER_NAME',
	];

	public function __construct(
		string $ID,
		DataProvider $entityDataProvider,
		?array $extraDataProviders = null,
		?array $params = null,
		array $additionalPresets = [],
	) {
		parent::__construct($ID, $entityDataProvider, $extraDataProviders, $params);

		if (isset($params['FILTER_SETTINGS']) && $params['FILTER_SETTINGS'] instanceof MailboxFilterSettings)
		{
			$this->filterSettings = $params['FILTER_SETTINGS'];
		}

		$presetManager = new FilterPresetManager($this->filterSettings, $additionalPresets);
		$this->filterPresets = $presetManager->getPresets();

		$this->filterOptions = new Options(
			$this->getId(),
			$presetManager->getPresetsArrayData(),
		);

		foreach ($presetManager->getPresets() as $preset)
		{
			$this->filterOptions->setFilterSettings(
				$preset->getId(),
				$preset->toArray(),
			);
		}

		foreach ($presetManager->getDisabledPresets() as $preset)
		{
			$this->filterOptions->deleteFilter($preset->getId());
		}

		$this->filterOptions->save();
	}

	public function getFilterSettings(): ?MailboxFilterSettings
	{
		return $this->filterSettings;
	}

	/**
	 * @return array of default and saved presets
	 */
	public function getFilterPresets(): array
	{
		return array_merge(
			$this->filterOptions->getPresets(),
			$this->filterOptions->getDefaultPresets(),
		);
	}

	public function getDefaultFilterPresets(): array
	{
		return $this->filterPresets;
	}
}
