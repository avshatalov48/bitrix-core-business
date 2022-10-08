<?php
namespace Bitrix\Calendar\Core\Builders\Rooms;

class RoomBuilderFromRequest extends RoomBuilder
{
	/** @var \Bitrix\Main\HttpRequest | \Bitrix\Main\Request */
	private $request;

	/**
	 * @param $request
	 */
	public function __construct($request)
	{
		$this->request = $request;
	}

	function getId(): int
	{
		return (int)$this->request->getPost('id');
	}

	function getLocationId(): int
	{
		return (int)$this->request->getPost('location_id');
	}

	function getCapacity()
	{
		return $this->request->getPost('capacity');
	}

	function getNecessity()
	{
		return $this->request->getPost('necessity');
	}

	function getName()
	{
		return $this->request->getPost('name');
	}

	function getColor()
	{
		return $this->request->getPost('color');
	}

	function getOwnerId(): int
	{
		return (int)$this->request->getPost('ownerId');
	}

	function getAccess()
	{
		return $this->request->getPost('access');
	}

	function getCategoryId(): int
	{
		return (int)$this->request->getPost('categoryId');
	}
}