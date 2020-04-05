<?php
namespace Bitrix\Report\VisualConstructor;
use Bitrix\Main\Localization\Loc;
use Bitrix\Report\VisualConstructor\Entity\Widget;

/**
 * Special form class wor widget configurations.
 * @package Bitrix\Report\VisualConstructor
 */
class WidgetForm extends Form
{
	private $view;
	private $widget;
	private $reportsInWidgetCount;
	private $boarId;

	/**
	 * Construct widget form by view and widget
	 * @param View $view
	 * @param Widget $widget
	 */
	public function __construct(View $view, Widget $widget)
	{
		$this->setView($view);
		$this->setWidget($widget);
	}

	/**
	 * @return void
	 */
	public function render()
	{
		$view = $this->getView();
		$view->prepareWidgetFormBeforeRender($this);
		parent::render();
	}

	/**
	 * Building configuration form.
	 *
	 * @param View $view View controller.
	 * @param Widget $widget Widget entity.
	 * @param array $params Parameters need for build form.
	 * @return static
	 */
	public static function build(View $view, Widget $widget, $params)
	{
		$boardId = $params['boardId'];
		$action = $params['action'];
		$saveButtonTitle = $params['saveButtonTitle'];

		$form = new static($view, $widget);
		$form->setId('report_widget_configuration_form_' . $widget->getGId());
		$form->setAction($action);
		$form->setBoarId($boardId);
		$form->addDataAttribute('widget-id', $widget->getGId());
		$form->addWidgetConfigurationFormFullContent();


		$footerContainer = new Fields\Div();
		$footerContainer->setKey('footer_container');
		$footerContainer->addClass('report-widget-configuration-form-footer-container');
		$footerContainer->addDataAttribute('role', 'footer-container');


		$buttonsContainer = new Fields\Div();
		$buttonsContainer->addClass('report-widget-configuration-form-action-buttons-container');
		$saveButton = new Fields\Button('widgetSaveConfigurations_' . $widget->getGId());
		$saveButton->addDataAttribute('type', 'save-button');
		$saveButton->addClass('ui-btn');
		$saveButton->addClass('ui-btn-md');
		$saveButton->addClass('ui-btn-success');
		$saveButton->setLabel($saveButtonTitle);


		$cancelButton = new Fields\Button('widgetCancelConfigurations_' . $widget->getGId());
		$cancelButton->addDataAttribute('type', 'cancel-button');
		$cancelButton->addClass('ui-btn');
		$cancelButton->addClass('ui-btn-md');
		$cancelButton->addClass('ui-btn-link');
		$cancelButton->setLabel(Loc::getMessage('SAVE_WIDGET_CONFIG_CANCEL_BUTTON'));

		$checkBoxContainer = new Fields\Div();
		$checkBoxContainer->addClass('report-configuration-footer-right-container');

		$isPatternCheckBox = new Fields\Valuable\CheckBox('isPattern');
		$isPatternCheckBox->setLabel(Loc::getMessage('SAVE_WIDGET_AS_PATTERN'));

		$form->add($footerContainer->start());
			$form->add($buttonsContainer->start());
				$form->add($saveButton);
				$form->add($cancelButton);
			$form->add($buttonsContainer->end());
			$form->add($checkBoxContainer->start());
				$form->add($isPatternCheckBox);
			$form->add($checkBoxContainer->end());
		$form->add($footerContainer->end());



		return $form;
	}


	private function addWidgetConfigurationFormFullContent()
	{
		$view = $this->getView();
		$formContentContainer = new Fields\Div();
		$formContentContainer->addClass('report-widget-configuration-form-content');
		$formContentContainer->setKey('form_content_container');
		$this->add($formContentContainer->start());
			$this->addWidgetConfigurationsBlock();

			$reportsConfigurationsContainerWrapper = new Fields\Div();
			$reportsConfigurationsContainerWrapper->addClass('reports-configurations-container-wrapper');
			$this->add($reportsConfigurationsContainerWrapper->start());
				$this->addReportsConfigurationsBlock();
				$this->addReportAddButtonBlock();
			$this->add($reportsConfigurationsContainerWrapper->end());

			$maxReportCountField = new Fields\Valuable\Hidden('maxReportCount');
			$maxReportCountField->addDataAttribute('hidden-field', 'maxRenderReportCount');
			$maxReportCountField->setValue($view::MAX_RENDER_REPORT_COUNT);
			$this->add($maxReportCountField);

			$boardIdField = new Fields\Valuable\Hidden('boardId');
			$boardIdField->setValue($this->getBoarId());
			$this->add($boardIdField);

			$widgetIdField = new Fields\Valuable\Hidden('widgetId');
			$widgetIdField->setValue($this->getWidget()->getGId());
			$this->add($widgetIdField);

			$categoryKeyField = new Fields\Valuable\Hidden('categoryKey');
			$categoryKeyField->setValue($this->getWidget()->getCategoryKey());
			$this->add($categoryKeyField);

		$this->add($formContentContainer->end());
	}

	private function addWidgetConfigurationsBlock()
	{
		$widgetConfigurationFields = $this->getWidgetConfigurationFields();
		$widgetConfigurationsContainer = new Fields\Div();
		$widgetConfigurationsContainer->addClass('widgets-configurations-container');

		$this->add($widgetConfigurationsContainer->start());
		foreach ($widgetConfigurationFields as $configurationField)
		{
			$this->add($configurationField);
		}
		$this->add($widgetConfigurationsContainer->end());
	}

	private function addReportsConfigurationsBlock()
	{
		$reportsConfigurationFields = $this->getReportConfigurationFields();
		$reportsConfigurationsContainer = new Fields\Div();
		$reportsConfigurationsContainer->addClass('reports-configurations-container');
		$reportsConfigurationsContainer->addDataAttribute('role', 'reports-configurations-container');
		$reportsConfigurationsContainer->addDataAttribute('widget-id', $this->getWidget()->getGId());
		$this->add($reportsConfigurationsContainer->start());
		$this->reportsInWidgetCount = count($reportsConfigurationFields);
		if($reportsConfigurationFields)
		{
			$reportConfigurationsContainer = new Fields\Div();
			$reportConfigurationsContainer->addClass('report-configuration-container');
			$reportConfigurationsContainer->addDataAttribute('role', 'report-configuration-container');
			$reportConfigurationsContainer->addDataAttribute('is-pseudo', '0');
			$reportConfigurationsContainer->addClass('report-configuration-container-visible');

			$num = 0;
			foreach ($reportsConfigurationFields as $reportGId => $reportConfiguration)
			{
				$num++;
				$reportConfigurationsContainer->addDataAttribute('report-id', $reportGId);
				$container = new Fields\Container();
				$container->setKey('report_configurations_container_' . $num);
				$container->addDataAttribute('role', 'report-configuration-container');



				$container->addElement($reportConfigurationsContainer->start());
				/** @var Fields\Base $configurationField */
				foreach ($reportConfiguration['FIELDS'] as $configurationField)
				{
					$container->addElement($configurationField);

				}

				$container->addElement($reportConfigurationsContainer->end());
				$this->add($container);
			}
		}

		$this->add($reportsConfigurationsContainer->end());
	}

	private function addReportAddButtonBlock()
	{
		$addButtonContainer = new Fields\Div();
		$addButtonContainer->addClass('report-configuration-add-report-button-container');
		$addButton = new Fields\Button('widgetAddReportButton_' . $this->getWidget()->getGId());
		$addButton->addClass('add-report-to-widget-button');
		$view = $this->getView();
		if ($view::MAX_RENDER_REPORT_COUNT <= $this->reportsInWidgetCount)
		{
			$addButtonContainer->addClass('report-configuration-add-report-button-container-invisible');
		}
		$addButton->setLabel(Loc::getMessage('ADD_REPORT_BUTTON'));
		$this->add($addButtonContainer->start());
			$this->add('&#43;');
			$this->add($addButton);
		$this->add($addButtonContainer->end());
	}

	/**
	 * @return Fields\Base[]
	 */
	private function getWidgetConfigurationFields()
	{
		return $this->getWidget()->getWidgetHandler()->getFormElements();
	}

	/**
	 * @return Fields\Base[]
	 */
	private function getReportConfigurationFields()
	{
		$reports = $this->getWidget()->getReports();
		$configurations = array();
		foreach ($reports as $report)
		{
			$reportHandler = $report->getReportHandler();
			$reportHandler->setView($this->view);
			$configurations[$report->getGId()]['FIELDS'] = $reportHandler->getFormElements();
		}
		return $configurations;
	}

	/**
	 * @return View
	 */
	public function getView()
	{
		return $this->view;
	}

	/**
	 * @param View $view View controller.
	 * @return void
	 */
	public function setView(View $view)
	{
		$this->view = $view;
	}

	/**
	 * @return Widget
	 */
	public function getWidget()
	{
		return $this->widget;
	}

	/**
	 * @param Widget $widget Widget entity.
	 * @return void
	 */
	public function setWidget(Widget $widget)
	{
		$this->widget = $widget;
	}

	/**
	 * @return string
	 */
	public function getBoarId()
	{
		return $this->boarId;
	}

	/**
	 * @param string $boarId Board id.
	 * @return void
	 */
	public function setBoarId($boarId)
	{
		$this->boarId = $boarId;
	}
}