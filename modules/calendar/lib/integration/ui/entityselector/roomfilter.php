<?php
namespace Bitrix\Calendar\Integration\UI\EntitySelector;

use Bitrix\Im\User;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\EntitySelector\BaseFilter;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;

class RoomFilter extends BaseFilter
{
	public function __construct()
	{
		parent::__construct();
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function apply(array $items, Dialog $dialog): void
	{
		$categories = \Bitrix\Calendar\Rooms\Categories\Manager::getCategoryList();
		$rooms = \Bitrix\Calendar\Rooms\Manager::getRoomsList();
		foreach ($items as $item)
		{
			if (!($item instanceof Item))
			{
				continue;
			}
			$categoryId = 0;
			$color = '';
			foreach ($rooms as $room)
			{
				if($item->getId() === $room['ID'])
				{
					$categoryId = $room['CATEGORY_ID'];
					$color = $room['COLOR'];
					break;
				}
			}

			$customData = $item->getCustomData();
			$customData->set('room', ['COLOR' => $color]);
			$item->setAvatarOptions([
				'bgColor' => $color,
				'bgSize' => '22px',
				'bgImage' => 'none',
			]);
			$item->setBadgesOptions([
				'fitContent' => true,
				'maxWidth' => '230px',
			]);
			if(!$categoryId)
			{
				continue;
			}

			foreach ($categories as $category)
			{
				if($categoryId === $category['ID'])
				{
					$item->addBadges([[
						'id' => 'CATEGORY',
						'title' => $category['NAME'],
					]]);
					break;
				}
			}
		}
	}
}