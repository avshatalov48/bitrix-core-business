<?php

namespace Bitrix\Report\VisualConstructor\Internal\Manager;

use Bitrix\Report\VisualConstructor\Category;
use Bitrix\Report\VisualConstructor\Config\Common;

/**
 * Class CategoryManager
 * @package Bitrix\Report\VisualConstructor\Internal\Manager
 */
class CategoryManager extends Base
{
	private static $categoriesList = array();
	private static $indices = array(
		'parent_keys' => array(),
	);

	/**
	 * @return string
	 */
	protected function getEventTypeKey()
	{
		return Common::EVENT_CATEGORY_COLLECT;
	}

	/**
	 * @return array
	 */
	public function call()
	{
		if (empty(self::$categoriesList))
		{
			/** @var Category[] $categories */
			$categories = $this->getResult();
			foreach ($categories as $key => $category)
			{
				self::$categoriesList[$category->getKey()] = $category;
				$parentKey = $category->getParentKey() ?: 'HEAD';
				self::$indices['parent_keys'][$parentKey][] = $category->getKey();
			}
		}

		return self::$categoriesList;
	}

	/**
	 * @return array
	 */
	public function getCategoriesList()
	{
		return self::$categoriesList;
	}

	/**
	 * @return array
	 */
	public function getIndices()
	{
		return self::$indices;
	}




}