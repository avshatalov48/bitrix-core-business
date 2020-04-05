<?php
namespace Bitrix\Report\VisualConstructor\Helper;

use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\RuntimeProvider\ReportProvider;

/**
 * Class Report
 * @package Bitrix\Report\VisualConstructor\Helper
 */
class Report
{
	/**
	 * Build Pseudo report in context of widget.
	 *
	 * @param string $reportHandlerClassName Report handler class name.
	 * @param \Bitrix\Report\VisualConstructor\Entity\Widget $widget Widget entity.
	 * @param bool $isPseudo Marker to set built widget will be pseudo or no.
	 * @return BaseReport|null
	 */
	public static function buildReportHandlerForWidget($reportHandlerClassName, \Bitrix\Report\VisualConstructor\Entity\Widget $widget, $isPseudo = false)
	{
		$reportHandler = ReportProvider::getReportHandlerByClassName($reportHandlerClassName);
		if ($reportHandler instanceof BaseReport)
		{
			/** @var BaseReport $reportHandler */
			$reportHandler = new $reportHandler;
			$reportHandler->setView($widget->getWidgetHandler()->getView());
			$reportHandler->setWidgetHandler($widget->getWidgetHandler());
			if ($isPseudo)
			{
				$reportHandler->getReport()->setGId('_pseudo' . Util::generateUserUniqueId());
			}
			else
			{
				$reportHandler->getReport()->setGId(Util::generateUserUniqueId());
			}

			$reportHandler->getReport()->setWidget($widget);
			$reportHandler->getCollectedFormElements();
			return $reportHandler;
		}
		return null;
	}
}