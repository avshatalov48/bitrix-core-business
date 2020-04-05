<?php
namespace Bitrix\Report\VisualConstructor\Views\Component;


use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Fields\Valuable\Hidden;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;

class Grid extends GroupedDataGrid
{
	const VIEW_KEY                  = 'grid';
	const MAX_RENDER_REPORT_COUNT   = 15;
	const USE_IN_VISUAL_CONSTRUCTOR = false;

	public function __construct()
	{
		parent::__construct();
		$this->setHeight('auto');
		$this->setLabel('Grid');
		$this->setLogoUri('/bitrix/images/report/visualconstructor/view-number.jpg');
		$this->setPreviewImageUri('/bitrix/images/report/visualconstructor/preview/grid.svg');
		$this->setComponentName('bitrix:report.visualconstructor.widget.content.grid');
		$this->setCompatibleDataType(Common::MULTIPLE_GROUPED_REPORT_TYPE);
		$this->setDraggable(false);
	}

	public function handlerFinallyBeforePassToView($calculatedPerformedData)
	{
		$calculatedPerformedData['data'] =  $calculatedPerformedData;
		$result = array(
			'items' => array()
		);
		if ($allCalculatedReportData = $calculatedPerformedData['data'])
		{
			foreach ($allCalculatedReportData as $reportKey => $reportHandlerResult)
			{
				$items = $reportHandlerResult['items'];

				foreach ($items as $item)
				{
					$result['items'][$item['groupBy']][$reportKey] = $item;
				}
				$result['config']['reportOptions'][$reportKey]['title'] = htmlspecialcharsbx($reportHandlerResult['config']['reportTitle']);
				$result['config']['reportOptions'][$reportKey]['amount'] = !empty($reportHandlerResult['config']['amount']) ? $reportHandlerResult['config']['amount'] : [];

				if (!empty($reportHandlerResult['config']['groupsLabelMap']))
				{
					foreach ($reportHandlerResult['config']['groupsLabelMap'] as $groupKey => $label)
					{
						$result['config']['groupOptions'][$groupKey]['title'] = htmlspecialcharsbx($label);
					}
				}

				if (!empty($reportHandlerResult['config']['groupsLogoMap']))
				{
					foreach ($reportHandlerResult['config']['groupsLogoMap'] as $groupKey => $logUrl)
					{
						$result['config']['groupOptions'][$groupKey]['logo'] = $logUrl;
					}
				}


				if (!empty($reportHandlerResult['config']['groupsTargetUrlMap']))
				{
					foreach ($reportHandlerResult['config']['groupsTargetUrlMap'] as $groupKey => $targetUrl)
					{
						$result['config']['groupOptions'][$groupKey]['link'] = $targetUrl;
					}
				}
			}
		}

		return $result;
	}



	/**
	 * Method to modify widget form elements.
	 *
	 * @param BaseWidget $widgetHandler Widget handler.
	 * @return void
	 */
	public function collectWidgetHandlerFormElements(BaseWidget $widgetHandler)
	{
		parent::collectWidgetHandlerFormElements($widgetHandler);
		$widgetHandler->addFormElement(new Hidden('amountFieldTitle'));
		$widgetHandler->addFormElement(new Hidden('groupingColumnTitle'));
	}

}