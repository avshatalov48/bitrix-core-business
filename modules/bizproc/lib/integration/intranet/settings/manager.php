<?php

namespace Bitrix\Bizproc\Integration\Intranet\Settings;

use Bitrix\Main\Config\Option;
use Bitrix\Intranet\Settings\Controls\Switcher;
use Bitrix\Intranet\Settings\Controls\Section;
use Bitrix\Main\Localization\Loc;

class Manager
{
	private array $controls = [];

	const WAIT_FOR_CLOSURE_TASK_OPTION = 'crm_activity_wait_for_closure_task';
	const WAIT_FOR_CLOSURE_COMMENTS_OPTION = 'crm_activity_wait_for_closure_comments';

	public function __construct()
	{
		$this->initializeDefaults();
	}

	private function initializeDefaults(): void
	{
		$this->controls['SECTION_MAIN'] = new Section(
			'settings-automation-section-main',
			Loc::getMessage('BIZPROC_AUTOMATION_SETTINGS_SECTION_TITLE_MAIN') ?? '',
			'ui-icon-set --apps',
			canCollapse: false
		);

		$this->controls[self::WAIT_FOR_CLOSURE_TASK_OPTION] = new Switcher(
			'settings-automation-field-' . self::WAIT_FOR_CLOSURE_TASK_OPTION,
			self::WAIT_FOR_CLOSURE_TASK_OPTION,
			Loc::getMessage('BIZPROC_AUTOMATION_SETTINGS_WAIT_FOR_CLOSURE_TASK') ?? '',
			Option::get('bizproc', self::WAIT_FOR_CLOSURE_TASK_OPTION),
			[
				'on' => Loc::getMessage('BIZPROC_AUTOMATION_SETTINGS_WAIT_FOR_CLOSURE_TASK_DESCR') ?? '',
			]
		);

		$this->controls[self::WAIT_FOR_CLOSURE_COMMENTS_OPTION] = new Switcher(
			'settings-automation-field-' . self::WAIT_FOR_CLOSURE_COMMENTS_OPTION,
			self::WAIT_FOR_CLOSURE_COMMENTS_OPTION,
			Loc::getMessage('BIZPROC_AUTOMATION_SETTINGS_WAIT_FOR_CLOSURE_COMMENTS') ?? '',
			Option::get('bizproc', self::WAIT_FOR_CLOSURE_COMMENTS_OPTION),
			[
				'on' => Loc::getMessage('BIZPROC_AUTOMATION_SETTINGS_WAIT_FOR_CLOSURE_COMMENTS_DESCR') ?? '',
			]
		);
	}

	/**
	 * @return array
	 */
	public function getList(): array
	{
		return $this->controls;
	}

	/**
	 * @param string $code
	 * @param $value
	 * @return void
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function saveControlValue(string $code, $value): void
	{
		Option::set('bizproc', $code, $value);
	}

	public function getControlValue(string $code): ?string
	{
		$control = $this->getControl($code);
		if ($control)
		{
			return $control?->getValue();
		}

		return null;
	}

	/**
	 * @param string $code
	 * @return \Bitrix\Intranet\Settings\Controls\Control|null
	 */
	public function getControl(string $code): ?\Bitrix\Intranet\Settings\Controls\Control
	{
		$item = $this->controls[$code] ?? null;
		if ($item)
		{
			return $item;
		}

		return null;
	}
}
