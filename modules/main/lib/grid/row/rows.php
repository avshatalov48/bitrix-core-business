<?php

namespace Bitrix\Main\Grid\Row;

use Bitrix\Main\Grid\GridRequest;
use Bitrix\Main\Grid\GridResponse;
use Bitrix\Main\Grid\Row\Action\DataProvider;
use Bitrix\Main\Grid\Row\Action\Action;
use Bitrix\Main\Grid\UI\Response\GridResponseFactory;

/**
 * Rows collection.
 *
 * The main task is to work with rows: preparing and process request of actions.
 *
 * @see \Bitrix\Main\Grid\Row\RowAssembler
 * @see \Bitrix\Main\Grid\Row\Action\DataProvider
 * @see \Bitrix\Main\Grid\Grid method `createRows`
 */
class Rows
{
	/**
	 * @var Action[]
	 * @psalm-var array<string, Action>
	 */
	private array $actions;

	protected RowAssembler $rowAssembler;

	/**
	 * @var DataProvider[]
	 */
	protected array $actionsProviders;

	/**
	 * @param RowAssembler $rowAssembler
	 * @param DataProvider[] $actionsProviders
	 */
	public function __construct(RowAssembler $rowAssembler, DataProvider ...$actionsProviders)
	{
		$this->rowAssembler = $rowAssembler;

		$this->actionsProviders = [];
		foreach ($actionsProviders as $provider)
		{
			$this->actionsProviders[] = $provider;
		}
	}

	/**
	 * Gets rows prepared for output.
	 *
	 * @param iterable|array[] $rawRows
	 *
	 * @return array[]
	 */
	public function prepareRows(iterable $rawRows): array
	{
		$result = [];

		foreach ($rawRows as $row)
		{
			$result[] = $this->prepareRow($row);
		}

		return $this->rowAssembler->prepareRows($result);
	}

	/**
	 * Prepare raw value to grid row.
	 *
	 * @param array[] $rawValue
	 *
	 * @return array[] with grid row keys: id, data, actions
	 */
	protected function prepareRow(array $rawValue): array
	{
		$result = [
			'data' => $rawValue,
		];

		if (isset($rawValue['ID']))
		{
			$result['id'] = $rawValue['ID'];
		}

		if (!empty($this->actionsProviders))
		{
			$result['actions'] = [];

			foreach ($this->actionsProviders as $provider)
			{
				$result['actions'] += $provider->prepareControls($rawValue);
			}
		}

		return $result;
	}

	/**
	 * @return Action[]
	 */
	final public function getActions(): array
	{
		if (!isset($this->actions))
		{
			$this->actions = [];

			foreach ($this->actionsProviders as $provider)
			{
				foreach ($provider->prepareActions() as $action)
				{
					$this->actions[$action::getId()] ??= $action;
				}
			}
		}

		return $this->actions;
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

		return $this->getActions()[$id] ?? null;
	}

	/**
	 * Processing all actions of the row.
	 *
	 * @param GridRequest $request
	 *
	 * @return GridResponse|null
	 */
	public function processRequest(GridRequest $request): ?GridResponse
	{
		$result = null;

		if (!check_bitrix_sessid())
		{
			return null;
		}

		$actionId = $request->getRowActionId();
		if (isset($actionId))
		{
			$action = $this->getActionById($actionId);
			if ($action)
			{
				$result = $action->processRequest($request->getHttpRequest());
			}
		}

		return
			isset($result)
				? (new GridResponseFactory)->createFromResult($result)
				: null
		;
	}
}
