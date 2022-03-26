<?php

namespace Bitrix\Calendar\Rooms;

use Bitrix\Calendar\Internals\LocationTable;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

class Room
{
	const TYPE = 'location';
	
	/** @var int $id*/
	private $id;
	/** @var int $locationId */
	private $locationId;
	/** @var int $capacity */
	private $capacity;
	/** @var string $necessity */
	private $necessity;
	/** @var string $name */
	private $name;
	/** @var string $type */
	private $type = self::TYPE;
	/** @var string $color */
	private $color;
	/** @var int $ownerId */
	private $ownerId;
	/** @var int $createdBy */
	private $createdBy;
	/** @var array $access */
	private $access;
	/** @var Error $error */
	private $error;
	
	protected function __construct()
	{
	}
	
	public static function createInstanceFromRequest($request): Room
	{
		$room = new self();
		$room->setId($request->getPost('id'))
			->setLocationId($request->getPost('location_id'))
			->setCapacity($request->getPost('capacity'))
			->setNecessity($request->getPost('necessity'))
			->setName($request->getPost('name'))
			->setColor($request->getPost('color'))
			->setOwnerId($request->getPost('ownerId'))
			->setCreatedBy()
			->setAccess($request->getPost('access'));
		
		return $room;
	}

	public static function createInstanceFromParams($params): Room
	{
		$room = new self();
		$room->setId($params['ID'])
			->setLocationId($params['LOCATION_ID'])
			->setCapacity($params['CAPACITY'])
			->setNecessity($params['NECESSITY'])
			->setName($params['NAME'])
			->setColor($params['COLOR'])
			->setOwnerId($params['OWNER_ID'])
			->setCreatedBy()
			->setAccess($params['ACCESS']);

		return $room;
	}
	
	public function createInstance(): Room
	{
		return new self();
	}
	
	private function setId($id): Room
	{
		$this->id = (int)$id;
		
		return $this;
	}
	
	private function setLocationId($locationId): Room
	{
		$this->locationId = (int)$locationId;
		
		return $this;
		
	}
	
	private function setCapacity($capacity): Room
	{
		$this->capacity = (int)$capacity;
		
		return $this;
	}
	
	private function setNecessity($necessity): Room
	{
		$this->necessity = ($necessity === 'Y') ? 'Y' : 'N';
		
		return $this;
	}
	
	public function setName($name): Room
	{
		$this->name = Manager::checkRoomName($name);
		
		return $this;
	}
	
	private function setColor($color): Room
	{
		$this->color = \CCalendar::Color($color);
		
		return $this;
	}
	
	private function setOwnerId($ownerId): Room
	{
		$this->ownerId = (int)$ownerId;
		
		return $this;
	}
	
	private function setCreatedBy(): Room
	{
		$this->createdBy = \CCalendar::GetCurUserId();
		
		return $this;
	}
	
	private function setAccess($access): Room
	{
		$this->access = $access;
		
		return $this;
	}
	
	private function addError($error)
	{
		$this->error = $error;
	}
	
	public function getId(): int
	{
		return $this->id;
	}
	
	public function getLocationId(): int
	{
		return $this->locationId;
	}
	
	public function getCapacity(): int
	{
		return $this->capacity;
	}
	
	public function getNecessity(): string
	{
		return $this->necessity;
	}
	
	public function getName(): string
	{
		return $this->name;
	}
	
	public function getType(): string
	{
		return $this->type;
	}
	
	public function getColor(): string
	{
		return $this->color;
	}
	
	public function getOwnerId(): int
	{
		return $this->ownerId;
	}
	
	public function getCreatedBy(): int
	{
		return $this->createdBy;
	}
	
	public function getAccess(): array
	{
		return $this->access;
	}
	
	public function getError(): ?Error
	{
		return $this->error;
	}
	
	/**
	 * @return $this
	 */
	public function create(): Room
	{
		$section = SectionTable::add(
			[
				'CAL_TYPE' => $this->type,
				'NAME' => $this->name,
				'COLOR' => $this->color,
				'OWNER_ID' => $this->ownerId,
				'SORT' => 100,
				'CREATED_BY' => $this->createdBy,
				'DATE_CREATE' => new DateTime(),
				'TIMESTAMP_X' => new DateTime(),
				'ACTIVE' => 'Y',
			]
		);
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
	 */
	public function update(): Room
	{
		$section = SectionTable::update(
			$this->id,
			[
				'NAME' => $this->name,
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