<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Report\VisualConstructor\Entity\Widget;
use Bitrix\Report\VisualConstructor\Handler\BaseWidget;
use Bitrix\Report\VisualConstructor\Fields;
use Bitrix\Main\Localization\Loc;

/**
 * Class ReportVisualConstructorBoardControls
 */
class ReportVisualConstructorBoardControls extends CBitrixComponent
{
	const DEFAULT_SHOW_MINIATURE_COUNT_IN_CATEGORY = 2;

	/**
	 * @return void
	 *
	 */
	public function executeComponent()
	{
		$this->arResult['REPORTS_CATEGORIES'] = array('main');
		$this->arResult['BOARD_ID'] = !empty($this->arParams['BOARD_ID']) ? $this->arParams['BOARD_ID'] : '';

		$templateName = $this->getTemplateName();
		switch ($templateName)
		{
			case '':
				break;
			case 'addform':
				$this->prepareParametersForAddForm();
				break;
		}

		$this->includeComponentTemplate();
	}

	private function prepareParametersForAddForm()
	{
		//$this->arResult['REPORTS_CATEGORIES_OPTIONS'] = Category::getOptionsTree($this->getCategories());
		$this->arResult['ADD_FORM'] = $this->buildAddForm();
		$this->arResult['SHOW_ALL_BUTTON_TITLE'] = Loc::getMessage('REPORT_ADD_FORM_SHOW_ALL_BUTTON_TITLE');
		$this->arResult['SHOW_HIDDEN_BUTTON_TITLE'] = Loc::getMessage('REPORT_ADD_FORM_HIDDEN_BUTTON_TITLE');
		$this->arResult['REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CONTENT'] = Loc::getMessage('REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CONTENT');
		$this->arResult['REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CONFIRM_TEXT'] = Loc::getMessage('REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CONFIRM_TEXT');
		$this->arResult['REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CANCEL_TEXT'] = Loc::getMessage('REPORT_PATTERN_WIDGET_REMOVE_DIALOG_CANCEL_TEXT');
	}

	/**
	 * @return \Bitrix\Report\VisualConstructor\View[]
	 */
	private function getAvailableViews()
	{
		static $views;
		if (!$views)
		{
			$viewProvider = new \Bitrix\Report\VisualConstructor\RuntimeProvider\ViewProvider();
			$results = $viewProvider->execute()->getResults();
			foreach ($results as $result)
			{
				$views[$result->getKey()] = $result;
			}
		}

		return $views;
	}



	/**
	 * @return array
	 */
	private function getPatternWidgets()
	{
		global $USER;
		$currentUserId = $USER->getId();
		$result = array();
		$patternWidgets = Widget::getCurrentUserPatternedWidgets();
		$views = $this->getAvailableViews();
		if (!$patternWidgets)
		{
			return array();
		}
		foreach ($patternWidgets as $widget)
		{
			if ($widget->getWidgetHandler() instanceof BaseWidget)
			{
				$logoUrl = !empty($views[$widget->getViewKey()]) ? $views[$widget->getViewKey()]->getLogoUri() : '';
				$result[] = array(
					'id' => $widget->getGId(),
					'logoUrl' => $logoUrl,
					'label' => $widget->getWidgetHandler()->getFormElement('label')->getValue(),
					'categoryKey' => $widget->getCategoryKey(),
					'isDeletable' => $widget->getOwnerId() === $currentUserId
				);
			}
		}
		return $result;
	}



	/**
	 * @return \Bitrix\Report\VisualConstructor\Form
	 */
	private function buildAddForm()
	{
		$form = new \Bitrix\Report\VisualConstructor\Form();
		$form->setId('report_visual_constructor_add_form');
		$form->setAction('board.submitAddForm');

		$patternWidgetsByCategory  = $this->getPatternWidgetListByCategories();




		$patternWidgetIdField = new Fields\Valuable\Hidden('patternWidgetId');
		$patternWidgetIdField->setId('pattern_widget_id');

		foreach ($patternWidgetsByCategory as $categoryKey => $list)
		{
			$widgetCount = count($list['widgets']);
			if ($categoryKey !== 'myWidgets' && !$widgetCount)
			{
				continue;
			}
			$miniaturesTitleContainerForViews = new Fields\Div();
			$miniaturesTitleContainerForViews->addClass('report-visualconstructor-view-miniatures-category-title');
			$form->add($miniaturesTitleContainerForViews->start());
			$categoryTitleContainer = new Fields\Div();
			$categoryTitleContainer->addClass('report-visualconstructor-view-miniatures-category-title-name');
			$form->add($categoryTitleContainer->start());
			$form->add(htmlspecialcharsbx($list['categoryOptions']['title']));
			$form->add($categoryTitleContainer->end());

			$categoryElementCountContainer = new Fields\Div();
			$categoryElementCountContainer->addClass('report-visualconstructor-view-miniatures-category-value');
			$form->add($categoryElementCountContainer->start());
			$form->add($widgetCount);
			$form->add($categoryElementCountContainer->end());
			$form->add($miniaturesTitleContainerForViews->end());

			$miniatureCategoriesWrap = new Fields\Div();
			$miniatureCategoriesWrap->addClass('report-visualconstructor-view-miniatures-category-wrap');

			$form->add($miniatureCategoriesWrap->start());
			$miniaturesWrapper = new Fields\Div();
			$miniaturesWrapper->addClass('report-visualconstructor-view-miniatures-wrapper');
			$miniaturesWrapper->addDataAttribute('container-category-key', $categoryKey);
			$miniaturesWrapper->addClass('report-visualconstructor-view-miniatures-wrapper-collapsed');
			$form->add($miniaturesWrapper->start());
			$miniaturesContainer = new Fields\Div();
			$miniaturesContainer->addClass('report-visualconstructor-view-miniatures-container');
			$form->add($miniaturesContainer->start());
			if (!isset($list['categoryOptions']['disableCreateOwn']) || $list['categoryOptions']['disableCreateOwn'] === false)
			{
				$this->addToFormCreateWidgetMiniature($form, $categoryKey);
			}
			foreach ($list['widgets'] as $patternWidget)
			{
				$patternLogoField = new Fields\Image($patternWidget['logoUrl']);
				$patternLogoField->setPrefix('<div class="report-visualconstructor-view-miniature-img-container">');
				$patternLogoField->setPostfix('</div>');
				$patternLogoField->setId('pattern-widget-id-' . $patternWidget['id']);

				$patternWidgetIdField->addJsEventListener($patternLogoField, $patternLogoField::JS_EVENT_ON_CLICK, array(
					'action' => 'setValue',
					'additionalParams' => array(
						'value' => $patternWidget['id']
					),
				));

				$miniatureContainerForPatternWidgets = new Fields\Div();
				$miniatureContainerForPatternWidgets->addClass('report-visualconstructor-view-miniature-item');
				$miniatureContainerForPatternWidgets->addDataAttribute('type', 'miniature-container');
				$miniatureContainerForPatternWidgets->addDataAttribute('widget-id', $patternWidget['id']);
				$form->add($miniatureContainerForPatternWidgets->start());



				if ($patternWidget['isDeletable'])
				{
					$patternWidgetLabelClose = new Fields\Div();
					$patternWidgetLabelClose->addClass('report-visualconstructor-view-miniature-close');
					$patternWidgetLabelClose->addDataAttribute('role', 'miniature-remove-button');
					$form->add($patternWidgetLabelClose->start());
					$form->add($patternWidgetLabelClose->end());
				}


				$patternWidgetLabelField = new Fields\Html(htmlspecialcharsbx($patternWidget['label']));
				$patternWidgetLabelField->setPrefix('<div class="report-visualconstructor-view-miniature-title">');
				$patternWidgetLabelField->setPostfix('</div>');
				$form->add($patternWidgetLabelField);
				$form->add($patternLogoField);




				$form->add($miniatureContainerForPatternWidgets->end());
			}
			$form->add($miniaturesContainer->end());

			$form->add($miniaturesWrapper->end());

			if ($widgetCount > self::DEFAULT_SHOW_MINIATURE_COUNT_IN_CATEGORY)
			{
				$buttonContainer = new Fields\Div();
				$buttonContainer->addClass('ui-btn');
				$buttonContainer->addClass('ui-btn-light-border');
				$buttonContainer->addClass('report-visualconstructor-view-miniature-show-all');
				$buttonContainer->addDataAttribute('toggle-button-category-key', $categoryKey);
				$buttonContainer->addDataAttribute('type', 'show-all-button');
				$form->add($buttonContainer->start());
				$form->add(Loc::getMessage('REPORT_ADD_FORM_SHOW_ALL_BUTTON_TITLE'));
				$form->add($buttonContainer->end());
			}


			$form->add($miniatureCategoriesWrap->end());
		}




		$cellIdField = new Fields\Valuable\Hidden('cellId');
		$form->add($cellIdField);

		$boardIdField = new Fields\Valuable\Hidden('boardId');
		$boardIdField->setValue($this->arResult['BOARD_ID']);
		$boardIdField->addDataAttribute('type', 'board-id');
		$form->add($boardIdField);


		$rowLayoutMapField = new Fields\Valuable\Hidden('rowLayoutMap');
		$rowLayoutMapField->setValue('');
		$form->add($rowLayoutMapField);
		$form->add($patternWidgetIdField);

		return $form;
	}

	/**
	 * @param \Bitrix\Report\VisualConstructor\Form $form
	 * @param $categoryKey
	 */
	private function addToFormCreateWidgetMiniature(\Bitrix\Report\VisualConstructor\Form $form, $categoryKey)
	{
		$miniatureContainerForPatternWidgets = new Fields\Div();
		$miniatureContainerForPatternWidgets->addClass('report-visualconstructor-view-miniature-item');
		$miniatureContainerForPatternWidgets->addDataAttribute('type', 'create-widget-by-category');
		$miniatureContainerForPatternWidgets->addDataAttribute('category-key', $categoryKey);
		$form->add($miniatureContainerForPatternWidgets->start());

		$createWidgetByCategoryContainer = new Fields\Div();
		$createWidgetByCategoryContainer->setKey('create_widget_by_category_container_' . $categoryKey);
		$createWidgetByCategoryContainer->addClass('report-create-widget-by-category-container');

		$innerDiv = new Fields\Div();
		$innerDiv->addClass('report-create-widget-icon');

		$text  = new Fields\Div();
		$text->addClass('report-create-title-inner');
		$form->add($createWidgetByCategoryContainer->start());
			$form->add($innerDiv->start());
			$form->add($innerDiv->end());
			$form->add($text->start());
				$form->add(Loc::getMessage('REPORT_CREATE_BUTTON_TITLE'));
			$form->add($text->end());
		$form->add($createWidgetByCategoryContainer->end());


		$form->add($miniatureContainerForPatternWidgets->end());
	}

	/**
	 * @return \Bitrix\Report\VisualConstructor\Category[]
	 */
	private function  getCategories()
	{
		$categoryProvider = new \Bitrix\Report\VisualConstructor\RuntimeProvider\CategoryProvider();
		$categoryProvider->addFilter('parent_keys', $this->arResult['REPORTS_CATEGORIES']);
		$categoryProvider->addRelation('children');
		$categoryProvider->addRelation('parent');
		$categoryProvider->execute();
		return $categoryProvider->getResults();
	}

	private function getPatternWidgetListByCategories()
	{
		$categoriesList = $this->getCategories();
		$patternWidgetList = $this->getPatternWidgets();
		$widgetsList = array();
		foreach ($categoriesList as $category)
		{
			$widgetsList[$category->getKey()]['categoryOptions'] = array(
				'title' => $category->getLabel()
			);
			$widgetsList[$category->getKey()]['widgets'] = array();
		}
		$widgetsList += array(
			'myWidgets' => array(
				'categoryOptions' => array(
					'title' => Loc::getMessage('REPORT_ADD_FORM_MY_WIDGETS_CATEGORY_NAME'),
				),
				'widgets' => array(),
			),
			'notExistCategory' => array(
				'categoryOptions' => array(
					'title' => Loc::getMessage('REPORT_ADD_FORM_NOT_EXIST_CATEGORY_NAME'),
					'disableCreateOwn' => true
				),
				'widgets' => array(),
			)
		);
		foreach ($patternWidgetList as $widget)
		{
			$categoryKey = $widget['categoryKey'];
			if (!$categoryKey)
			{
				$widgetsList['myWidgets']['widgets'][] = $widget;
			}
			elseif (isset($widgetsList[$categoryKey]))
			{
				$widgetsList[$categoryKey]['widgets'][] = $widget;
			}
			else
			{
				$widgetsList['notExistCategory']['widgets'][] = $widget;
			}
		}

		return $widgetsList;
	}
}