<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\HumanResources\Repository\StructureRepository;
use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Integration\HumanResources\Structure;
use Bitrix\Im\V2\Relation\AddUsersConfig;
use Bitrix\Im\V2\Relation\Reason;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Intranet\Settings\CommunicationSettings;
use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use CAllSite;

class GeneralChannel extends OpenChannelChat
{
	public const ID_CACHE_ID = 'general_channel_id';

	private const MESSAGE_COMPONENT_START = 'GeneralChannelCreationMessage';

	protected static ?self $instance = null;
	protected static bool $wasSearched = false;
	protected static int $idStaticCache;

	protected function getDefaultEntityType(): string
	{
		return self::ENTITY_TYPE_GENERAL_CHANNEL;
	}

	public function getDefaultManageMessages(): string
	{
		return self::MANAGE_RIGHTS_MEMBER;
	}

	public static function get(): ?GeneralChannel
	{
		if (self::$wasSearched)
		{
			return self::$instance;
		}

		$chatId = self::getGeneralChannelId();
		$chat = Chat::getInstance($chatId);
		self::$instance = ($chat instanceof NullChat) ? null : $chat;
		self::$wasSearched = true;

		return self::$instance;
	}

	public static function getGeneralChannelId(): ?int
	{
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
			->where('TYPE', self::IM_TYPE_OPEN_CHANNEL)
			->where('ENTITY_TYPE', self::ENTITY_TYPE_GENERAL_CHANNEL)
			->fetch() ?: []
		;

		self::$idStaticCache = $result['ID'] ?? 0;
		$cache->startDataCache();
		$cache->endDataCache(self::$idStaticCache);

		return self::$idStaticCache;
	}

	protected function getGeneralChannelIdWithoutCache(): ?int
	{
		$result = ChatTable::query()
			->setSelect(['ID'])
			->where('ENTITY_TYPE', self::ENTITY_TYPE_GENERAL_CHANNEL)
			->setLimit(1)
			->fetch()
		;

		if ($result)
		{
			return (int)$result['ID'];
		}

		return null;
	}

	public function add(array $params, ?Context $context = null): Result
	{
		$result = new Result;

		$generalChannel = Chat::getInstance($this->getGeneralChannelIdWithoutCache());
		if ($generalChannel instanceof self)
		{
			return 	$result->setResult([
				'CHAT_ID' => $generalChannel->getChatId(),
				'CHAT' => $generalChannel,
			]);
		}

		$portalLanguage = self::getPortalLanguage();

		$params = [
			'TYPE' => self::IM_TYPE_OPEN_CHANNEL,
			'ENTITY_TYPE' => self::ENTITY_TYPE_GENERAL_CHANNEL,
			'COLOR' => 'AZURE',
			'TITLE' => Loc::getMessage('IM_CHAT_GENERAL_CHANNEL_TITLE', null, $portalLanguage),
			'DESCRIPTION' => Loc::getMessage('IM_CHAT_GENERAL_CHANNEL_DESCRIPTION', null, $portalLanguage),
			'AUTHOR_ID' => User::getFirstAdmin(),
			'MEMBER_ENTITIES' => [['department', $this->getCompanyStructureId()]],
		];

		$result = parent::add($params);
		self::cleanGeneralChannelCache(self::ID_CACHE_ID);

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

	public function addUsers(array $userIds, AddUsersConfig $config = new AddUsersConfig()): self
	{
		$managerIds = [];
		foreach ($userIds as $userId)
		{
			$user = User::getInstance((int)$userId);

			if ($user->isAdmin())
			{
				$managerIds[] = $user->getId();
			}
		}

		$generalChat = GeneralChat::get();
		if (isset($generalChat))
		{
			foreach ($generalChat->getManagerList() as $manager)
			{
				if (in_array((int)$manager, $userIds, true))
				{
					$managerIds[] = $manager;
				}
			}
		}

		$managerIds = array_unique($managerIds);
		$config->setManagerIds($managerIds);

		return parent::addUsers($userIds, $config);
	}

	protected function sendMessageUsersAdd(array $usersToAdd, AddUsersConfig $config): void
	{
		return;
	}

	protected function getCompanyStructureId(): ?string
	{
		if (!\Bitrix\Main\Loader::includeModule('humanresources'))
		{
			return null;
		}

		$structure = (new StructureRepository())->getByXmlId(\Bitrix\HumanResources\Item\Structure::DEFAULT_STRUCTURE_XML_ID);
		if (!isset($structure))
		{
			return null;
		}

		$rootNode = (new \Bitrix\HumanResources\Repository\NodeRepository())->getRootNodeByStructureId($structure->id);
		if (!isset($rootNode))
		{
			return null;
		}

		$accessCode = $rootNode->accessCode;
		preg_match('/D(\d+)/', $accessCode ?? '', $matches);

		return $matches[1] ?? null;
	}

	protected function sendBanner(?int $authorId = null): void
	{
		\CIMMessage::Add([
			'MESSAGE_TYPE' => self::IM_TYPE_OPEN_CHANNEL,
			'TO_CHAT_ID' => $this->getChatId(),
			'FROM_USER_ID' => 0,
			'MESSAGE' => Loc::getMessage('IM_CHAT_GENERAL_CHANNEL_CREATE_WELCOME', null, self::getPortalLanguage()),
			'SYSTEM' => 'Y',
			'PUSH' => 'N',
			'PARAMS' => [
				'COMPONENT_ID' => self::MESSAGE_COMPONENT_START,
				'NOTIFY' => 'N',
			],
			'SKIP_COUNTER_INCREMENTS' => 'Y',
		]);
	}

	protected function sendGreetingMessage(?int $authorId = null)
	{
		return;
	}

	protected function sendDescriptionMessage(?int $authorId = null): void
	{
		return;
	}

	protected function needToSendGreetingMessages(): bool
	{
		return true;
	}

	public static function deleteGeneralChannel(): Result
	{
		$chat = self::get();
		if (!isset($chat))
		{
			return (new Result())->addError(new ChatError(ChatError::NOT_FOUND));
		}

		$structureNodes = Structure::splitEntities([['department', $chat->getCompanyStructureId()]]);
		$chat->unlinkStructureNodes($structureNodes[1] ?? []);

		$result = $chat->deleteChat();
		self::cleanGeneralChannelCache(self::ID_CACHE_ID);

		return $result;
	}

	public static function installGeneralChannel(): void
	{
		(new self())->add([]);
	}

	public static function installAgent(): string
	{
		if (self::get() !== null)
		{
			return '';
		}

		if (!Structure::isSyncAvailable())
		{
			return "\\" . __METHOD__ . '();';
		}

		self::installGeneralChannel();

		return '';
	}

	public static function changeLangAgent(): string
	{
		if (!Loader::includeModule('im'))
		{
			return '';
		}

		GeneralChannel::cleanGeneralChannelCache(self::ID_CACHE_ID);

		$chatId = GeneralChannel::getGeneralChannelId();
		if ($chatId > 0)
		{
			$portalLanguage = self::getPortalLanguage();

			ChatTable::update($chatId, [
				'TITLE' => Loc::getMessage('IM_CHAT_GENERAL_CHANNEL_TITLE', null, $portalLanguage),
				'DESCRIPTION' => Loc::getMessage('IM_CHAT_GENERAL_CHANNEL_DESCRIPTION', null, $portalLanguage),
			]);
		}

		return '';
	}

	public function getRightsForIntranetConfig(): array
	{
		$result['generalChannelCanPostList'] = self::getCanPostList();
		$result['generalChannelCanPost'] = $this->getManageMessages();
		$result['generalChannelShowManagersList'] = self::MANAGE_RIGHTS_MANAGERS;
		$managerIds = $this->getRelationFacade()->getManagerOnly()->getUserIds();
		$managers = array_map(function ($managerId) {
			return 'U' . $managerId;
		}, $managerIds);

		if (!Loader::includeModule('intranet'))
		{
			return $result;
		}

		if (method_exists('\Bitrix\Intranet\Settings\CommunicationSettings', 'processOldAccessCodes'))
		{
			$result['generalChannelManagersList'] = CommunicationSettings::processOldAccessCodes($managers);
		}
		else
		{
			$result['generalChannelManagersList'] = \IntranetConfigsComponent::processOldAccessCodes($managers);
		}

		return $result;
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
		return '/bx/imc/general_channel';
	}

	public static function cleanGeneralChannelCache(string $cacheId): void
	{
		Application::getInstance()->getCache()->clean($cacheId, static::getCacheDir());
	}
}
