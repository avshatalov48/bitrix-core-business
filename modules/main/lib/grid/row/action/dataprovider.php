<?php

namespace Bitrix\Main\Grid\Row\Action;

use Bitrix\Main\Grid\Settings;

abstract class DataProvider
{
	private array $actions;
	private ?Settings $settings;

	/**
	 * @param Settings|null $settings if not used, may be `null`
	 */
	public function __construct(?Settings $settings = null)
	{
		$this->settings = $settings;
	}

	/**
	 * Provider settings.
	 *
	 * @return Settings
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
	 * @return Action[]
	 */
	abstract public function prepareActions(): array;

	/**
	 * Gets row's context menu controls for all actions.
	 *
	 * @param array $rawFields
	 *
	 * @return array
	 */
	public function prepareControls(array $rawFields): array
	{
		$result = [];

		foreach ($this->prepareActions() as $actionsItem)
		{
			$actionConfig = $actionsItem->getControl($rawFields);
			if (isset($actionConfig))
			{
				$result[] = $actionConfig;
			}
		}

		return $result;
	}
}
