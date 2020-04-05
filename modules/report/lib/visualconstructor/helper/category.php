<?php
namespace Bitrix\Report\VisualConstructor\Helper;

/**
 * Helper Class for working with categories of report, widget
 * @package Bitrix\Report\VisualConstructor\Helper
 */
class Category
{
	/**
	 * Build readable categories tree.
	 *
	 * @param  \Bitrix\Report\VisualConstructor\Category[] $categories Categories collection.
	 * @param array $options Array of options.
	 * @param int $depth Service parameter, to calculate '-' count.
	 * @return array
	 */
	public static function getOptionsTree($categories, $options = array(), $depth = 0)
	{

		$prefix = str_repeat('-', $depth);
		$depth++;

		foreach ($categories as $category)
		{
			$options[$category->getKey()] = $prefix . $category->getLabel();
			$options = self::getOptionsTree($category->children, $options, $depth);
		}

		return $options;
	}
}