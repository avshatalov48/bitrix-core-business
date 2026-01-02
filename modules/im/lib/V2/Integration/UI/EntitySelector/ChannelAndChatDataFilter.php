<?php
namespace Bitrix\Im\V2\Integration\UI\EntitySelector;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\EntitySelector\BaseFilter;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;

/**
 * This data filter is used to transform channels and chats
 * It includes subtitle with user count, makes avatars from item options usable,
 * and optionally puts items into tabs
 */
class ChannelAndChatDataFilter extends BaseFilter
{
	/**
	 * @param array $options
	 *    $params = [
	 *      'tabId'				=> (int) id of tab options object
	 *      'includeSubtitle'	=> (bool) should we add chat type below chat name
	 *    ]
	 */
	public function __construct(array $options = [])
	{
		parent::__construct();

		if (isset($options['tabId']) && is_string($options['tabId']))
		{
			$this->options['tabId'] = $options['tabId'];
		}

		$this->options['includeSubtitle'] = $options['includeSubtitle'] ?? true;
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function apply(array $items, Dialog $dialog): void
	{
		if ($this->options['includeSubtitle'])
		{
			foreach ($items as $item)
			{
				$chat = $item->getCustomData()->get('chat');
				$subtitle = match ($chat['messageType'] ?? null) {
					'C', 'O' => Loc::getMessage('IM_ENTITY_SELECTOR_CHAT_SUBTITLE_TYPE_CHAT'),
					'J' => Loc::getMessage('IM_ENTITY_SELECTOR_CHAT_SUBTITLE_TYPE_OPEN_CHANNEL'),
					'N' => Loc::getMessage('IM_ENTITY_SELECTOR_CHAT_SUBTITLE_TYPE_CLOSED_CHANNEL'),
					default => '',
				};
				$item->setSubtitle($subtitle);
			}
		}

		foreach ($items as $item)
		{
			if (!($item instanceof Item))
			{
				continue;
			}

			// allow default avatar if empty
			if ($item->getAvatar() === '')
			{
				$item->setAvatar(null);
			}

			if (isset($this->options['tabId']))
			{
				$item->addTab($this->options['tabId']);
			}
		}
	}
}
