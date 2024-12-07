<?php

namespace Bitrix\Calendar\Integration\UI\EntitySelector\OpenEvents;

use Bitrix\Calendar\Integration\Im\EventCategoryService;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;

final class ChannelProvider extends BaseProvider
{
	public const ENTITY_ID = 'im-channel';

	private int $userId;

	public function __construct()
	{
		parent::__construct();

		$this->userId = (int)CurrentUser::get()->getId();
	}

	public function isAvailable(): bool
	{
		return $this->userId > 0;
	}

	public function getItems(array $ids): array
	{
		return $this->getChannels();
	}

	public function fillDialog(Dialog $dialog): void
	{
		$dialog->addItems($this->getChannels());
	}

	private function getChannels(): array
	{
		$channels = (new EventCategoryService())->getAvailableChannelsList($this->userId);

		return array_map(static fn (array $it) => self::makeItem($it), $channels);
	}

	private static function makeItem(array $item): Item
	{
		return new Item([
			'id' => $item['id'],
			'entityId' => self::ENTITY_ID,
			'title' => $item['title'],
			'tabs' => 'recents',
			'avatar' => $item['avatar'],
			'customData' => [
				'color' => $item['color'],
				'closed' => $item['closed'],
			],
		]);
	}
}
