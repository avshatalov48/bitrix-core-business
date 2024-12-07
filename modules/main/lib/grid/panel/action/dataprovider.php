<?php

namespace Bitrix\Main\Grid\Panel\Action;

use Bitrix\Main\Grid\Settings;
use Error;

abstract class DataProvider
{
	/**
	 * @var Action[]
	 */
	private array $actions;
	private Settings $settings;

	/**
	 * @param Settings|null $settings if not used, may be `null`
	 */
	public function __construct(?Settings $settings = null)
	{
		if (isset($settings))
		{
			$this->settings = $settings;
		}
	}

	/**
	 * Settings with additional and configurable params.
	 *
	 * @return Settings
	 *
	 * @throws Error if settings not setted in construct.
	 */
	final protected function getSettings(): Settings
	{
		return $this->settings;
	}

	/**
	 * Actions item.
	 *
	 * @param string $id
	 *
	 * @return Action|null
	 */
	final protected function getActionById(string $id): ?Action
	{
		if (empty($id))
		{
			return null;
		}

		if (!isset($this->actions))
		{
			$this->actions = [];

			foreach ($this->prepareActions() as $action)
			{
				$this->actions[$action::getId()] = $action;
			}
		}

		return $this->actions[$id] ?? null;
	}

	/**
	 * Gets actions items.
	 *
	 * @return Action[]
	 */
	abstract public function prepareActions(): array;

	/**
	 * @return array[]
	 */
	public function prepareControls(): array
	{
		$result = [];

		foreach ($this->prepareActions() as $action)
		{
			$control = $action->getControl();
			if (isset($control))
			{
				$result[] = $control;
			}
		}

		return $result;
	}
}
