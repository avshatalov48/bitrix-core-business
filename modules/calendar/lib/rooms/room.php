<?php

namespace Bitrix\Calendar\Rooms;

use Bitrix\Calendar\Internals\LocationTable;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

class Room
{
	public const TYPE = 'location';

	/** @var int|null $id*/
	private ?int $id = null;
	/** @var int|null $locationId */
	private ?int$locationId = null;
	/** @var int|null  $capacity */
	private ?int $capacity = null;
	/** @var string $necessity */
	private string $necessity = 'N';
	/** @var string $name */
	private string $name = '';
	/** @var string $type */
	private string $type = self::TYPE;
	/** @var string $color */
	private string $color = '#9dcf00';
	/** @var int|null $ownerId */
	private ?int $ownerId = null;
	/** @var int|null $createdBy */
	private ?int $createdBy = null;
	/** @var int|null $categoryId */
	private ?int $categoryId = null;
	/** @var array|null $access */
	private ?array $access = [];
	/** @var Error|null $error */
	private ?Error $error = null;

	/**
	 * @param $params
	 *
	 * @return Room
	 */
	public static function createInstanceFromParams($params): Room
	{
		$params = [
			'ID' => $params['ID'] ?? null,
			'LOCATION_ID' => $params['LOCATION_ID'] ?? null,
			'CAPACITY' => $params['CAPACITY'] ?? null,
			'NECESSITY' => $params['NECESSITY'] ?? null,
			'NAME' => $params['NAME'] ?? null,
			'COLOR' => $params['COLOR'] ?? null,
			'OWNER_ID' => $params['OWNER_ID'] ?? null,
			'ACCESS' => $params['ACCESS'] ?? null,
			'CATEGORY_ID' => $params['CATEGORY_ID'] ?? null,
		];

		$room = new self();
		$room->setId($params['ID'])
			->setLocationId($params['LOCATION_ID'])
			->setCapacity($params['CAPACITY'])
			->setNecessity($params['NECESSITY'])
			->setName($params['NAME'])
			->setColor($params['COLOR'])
			->setOwnerId($params['OWNER_ID'])
			->setCreatedBy()
			->setAccess($params['ACCESS'])
			->setCategoryId($params['CATEGORY_ID'])
		;

		return $room;
	}

	/**
	 * @return Room
	 */
	public function createInstance(): Room
	{
		return new self();
	}

	/**
	 * @param int|null $id
	 *
	 * @return Room
	 */
	public function setId(?int $id = null): Room
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @param int|null $locationId
	 *
	 * @return Room
	 */
	public function setLocationId(?int $locationId = null): Room
	{
		$this->locationId = $locationId;

		return $this;

	}

	/**
	 * @param int|null $capacity
	 *
	 * @return Room
	 */
	public function setCapacity(?int $capacity = null): Room
	{
		$this->capacity = $capacity;

		return $this;
	}

	/**
	 * @param string|null $necessity
	 *
	 * @return Room
	 */
	public function setNecessity(?string $necessity = 'N'): Room
	{
		$this->necessity = ($necessity === 'Y') ? 'Y' : 'N';

		return $this;
	}

	/**
	 * @param string|null $name
	 *
	 * @return $this
	 */
	public function setName(?string $name = ''): Room
	{
		$this->name = Manager::checkRoomName($name);

		return $this;
	}

	/**
	 * @param string|null $color
	 *
	 * @return Room
	 */
	public function setColor(?string $color = ''): Room
	{
		$this->color = \CCalendar::Color($color);

		return $this;
	}

	/**
	 * @param int|null $ownerId
	 *
	 * @return Room
	 */
	public function setOwnerId(?int $ownerId = null): Room
	{
		$this->ownerId = $ownerId;

		return $this;
	}

	/**
	 * @return Room
	 */
	public function setCreatedBy(): Room
	{
		$this->createdBy = \CCalendar::GetCurUserId();

		return $this;
	}

	/**
	 * @param array|null $access
	 *
	 * @return Room
	 */
	public function setAccess(?array $access = []): Room
	{
		$this->access = $access;

		return $this;
	}

	/**
	 * @param int|null $categoryId
	 *
	 * @return Room
	 */
	public function setCategoryId(?int $categoryId = null): Room
	{
		$this->categoryId = $categoryId ? $categoryId : null;

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
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @return int|null
	 */
	public function getLocationId(): ?int
	{
		return $this->locationId;
	}

	/**
	 * @return int|null
	 */
	public function getCapacity(): ?int
	{
		return $this->capacity;
	}

	/**
	 * @return string
	 */
	public function getNecessity(): ?string
	{
		return $this->necessity;
	}

	/**
	 * @return string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getType(): ?string
	{
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getColor(): ?string
	{
		return $this->color;
	}

	/**
	 * @return int|null
	 */
	public function getOwnerId(): ?int
	{
		return $this->ownerId;
	}

	/**
	 * @return int|null
	 */
	public function getCreatedBy(): ?int
	{
		return $this->createdBy;
	}

	/**
	 * @return array
	 */
	public function getAccess(): ?array
	{
		return $this->access;
	}

	/**
	 * @return int|null
	 */
	public function getCategoryId(): ?int
	{
		return $this->categoryId;
	}

	/**
	 * @return Error|null
	 */
	public function getError(): ?Error
	{
		return $this->error;
	}

	/**
	 * @return $this
	 * @throws \Exception
	 */
	public function create(): Room
	{
		$section = SectionTable::add([
			'CAL_TYPE' => $this->type,
			'NAME' => Emoji::encode($this->name),
			'COLOR' => $this->color,
			'OWNER_ID' => $this->ownerId,
			'SORT' => 100,
			'CREATED_BY' => $this->createdBy,
			'DATE_CREATE' => new DateTime(),
			'TIMESTAMP_X' => new DateTime(),
			'ACTIVE' => 'Y',
		]);
		if (!$section->isSuccess())
		{
			$this->addError(Loc::getMessage('EC_ROOM_SAVE_ERROR'));

			return $this;
		}
		$this->setId($section->getId());

		$location = LocationTable::add(
			[
				'SECTION_ID' => $this->id,
				'NECESSITY' => $this->necessity,
				'CAPACITY' => $this->capacity,
				'CATEGORY_ID' => $this->categoryId,
			]
		);
		if (!$location->isSuccess())
		{
			SectionTable::delete($this->id);
			$this->addError(new Error(Loc::getMessage('EC_ROOM_SAVE_ERROR')));

			return $this;
		}

		return $this;
	}

	/**
	 * @return $this
	 * @throws \Exception
	 */
	public function update(): Room
	{
		$section = SectionTable::update(
			$this->id,
			[
				'NAME' => Emoji::encode($this->name),
				'COLOR' => $this->color,
				'TIMESTAMP_X' => new DateTime(),
			]
		);
		if (!$section->isSuccess())
		{
			$this->addError(new Error(Loc::getMessage('EC_ROOM_SAVE_ERROR')));

			return $this;
		}
		$this->setId($section->getId());

		$location = LocationTable::update(
			$this->locationId,
			[
				'NECESSITY' => $this->necessity,
				'CAPACITY' => $this->capacity,
				'CATEGORY_ID' => $this->categoryId,
			]
		);
		if (!$location->isSuccess())
		{
			$this->addError(new Error(Loc::getMessage('EC_ROOM_SAVE_ERROR')));

			return $this;
		}

		return $this;
	}

	/**
	 * @return $this
	 * @throws \Exception
	 */
	public function delete(): Room
	{
		$section = SectionTable::delete($this->id);
		if (!$section->isSuccess())
		{
			$this->addError(new Error(Loc::getMessage('EC_ROOM_DELETE_ERROR')));

			return $this;
		}

		$location = LocationTable::delete($this->locationId);
		if (!$location->isSuccess())
		{
			$this->addError(new Error(Loc::getMessage('EC_ROOM_DELETE_ERROR')));

			return $this;
		}

		return $this;
	}
}