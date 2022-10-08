<?php

namespace Bitrix\Calendar\Core\Builders\Rooms;

use Bitrix\Calendar\Rooms\Room;

abstract class RoomBuilder implements \Bitrix\Calendar\Core\Builders\Builder
{
	private Room $room;

	/**
	 * @return Room
	 */
	public function build(): Room
	{
		return
			$this
				->getBaseRoom()
				->setId($this->getId())
				->setLocationId($this->getLocationId())
				->setCapacity($this->getCapacity())
				->setNecessity($this->getNecessity())
				->setName($this->getName())
				->setColor($this->getColor())
				->setOwnerId($this->getOwnerId())
				->setCreatedBy()
				->setAccess($this->getAccess())
				->setCategoryId($this->getCategoryId())
		;
	}

	abstract function getId();
	abstract function getLocationId();
	abstract function getCapacity();
	abstract function getNecessity();
	abstract function getName();
	abstract function getColor();
	abstract function getOwnerId();
	abstract function getAccess();
	abstract function getCategoryId();

	/**
	 * @return Room
	 */
	protected function getBaseRoom(): Room
	{
		if(empty($this->room))
		{
			$this->room = new Room();
		}

		return $this->room;
	}
}