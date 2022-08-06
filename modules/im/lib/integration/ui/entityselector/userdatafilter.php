<?php
namespace Bitrix\Im\Integration\UI\EntitySelector;

use Bitrix\Im\User;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\EntitySelector\BaseFilter;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;

class UserDataFilter extends BaseFilter
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
		foreach ($items as $item)
		{
			if (!($item instanceof Item))
			{
				continue;
			}

			if ($item->getId() === Helper\User::getCurrentUserId())
			{
				$item->addBadges([[
					'id' => 'IT_IS_YOU',
					'title' => Loc::getMessage('IM_UI_ENTITY_SELECTOR_IT_IS_YOU'),
				]]);
			}

			$customData = $item->getCustomData();
			$userInfo = User::getInstance($item->getId())->getArray();
			$customData->set('imUser', $userInfo);

			//TODO delete after immobile chatselector fix with revision 21c5a9948579
			$defaultIcon = '';
			if (!$item->getAvatar())
			{
				$item->setAvatar($defaultIcon);
			}
		}
	}
}
