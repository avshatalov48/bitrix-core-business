<?php

namespace Bitrix\Bizproc\Integration\Intranet\Settings;

use Bitrix\Intranet;
use Bitrix\Main\Localization\Loc;

/**
 * Class for settings provider
 */
class AutomationSettingsPageProvider implements Intranet\Settings\SettingsExternalPageProviderInterface
{
	private $sort = 100;

	public function getType(): string
	{
		return 'automation';
	}

	public function getTitle(): string
	{
		return Loc::getMessage('BIZPROC_AUTOMATION_SETTINGS_PROVIDER_TITLE') ?? '';
	}

	public function setSort(int $sort): static
	{
		$this->sort = $sort;

		return $this;
	}

	public function getSort(): int
	{
		return $this->sort;
	}

	public function getJsExtensions(): array
	{
		return [
			'bizproc.integration.intranet-settings'
		];
	}

	public function getDataManager(array $data = []): Intranet\Settings\SettingsInterface
	{
		return new AutomationSettings($data);
	}
}