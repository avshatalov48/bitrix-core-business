<?php
namespace Bitrix\Report\VisualConstructor\RuntimeProvider;

use Bitrix\Report\VisualConstructor\Internal\Manager\ViewManager;
use Bitrix\Report\VisualConstructor\View;

/**
 * @method View|null getFirstResult()
 * @method View[] getResults()
 * Class ViewProvider
 * @package Bitrix\Report\VisualConstructor\RuntimeProvider
 */
class ViewProvider extends Base
{
	/**
	 * @return array
	 */
	protected function availableFilterKeys()
	{
		return array('primary', 'dataType');
	}

	/**
	 * @return ViewManager
	 */
	protected function getManagerInstance()
	{
		return ViewManager::getInstance();
	}

	/**
	 * @return View[]
	 */
	protected function getEntitiesList()
	{
		return $this->getManagerInstance()->getViewControllers();
	}

	/**
	 * @return array
	 */
	protected function getIndices()
	{
		return $this->getManagerInstance()->getIndices();
	}

	/**
	 * @param string $viewKey View controller key.
	 * @return View|null
	 */
	public static function getViewByViewKey($viewKey)
	{
		$viewProvider = new ViewProvider();
		$viewProvider->addFilter('primary', $viewKey);
		return $viewProvider->execute()->getFirstResult();
	}
}