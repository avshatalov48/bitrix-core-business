<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Intranet\Settings\CommunicationSettings;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use CAllSite;

class GeneralChat extends GroupChat
{
	public const GENERAL_MESSAGE_TYPE_JOIN = 'join';
	public const GENERAL_MESSAGE_TYPE_LEAVE = 'leave';
	public const ID_CACHE_ID = 'general_chat_id';
	public const MANAGERS_CACHE_ID = 'general_chat_managers';
	public const DISABLE_GENERAL_CHAT_OPTION = 'disable_general_chat';

	private const MESSAGE_COMPONENT_START = 'GeneralChatCreationMessage';

	protected static ?self $instance = null;
	protected static bool $wasSearched = false;
	protected static Result $resultFind;
	protected static int $idStaticCache;

	protected function getDefaultType(): string
	{
		return self::IM_TYPE_OPEN;
	}

	protected function getDefaultEntityType(): string
	{
		return self::ENTITY_TYPE_GENERAL;
	}

	public function hasManageMessagesAccess(?int $userId = null): bool
	{
		if ($this->getId() === null || $this->getId() === 0)
		{
			return false;
		}

		if ($this->getManageMessages() === Chat::MANAGE_RIGHTS_NONE)
		{
			return false;
		}

		$userId ??= $this->getContext()->getUserId();
		if ($this->getManageMessages() === Chat::MANAGE_RIGHTS_MEMBER)
		{
			return true;
		}

		if ($this->getAuthorId() === $userId)
		{
			return true;
		}

		if ($this->getManageMessages() === Chat::MANAGE_RIGHTS_OWNER)
		{
			return false;
		}

		return in_array($userId, $this->getManagerList(), true);
	}

	public static function isEnable(): bool
	{
		return Option::get('im', self::DISABLE_GENERAL_CHAT_OPTION, 'N') === 'N';
	}

	public function getManagerList(): array
	{
		$cache = static::getCache(self::MANAGERS_CACHE_ID);

		$cachedManagerList = $cache->getVars();

		if ($cachedManagerList !== false)
		{
			return $cachedManagerList;
		}

		$managerList = $this->getRelationFacade()->getManagerOnly()->getUserIds();

		$cache->startDataCache();
		$cache->endDataCache($managerList);

		return $this->getRelationFacade()->getManagerOnly()->getUserIds();
	}

	protected function changeManagers(array $userIds, bool $isManager, bool $sendPush = true): self
	{
		self::cleanGeneralChatCache(self::MANAGERS_CACHE_ID);

		return parent::changeManagers($userIds, $isManager, $sendPush);
	}

	public static function get(): ?GeneralChat
	{
		if (self::$wasSearched)
		{
			return self::$instance;
		}

		$chatId = static::getGeneralChatId();
		$chat = Chat::getInstance($chatId);
		self::$instance = ($chat instanceof NullChat) ? null : $chat;
		self::$wasSearched = true;

		return self::$instance;
	}

	public static function getGeneralChatId(): ?int
	{
		if (!static::isEnable())
		{
			return 0;
		}

		if (isset(self::$idStaticCache))
		{
			return self::$idStaticCache;
		}

		$cache = static::getCache(self::ID_CACHE_ID);

		$cachedId = $cache->getVars();

		if ($cachedId !== false)
		{
			self::$idStaticCache = $cachedId ?? 0;

			return self::$idStaticCache;
		}

		$result = ChatTable::query()
			->setSelect(['ID'])
			->where('TYPE', Chat::IM_TYPE_OPEN)
			->where('ENTITY_TYPE', Chat::ENTITY_TYPE_GENERAL)
			->fetch() ?: []
		;

		self::$idStaticCache = $result['ID'] ?? 0;
		$cache->startDataCache();
		$cache->endDataCache(self::$idStaticCache);

		return self::$idStaticCache;
	}

	public function setManagers(array $managerIds): Chat
	{
		static::cleanGeneralChatCache(self::MANAGERS_CACHE_ID);

		return parent::setManagers($managerIds);
	}

	/**
	 * @param array $params
	 * @param Context|null $context
	 * @return Result
	 */
	public static function find(array $params = [], ?Context $context = null): Result
	{
		if (isset(self::$resultFind))
		{
			return self::$resultFind;
		}

		$result = new Result;

		$row = ChatTable::query()
			->setSelect(['ID', 'TYPE', 'ENTITY_TYPE', 'ENTITY_ID'])
			->where('ENTITY_TYPE', self::ENTITY_TYPE_GENERAL)
			->setLimit(1)
			->setOrder(['ID' => 'DESC'])
			->fetch()
		;

		if ($row)
		{
			$result->setResult([
				'ID' => (int)$row['ID'],
				'TYPE' => $row['TYPE'],
				'ENTITY_TYPE' => $row['ENTITY_TYPE'],
				'ENTITY_ID' => $row['ENTITY_ID'],
			]);
		}

		self::$resultFind = $result;

		return $result;
	}

	public function add(array $params, ?Context $context = null): Result
	{
		$result = new Result;

		$generalChatResult = self::find();
		if ($generalChatResult->hasResult())
		{
			$generalChat = new GeneralChat(['ID' => $generalChatResult->getResult()['ID']]);
			return 	$result->setResult([
				'CHAT_ID' => $generalChat->getChatId(),
				'CHAT' => $generalChat,
			]);
		}

		$installUsers = $this->getUsersForInstall();

		$portalLanguage = self::getPortalLanguage();

		$params = [
			'TYPE' => self::IM_TYPE_OPEN,
			'ENTITY_TYPE' => self::ENTITY_TYPE_GENERAL,
			'COLOR' => 'AZURE',
			'TITLE' => Loc::getMessage('IM_CHAT_GENERAL_TITLE', null, $portalLanguage),
			'DESCRIPTION' => Loc::getMessage('IM_CHAT_GENERAL_DESCRIPTION_MSGVER_1', null, $portalLanguage),
			'AUTHOR_ID' => User::getFirstAdmin(),
			'USER_COUNT' => count($installUsers),
		];

		$chat = new static($params);
		$chat->setExtranet(false);
		$chat->save();

		if (!$chat->getChatId())
		{
			return $result->addError(new ChatError(ChatError::CREATION_ERROR));
		}

		$chat->sendBanner();

		$adminIds = [];
		if (Loader::includeModule('bitrix24'))
		{
			$adminIds = \CBitrix24::getAllAdminId();
		}

		foreach ($installUsers as $user)
		{
			$relation = new Relation();
			$relation->setChatId($chat->getChatId());
			$relation->setUserId((int)$user['ID']);
			$relation->setManager(in_array((int)$user['ID'], $adminIds, true));
			$relation->setMessageType(self::IM_TYPE_OPEN);
			$relation->setStatus(IM_STATUS_READ);
			$relation->save();
		}

		$chat->addIndex();

		self::linkGeneralChat($chat->getChatId());

		$result->setResult([
			'CHAT_ID' => $chat->getChatId(),
			'CHAT' => $chat,
		]);

		self::cleanGeneralChatCache(self::ID_CACHE_ID);
		self::cleanGeneralChatCache(self::MANAGERS_CACHE_ID);
		self::cleanCache($chat->getChatId());
		$chat->isFilledNonCachedData = false;

		return $result;
	}

	private static function getPortalLanguage(): ?string
	{
		$defSite = CAllSite::GetDefSite();
		if ($defSite === false)
		{
			return null;
		}

		$portalData = CAllSite::GetByID($defSite)->Fetch();
		if ($portalData)
		{
			$languageId = $portalData['LANGUAGE_ID'];
			if ($languageId !== '')
			{
				return $languageId;
			}
		}

		return null;
	}

	public static function linkGeneralChat(?int $chatId = null): bool
	{
		if (!$chatId)
		{
			$chatId = self::getGeneralChatId();
		}

		if (!$chatId)
		{
			return false;
		}

		if (Loader::includeModule('pull'))
		{
			\CPullStack::AddShared([
				'module_id' => 'im',
				'command' => 'generalChatId',
				'params' => [
					'id' => $chatId
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}

		return true;
	}

	/**
	 * @param self::MANAGERS_CACHE_ID|self::ID_CACHE_ID $cacheId
	 * @return void
	 */
	public static function cleanGeneralChatCache(string $cacheId): void
	{
		Application::getInstance()->getCache()->clean($cacheId, static::getCacheDir());
	}

	public static function unlinkGeneralChat(): bool
	{
		if (Loader::includeModule('pull'))
		{
			\CPullStack::AddShared([
				'module_id' => 'im',
				'command' => 'generalChatId',
				'params' => [
					'id' => 0
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}

		static::cleanGeneralChatCache(self::ID_CACHE_ID);
		static::cleanGeneralChatCache(self::MANAGERS_CACHE_ID);

		return true;
	}

	public function canJoinGeneralChat(int $userId): bool
	{
		if (
			$userId <= 0
			|| !self::getGeneralChatId()
			|| !Loader::includeModule('intranet')
		)
		{
			return false;
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$sql = "
			SELECT DISTINCT U.ID
			FROM
				b_user U
				INNER JOIN b_user_field F ON F.ENTITY_ID = 'USER' AND F.FIELD_NAME = 'UF_DEPARTMENT'
				INNER JOIN b_utm_user UF ON
					UF.FIELD_ID = F.ID
					AND UF.VALUE_ID = U.ID
					AND UF.VALUE_INT > 0
			WHERE
				U.ACTIVE = 'Y'
				AND U.ID = " . $userId . "
				AND F.ENTITY_ID = 'USER'
				AND F.FIELD_NAME = 'UF_DEPARTMENT'
			LIMIT 1
		";
		if ($connection->query($sql)->fetch())
		{
			return true;
		}

		return false;
	}

	private function getUsersForInstall(): array
	{
		$externalUserTypes = \Bitrix\Main\UserTable::getExternalUserTypes();
		$types = implode("', '", $externalUserTypes);
		if (Loader::includeModule('intranet'))
		{
			$sql = "
				SELECT DISTINCT U.ID
				FROM
					b_user U
					INNER JOIN b_user_field F ON F.ENTITY_ID = 'USER' AND F.FIELD_NAME = 'UF_DEPARTMENT'
					INNER JOIN b_utm_user UF ON
						UF.FIELD_ID = F.ID
						AND UF.VALUE_ID = U.ID
						AND UF.VALUE_INT > 0
				WHERE
					U.ACTIVE = 'Y'
					AND (U.EXTERNAL_AUTH_ID IS NULL OR U.EXTERNAL_AUTH_ID NOT IN ('{$types}') )
					AND F.ENTITY_ID = 'USER'
					AND F.FIELD_NAME = 'UF_DEPARTMENT'
			";
		}
		else
		{
			$sql = "
				SELECT ID
				FROM b_user U
				WHERE 
				    U.ACTIVE = 'Y'
					AND (U.EXTERNAL_AUTH_ID IS NULL OR U.EXTERNAL_AUTH_ID NOT IN ('{$types}') )
			";
		}

		$connection = \Bitrix\Main\Application::getConnection();
		return $connection->query($sql)->fetchAll();
	}

	protected function sendBanner(?int $authorId = null): void
	{
		\CIMMessage::Add([
			'MESSAGE_TYPE' => self::IM_TYPE_CHAT,
			'TO_CHAT_ID' => $this->getChatId(),
			'FROM_USER_ID' => 0,
			'MESSAGE' => Loc::getMessage('IM_CHAT_GENERAL_CREATE_WELCOME', null, self::getPortalLanguage()),
			'SYSTEM' => 'Y',
			'PUSH' => 'N',
			'PARAMS' => [
				'COMPONENT_ID' => self::MESSAGE_COMPONENT_START,
				'NOTIFY' => 'N',
			],
			'SKIP_COUNTER_INCREMENTS' => 'Y',
		]);
	}

	public static function getAutoMessageStatus(string $type): bool
	{
		switch ($type)
		{
			case self::GENERAL_MESSAGE_TYPE_JOIN:
				return (bool)\COption::GetOptionString("im", "general_chat_message_join");
			case self::GENERAL_MESSAGE_TYPE_LEAVE:
				return (bool)\COption::GetOptionString("im", "general_chat_message_leave");
			default:
				return false;
		}
	}

	public function getRightsForIntranetConfig(): array
	{
		$result['generalChatCanPostList'] = self::getCanPostList();
		$result['generalChatCanPost'] = $this->getManageMessages();
		$result['generalChatShowManagersList'] = self::MANAGE_RIGHTS_MANAGERS;
		$managerIds = $this->getRelationFacade()->getManagerOnly()->getUserIds();
		$managers = array_map(function ($managerId) {
			return 'U' . $managerId;
		}, $managerIds);
		Loader::includeModule('intranet');
		if (method_exists('\Bitrix\Intranet\Settings\CommunicationSettings', 'processOldAccessCodes'))
		{
			$result['generalChatManagersList'] = CommunicationSettings::processOldAccessCodes($managers);
		}
		else
		{
			$result['generalChatManagersList'] = \IntranetConfigsComponent::processOldAccessCodes($managers);
		}

		return $result;
	}

	protected function getAccessCodesForDiskFolder(): array
	{
		$accessCodes = parent::getAccessCodesForDiskFolder();
		$departmentCode = \CIMDisk::GetTopDepartmentCode();

		if ($departmentCode)
		{
			$driver = \Bitrix\Disk\Driver::getInstance();
			$rightsManager = $driver->getRightsManager();
			$accessCodes[] = [
				'ACCESS_CODE' => $departmentCode,
				'TASK_ID' => $rightsManager->getTaskIdByName($rightsManager::TASK_READ)
			];
		}

		return $accessCodes;
	}

	public static function deleteGeneralChat(): Result
	{
		$generalChat = self::get();
		if (!$generalChat)
		{
			return (new Result())->addError(new ChatError(ChatError::NOT_FOUND));
		}

		return $generalChat->deleteChat();
	}

	protected function sendMessageUsersAdd(array $usersToAdd, Relation\AddUsersConfig $config): void
	{
		if ($this->getContext()->getUserId() > 0)
		{
			parent::sendMessageUsersAdd($usersToAdd, $config);

			return;
		}

		if (!self::getAutoMessageStatus(self::GENERAL_MESSAGE_TYPE_JOIN))
		{
			return;
		}

		$userCodes = [];
		foreach ($usersToAdd as $userId)
		{
			$userCodes[] = "[USER={$userId}][/USER]";
		}
		$userCodesString = implode(', ', $userCodes);

		if (count($usersToAdd) > 1)
		{
			$messageText = Loc::getMessage("IM_CHAT_GENERAL_JOIN_PLURAL", ['#USER_NAME#' => $userCodesString]);
		}
		else
		{
			$user = User::getInstance(current($usersToAdd));
			$genderModifier = $user->getGender() === 'F' ? '_F' : '';
			$messageText = Loc::getMessage('IM_CHAT_GENERAL_JOIN' . $genderModifier, ['#USER_NAME#' => $userCodesString]);
		}

		\CIMChat::AddMessage([
			"TO_CHAT_ID" => $this->getId(),
			"MESSAGE" => $messageText,
			"FROM_USER_ID" => $this->getContext(),
			"SYSTEM" => 'Y',
			"RECENT_ADD" => $config->skipRecent() ? 'N' : 'Y',
			"PARAMS" => [
				"CODE" => 'CHAT_JOIN',
				"NOTIFY" => $this->getEntityType() === self::ENTITY_TYPE_LINE? 'Y': 'N',
			],
			"PUSH" => 'N',
			"SKIP_USER_CHECK" => 'Y',
		]);
	}

	protected function needToSendMessageUserDelete(): bool
	{
		return true;
	}

	protected function sendMessageUserDelete(int $userId, Relation\DeleteUserConfig $config): void
	{
		if (!self::getAutoMessageStatus(self::GENERAL_MESSAGE_TYPE_LEAVE))
		{
			return;
		}

		parent::sendMessageUserDelete($userId, $config);
	}

	protected function getMessageUserDeleteText(int $userId): string
	{
		$user = User::getInstance($userId);

		return Loc::getMessage("IM_CHAT_GENERAL_LEAVE_{$user->getGender()}", Array('#USER_NAME#' => htmlspecialcharsback($user->getName())));
	}

	public static function changeLangAgent(): string
	{
		if (!Loader::includeModule('im'))
		{
			return '';
		}

		GeneralChat::cleanGeneralChatCache(self::ID_CACHE_ID);

		$chatId = GeneralChat::getGeneralChatId();
		if ($chatId > 0)
		{
			$portalLanguage = self::getPortalLanguage();

			ChatTable::update($chatId, [
				'TITLE' => Loc::getMessage('IM_CHAT_GENERAL_TITLE', null, $portalLanguage),
				'DESCRIPTION' => Loc::getMessage('IM_CHAT_GENERAL_DESCRIPTION_MSGVER_1', null, $portalLanguage),
			]);
		}

		return '';
	}

	private static function getCache(string $cacheId): Cache
	{
		$cache = Application::getInstance()->getCache();
		$cacheTTL = 18144000;
		$cacheDir = static::getCacheDir();
		$cache->initCache($cacheTTL, $cacheId, $cacheDir);

		return $cache;
	}

	private static function getCacheDir(): string
	{
		return '/bx/imc/general_chat';
	}
}
