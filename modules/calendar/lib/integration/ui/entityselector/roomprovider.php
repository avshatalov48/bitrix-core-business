<?php
namespace Bitrix\Calendar\Integration\UI\EntitySelector;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;

class RoomProvider extends \Bitrix\UI\EntitySelector\BaseProvider
{
	public const ENTITY_ID = 'room';

	public function __construct(array $options = [])
	{
		parent::__construct();
		$this->prepareOptions($options);
	}

	protected function prepareOptions(array $options  = [])
	{

	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function shouldFillDialog(): bool
	{
		return $this->getOption('fillDialog', true);
	}

	public function getItems(array $ids): array
	{
		$roomList = \Bitrix\Calendar\Rooms\Manager::getRoomsList();

		return $this->getItemsFromRoomList($roomList);
	}

	public function getSelectedItems(array $ids): array
	{
		$roomList = \Bitrix\Calendar\Rooms\Manager::getRoomsList();

		return $this->getItemsFromRoomList($roomList);
	}

	public function getItemsFromRoomList(array $roomList)
	{
		$items = [];
		foreach ($roomList as $room)
		{
			$items[] = $this->makeItem(['id' => $room['ID'], 'title' => $room['NAME'], 'color' => $room['COLOR']]);
		}

		return $items;
	}

	public function makeItem(array $item, array $options = []): Item
	{
		$itemOptions = [
			'id' => $item['id'],
			'entityId' => self::ENTITY_ID,
			'title' => $item['title'],
			'avatarOptions' => [
				'bgColor' => $item['color'],
				'bgSize' => '22px',
				'bgImage' => '',
			],
			'tabs' => 'room',
		];

		return new Item($itemOptions);
	}

	public function getRooms()
	{
		$roomList = \Bitrix\Calendar\Rooms\Manager::getRoomsList();
		return $this->getItemsFromRoomList($roomList);
	}

	public function fillDialog(Dialog $dialog): void
	{
		$dialog->addItems($this->getRooms());
	}
}