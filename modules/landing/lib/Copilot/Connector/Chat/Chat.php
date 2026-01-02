<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\Chat;

use Bitrix\Main\Loader;
use Bitrix\Main\Security;
use Bitrix\Landing;
use Bitrix\Landing\Copilot\Model\SiteToChatTable;

class Chat
{
	private int $userId;

	public function __construct()
	{
		$this->userId = Landing\Manager::getUserId();
	}

	/**
	 * @param int $userId
	 * @return Chat
	 */
	public function setUserId(int $userId): self
	{
		if ($userId > 0)
		{
			$this->userId = $userId;
		}

		return $this;
	}

	public static function getCreateSiteChatBot(): ?ICopilotChatBot
	{
		return self::getChatBot(CreateSiteChatBot::class);
	}

	public static function getChangeBlockChatBot(): ?ICopilotChatBot
	{
		return self::getChatBot(ChangeBlockChatBot::class);
	}

	/**
	 * Correctly create ai-chat bot
	 * @param string $botClass
	 * @return ICopilotChatBot|null
	 */
	private static function getChatBot(string $botClass): ?ICopilotChatBot
	{
		if (!Loader::includeModule('ai'))
		{
			return null;
		}

		static $chatBots = [];
		if (!isset($chatBots[$botClass]))
		{
			/**
			 * @var ICopilotChatBot $chatBot
			 */
			$chatBot = new $botClass();
			$chatBot?->register();
			$chatBots[$botClass] = $chatBot;
		}

		return $chatBots[$botClass];
	}

	/**
	 * Create unique string for chat entity id
	 * @return string
	 */
	public static function createChatEntityId(): string
	{
		return 'site_copilot_' . Security\Random::getString(6);
	}

	public function setChatForSite(int $siteId, int $chatId): bool
	{
		$exists = SiteToChatTable::query()
			->where('SITE_ID', $siteId)
			->where('CHAT_ID', $chatId)
			->where('USER_ID', $this->userId)
			->fetch()
		;
		if ($exists)
		{
			return false;
		}

		$add = SiteToChatTable::add([
			'SITE_ID' => $siteId,
			'CHAT_ID' => $chatId,
			'USER_ID' => $this->userId,
		]);

		return $add->isSuccess();
	}

	public function getChatForSite(int $siteId): ?int
	{
		$exists = SiteToChatTable::query()
			->setSelect(['CHAT_ID'])
			->where('SITE_ID', $siteId)
			->where('USER_ID', $this->userId)
			->fetch()
		;
		if ($exists)
		{
			return (int)$exists['CHAT_ID'];
		}

		return null;
	}
}