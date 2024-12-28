<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\V2\Analytics\ChatAnalytics;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;

class ChatFactory
{
	use ContextCustomer;

	public const NON_CACHED_FIELDS = ['MESSAGE_COUNT', 'USER_COUNT', 'LAST_MESSAGE_ID'];

	protected static self $instance;

	private function __construct()
	{
	}

	/**
	 * Returns current instance of the Dispatcher.
	 * @return self
	 */
	public static function getInstance(): self
	{
		if (isset(self::$instance))
		{
			return self::$instance;
		}

		self::$instance = new static();

		return self::$instance;
	}



	//region Chat actions

	/**
	 * @param array|int|string $params
	 * @return Chat|null
	 */
	public function getChat($params): ?Chat
	{
		$type = $params['TYPE'] ?? $params['MESSAGE_TYPE'] ?? '';

		if (empty($params))
		{
			return null;
		}
		if (is_numeric($params))
		{
			$params = ['CHAT_ID' => (int)$params];
		}
		elseif (is_string($params))
		{
			$params = ['DIALOG_ID' => $params];
			if (\Bitrix\Im\Common::isChatId($params['DIALOG_ID']))
			{
				$params['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($params['DIALOG_ID']);
			}
		}

		$findResult = $this->findChat($params);

		if ($findResult->hasResult())
		{
			$chatParams = $findResult->getResult();

			return $this->initChat($chatParams);
		}

		if (
			$type === Chat::IM_TYPE_SYSTEM
			|| $type ===  Chat::IM_TYPE_PRIVATE
		)
		{
			$addResult = $this->addChat($params);
			if ($addResult->hasResult())
			{
				$chat = $addResult->getResult()['CHAT'];
				$chat->setContext($this->context);

				return $chat;
			}
		}

		return null;
	}

	/**
	 * @return Chat|NotifyChat|null
	 */
	public function getNotifyFeed($userId = null): ?NotifyChat
	{
		if (!$userId)
		{
			$userId = $this->getContext()->getUserId();
		}

		$params = [
			'TYPE' => Chat::IM_TYPE_SYSTEM,
			'TO_USER_ID' => $userId,
		];

		return $this->getChat($params);
	}

	/**
	 * @param string $entityType
	 * @param int|string $entityId
	 * @return Chat|EntityChat|null
	 */
	public function getEntityChat(string $entityType, $entityId): ?EntityChat
	{
		$params = [
			'TYPE' => Chat::IM_TYPE_CHAT,
			'ENTITY_TYPE' => $entityType,
			'ENTITY_ID' => $entityId,
		];

		return $this->getChat($params);
	}

	/**
	 * @param string $entityType
	 * @param int|string $entityId
	 * @return Chat|GeneralChat|null
	 */
	public function getGeneralChat(): ?GeneralChat
	{
		return GeneralChat::get();
	}

	public function getGeneralChannel(): ?ChannelChat
	{
		return GeneralChannel::get();
	}

	/**
	 * @return Chat|Chat\PrivateChat|null
	 */
	public function getPrivateChat($fromUserId, $toUserId): ?Chat\PrivateChat
	{
		$params = [
			'TYPE' => Chat::IM_TYPE_PRIVATE,
			'FROM_USER_ID' => $fromUserId,
			'TO_USER_ID' => $toUserId,
		];

		return $this->getChat($params);
	}

	/**
	 * @return Chat|Chat\FavoriteChat|null
	 */
	public function getPersonalChat($userId = null): ?Chat\FavoriteChat
	{
		if (!$userId)
		{
			$userId = $this->getContext()->getUserId();
		}

		$params = [
			'TYPE' => Chat::IM_TYPE_PRIVATE,
			'FROM_USER_ID' => $userId,
			'TO_USER_ID' => $userId,
		];

		return $this->getChat($params);
	}
	//endregion

	//region Chat Create
	/**
	 * @param array|null $params
	 * @return Chat
	 */
	public function initChat(?array $params = null): Chat
	{
		$type = $params['TYPE'] ?? $params['MESSAGE_TYPE'] ?? '';
		$entityType = $params['ENTITY_TYPE'] ?? '';
		$chat = match (true)
		{
			$entityType === Chat::ENTITY_TYPE_FAVORITE || $entityType === 'PERSONAL' => new FavoriteChat($params),
			$entityType === Chat::ENTITY_TYPE_GENERAL => new GeneralChat($params),
			$entityType === Chat::ENTITY_TYPE_GENERAL_CHANNEL => new GeneralChannel($params),
			$entityType === Chat::ENTITY_TYPE_LINE || $type === Chat::IM_TYPE_OPEN_LINE => new OpenLineChat($params),
			$entityType === Chat::ENTITY_TYPE_LIVECHAT => new OpenLineLiveChat($params),
			$entityType === Chat::ENTITY_TYPE_VIDEOCONF => new VideoConfChat($params),
			$type === Chat::IM_TYPE_CHANNEL => new ChannelChat($params),
			$type === Chat::IM_TYPE_OPEN_CHANNEL => new OpenChannelChat($params),
			$type === Chat::IM_TYPE_OPEN => new OpenChat($params),
			$type === Chat::IM_TYPE_SYSTEM => new NotifyChat($params),
			$type === Chat::IM_TYPE_PRIVATE => new PrivateChat($params),
			$type === Chat::IM_TYPE_CHAT => new GroupChat($params),
			$type === Chat::IM_TYPE_COMMENT => new CommentChat($params),
			$type === Chat::IM_TYPE_COPILOT => new CopilotChat($params),
			$type === Chat::IM_TYPE_COLLAB => new CollabChat($params),
			default => new NullChat(),
		};

		$chat->setContext($this->context);

		return $chat;
	}

	/**
	 * @param array|null $params
	 * @return Chat|NotifyChat
	 */
	public function createNotifyFeed(?array $params = null): Chat
	{
		$params = $params ?? [];
		$params['TYPE'] = Chat::IM_TYPE_SYSTEM;

		return $this->initChat($params);
	}

	/**
	 * @param array|null $params
	 * @return Chat|FavoriteChat
	 */
	public function createPersonalChat(?array $params = null): Chat
	{
		$params = $params ?? [];
		$params['ENTITY_TYPE'] = Chat::ENTITY_TYPE_FAVORITE;

		return $this->initChat($params);
	}

	/**
	 * @param array|null $params
	 * @return Chat|PrivateChat
	 */
	public function createPrivateChat(?array $params = null): Chat
	{
		$params = $params ?? [];
		$params['TYPE'] = Chat::IM_TYPE_PRIVATE;

		return $this->initChat($params);
	}

	/**
	 * @param array|null $params
	 * @return Chat|OpenChat
	 */
	public function createOpenChat(?array $params = null): Chat
	{
		$params = $params ?? [];
		$params['TYPE'] = Chat::IM_TYPE_OPEN;

		return $this->initChat($params);
	}

	/**
	 * @param array|null $params
	 * @return Chat|OpenLineChat
	 */
	public function createOpenLineChat(?array $params = null): Chat
	{
		$params = $params ?? [];
		$params['TYPE'] = Chat::IM_TYPE_OPEN_LINE;

		return $this->initChat($params);
	}

	//endregion

	//region Chat Find


	/**
	 * @param int $chatId
	 * @return Chat|null
	 */
	public function getChatById(int $chatId): Chat
	{
		$findResult = $this->findChat(['CHAT_ID' => $chatId]);
		if ($findResult->hasResult())
		{
			$chatParams = $findResult->getResult();

			/** @var Chat $chat */
			$chat = $this->initChat($chatParams);
		}
		else
		{
			$chat = new NullChat();
		}

		return $chat;
	}


	/**
	 * @param array $params
	 * <pre>
	 * [
	 * 	(string) MESSAGE_TYPE - Message type:
	 * 		@see \IM_MESSAGE_SYSTEM = S - notification,
	 * 		@see \IM_MESSAGE_PRIVATE = P - private chat,
	 * 		@see \IM_MESSAGE_CHAT = C - group chat,
	 * 		@see \IM_MESSAGE_OPEN = O - open chat,
	 * 		@see \IM_MESSAGE_OPEN_LINE = L - open line chat.
	 *
	 * 	(string|int) DIALOG_ID - Dialog Id:
	 * 		chatNNN - chat,
	 * 		sgNNN - sonet group,
	 * 		crmNNN - crm chat,
	 * 		NNN - recipient user.
	 *
	 * 	(int) CHAT_ID - Chat Id.
	 * 	(int) TO_USER_ID - Recipient user Id.
	 * 	(int) FROM_USER_ID - Sender user Id.
	 * ]
	 * </pre>
	 * @return Result
	 */
	public function findChat(array $params): Result
	{
		$result = new Result;

		if (isset($params['TYPE']))
		{
			$params['MESSAGE_TYPE'] = $params['TYPE'];
		}

		if (empty($params['CHAT_ID']) && !empty($params['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isChatId($params['DIALOG_ID']))
			{
				$params['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($params['DIALOG_ID']);
				if (!isset($params['MESSAGE_TYPE']))
				{
					$params['MESSAGE_TYPE'] = Chat::IM_TYPE_CHAT;
				}
			}
			else
			{
				$params['TO_USER_ID'] = (int)$params['DIALOG_ID'];
				$params['MESSAGE_TYPE'] = Chat::IM_TYPE_PRIVATE;
			}
		}

		if (!empty($params['CHAT_ID']) && (int)$params['CHAT_ID'] > 0)
		{
			$chatId = (int)$params['CHAT_ID'];
			$cache = $this->getCache($chatId);
			$cachedChat = $cache->getVars();

			if ($cachedChat !== false)
			{
				return $result->setResult($this->filterNonCachedFields($cachedChat));
			}

			$chat = $this->getRawById($chatId);

			if ($chat)
			{
				$cache->startDataCache();
				$cache->endDataCache($chat);
			}
			else
			{
				$chat = null;
			}

			return $result->setResult($chat);
		}

		switch ($params['MESSAGE_TYPE'] ?? '')
		{
			case Chat::IM_TYPE_SYSTEM:
				$result = NotifyChat::find($params, $this->context);
				break;

			case Chat::IM_TYPE_PRIVATE:
				if (
					isset($params['TO_USER_ID'], $params['FROM_USER_ID'])
					&& $params['TO_USER_ID'] == $params['FROM_USER_ID']
				)
				{
					$result = FavoriteChat::find($params, $this->context);
				}
				else
				{
					$result = PrivateChat::find($params, $this->context);
				}
				break;

			case Chat::IM_TYPE_CHAT:
			case Chat::IM_TYPE_OPEN:
				if (
					isset($params['ENTITY_TYPE'])
					&& $params['ENTITY_TYPE'] == Chat::ENTITY_TYPE_GENERAL
				)
				{
					$result = GeneralChat::find($params);
					break;
				}
			case Chat::IM_TYPE_OPEN_LINE:
				$result = Chat::find($params, $this->context);
				break;

			default:
				return $result->addError(new ChatError(ChatError::WRONG_TYPE));
		}

		return $result;
	}

	private function filterNonCachedFields(array $chat): array
	{
		foreach (self::NON_CACHED_FIELDS as $key)
		{
			unset($chat[$key]);
		}

		return $chat;
	}

	private function getRawById(int $id): ?array
	{
		$chat = ChatTable::query()
			->setSelect(['*', '_ALIAS' => 'ALIAS.ALIAS'])
			->where('ID', $id)
			->fetch()
		;

		if (!$chat)
		{
			return null;
		}

		$chat['ALIAS'] = $chat['_ALIAS'];

		return $chat;
	}

	//endregion

	//region Add new chat

	/**
	 * @param array $params
	 * @return Result
	 */
	public function addChat(array $params): Result
	{
		$params['ENTITY_TYPE'] = $params['ENTITY_TYPE'] ?? '';

		$params['TYPE'] = $params['TYPE'] ?? Chat::IM_TYPE_CHAT;

		// Temporary workaround for Open chat type
		if (($params['SEARCHABLE'] ?? 'N') === 'Y')
		{
			if ($params['TYPE'] === Chat::IM_TYPE_CHAT)
			{
				$params['TYPE'] = Chat::IM_TYPE_OPEN;
			}
			elseif ($params['TYPE'] === Chat::IM_TYPE_CHANNEL)
			{
				$params['TYPE'] = Chat::IM_TYPE_OPEN_CHANNEL;
			}
			else
			{
				$params['SEARCHABLE'] = 'N';
			}
		}

		ChatAnalytics::blockSingleUserEvents();

		$initParams = ['TYPE' => $params['TYPE'] ?? null, 'ENTITY_TYPE' => $params['ENTITY_TYPE'] ?? null];
		$chat = $this->initChat($initParams);
		$addResult = $chat->add($params);

		if ($chat instanceof NullChat)
		{
			return $addResult->addError(new ChatError(ChatError::CREATION_ERROR));
		}

		$resultChat = $addResult->getResult()['CHAT'] ?? null;
		if ($resultChat instanceof Chat)
		{
			(new ChatAnalytics($resultChat))->addSubmitCreateNew();
		}

		return $addResult;
	}

	//endregion

	//region Cache

	public function cleanCache(int $id): void
	{
		Application::getInstance()->getCache()->cleanDir($this->getCacheDir($id));
	}

	protected function getCache(int $id): Cache
	{
		$cache = Application::getInstance()->getCache();

		$cacheTTL = defined("BX_COMP_MANAGED_CACHE") ? 18144000 : 1800;
		$cacheId = "chat_data_{$id}";
		$cacheDir = $this->getCacheDir($id);

		$cache->initCache($cacheTTL, $cacheId, $cacheDir);

		return $cache;
	}

	private function getCacheDir(int $id): string
	{
		$cacheSubDir = $id % 100;

		return "/bx/imc/chatdata/7/{$cacheSubDir}/{$id}";
	}

	//endregion
}
