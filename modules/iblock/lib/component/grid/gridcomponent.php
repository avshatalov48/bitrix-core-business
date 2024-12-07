<?php

namespace Bitrix\Iblock\Component\Grid;

use Bitrix\Iblock\Integration\UI\Grid\General\BaseProvider;
use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\Context;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Grid\MessageType;
use Bitrix\Main\Grid\Options;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Web\Json;
use CBitrixComponent;
use CMain;
use Iterator;
use Throwable;

/**
 * Base component for grid.
 */
abstract class GridComponent extends CBitrixComponent
{
	private array $rowsFilter;
	private Options $gridOptions;

	protected PageNavigation $pagination;

	/**
	 * Grid raw rows.
	 *
	 * @param array $params
	 *
	 * @return array|Iterator
	 */
	abstract protected function getRawRows(array $params);

	/**
	 * Filter object.
	 *
	 * @return Filter|null if grid without filter, return null.
	 */
	abstract protected function getFilter(): ?Filter;

	/**
	 * Total count of rows (taking into account filter).
	 *
	 * @return int
	 */
	abstract protected function getTotalCount(): int;

	/**
	 * Grid provider.
	 *
	 * @return BaseProvider
	 */
	abstract protected function getGridProvider(): BaseProvider;

	/**
	 * Grid options.
	 *
	 * @return Options
	 */
	protected function getGridOptions(): Options
	{
		$this->gridOptions ??= new Options($this->getGridProvider()->getId());

		return $this->gridOptions;
	}

	/**
	 * Init.
	 *
	 * Runs before all operations.
	 *
	 * @return void
	 */
	protected function init(): void
	{
	}

	/**
	 * Init grid rows.
	 *
	 * @return void
	 */
	protected function initRows(): void
	{
		$params = [
			'select' => $this->getRowsSelect(),
			'filter' => $this->getRowsFilter(),
			'order' => $this->getRowsSorting(),
		];

		$pagination = $this->getRowsPagination();
		if ($pagination)
		{
			$params['limit'] = $pagination->getLimit();
			$params['offset'] = $pagination->getOffset();
		}

		$rows = [];
		foreach ($this->getRawRows($params) as $row)
		{
			$rows[] = $this->prepareRow($row);
		}

		$this->getGridProvider()->setRows($rows);

		if ($pagination)
		{
			$this->getGridProvider()->setNavObject($pagination);
		}
	}

	/**
	 * Preparing row.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	protected function prepareRow(array $row): array
	{
		return $this->getGridProvider()->prepareRow($row);
	}

	/**
	 * Init component result.
	 *
	 * @return void
	 */
	protected function initResult(): void
	{
		$this->arResult['GRID'] = $this->getGridProvider()->toArray();
	}

	/**
	 * Init page toolbar (grid filter and buttons).
	 *
	 * @return void
	 */
	protected function initToolbar(): void
	{
	}

	/**
	 * @inheritDoc
	 */
	public function executeComponent()
	{
		try
		{
			$this->init();

			if (!$this->checkReadPermissions())
			{
				throw new AccessDeniedException();
			}

			$this->processActionsGrid();

			$this->initRows();
			$this->initResult();
			$this->initToolbar();

			$this->includeComponentTemplate();
		}
		catch (AccessDeniedException $e)
		{
			$this->arResult['ERROR'] = Loc::getMessage('IBLOCK_GRID_COMPONENT_ERROR_ACCESS_DENIED');
			$this->includeComponentTemplate('error');
		}
	}

	/**
	 * Checks read user permissions.
	 *
	 * @return bool true - if user has access.
	 */
	protected function checkReadPermissions(): bool
	{
		return true;
	}

	/**
	 * Filter for read rows.
	 *
	 * @return array
	 */
	final protected function getRowsFilter(): array
	{
		if (isset($this->rowsFilter))
		{
			return $this->rowsFilter;
		}

		$filterObject = $this->getFilter();
		if ($filterObject)
		{
			$this->rowsFilter = $filterObject->getValue();
		}
		else
		{
			$this->rowsFilter = [];
		}

		$additionalFilter = $this->getAdditionalRowsFilter();
		if (isset($additionalFilter))
		{
			$this->rowsFilter[] = $additionalFilter;
		}

		return $this->rowsFilter;
	}

	protected function getAdditionalRowsFilter(): ?array
	{
		return null;
	}

	/**
	 * Selected fields for read rows.
	 *
	 * @return array
	 */
	protected function getRowsSelect(): array
	{
		$columns = array_column($this->getGridProvider()->getColumns(), 'id');

		return array_filter($columns);
	}

	/**
	 * Order for read rows.
	 *
	 * @return array
	 */
	protected function getRowsSorting(): array
	{
		return $this->getGridOptions()->getSorting()['sort'];
	}

	/**
	 * Pagination for read rows.
	 *
	 * @return PageNavigation|null
	 */
	protected function getRowsPagination(): ?PageNavigation
	{
		if (!isset($this->pagination))
		{
			$this->pagination = new PageNavigation('page');
			$this->pagination->setPageSizes(
				$this->getPageSizes()
			);
			$this->pagination->setRecordCount(
				$this->getTotalCount()
			);

			$this->initFromGrid($this->pagination);
			$this->pagination->initFromUri();
		}

		return $this->pagination;
	}

	/**
	 * Available page sizes.
	 *
	 * @return array
	 */
	protected function getPageSizes(): array
	{
		$gridSizes = $this->getGridProvider()->getPageSizes();
		$gridSizes = array_column($gridSizes, 'VALUE');

		\Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($gridSizes);

		return $gridSizes;
	}

	/**
	 * Init pagination from grid options.
	 *
	 * @param PageNavigation $pagination
	 *
	 * @return void
	 */
	private function initFromGrid(PageNavigation $pagination): void
	{
		$params = $this->getGridOptions()->GetNavParams();
		$pagination->setPageSize((int)$params['nPageSize']);
	}

	/**
	 * Processing the request and searching for grid actions to be called.
	 *
	 * @return void
	 */
	protected function processActionsGrid(): void
	{
		$request = Context::getCurrent()->getRequest();

		$actionName = 'action_button_' . $this->getGridProvider()->getId();
		$action = $request->getPost($actionName);

		if (isset($action))
		{
			try
			{
				$result = $this->processActionGrid($action, $request);
				if (isset($result) && !$result->isSuccess())
				{
					$this->sendErrorsResponse(
						$result->getErrorMessages()
					);
				}
			}
			catch (AccessDeniedException $e)
			{
				$this->sendErrorsResponse([
					Loc::getMessage('IBLOCK_GRID_COMPONENT_ERROR_ACCESS_DENIED')
				]);
			}
			catch (Throwable $e)
			{
				$this->sendErrorsResponse([
					$e->getMessage()
				]);
			}
		}
	}

	/**
	 * Sends JSON response with errors, and end script.
	 *
	 * @param array $messages
	 *
	 * @return never
	 */
	protected function sendErrorsResponse(array $messages): void
	{
		global $APPLICATION;

		/**
		 * @var CMain $APPLICATION
		 */

		$APPLICATION->RestartBuffer();

		foreach ($messages as &$message)
		{
			if (is_array($message) && isset($message['TEXT']))
			{
				$message = [
					'TYPE' => $message['TYPE'] ?? MessageType::ERROR,
					'TEXT' => $message['TEXT'],
				];
			}
			else
			{
				$message = [
					'TYPE' => MessageType::ERROR,
					'TEXT' => (string)$message,
				];
			}
		}
		unset($message);

		CMain::FinalActions(
			Json::encode([
				'messages' => $messages,
			])
		);
	}

	/**
	 * Processing grid action.
	 *
	 * @param string $actionName
	 * @param HttpRequest $request
	 *
	 * @return Result|null
	 */
	protected function processActionGrid(string $actionName, HttpRequest $request): ?Result
	{
		return null;
	}
}
