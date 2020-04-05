<?php

class ReportAnalyticsBase extends CBitrixComponent
{
	public function executeComponent()
	{
		\Bitrix\Main\Loader::includeModule('report');
		$this->arResult['VIEW_MODE'] = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->get('mode');
		$this->arResult['LEFT_MENU_ITEMS'] = $this->getLeftMenuItemsCollection();
		$this->arResult['MENU_ITEMS'] = $this->getLeftMenuItems();
		$this->arResult['ANALYTIC_BOARD_LEFT_TITLE'] = $this->arParams['PAGE_TITLE'];

		$currentAnalyticBoardKey = $this->getAnalyticBoardKey();
		$this->arResult['ANALYTIC_BOARD_KEY'] = $currentAnalyticBoardKey;
		$currentAnalyticBoard = $this->getAnalyticBoardByKey($currentAnalyticBoardKey);
		$this->arResult['ANALYTIC_BOARD_TITLE'] = $currentAnalyticBoard ? $currentAnalyticBoard->getTitle() : '';
		$this->arResult['BOARD_BUTTONS'] = $currentAnalyticBoard ? $currentAnalyticBoard->getButtons() : [];
		$this->arResult['IS_DISABLED_BOARD'] = $currentAnalyticBoard ? $currentAnalyticBoard->isDisabled() : false;
		$this->arResult['IS_ENABLED_STEPPER'] = $currentAnalyticBoard ? $currentAnalyticBoard->isStepperEnabled() : false;
		$this->arResult['STEPPER_IDS'] = $currentAnalyticBoard ? $currentAnalyticBoard->getSteperIds() : [];
		$this->arResult['ANALYTIC_BOARD_FILTER'] = $currentAnalyticBoard ? $currentAnalyticBoard->getFilter() : new \Bitrix\Report\VisualConstructor\Helper\Filter($currentAnalyticBoardKey);
		$this->includeComponentTemplate();
	}


	/**
	 * @return null
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getAnalyticBoardKey()
	{
		$analyticBoardKey = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->get('analyticBoardKey');
		if (!$analyticBoardKey)
		{
			$boardList = $this->getAnalyticsBoardsList();
			if (!empty($boardList))
			{
				$analyticBoardKey = $boardList[0]->getMachineKey();
			}
		}
		return $analyticBoardKey ?: null;
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
		$currentAnalyticBoardKey = $this->getAnalyticBoardKey();
		foreach ($boardList as $board)
		{
			$item = [
				'NAME' => $board->getTitle(),
				'ATTRIBUTES' => [
					'href' => "?analyticBoardKey=" . $board->getBoardKey(),
					'title' => $board->getTitle(),
					'DATA' => [
						'disabled-board' => $board->isDisabled(),
						'role' => 'report-analytics-menu-item',
						'report-board-key' => $board->getBoardKey()
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
	private function getAnalyticsBoardsList()
	{
		$defaultBoardProvider = new \Bitrix\Report\VisualConstructor\RuntimeProvider\AnalyticBoardProvider();
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
		$batchProvider = new \Bitrix\Report\VisualConstructor\RuntimeProvider\AnalyticBoardBatchProvider();
		$list = $batchProvider->execute()->getResults();
		return $list;
	}
}
