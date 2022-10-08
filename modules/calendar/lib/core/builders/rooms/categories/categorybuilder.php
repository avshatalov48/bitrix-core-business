<?php

namespace Bitrix\Calendar\Core\Builders\Rooms\Categories;

use Bitrix\Calendar\Core\Builders\Builder;
use Bitrix\Calendar\Rooms\Categories\Category;

abstract class CategoryBuilder implements Builder
{
	private Category $category;

	/**
	 * @return Category
	 */
	public function build(): Category
	{
		return
			$this
				->getBaseCategory()
				->setId($this->getId())
				->setName($this->getName())
				->setRooms($this->getRooms())
		;
	}

	abstract protected function getId();
	abstract protected function getName();
	abstract protected function getRooms();

	protected function getBaseCategory(): Category
	{
		if(empty($this->category))
		{
			$this->category = new Category();
		}

		return $this->category;
	}
}