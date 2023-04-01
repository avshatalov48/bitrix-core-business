<?php

namespace Bitrix\Calendar\Rooms\Categories;



use Bitrix\Calendar\Internals\RoomCategoryTable;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Emoji;

class Category
{
	/** @var int $id*/
	private ?int $id;
	/** @var string $name */
	private string $name = '';
	/** @var Error|null $error */
	private ?Error $error = null;
	/** @var array|null $rooms */
	private ?array $rooms = null;

	/**
	 * @param int|null $id
	 *
	 * @return Category
	 */
	public function setId(?int $id): Category
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @param string|null $name
	 *
	 * @return Category
	 */
	public function setName(?string $name): Category
	{
		$this->name = Manager::checkCategoryName($name);

		return $this;
	}

	/**
	 * @param array|null $rooms
	 * @return $this
	 */
	public function setRooms(?array $rooms): Category
	{
		$this->rooms = $rooms;

		return $this;
	}

	/**
	 * @param $error
	 *
	 * @return void
	 */
	private function addError($error)
	{
		$this->error = $error;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return array|null
	 */
	public function getRooms() : ?array
	{
		return $this->rooms;
	}

	/**
	 * @return Error|null
	 */
	public function getError(): ?Error
	{
		return $this->error;
	}

	/**
	 * @return Category
	 */
	public function create(): Category
	{
		$section = RoomCategoryTable::add([
			'NAME' => Emoji::encode($this->name),
		]);
		if (!$section->isSuccess())
		{
			$this->addError(new Error('An error occurred while saving the category'));
		}

		$this->setId($section->getId());

		return $this;
	}

	/**
	 * @return Category
	 */
	public function update(): Category
	{
		$section = RoomCategoryTable::update(
			$this->id,
			[
				'NAME' => Emoji::encode($this->name),
			]
		);
		if (!$section->isSuccess())
		{
			$this->addError(new Error('An error occurred while saving the category'));
		}
		return $this;
	}

	/**
	 * @return Category
	 */
	public function delete(): Category
	{
		$category = RoomCategoryTable::delete($this->id);
		if (!$category->isSuccess())
		{
			$this->addError(new Error('An error occurred while deleting the category'));
		}
		return $this;
	}
}