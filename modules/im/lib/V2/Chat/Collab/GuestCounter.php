<?php

namespace Bitrix\Im\V2\Chat\Collab;

use Bitrix\Im\Common;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Entity\User\UserCollaber;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Main\Application;
use Bitrix\Pull\Event;

class GuestCounter
{
	protected const CACHE_TTL = 18144000;

	protected static array $staticCache = [];

	protected Chat $chat;
	protected ?RelationCollection $relations = null;

	public function __construct(Chat $chat)
	{
		$this->chat = $chat;
	}

	public function getGuestCount(): int
	{
		if (isset(self::$staticCache[$this->chat->getId()]))
		{
			return self::$staticCache[$this->chat->getId()];
		}

		$cache = Application::getInstance()->getCache();
		if ($cache->initCache(self::CACHE_TTL, $this->getCacheId(), $this->getCacheDir()))
		{
			$cachedGuestCount = $cache->getVars();

			if (!is_array($cachedGuestCount))
			{
				$cachedGuestCount = [];
			}

			return self::$staticCache[$this->chat->getId()] = $cachedGuestCount['guestCount'] ?? 0;
		}

		self::$staticCache[$this->chat->getId()] = $this->getGuestCountByRelation();

		$cache->startDataCache();
		$cache->endDataCache(['guestCount' => self::$staticCache[$this->chat->getId()]]);

		return self::$staticCache[$this->chat->getId()];
	}

	public function sendPushGuestCount(): void
	{
		$relations = $this->relations ?? $this->chat->getRelations();

		$push = [
			'module_id' => 'im',
			'command' => 'updateCollabGuestCount',
			'params' => [
				'dialogId' => $this->chat->getDialogId(),
				'chatId' => $this->chat->getId(),
				'guestCount' => $this->getGuestCount(),
			],
			'extra' => Common::getPullExtra()
		];
		Event::add($relations->getUserIds(), $push);
	}

	protected function getGuestCountByRelation(): int
	{
		$guestCount = 0;

		$relations = $this->relations ?? $this->chat->getRelations();
		foreach ($relations->getUsers() as $user)
		{
			if ($user instanceof UserCollaber)
			{
				$guestCount++;
			}
		}

		return $guestCount;
	}

	public function setRelations(RelationCollection $relations): self
	{
		$this->relations = $relations;
		return $this;
	}

	private function getCacheId(): string
	{
		return "chat_collab_guest_count_{$this->chat->getId()}";
	}

	private function getCacheDir(): string
	{
		return static::getCacheDirByChatId($this->chat->getId());
	}

	private static function getCacheDirByChatId(int $chatId): string
	{
		$cacheSubDir = $chatId % 100;

		return "/bx/im/collab/guest_count/v1/{$cacheSubDir}/{$chatId}";
	}

	public static function cleanCache(int $chatId): void
	{
		unset(self::$staticCache[$chatId]);
		Application::getInstance()->getCache()->cleanDir(static::getCacheDirByChatId($chatId));
	}
}
