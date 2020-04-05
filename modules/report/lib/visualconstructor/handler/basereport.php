<?php

namespace Bitrix\Report\VisualConstructor\Handler;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Entity\Report;
use Bitrix\Report\VisualConstructor\Fields\Base as BaseFormField;
use Bitrix\Report\VisualConstructor\Fields\Div;
use Bitrix\Report\VisualConstructor\Fields\Valuable\CustomDropDown;
use Bitrix\Report\VisualConstructor\Fields\Valuable\BaseValuable;
use Bitrix\Report\VisualConstructor\Fields\Valuable\DropDown;
use Bitrix\Report\VisualConstructor\Fields\Valuable\IValuable;
use Bitrix\Report\VisualConstructor\Helper\Category;
use Bitrix\Report\VisualConstructor\IReportData;
use Bitrix\Report\VisualConstructor\RuntimeProvider\CategoryProvider;
use Bitrix\Report\VisualConstructor\RuntimeProvider\ReportProvider;
use Bitrix\Report\VisualConstructor\RuntimeProvider\ViewProvider;

use Bitrix\Report\VisualConstructor\Handler\Base as BaseHandler;


/**
 * Class BaseReport
 * @property mixed category
 * @property void unit
 * @package Bitrix\Report\VisualConstructor
 */
abstract class BaseReport extends BaseHandler implements IReportData
{
	private $title;
	private $categoryKey;
	private $unitKey;
	private $weight = 0;

	private $report;
	private $calculatedData;
	private $widgetHandler;

	/**
	 * BaseReport constructor.
	 */
	public function __construct()
	{
		$report = new Report();
		$report->setReportHandler($this);
		$this->setReport($report);
		$this->setTitle(Loc::getMessage('WITHOUT_DATA'));
	}

	/**
	 * @return \Bitrix\Report\VisualConstructor\Fields\Base[]
	 */
	public function getCollectedFormElements()
	{
		parent::getCollectedFormElements();
		$this->getView()->collectReportHandlerFormElements($this);
		return $this->getFormElements();
	}

	/**
	 * Collecting form elements for configuration form.
	 *
	 * @return void
	 */
	protected function collectFormElements()
	{
		$mainContainer = new Div();
		$mainContainer->setKey('main_container');
		if ($this->getConfiguration('color'))
		{
			$mainContainer->addInlineStyle('background-color', $this->getConfiguration('color')->getValue() . '5f');
		}
		else
		{
			$reportHandlersCount = count($this->getWidgetHandler()->getReportHandlers());
			$mainContainer->addInlineStyle('background-color', $this->getView()->getReportDefaultColor($reportHandlersCount) . '5f');
		}
		$mainContainer->addClass('report-configuration-main');
		$mainContainer->addAssets(array(
			'css' => array('/bitrix/js/report/css/visualconstructor/configmain.css')
		));
		$this->addFormElement($mainContainer->start());
		$fieldListContainer = new Div();
		$fieldListContainer->setKey('field_list_container');
		$fieldListContainer->addClass('report-configuration-field-list');
		$this->addFormElement($fieldListContainer->start());

		$reportHandlerSelectContainer = new Div();
		$reportHandlerSelectContainer->setKey('report_handler_select_container');
		$reportHandlerSelectContainer->addClass('report-configuration-row');
		$reportHandlerSelectContainer->addClass('report-configuration-no-padding-bottom');
		$categorySelectField = $this->getReportHandlerCategoryField();
		$reportHandlerSelectField = $this->getReportHandlerSelectField($categorySelectField->getValue());

		$reportHandlerSelectField->addJsEventListener($categorySelectField, $categorySelectField::JS_EVENT_ON_CHANGE, array(
			'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.ReportHandlerSelect',
			'action' => 'categorySelected'
		));
		$reportHandlerSelectField->addAssets(array(
			'js' => array('/bitrix/js/report/js/visualconstructor/fields/reporthandlerselect.js')
		));

		$this->addFormElement($reportHandlerSelectContainer->start());
			$this->addFormElement($categorySelectField);
			$this->addFormElement($reportHandlerSelectField);

		$whatWillCalculateField = $this->getCalculateField();

		$viewCompatibleDataType = $this->getView()->getCompatibleDataType();

		if (in_array($viewCompatibleDataType, array(
			Common::MULTIPLE_REPORT_TYPE,
			Common::MULTIPLE_GROUPED_REPORT_TYPE,
			Common::MULTIPLE_BI_GROUPED_REPORT_TYPE,
		)))
		{
			$groupingField = $this->getGroupingField($whatWillCalculateField);
			$this->addFormElement($groupingField);
			$widgetHandler = $this->getWidgetHandler();
			$previewBlock = $widgetHandler->getFormElement('view_type');
			$previewBlock->addJsEventListener($groupingField, $groupingField::JS_EVENT_ON_CHANGE, array(
				'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.PreviewBlock',
				'action' => 'reloadWidgetPreview'
			));
		}

		$this->addFormElement($reportHandlerSelectContainer->end());

		$selectListContainer = new Div();
		$selectListContainer->setKey('report_configuration_select_list');
		$selectListContainer->addClass('report-configuration-row');
		$selectListContainer->addClass('report-configuration-row-list');

		$this->addFormElement($selectListContainer->start());
		$this->addFormElement($whatWillCalculateField);
		$this->addFormElement($selectListContainer->end());
		$this->addFormElement($fieldListContainer->end());
		$this->addFormElement($mainContainer->end());
	}

	/**
	 * @return DropDown
	 */
	public function getCalculateField()
	{
		$whatWillCalculate = new DropDown('calculate');
		$whatWillCalculate->setLabel(Loc::getMessage('BASE_REPORT_HANDLER_WHAT_WILL_CALCULATE'));
		$whatWillCalculate->addOptions($this->getWhatWillCalculateOptions());

		return $whatWillCalculate;
	}

	/**
	 * @param DropDown $whatWillCalculateField What will calculate field.
	 * @return DropDown
	 */
	public function getGroupingField(DropDown $whatWillCalculateField)
	{
		$groupingField = new CustomDropDown('groupingBy');
		$groupingField->setLabel(Loc::getMessage('BASE_REPORT_HANDLER_GROUPING'));
		$groupingField->addOptions($this->getGroupByOptions());
		$whatWillCalculateField->addJsEventListener($groupingField, $groupingField::JS_EVENT_ON_CHANGE, array(
			'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.WhatWillCalculate',
			'action' => 'reloadCompatibleCalculatedTypes',
		));
		$whatWillCalculateField->addAssets(array(
			'js' => array('/bitrix/js/report/js/visualconstructor/fields/whatwillcalculate.js')
		));

		return $groupingField;
	}

	/**
	 * @return array
	 */
	protected function getGroupByOptions()
	{
		return array();
	}

	/**
	 * @return BaseFormField[]
	 */
	public function getFormElements()
	{
		$pseudoReportId = '_pseudo' . randString(4);
		$result = array();
		foreach ($this->formElementsList as $key => $element)
		{
			$viewModesWhereFieldAvailable = $element->getCompatibleViewTypes();
			if ($viewModesWhereFieldAvailable != null)
			{
				$viewKey = $this->getView()->getKey();
				$viewProvider = new ViewProvider();
				$viewProvider->addFilter('primary', $viewKey);
				$viewProvider->addFilter('dataType', $viewModesWhereFieldAvailable);
				$views = $viewProvider->execute()->getResults();
				if (!empty($views))
				{
					$result[$key] = $element;
				}
			}
			else
			{
				$result[$key] = $element;
			}
			if ($element instanceof BaseValuable)
			{
				$element->setName($this->getNameForFormElement($element, $pseudoReportId));
			}
		}

		return $result;
	}


	/**
	 * @param BaseValuable $element Element for which construct name.
	 * @param string $pseudoReportId pseudo report id is report not exist.
	 * @return string
	 */
	protected function getNameForFormElement(BaseValuable $element, $pseudoReportId = '')
	{
		$name = '';
		if ($this->getWidgetHandler() && $this->getReport())
		{
			$name = 'widget[' .
				$this->getWidgetHandler()->getWidget()->getGId() .
				'][reports][' .
				$this->getReport()->getGId() .
				']';
		}
		elseif(!$this->getReport())
		{
			$name =  'widget[' .
				$this->getWidgetHandler()->getWidget()->getGId() .
				'][reports][' .
				$pseudoReportId .
				']';
		}

		$name .= parent::getNameForFormElement($element);
		return $name;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title Title of report handler.
	 * @return void
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return mixed
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * @param mixed $weight Weight value for sorting.
	 * @return void
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;
	}

	/**
	 * @return Report
	 */
	public function getReport()
	{
		return $this->report;
	}

	/**
	 * @param Report $report Report entity.
	 * @return void
	 */
	public function setReport($report)
	{

		$this->report = $report;
	}

	/**
	 * @return mixed
	 */
	public function getCalculatedData()
	{
		return $this->calculatedData;
	}

	/**
	 * @param mixed $calculatedData Report handler calculated data.
	 * @return void
	 */
	public function setCalculatedData($calculatedData)
	{
		$this->calculatedData = $calculatedData;
	}

	/**
	 * Fill report handler properties from Report entity.
	 *
	 * @param Report $report Report entity.
	 * @return void
	 */
	public function fillReport(Report $report)
	{
		$viewHandler = $this->getView();
		if ($viewHandler)
		{
			$this->setView($viewHandler);
		}
		$this->setReport($report);
		$this->setConfigurations($report->getConfigurations());
		$this->getCollectedFormElements();
		$this->fillFormElementsValues();
	}

	private function fillFormElementsValues()
	{
		$formElements = $this->getFormElements();
		$configurations = $this->getConfigurations();
		if (!empty($configurations))
		{
			foreach ($configurations as $configuration)
			{
				if (isset($formElements[$configuration->getKey()]) && ($formElements[$configuration->getKey()] instanceof BaseValuable))
				{
					/** @var BaseValuable[] $formElements */
					$formElements[$configuration->getKey()]->setValue($configuration->getValue());
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function getCategoryKey()
	{
		return $this->categoryKey;
	}

	/**
	 * Attach report handler to category.
	 *
	 * @param string $categoryKey Category key.
	 * @return void
	 */
	protected function setCategoryKey($categoryKey)
	{
		$this->categoryKey = $categoryKey;
	}

	/**
	 * @return string
	 */
	public function getUnitKey()
	{
		return $this->unitKey;
	}

	/**
	 * @param string $unitKey Unit measurement key.
	 * @return void
	 */
	protected function setUnitKey($unitKey)
	{
		$this->unitKey = $unitKey;
	}

	/**
	 * Field for selecting category.
	 *
	 * @return DropDown
	 */
	private function getReportHandlerCategoryField()
	{
		$selectField = new CustomDropDown('reportCategory');
		$selectField->setLabel(Loc::getMessage('SELECT_REPORT_HANDLER_CATEGORY'));
		$categories = new CategoryProvider();
		$categories->addFilter('parent_keys', 'main');
		$categories->addRelation('children');
		$categories->addRelation('parent');
		$categories  = $categories->execute()->getResults();
		$options = Category::getOptionsTree($categories);
		$selectField->addOptions($options);

		$selectField->setValue($this->getCategoryKey());
		return $selectField;
	}

	/**
	 * Build report handler select drop down.
	 * Collect all report handler sin selected category.
	 *
	 * @param string $categoryKey Category key.
	 * @return CustomDropDown
	 */
	private function getReportHandlerSelectField($categoryKey = '__')
	{
		$selectField = new CustomDropDown('reportHandler');
		$selectField->addDataAttribute('field-type', 'report-handler-class');
		$selectField->setLabel(Loc::getMessage('SELECT_DATA_PROVIDER'));
		$selectField->addJsEventListener($selectField, $selectField::JS_EVENT_ON_CHANGE, array(
			'class' => 'BX.Report.VisualConstructor.FieldEventHandlers.ReportHandlerSelect',
			'action' => 'reportHandlerSelected',
		));
		$selectField->addAssets(array(
			'js' => array('/bitrix/js/report/js/visualconstructor/fields/reporthandlerselect.js')
		));

		$reports = new ReportProvider();
		$reports->addFilter('dataType', $this->getView()->getCompatibleDataType());

		if (!empty($categoryKey) && $categoryKey !== '__')
		{
			$reports->addFilter('categories', array($categoryKey));
		}

		$reports->execute();

		/** @var BaseReport[] $reportHandlers */
		$reportHandlers = $reports->getResults();
		foreach ($reportHandlers as $report)
		{
			$selectField->addOption($report::getClassName(), $report->getTitle());
		}

		$selectField->setDefaultValue($this::getClassName());

		return $selectField;
	}

	/**
	 * @return array
	 */
	public function getReportImplementedDataTypes()
	{
		$dataTypeMap = Common::$reportImplementationTypesMap;
		$class = get_class($this);
		$implementedInterfaceList = class_implements($class);
		$implementedInterfaceList = array_values($implementedInterfaceList);
		$result = array();
		foreach ($dataTypeMap as $reportTypeKey => $settings)
		{
			if (in_array($settings['interface'], $implementedInterfaceList))
			{
				$result[] = $reportTypeKey;
			}
		}
		return $result;
	}

	/**
	 * @return BaseWidget|null
	 */
	public function getWidgetHandler()
	{
		if ($this->getReport()->getWidget())
		{
			return $this->getReport()->getWidget()->getWidgetHandler();
		}
		elseif ($this->widgetHandler)
		{
			return $this->widgetHandler;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Attach report handler to widget handler.
	 *
	 * @param BaseWidget $widgetHandler Widget handler.
	 * @return void
	 */
	public function setWidgetHandler(BaseWidget $widgetHandler)
	{
		$this->widgetHandler = $widgetHandler;
	}

	/**
	 * In cloning of entity clone nested entities too.(configurations, form elements list).
	 * @return void
	 */
	public function __clone()
	{
		foreach ($this->configurations as $key => $configuration)
		{
			$this->configurations[$key] = clone $configuration;
		}

		foreach ($this->formElementsList as $key => $formElement)
		{
			$this->formElementsList[$key] = clone $formElement;
		}
	}

	/**
	 *
	 * @param null $groupingValue Grouping field value.
	 * @return array
	 */
	public function getWhatWillCalculateOptions($groupingValue = null)
	{
		return array();
	}

	/**
	 * In some case, need to dynamically disable some report handler
	 * @return bool
	 */
	public function isEnabled()
	{
		return true;
	}
}
