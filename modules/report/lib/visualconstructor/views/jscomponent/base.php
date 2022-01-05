<?php

namespace Bitrix\Report\VisualConstructor\Views\JsComponent;

use Bitrix\Report\VisualConstructor\Entity\Widget;
use Bitrix\Report\VisualConstructor\Helper\Widget as WidgetHelper;
use Bitrix\Report\VisualConstructor\View;

/**
 * Base class for js "components: to render in content in widgets of dashboard
 * @package Bitrix\Report\VisualConstructor\Views\JsComponent
 */
abstract class Base extends View
{
	/**
	 * Method to modify Content which pass to widget view, in absoulte end.
	 *
	 * @param Widget $widget Widget entity.
	 * @param bool $withCalculatedData Marker for calculate or no data in widget.
	 * @return array
	 */
	public function prepareWidgetContent(Widget $widget, $withCalculatedData = false)
	{
		$resultWidget = parent::prepareWidgetContent($widget, $withCalculatedData);
		try
		{
			$calculatedPerformedData = $withCalculatedData ? WidgetHelper::getCalculatedPerformedData($this, $widget) : array();
			$resultWidget['content']['params']['data'] = $this->handlerFinallyBeforePassToView($calculatedPerformedData);
			$resultWidget['content']['params']['data']['isFilled'] = !empty($resultWidget['content']['params']['data']);
			$resultWidget['content']['params']['color'] = $resultWidget['config']['color'];
			$resultWidget['content']['params']['errors'] = $calculatedPerformedData['errors'];
		}
		catch (\Throwable $exception)
		{
			$resultWidget['content']['params']['errors'] = [$exception->getMessage()];
		}

		return $resultWidget;
	}
}