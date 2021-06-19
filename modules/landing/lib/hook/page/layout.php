<?php

namespace Bitrix\Landing\Hook\Page;

use Bitrix\Landing\Field;
use Bitrix\Landing\Hook\Page;
use Bitrix\Landing\Manager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);

class Layout extends Page
{
	protected const DEFAULT_BREAKPOINT = 'tablet';
	/**
	 * Return view types.
	 * @return array
	 */
	protected function getItems(): array
	{
		return [
			'mobile' => 'on mobile',
			'tablet' => 'on tablet',
			'desktop' => 'on desktop',
			'all' => 'never',
		];
	}

	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap(): array
	{
		return [
			'BREAKPOINT' => new Field\Select('BREAKPOINT', [
				'title' => 'Adaptive view',
				'options' => $this->getItems(),
			]),
		];
	}

	/**
	 * Title of Hook, if you want.
	 * @return string
	 */
	public function getTitle(): string
	{
		return 'Layout breakpoint title';
//		return Loc::getMessage('LANDING_HOOK_VIEW_NAME');
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled(): bool
	{
		if ($this->issetCustomExec())
		{
			return true;
		}

		return (bool)$this->fields['BREAKPOINT']->getValue();
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec(): void
	{
		if ($this->execCustom())
		{
			return;
		}

		$breakpoint = trim(HtmlFilter::encode($this->fields['BREAKPOINT']));
		if(!$breakpoint)
		{
			$breakpoint = self::DEFAULT_BREAKPOINT;
		}

		// Manager::setPageView('BodyClass', $bodyClass);
		Manager::setPageView('MainClass', 'landing-layout-breakpoint--' . $breakpoint);
	}
}