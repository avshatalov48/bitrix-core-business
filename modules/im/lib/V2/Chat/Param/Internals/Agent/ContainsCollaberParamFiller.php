<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Chat\Param\Internals\Agent;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\EO_Chat_Collection;
use Bitrix\Im\Model\EO_ChatParam;
use Bitrix\Im\Model\EO_ChatParam_Collection;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Chat\Param\Params;
use Bitrix\Im\V2\Entity\User\UserCollection;
use Bitrix\Im\V2\Entity\User\UserType;
use Bitrix\Main\Config\Option;

class ContainsCollaberParamFiller
{
	private EO_ChatParam_Collection $storage;
	private EO_Chat_Collection $chats;
	private array $chatsRelationMap = [];
	private static int $lastChatId;

	private const LIMIT = 50;

	public function __construct()
	{
		$this->storage = new EO_ChatParam_Collection();
	}

	public static function execute(): string
	{
		if (self::isCompleted())
		{
			return '';
		}

		self::$lastChatId = (int)(func_get_args()[0] ?? self::getMaxChatId());

		if (self::$lastChatId <= 0)
		{
			self::setCompletedOption();

			return '';
		}

		$doNeedToContinue = (new self())->run();

		if (!$doNeedToContinue)
		{
			self::setCompletedOption();

			return '';
		}

		return self::getAgentName();
	}

	private static function isCompleted(): bool
	{
		return Option::get('im', 'contains_collaber_param_filler_completed', 'N') === 'Y';
	}

	private static function setCompletedOption(): void
	{
		Option::set('im', 'contains_collaber_param_filler_completed', 'Y');
	}

	private static function getMaxChatId(): ?int
	{
		$query = ChatTable::query()
			->setSelect(['ID'])
			->setOrder(['ID' => 'DESC'])
			->setLimit(1)
		;

		return $query->exec()->fetchObject()?->getId();
	}

	private static function getAgentName(): string
	{
		return '\\' . self::class . '::execute(' . self::$lastChatId . ');';
	}

	public function run(): bool
	{
		$this->fetchChats();
		if ($this->chats->isEmpty())
		{
			return false;
		}

		$this->fetchChatsRelationMap();
		$this->fillContainsCollaberParam();

		$this->save();
		$this->setLastChatId();

		return self::$lastChatId !== 0;
	}

	private function fetchChats(): void
	{
		$query = ChatTable::query()
			->setSelect(['ID'])
			->setFilter([
				'<=ID' => self::$lastChatId,
			])
			->setOrder(['ID' => 'DESC'])
			->setLimit(self::LIMIT)
		;

		$this->chats = $query->exec()->fetchCollection();
	}

	private function fillContainsCollaberParam(): void
	{
		foreach ($this->chats as $chat)
		{
			$userIds = $this->chatsRelationMap[$chat->getId()] ?? [];
			if (empty($userIds))
			{
				continue;
			}

			if (!UserCollection::hasUserByType($userIds, UserType::COLLABER))
			{
				continue;
			}

			$param = (new EO_ChatParam(false))
				->setChatId($chat->getId())
				->setParamName(Params::CONTAINS_COLLABER)
				->setParamValue('Y')
			;

			$this->store($param);
		}
	}

	private function fetchChatsRelationMap(): void
	{
		$chatIds = $this->chats->getIdList();
		if (empty($chatIds))
		{
			return;
		}

		$relationCollection = RelationTable::query()
			->setSelect(['CHAT_ID', 'USER_ID'])
			->whereIn('CHAT_ID', $chatIds)
			->exec()
			->fetchAll()
		;

		foreach ($relationCollection as $relation)
		{
			$chatId = (int)$relation['CHAT_ID'];
			$userId = (int)$relation['USER_ID'];

			$this->chatsRelationMap[$chatId][] = $userId;
		}
	}

	private function store(EO_ChatParam $param): void
	{
		$this->storage->add($param);
	}

	private function save(): void
	{
		$this->storage->save(true);
		$this->cleanCacheByChatIds(...$this->storage->getChatIdList());
	}

	private function cleanCacheByChatIds(int ...$chatIdList): void
	{
		foreach ($chatIdList as $chatId)
		{
			Params::cleanCache($chatId);
		}
	}

	private function setLastChatId(): void
	{
		self::$lastChatId = min($this->chats->getIdList()) - 1;
	}
}