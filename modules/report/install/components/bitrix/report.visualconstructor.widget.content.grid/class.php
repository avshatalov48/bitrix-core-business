<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

class ReportVisualconstructorWidgetContentGrid extends \Bitrix\Report\VisualConstructor\Views\Component\BaseViewComponent
{
	public function executeComponent()
	{
		$this->arResult['HEADERS'] = $this->getHeaders();
		$this->arResult['ROWS'] = $this->getRows();
		$this->arResult['GRID_ID'] = $this->getGridId();
		$this->includeComponentTemplate();
	}

	private function getGridId()
	{
		return 'grid_id_' . $this->getWidget()->getId();
	}

	private function getHeaders()
	{
		$headerList = [];
		$result = $this->getResult();

		if (!empty($result['config']['groupOptions']))
		{
			$headerList['groupingColumn'] = [
				'id' => 'groupingColumn',
				'name' => $this->getWidget()->getWidgetHandler()->getFormElement('groupingColumnTitle')->getValue(),
				'default' => true,
			];
		}
		if (!empty($result['config']['reportOptions']))
		{
			foreach ($result['config']['reportOptions'] as $reportHandlerId => $reportOption)
			{
				$headerList['report_id_' . $reportHandlerId] = [
					'id' => 'report_id_' . $reportHandlerId,
					'name' => $reportOption['title'],
					'default' => true
				];
			}
		}
		return $headerList;
	}

	private function getRows()
	{
		$rows = [];
		$result = $this->getResult();

		if (!empty($result['items']))
		{
			foreach ($result['items'] as $key => $reportHandlerResults)
			{
				$templateParams = $result['config']['groupOptions'][$key];
				$columns = [
					'groupingColumn' => $this->getIncludedComponentTemplate('groupingelement', $templateParams)
				];
				foreach ($reportHandlerResults as $reportHandlerKey => $reportHandlerResult)
				{
					$templateParams = $reportHandlerResult;
					$columns['report_id_' . $reportHandlerKey] = $this->getIncludedComponentTemplate('element', $templateParams);
				}
				$rows[] = [
					'id' => $key,
					'has_child' => false,
					'columns' => $columns
				];
			}


			$widget = $this->getWidget();

			$amountColumns = [
				'groupingColumn' => $widget->getWidgetHandler()->getFormElement('amountFieldTitle')->getValue()
			];

			foreach ($result['config']['reportOptions'] as $reportOptionKey => $reportOption)
			{
				$templateParams = $reportOption['amount'];
				$amountColumns['report_id_' . $reportOptionKey] = $this->getIncludedComponentTemplate('element', $templateParams);
			}

			$rows[] = [
				'id' => 'amount',
				'has_child' => false,
				'columns' => $amountColumns
			];

		}

		return $rows;
	}


	private function getIncludedComponentTemplate($templateFileName, $params = [])
	{
		$oldResults = $this->arResult;

		ob_start();
		$this->arResult = $params;
		$this->includeComponentTemplate($templateFileName);
		$result = ob_get_clean();

		$this->arResult = $oldResults;

		return $result;
	}

	private function getResult()
	{
		return $this->arParams['RESULT'];
	}

	/**
	 * @return \Bitrix\Report\VisualConstructor\Entity\Widget
	 */
	private function getWidget()
	{
		return $this->arParams['WIDGET'];
	}
}