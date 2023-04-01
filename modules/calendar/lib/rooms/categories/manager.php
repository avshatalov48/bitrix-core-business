<?php

namespace Bitrix\Calendar\Rooms\Categories;

use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Calendar\Internals\LocationTable;
use Bitrix\Calendar\Internals\RoomCategoryTable;
use Bitrix\Calendar\UserSettings;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Emoji;

/**
 * Manager for categories
 */
class Manager
{
	/** @var Category $category */
	private Category $category;
	/** @var Error $error */
	private $error;

	protected function __construct()
	{
	}

	public static function createInstance(Category $category): Manager
	{
		return (new self())->setCategory($category);
	}

	public function setCategory(Category $category): Manager
	{
		$this->category = $category;

		return $this;
	}

	private function addError(Error $error): Manager
	{
		$this->error = $error;

		return $this;
	}

	public function getCategory(): Category
	{
		return $this->category;
	}

	public function getError(): ?Error
	{
		return $this->error;
	}

	/**
	 * Creating Room Category in Location Calendar
	 *
	 * @return Manager
	 */
	public function createCategory(): Manager
	{
		if($this->getError())
		{
			return $this;
		}

		$this->category->create();

		if($this->category->getError())
		{
			$this->addError($this->category->getError());
		}

		$createdCategoryId = $this->category->getId();
		$rooms = $this->category->getRooms();
		if(!empty($rooms) && $createdCategoryId)
		{
			foreach ($rooms as &$room)
			{
				$room = (int)$room;
			}
			global $DB;
			$tableName = LocationTable::getTableName();
			$roomsIds = implode(',', $rooms);

			$sqlStr = "
				UPDATE $tableName
				SET CATEGORY_ID = $createdCategoryId
				WHERE SECTION_ID IN ($roomsIds)
			";
			$result = $DB->Query($sqlStr, true);
			if(!$result)
			{
				$this->category->delete();
				$this->addError(new Error('An error occurred while saving the category'));
			}
		}

		return $this;
	}

	/**
	 * Updating data of room category in Location calendar
	 *
	 * @return Manager
	 */
	public function updateCategory(): Manager
	{
		if ($this->getError())
		{
			return $this;
		}

		$this->category->update();

		if ($this->category->getError())
		{
			$this->addError($this->category->getError());
		}

		global $DB;
		$tableName = LocationTable::getTableName();
		$categoryId = $this->category->getId();
		$rooms = $this->category->getRooms();

		$result = true;

		if(isset($rooms['toAddCategory']))
		{
			foreach ($rooms['toAddCategory'] as &$toAddId)
			{
				$toAddId = (int)$toAddId;
			}
			$toAddIds = implode(',', $rooms['toAddCategory']);
			$sqlStr = "
				UPDATE $tableName
				SET CATEGORY_ID = $categoryId
				WHERE SECTION_ID IN ($toAddIds)
			";
			$result = $DB->Query($sqlStr, true);
		}

		if($result && isset($rooms['toRemoveCategory']))
		{
			foreach ($rooms['toRemoveCategory'] as &$toRemoveId)
			{
				$toRemoveId = (int)$toRemoveId;
			}
			$toRemoveIds = implode(',', $rooms['toRemoveCategory']);
			$sqlStr = "
				UPDATE $tableName
				SET CATEGORY_ID = null
				WHERE SECTION_ID IN ($toRemoveIds)
			";
			$result = $DB->Query($sqlStr, true);
		}

		if(!$result)
		{
			$this->addError(new Error('An error occurred while saving the category'));
		}

		return $this;
	}

	/**
	 * Deleting room category by id in Location calendar
	 *
	 * @return Manager
	 */
	public function deleteCategory(): Manager
	{
		if ($this->getError())
		{
			return $this;
		}

		$this->category->delete();

		if ($this->category->getError())
		{
			$this->addError($this->category->getError());
		}

		global $DB;
		$tableName = LocationTable::getTableName();
		$categoryId = $this->category->getId();

		$DB->Query("
			UPDATE $tableName
			SET CATEGORY_ID = null
			WHERE CATEGORY_ID = $categoryId
		");

		return $this;
	}

	public static function getCategoryList()
	{
		$categories = RoomCategoryTable::getList([
				'select' => [
					'ID',
					'NAME',
				]
			])
			->fetchAll()
		;

		foreach ($categories as &$category)
		{
			$category['NAME'] = Emoji::decode($category['NAME']);
		}

		return $categories;
	}

	/**
	 * @param $name
	 * Validation for name of room category
	 *
	 * @return string|null
	 */
	public static function checkCategoryName(?string $name): ?string
	{
		$name = trim($name);

		if (empty($name))
		{
			return '';
		}

		return $name;
	}

	public function addPullEvent($event): Manager
	{
		if ($this->getError())
		{
			return $this;
		}

		\Bitrix\Calendar\Util::addPullEvent(
			$event,
			\CCalendar::GetCurUserId(),
			[
				'ID' => $this->category->getId()
			],
		);

		return $this;
	}

	public function clearCache(): Manager
	{
		\Bitrix\Calendar\Rooms\Manager::createInstance()->clearCache();

		return $this;
	}
}