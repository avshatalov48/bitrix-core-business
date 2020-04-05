<?php
namespace Bitrix\Report\VisualConstructor\Fields\Valuable;

use Bitrix\Report\VisualConstructor\Helper\Widget;
use Bitrix\Report\VisualConstructor\RuntimeProvider\ViewProvider;

/**
 * Preview block field, contains all exist miniature and functionality to see preview, can toggle to other view type
 * @package Bitrix\Report\VisualConstructor\Fields\Valuable
 */
class PreviewBlock extends BaseValuable
{
	const JS_EVENT_ON_VIEW_SELECT = 'onSelect';

	protected $widget;
	/**
	 * Preview block field constructor.
	 * Default view type is linear graph.
	 *
	 * @param $key
	 */
	public function __construct($key)
	{
		parent::__construct($key);
		$this->setLabel('');
		$this->setDefaultValue('linearGraph');
	}

	/**
	 * Load field component with label or previewblock template.
	 * Pass available view types list.
	 * And prepared widget params to render in preview block.
	 *
	 * @return void
	 */
	public function printContent()
	{
		$params = array(
			'AVAILABLE_VIEWS' => $this->getAvailableViewList(),
			'PREPARED_WIDGET' => Widget::prepareWidgetContent($this->getWidget(), true),
		);
		$this->includeFieldComponent('previewblock', $params);
	}

	/**
	 * @return \Bitrix\Report\VisualConstructor\Entity\Widget
	 */
	public function getWidget()
	{
		return $this->widget;
	}

	/**
	 * Preview widget setter.
	 *
	 * @param \Bitrix\Report\VisualConstructor\Entity\Widget $widget Widget will render in preview block.
	 * @return void
	 */
	public function setWidget(\Bitrix\Report\VisualConstructor\Entity\Widget $widget)
	{
		$this->widget = $widget;
		$this->setDefaultValue($widget->getViewKey());
	}

	/**
	 * @return \Bitrix\Report\VisualConstructor\View[]
	 */
	private function getAvailableViews()
	{
		static $views;
		if (!$views)
		{
			$viewProvider = new ViewProvider();
			$results = $viewProvider->execute()->getResults();
			foreach ($results as $result)
			{
				if ($result::USE_IN_VISUAL_CONSTRUCTOR)
				{
					$views[$result->getKey()] = $result;
				}
			}
		}

		return $views;
	}

	/**
	 * @return array
	 */
	private function getAvailableViewList()
	{
		$result = array();
		$views = $this->getAvailableViews();
		foreach ($views as $view)
		{
			$result[] = array(
				'key' => $view->getKey(),
				'label' => $view->getLabel(),
				'logoUrl' => $view->getLogoUri(),
			);
		}
		return $result;
	}


}