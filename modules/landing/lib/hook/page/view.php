<?php

namespace Bitrix\Landing\Hook\Page;

use Bitrix\Landing\Field;
use Bitrix\Landing\Hook\Page;
use Bitrix\Landing\Manager;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class View extends Page
{
	/**
	 * Return view types.
	 * @return array
	 */
	public function getItems(): array
	{
		static $items = [];
		if (empty($items))
		{
			$items = [
				'no' => Loc::getMessage('LANDING_HOOK_VIEW_TYPE_NO'),
				'ltr' => Loc::getMessage('LANDING_HOOK_VIEW_TYPE_LTR'),
				'all' => Loc::getMessage('LANDING_HOOK_VIEW_TYPE_ALL'),
				'mobile' => Loc::getMessage('LANDING_HOOK_VIEW_TYPE_MOBILE'),
				'adaptive' => Loc::getMessage('LANDING_HOOK_VIEW_TYPE_ADAPTIVE'),
			];
		}
		return $items;
	}

	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap(): array
	{
		return [
			'USE' => new Field\Checkbox('USE', [
				'title' => Loc::getMessage('LANDING_HOOK_VIEW_USE'),
			]),
			'TYPE' => new Field\Select('TYPE', [
				'title' => Loc::getMessage('LANDING_HOOK_VIEW_TYPE'),
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
		return Loc::getMessage('LANDING_HOOK_VIEW_NAME');
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

		return $this->fields['USE']->getValue() === 'Y';
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

		$type = trim($this->fields['TYPE']);
		$bodyClass = '';
		$mainClasses = '';

		if ($type === 'ltr')
		{
			$bodyClass = 'landing-viewtype--ltr';
			$mainClasses = 'g-pt-6 g-px-10 g-pt-30--md g-px-50--md';
		}
		elseif ($type === 'all')
		{
			$bodyClass = 'landing-viewtype--all';
			$mainClasses = 'g-py-6 g-px-10 g-py-30--md g-px-50--md';
		}
		elseif ($type === 'mobile')
		{
			$bodyClass = 'landing-viewtype--mobile';
			$mainClasses = 'mx-auto';
		}
		elseif ($type === 'adaptive')
		{
			$bodyClass = 'landing-viewtype--adaptive';
			// $mainClasses = 'mx-auto';
		}

		Manager::setPageView('BodyClass', $bodyClass);
		Manager::setPageView('MainClass', $mainClasses);
	}
}