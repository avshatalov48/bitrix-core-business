<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Toolbar\Facade\Toolbar;

class ReportAnalyticsBase extends CBitrixComponent
{

	private $reportGroups = [];

	public function executeComponent()
	{
		if (!\Bitrix\Main\Loader::includeModule('report'))
		{
			$this->showError(Loc::getMessage('RAB_MODULE_NOT_FOUND'));

			return;
		}

		$this->reportGroups = $this->arParams['REPORT_GROUPS'];

		$this->arResult['VIEW_MODE'] = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->get('mode');
		$this->arResult['LEFT_MENU_ITEMS'] = $this->getLeftMenuItemsCollection();
		$this->arResult['MENU_ITEMS'] = $this->getLeftMenuItems();
		$this->arResult['ANALYTIC_BOARD_LEFT_TITLE'] = $this->arParams['PAGE_TITLE'];

		$currentAnalyticBoardKey = $this->getCurrentAnalyticBoardKey();
		$this->arResult['ANALYTIC_BOARD_KEY'] = $currentAnalyticBoardKey;
		$currentAnalyticBoard = $this->getAnalyticBoardByKey($currentAnalyticBoardKey);
		if(!$currentAnalyticBoard)
		{
			$this->showError(Loc::getMessage('RAB_REPORT_NOT_FOUND'));

			return;
		}

		$this->arResult['ANALYTIC_BOARD_TITLE'] = $currentAnalyticBoard ? $currentAnalyticBoard->getTitle() : '';
		$this->arResult['ANALYTIC_BOARD_COMPONENT_NAME'] = $currentAnalyticBoard ? $currentAnalyticBoard->getDisplayComponentName() : '';
		$this->arResult['ANALYTIC_BOARD_COMPONENT_TEMPLATE_NAME'] = $currentAnalyticBoard ? $currentAnalyticBoard->getDisplayComponentTemplate() : '';
		$this->arResult['ANALYTIC_BOARD_COMPONENT_PARAMS'] = $currentAnalyticBoard ? $currentAnalyticBoard->getDisplayComponentParams() : [];
		$this->includeComponentTemplate();
	}

	private function showError($message)
	{
		Toolbar::deleteFavoriteStar();

		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			"bitrix:ui.info.error",
			"",
			[
				'TITLE' => $message,
			]
		);
	}
	/**
	 * @param string $firstBoardBatch
	 * @return null
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getCurrentAnalyticBoardKey($firstBoardBatch = "")
	{
		static $result = null;
		if(is_null($result))
		{
			$analyticBoardKey = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->get('analyticBoardKey');
			if (!$analyticBoardKey)
			{
				$boardList = $this->getAnalyticsBoardsList($firstBoardBatch);
				if (!empty($boardList))
				{
					$analyticBoardKey = $boardList[0]->getMachineKey();
				}
			}
			$result = $analyticBoardKey ?: null;
		}
		return $result;
	}

	private function getLeftMenuItems()
	{
		$batchList = $this->getAnalyticsBoardsBatchList();
		$items = [];
		foreach ($batchList as $batch)
		{
			$items[$batch->getKey()] = [
				'NAME' => $batch->getTitle(),
				'ATTRIBUTES' => [
					'bx-hide-active' => 'Y'
				],
				'CHILDREN' => []
			];
		}
		$boardList = $this->getAnalyticsBoardsList();
		$currentAnalyticBoardKey = $this->getCurrentAnalyticBoardKey($batchList[0]->getKey());
		foreach ($boardList as $board)
		{
			$item = [
				'NAME' => $board->getTitle(),
				'ATTRIBUTES' => [
					'href' => "?analyticBoardKey=" . $board->getBoardKey(),
					'title' => $board->getTitle(),
					'DATA' => [
						'role' => 'report-analytics-menu-item',
						'report-board-key' => $board->getBoardKey(),
						'is-external' => $board->isExternal() ? 'Y' : 'N',
						'external-url' => $board->getExternalUrl(),
						'is-slider-support' => $board->isSliderSupport() ? 'Y' : 'N'
					]
				]
			];

			if ($board->getBoardKey() == $currentAnalyticBoardKey)
			{
				$item['ACTIVE'] = true;
			}

			if ($board->isNestedInBatch())
			{
				$items[$board->getBatchKey()]['CHILDREN'][$board->getBoardKey()] = $item;
			}
			else
			{
				$items[] = $item;
			}
		}

		return $items;
	}

	private function getLeftMenuItemsCollection()
	{
		$batchList = $this->getAnalyticsBoardsBatchList();
		$items = [];
		foreach ($batchList as $batch)
		{
			$items[$batch->getKey()] = [
				'IS_BATCH' => true,
				'BATCH_KEY'	=> $batch->getKey(),
				'BATCH_TITLE' => $batch->getTitle(),
				'BOARD_LIST' => []
			];
		}
		$boardList = $this->getAnalyticsBoardsList();
		foreach ($boardList as $board)
		{
			if ($board->isNestedInBatch())
			{
				$items[$board->getBatchKey()]['BOARD_LIST'][$board->getBoardKey()] = $board;
			}
			else
			{
				$items[] = [
					'IS_BATCH' => false,
					'BOARD' => $board
				];
			}
		}

		return $items;
	}


	/**
	 * @return \Bitrix\Report\VisualConstructor\AnalyticBoard[]
	 */
	private function getAnalyticsBoardsList($boardBatchKey = "")
	{
		$defaultBoardProvider = new \Bitrix\Report\VisualConstructor\RuntimeProvider\AnalyticBoardProvider();
		if($boardBatchKey != "")
		{
			$defaultBoardProvider->addFilter("boardBatchKey", $boardBatchKey);
		}

		foreach ($this->reportGroups as $group)
		{
			$defaultBoardProvider->addFilter('group', $group);
		}

		return $defaultBoardProvider->execute()->getResults();
	}

	/**
	 * @param $key
	 * @return \Bitrix\Report\VisualConstructor\AnalyticBoard | null
	 */
	private function getAnalyticBoardByKey($key)
	{
		$analyticBoardProvider = new \Bitrix\Report\VisualConstructor\RuntimeProvider\AnalyticBoardProvider();
		$analyticBoardProvider->addFilter('boardKey', $key);
		return $analyticBoardProvider->execute()->getFirstResult();

	}
	/**
	 * @return \Bitrix\Report\VisualConstructor\AnalyticBoardBatch[]
	 */
	private function getAnalyticsBoardsBatchList()
	{
		static $result = null;
		if(is_null($result))
		{
			$batchProvider = new \Bitrix\Report\VisualConstructor\RuntimeProvider\AnalyticBoardBatchProvider();

			foreach ($this->reportGroups as $group)
			{
				$batchProvider->addFilter('group', $group);
			}

			$list = $batchProvider->execute()->getResults();
			$result = $list;
		}
		return $result;
	}
}
