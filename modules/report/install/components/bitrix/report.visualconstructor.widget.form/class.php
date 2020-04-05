<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Report\VisualConstructor\RuntimeProvider\ViewProvider;
use \Bitrix\Report\VisualConstructor\Fields;
/**
 * Class ReportVisualConstructorWidgetForm
 */
class ReportVisualConstructorWidgetForm extends CBitrixComponent
{
	const CREATE_MODE_NAME = 'create';
	const UPDATE_MODE_NAME = 'update';
	/**
	 * @var \Bitrix\Report\VisualConstructor\Entity\Widget
	 */
	private $widget;
	/**
	 * @var \Bitrix\Report\VisualConstructor\View
	 */
	private $view;
	private $boardId;
	private $saveButtonTitle;
	private $mode;

	/**
	 * @return void
	 */
	public function executeComponent()
	{
		$this->boardId = $this->arParams['BOARD_ID'];
		$this->widget = $this->arParams['WIDGET'];
		$this->mode = $this->arParams['MODE'];
		$this->saveButtonTitle = $this->arParams['SAVE_BUTTON_TITLE'];
		$this->view = ViewProvider::getViewByViewKey($this->widget->getViewKey());

		$params = array(
			'boardId' => $this->boardId,
			'action' => $this->getFormAction(),
			'saveButtonTitle' => $this->saveButtonTitle
		);
		$form = \Bitrix\Report\VisualConstructor\WidgetForm::build($this->view, $this->widget, $params);

		$originalWidgetGid = $this->arParams['ORIGINAL_WIDGET_GID'];

		$originalWidgetGidField = new Fields\Valuable\Hidden('originalWidgetGId');
		$originalWidgetGidField->setValue($originalWidgetGid);
		$form->add($originalWidgetGidField);


		$modeField = new Fields\Valuable\Hidden('mode');
		$modeField->setValue($this->mode);
		$form->add($modeField);


		$this->arResult['FORM'] = $form;
		$this->arResult['WIDGET_GID'] = $this->widget->getGId();
		$this->arResult['PAGE_TITLE'] = $this->arParams['PAGE_TITLE'];
		$this->arResult['MODE'] = $this->mode;
		$this->includeComponentTemplate();
	}


	/**
	 * Return form action name, different for create form and update form
	 * @return string|null
	 */
	private function getFormAction()
	{
		if ($this->mode === self::CREATE_MODE_NAME)
		{
			return 'widget.addWidgetFromConfigurationForm';
		}
		elseif ($this->mode === self::UPDATE_MODE_NAME)
		{
			return 'widget.saveConfigurationForm';
		}

		return null;
	}

}