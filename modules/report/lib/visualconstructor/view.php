<?php

namespace Bitrix\Report\VisualConstructor;

use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Entity\Widget;
use Bitrix\Report\VisualConstructor\Fields\ComplexHtml;
use Bitrix\Report\VisualConstructor\Fields\Container;
use Bitrix\Report\VisualConstructor\Fields\Div;
use Bitrix\Report\VisualConstructor\Fields\Valuable\ColorPicker;
use Bitrix\Report\VisualConstructor\Fields\Valuable\Hidden;
use Bitrix\Report\VisualConstructor\Fields\Valuable\LabelField;
use Bitrix\Report\VisualConstructor\Fields\Valuable\PreviewBlock;
use Bitrix\Report\VisualConstructor\Fields\Valuable\TimePeriod;
use Bitrix\Report\VisualConstructor\Handler\BaseReport;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;
use Bitrix\Report\VisualConstructor\Handler\EmptyReport;
use Bitrix\Report\VisualConstructor\Helper\Report;
use Bitrix\Report\VisualConstructor\RuntimeProvider\ReportProvider;

/**
 * Class View
 * @package Bitrix\Report\VisualConstructor
 */
abstract class View
{
	const VIEW_KEY                   = '';
	const MAX_RENDER_REPORT_COUNT    = 0;
	const DEFAULT_EMPTY_REPORT_COUNT = 1;
	const USE_IN_VISUAL_CONSTRUCTOR  = true;

	private $label;
	private $logoUri;
	private $previewImageUri;
	private $compatibleDataType;
	private $height;
	private $jsClassName;
	private $draggable = true;
	private $horizontalResizable = true;

	/**
	 * @return mixed
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * Setter for height.
	 * Can be 'auto'
	 *
	 * @param mixed $height Height of widget with header 55px.
	 * @return void
	 */
	public function setHeight($height)
	{
		$this->height = $height;
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return static::VIEW_KEY;
	}

	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * Setter for label.
	 *
	 * @param string $label Label text.
	 * @return void
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}

	/**
	 * @return string
	 */
	public function getLogoUri()
	{
		return $this->logoUri;
	}

	/**
	 * Setter for miniature src.
	 *
	 * @param string $logoUri Path to miniature image.
	 * @return void
	 */
	public function setLogoUri($logoUri)
	{
		$this->logoUri = $logoUri;
	}

	/**
	 * @return string
	 */
	public function getCompatibleDataType()
	{
		return $this->compatibleDataType;
	}

	/**
	 * Set compatible data type.
	 *
	 * @param string $compatibleDataType Data type which compatible with view type.
	 * @return void
	 */
	public function setCompatibleDataType($compatibleDataType)
	{
		$this->compatibleDataType = $compatibleDataType;
	}

	/**
	 * Return list of compatible view type keys, to this view types can switch without reform configurations.
	 *
	 * @return array
	 */
	public function getCompatibleViewTypes()
	{
		return array();
	}

	/**
	 * Handle all data prepared for this view.
	 *
	 * @param array $dataFromReport Calculated data from report handler.
	 * @return array
	 */
	abstract public function handlerFinallyBeforePassToView($dataFromReport);

	/**
	 * Use if need to construct widget entity by view type.
	 *
	 * @param string $boardId Board id.
	 * @return BaseWidget
	 */
	final public function buildWidgetHandlerForBoard($boardId)
	{
		$widgetHandler = new BaseWidget();
		$widgetHandler->setView($this);
		$widgetHandler->getWidget()->setWidgetHandler($widgetHandler);
		$widgetHandler->getWidget()->setBoardId($boardId);
		$widgetHandler->getWidget()->setViewKey($this->getKey());
		$widgetHandler->getCollectedFormElements();
		return $widgetHandler;
	}

	/**
	 * When building new widget, add default Report handlers to widget.
	 *
	 * @param BaseWidget $widgetHandler Widget handler.
	 * @return BaseWidget
	 */
	public function addDefaultReportHandlersToWidgetHandler(BaseWidget $widgetHandler)
	{
		for ($emptyReportNum = 0; $emptyReportNum < static::DEFAULT_EMPTY_REPORT_COUNT; $emptyReportNum++)
		{
			$reportHandler = Report::buildReportHandlerForWidget(EmptyReport::getClassName(), $widgetHandler->getWidget(), true);
			$widgetHandler->addReportHandler($reportHandler);
		}

		return $widgetHandler;
	}

	/**
	 * Find report handler by class name and build Report handler in context of widget handler and view type.
	 *
	 * @param string $reportHandlerClassName Report handler class name.
	 * @param BaseWidget $widgetHandler Widget handler.
	 * @return null
	 */
	public function getReportHandler($reportHandlerClassName, BaseWidget $widgetHandler)
	{
		$reportHandler = ReportProvider::getReportHandlerByClassName($reportHandlerClassName);
		if (!$reportHandler)
		{
			return null;
		}
		if ($reportHandler instanceof BaseReport)
		{
			/** @var BaseReport $reportHandler */
			$reportHandler = new $reportHandler;
			$reportHandler->setView($this);
			$reportHandler->setWidgetHandler($widgetHandler);
			$reportHandler->getCollectedFormElements();
			$reportHandler->getReport()->setReportHandler($reportHandler);
		}
		return $reportHandler;
	}

	/**
	 * Method to modify widget configuration form in context of view.
	 *
	 * @param WidgetForm $form Form Entity.
	 * @return WidgetForm $form
	 */
	public function prepareWidgetFormBeforeRender(WidgetForm $form)
	{
		return $form;
	}


	/**
	 * Method to modify widget form elements.
	 *
	 * @param BaseWidget $widgetHandler Widget handler.
	 * @return void
	 */
	public function collectWidgetHandlerFormElements(BaseWidget $widgetHandler)
	{
		$label = new LabelField('label', 'big');
		$label->setDefaultValue(Loc::getMessage('REPORT_WIDGET_DEFAULT_TITLE'));
		$label->addAssets(array(
			'js' => array('/bitrix/js/report/js/visualconstructor/fields/reporttitle.js')
		));
		$label->setIsDisplayLabel(false);

		$timePeriod = new TimePeriod('time_period', $widgetHandler->getWidget()->getFilterId());
		$timePeriod->setLabel(Loc::getMessage('REPORT_CALCULATION_PERIOD'));

		$colorPicker = new ColorPicker('color');
		$colorPicker->setLabel(Loc::getMessage('BACKGROUND_COLOR_OF_WIDGET'));
		$colorPicker->setDefaultValue('#ffffff');

		$previewBlockField = new PreviewBlock('view_type');
		$previewBlockField->setWidget($widgetHandler->getWidget());
		$previewBlockField->addJsEventListener($previewBlockField, $previewBlockField::JS_EVENT_ON_VIEW_SELECT, array(
			'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.PreviewBlock',
			'action' => 'viewTypeSelect'
		));

		$previewBlockField->addJsEventListener($label, $label::JS_EVENT_ON_CHANGE, array(
			'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.PreviewBlock',
			'action' => 'reloadWidgetPreview'
		));
		$previewBlockField->addJsEventListener($timePeriod, $timePeriod::JS_EVENT_ON_SELECT, array(
			'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.PreviewBlock',
			'action' => 'reloadWidgetPreview'
		));

		$previewBlockField->addAssets(array(
			'js' => array('/bitrix/js/report/js/visualconstructor/fields/previewblock.js')
		));
		$titleContainer = new Div();
		$titleContainer->addClass('report-configuration-row');
		$titleContainer->addClass('report-configuration-no-padding-bottom');
		$titleContainer->addClass('report-configuration-row-white-background');
		$titleContainer->addClass('report-configuration-row-margin-bottom');
		$widgetHandler->addFormElement($titleContainer->start());
		$widgetHandler->addFormElement($label);
		$widgetHandler->addFormElement($colorPicker);
		$widgetHandler->addFormElement($titleContainer->end());

		$timePeriodContainer = new Div();
		$timePeriodContainer->addClass('report-configuration-row');
		$timePeriodContainer->addClass('report-configuration-row-white-background');
		$widgetHandler->addFormElement($timePeriodContainer->start());
		$widgetHandler->addFormElement($timePeriod);
		$widgetHandler->addFormElement($timePeriodContainer->end());

		$previewBlockContainer = new Div();
		$previewBlockContainer->addClass('report-configuration-row');
		$previewBlockContainer->addClass('report-configuration-row-margin-top-big');
		$previewBlockContainer->addClass('report-configuration-row-white-background');
		$widgetHandler->addFormElement($previewBlockContainer->start());
		$widgetHandler->addFormElement($previewBlockField);
		$widgetHandler->addFormElement($previewBlockContainer->end());
	}

	/**
	 * Method to modify widget form elements.
	 *
	 * @param BaseReport $reportHandler Widget handler.
	 * @return void
	 */
	public function collectReportHandlerFormElements($reportHandler)
	{
		$headContainer = new Div();
		$headContainer->addAssets(array(
			'css' => array('/bitrix/js/report/css/visualconstructor/configheader.css')
		));

		$widgetHandler = $reportHandler->getWidgetHandler();
		$previewBlock = $widgetHandler->getFormElement('view_type');

		$headContainer->setKey('head_container');
		$headContainer->addClass('report-configuration-head');
		$labelColorContainer = new Div();
		$labelColorContainer->setKey('label_color_container');
		$labelColorContainer->addClass('report-configuration-row');

		$labelField = new LabelField('label');
		$labelField->setDefaultValue(Loc::getMessage('REPORT_DEFAULT_TITLE'));
		$labelField->setIsDisplayLabel(false);
		$labelField->addAssets(array(
			'js' => array('/bitrix/js/report/js/visualconstructor/fields/reporttitle.js')
		));
		$previewBlock->addJsEventListener($labelField, $labelField::JS_EVENT_ON_CHANGE, array(
			'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.PreviewBlock',
			'action' => 'reloadWidgetPreview'
		));

		$colorField = new ColorPicker('color');
		$colorField->setDefaultValue('#4fc3f7');
		$colorField->addAssets(array(
			'js' => array('/bitrix/js/report/js/visualconstructor/fields/colorfield.js')
		));
		$colorField->addJsEventListener($colorField, $colorField::JS_EVENT_ON_SELECT, array(
			'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.ColorField',
			'action' => 'selectColorInConfigurationForm'
		));

		if ($reportHandler->getConfiguration('color'))
		{
			$headContainer->addInlineStyle('background-color', $reportHandler->getConfiguration('color')->getValue());
		}
		else
		{
			$reportHandlersCount = count($reportHandler->getWidgetHandler()->getReportHandlers());
			$colorDefaultValue = $this->getReportDefaultColor($reportHandlersCount);
			$headContainer->addInlineStyle('background-color', $colorDefaultValue);
			$colorField->setValue($colorDefaultValue);
		}

		$previewBlock->addJsEventListener($colorField, $colorField::JS_EVENT_ON_SELECT, array(
			'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.PreviewBlock',
			'action' => 'reloadWidgetPreview'
		));
		$container = new Container();
		$container->addDataAttribute('role', 'report-remove-button');
		$removeButton = new ComplexHtml('report-remove-button-' . $reportHandler->getReport()->getGId(), '<div class="report-remove-button"></div>');
		$removeButton->addJsEventListener($removeButton, $removeButton::JS_EVENT_ON_CLICK, array(
			'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.ReportHandlerSelect',
			'action' => 'removeReportFromConfiguration'
		));
		$previewBlock->addJsEventListener($removeButton, $removeButton::JS_EVENT_ON_CLICK, array(
			'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.PreviewBlock',
			'action' => 'reloadWidgetPreview'
		));

		$container->addElement($removeButton);
		$headContainerStart = $headContainer->start();
		$headContainerEnd = $headContainer->end();
		$containerStartElement = $labelColorContainer->start();
		$containerEndElement = $labelColorContainer->end();

		$reportHandler->addFormElementToStart($headContainerStart);
		$reportHandler->addFormElementAfter($containerStartElement, $headContainerStart);
		$reportHandler->addFormElementAfter($labelField, $containerStartElement);
		$reportHandler->addFormElementAfter($colorField, $labelField);
		$reportHandler->addFormElementAfter($container, $colorField);
		$reportHandler->addFormElementAfter($containerEndElement, $container);
		$reportHandler->addFormElementAfter($headContainerEnd, $containerEndElement);
	}

	/**
	 * @return string
	 */
	public function getJsClassName()
	{
		return $this->jsClassName;
	}

	/**
	 * Setter for js class name.
	 *
	 * @param string $jsClassName Js class name.
	 * @return void
	 */
	public function setJsClassName($jsClassName)
	{
		$this->jsClassName = $jsClassName;
	}

	/**
	 * Method to modify Content which pass to widget view, in absolute end.
	 *
	 * @param Widget $widget Widget entity.
	 * @param bool $withCalculatedData Marker for calculate or no data in widget.
	 * @return array
	 */
	public function prepareWidgetContent(Widget $widget, $withCalculatedData = false)
	{
		$resultWidget = array(
			'id' => $widget->getGId(),
			'title' => 'No Title',
			'isHeadEnabled' => true,
			'draggable' => $this->isDraggable(),
			'droppable' => true,
			'loaded' => $withCalculatedData,
			'weight' => $widget->getWeight(),
			'className' => 'BX.VisualConstructor.Widget',
			'resizable' => $this->isHorizontalResizable(),
			'content' => array(
				'params' => array(
					'height' => $this->getHeight(),
					'previewImageUri' => $this->getPreviewImageUri()
				),
				'className' => $this->getJsClassName()
			)
		);

		$widgetHandler = $widget->getWidgetHandler();
		/** @var ColorPicker $color */
		$color = $widgetHandler->getFormElement('color');
		$colorValue = $color->getValue();
		$resultWidget['config']['color'] = htmlspecialcharsbx($colorValue);
		$resultWidget['config']['header']['color'] = htmlspecialcharsbx($colorValue);

		/** @var LabelField $label */
		$label = $widgetHandler->getFormElement('label');
		$labelValue = $label->getValue();
		$resultWidget['config']['title'] = htmlspecialcharsbx($labelValue);

		/** @var TimePeriod $timePeriodField */
		$timePeriodField = $widgetHandler->getFormElement('time_period');
		if ($timePeriodField)
		{
			$timePeriodTitle = $timePeriodField->getValueForHuman();
			$resultWidget['config']['timePeriod'] = Loc::getMessage('REPORT_TIME_PERIOD_MARK_TEXT') . ': ' . $timePeriodTitle;
		}

		return $resultWidget;
	}

	/**
	 * @return bool
	 */
	public function isDraggable()
	{
		return $this->draggable;
	}

	/**
	 * Setter for draggable.
	 *
	 * @param bool $draggable Marker for dragging functionality.
	 * @return void
	 */
	public function setDraggable($draggable)
	{
		$this->draggable = $draggable;
	}

	/**
	 * Check is $view compatible with current view type.
	 *
	 * @param View $view View entity.
	 * @return bool
	 */
	public function isCompatibleWithView(View $view)
	{
		return in_array($this->getKey(), $view->getCompatibleViewTypes());
	}

	/**
	 * Default colors set for reports.
	 *
	 * @param int $num Number of color which need.
	 * @return string
	 */
	public function getReportDefaultColor($num)
	{
		$defaultColorList = array(
			"#00c4fb",
			"#75d900",
			"#ffab00",
			"#47d1e2",
			"#ff5752",
			"#468ee5",
			"#1eae43",
			"#f7d622",
			"#4fc3f7",
			'#9dcf00',
			'#f6ce00'
		);
		return $defaultColorList[$num % count($defaultColorList)];
	}

	/**
	 * @return bool
	 */
	public function isHorizontalResizable()
	{
		return $this->horizontalResizable;
	}

	/**
	 * If true then big widget can set to small place.
	 *
	 * @param bool $horizontalResizable Marker to set resizable mode.
	 * #return void
	 */
	public function setHorizontalResizable($horizontalResizable)
	{
		$this->horizontalResizable = $horizontalResizable;
	}

	/**
	 * @return mixed
	 */
	public function getPreviewImageUri()
	{
		return $this->previewImageUri;
	}

	/**
	 * @param mixed $previewImageUri
	 */
	public function setPreviewImageUri($previewImageUri)
	{
		$this->previewImageUri = $previewImageUri;
	}
}
