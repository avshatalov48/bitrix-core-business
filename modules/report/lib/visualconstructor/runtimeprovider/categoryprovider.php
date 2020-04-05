<?php
namespace Bitrix\Report\VisualConstructor\RuntimeProvider;

use Bitrix\Report\VisualConstructor\Category;
use Bitrix\Report\VisualConstructor\Internal\Manager\CategoryManager;

/**
 * @method Category|null getFirstResult()
 * Class CategoryProvider
 * @package Bitrix\Report\VisualConstructor\RuntimeProvider
 */
class CategoryProvider extends Base
{

	/**
	 * @return array
	 */
	protected function availableFilterKeys()
	{
		return array('primary', 'parent_keys');
	}

	/**
	 * @return array
	 */
	protected function availableRelations()
	{
		return array('parent', 'children');
	}

	/**
	 * @return CategoryManager
	 */
	protected function getManagerInstance()
	{
		return CategoryManager::getInstance();
	}

	/**
	 * @return array
	 */
	protected function getEntitiesList()
	{
		return $this->getManagerInstance()->getCategoriesList();
	}

	/**
	 * @return array
	 */
	protected function getIndices()
	{
		return $this->getManagerInstance()->getIndices();
	}

	/**
	 * @param Category $entity
	 */
	protected function processWithParent(Category $entity)
	{
		$categoryProvider = new CategoryProvider();
		$categoryProvider->addFilter('primary', $entity->getParentKey());
		$categoryProvider->execute();

		$entity->parent = $categoryProvider->getResults();
	}

	/**
	 * @param Category $entity
	 */
	protected function processWithChildren(Category $entity)
	{
		$categoryProvider = new CategoryProvider();
		$categoryProvider->addFilter('parent_keys', $entity->getKey());
		$categoryProvider->addRelation('children');
		$categoryProvider->execute();

		$entity->children  = $categoryProvider->getResults();
	}


}