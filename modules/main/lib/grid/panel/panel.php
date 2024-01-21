<?php

namespace Bitrix\Main\Grid\Panel;

use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Grid\GridRequest;
use Bitrix\Main\Grid\GridResponse;
use Bitrix\Main\Grid\Panel\Action\DataProvider;
use Bitrix\Main\Grid\Panel\Action\Action;
use Bitrix\Main\Grid\Panel\Action\GroupAction;
use Bitrix\Main\Grid\UI\Response\GridResponseFactory;

class Panel
{
	/**
	 * @var Action[]
	 */
	private array $actions;
	/**
	 * @var array[]
	 */
	private array $controls;
	/**
	 * @var DataProvider[]
	 */
	private array $providers;

	/**
	 * @param DataProvider[] $providers
	 */
	public function __construct(DataProvider ...$providers)
	{
		$this->providers = [];
		foreach ($providers as $provider)
		{
			$this->providers[] = $provider;
		}
	}

	/**
	 * @return Action[]
	 */
	final public function getActions(): array
	{
		if (!isset($this->actions))
		{
			$this->actions = [];

			foreach ($this->providers as $provider)
			{
				foreach ($provider->prepareActions() as $action)
				{
					$this->actions[$action::getId()] ??= $action;
				}
			}
		}

		return $this->actions;
	}

	final protected function getActionById(string $id): ?Action
	{
		if (empty($id))
		{
			return null;
		}

		return $this->getActions()[$id] ?? null;
	}

	/**
	 * @return array[]
	 */
	public function getControls(): array
	{
		if (!isset($this->controls))
		{
			$this->controls = [];

			foreach ($this->providers as $extraProvider)
			{
				$this->controls += $extraProvider->prepareControls();
			}
		}

		return $this->controls;
	}

	/**
	 * Processing all actions of the panel.
	 *
	 * @param GridRequest $request
	 * @param Filter|null $filter
	 *
	 * @return GridResponse|null
	 */
	public function processRequest(GridRequest $request, ?Filter $filter): ?GridResponse
	{
		$result = null;

		if (!check_bitrix_sessid())
		{
			return null;
		}

		// direct actions
		$action = $this->getActionById(
			$request->getPanelActionId() ?? ''
		);
		if ($action)
		{
			$result = $action->processRequest(
				$request->getHttpRequest(),
				$request->isSelectedAllPanelRows(),
				$filter
			);
		}
		else
		{
			// group actions
			$groupAction = $this->getActionById(GroupAction::getId());
			if ($groupAction)
			{
				$result = $groupAction->processRequest(
					$request->getHttpRequest(),
					$request->isSelectedAllPanelGroupRows(),
					$filter
				);
			}
		}

		return
			isset($result)
				? (new GridResponseFactory)->createFromResult($result)
				: null
		;
	}
}
