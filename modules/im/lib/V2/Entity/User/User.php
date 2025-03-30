<?php

namespace Bitrix\Im\V2\Entity\User;

use Bitrix\Im\Common;
use Bitrix\Im\Integration\Socialnetwork\Extranet;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\Model\StatusTable;
use Bitrix\Im\V2\Chat\ChatError;
use Bitrix\Im\V2\Chat\FavoriteChat;
use Bitrix\Im\V2\Chat\PrivateChat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\Department\Departments;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

class User implements RestEntity
{
	use ContextCustomer;

	public const PHONE_MOBILE = 'PERSONAL_MOBILE';
	public const PHONE_WORK = 'WORK_PHONE';
	public const PHONE_INNER = 'INNER_PHONE';
	public const PERSONAL_PHONE = 'PERSONAL_PHONE';
	public const ONLINE_DATA_SELECTED_FIELDS = [
		'USER_ID' => 'ID',
		'IDLE' => 'STATUS.IDLE',
		'DESKTOP_LAST_DATE' => 'STATUS.DESKTOP_LAST_DATE',
		'MOBILE_LAST_DATE' => 'STATUS.MOBILE_LAST_DATE',
		'LAST_ACTIVITY_DATE'
	];
	public const ONLINE_DATA_SELECTED_FIELDS_WITHOUT_STATUS = [
		'USER_ID' => 'ID',
		'LAST_ACTIVITY_DATE'
	];

	/**
	 * @var ModuleManager
	 */
	protected static string $moduleManager = ModuleManager::class;
	/**
	 * @var Loader
	 */
	protected static string $loader = Loader::class;

	/**
	 * @var static[]
	 */
	protected static array $userStaticCache = [];

	protected array $accessCache = [];
	protected array $userData = [];

	protected bool $isOnlineDataFilled = false;
	protected bool $isOnlineDataWithStatusFilled = false;
	protected ?bool $isAdmin = null;

	protected ?DateTime $idle = null;
	protected ?DateTime $lastActivityDate = null;
	protected ?DateTime $mobileLastDate = null;
	protected ?DateTime $desktopLastDate = null;

	public static function getInstance(?int $id): self
	{
		if (!isset($id))
		{
			return new NullUser();
		}

		if (isset(self::$userStaticCache[$id]))
		{
			return self::$userStaticCache[$id];
		}

		self::$userStaticCache[$id] = UserFactory::getInstance()->getUserById($id);

		return self::$userStaticCache[$id];
	}

	public static function getCurrent(): self
	{
		return Locator::getContext()->getUser();
	}

	public static function initByArray(array $userData): self
	{
		if (!isset($userData['ID']))
		{
			return new NullUser();
		}

		$user = new static();
		$user->userData = $userData;

		return $user;
	}

	/**
	 * Return chat with user AND create chat if it does not exist
	 *
	 * @param int $userId
	 * @return PrivateChat|null
	 */
	public function getChatWith(int $userId, bool $createIfNotExist = true): ?PrivateChat
	{
		$chatId = false;
		if ($userId === $this->getId())
		{
			$result = FavoriteChat::find(['TO_USER_ID' => $userId])->getResult();
			if ($result && isset($result['ID']))
			{
				$chatId = (int)$result['ID'];
			}
		}
		else
		{
			$result = RelationTable::query()
				->setSelect(['CHAT_ID'])
				->registerRuntimeField(
					'SELF',
					new Reference(
						'SELF',
						RelationTable::class,
						Join::on('this.CHAT_ID', 'ref.CHAT_ID'),
						['join_type' => Join::TYPE_INNER]
					)
				)->where('USER_ID', $this->getId())
				->where('SELF.USER_ID', $userId)
				->where('MESSAGE_TYPE', \IM_MESSAGE_PRIVATE)
				->setLimit(1)
				->fetch()
			;
			if ($result && isset($result['CHAT_ID']))
			{
				$chatId = (int)$result['CHAT_ID'];
			}
		}

		if ($chatId !== false)
		{
			$chat = PrivateChat::getInstance($chatId);

			if ($chat instanceof PrivateChat)
			{
				return $chat;
			}

			return null;
		}

		if (!$createIfNotExist)
		{
			return null;
		}

		try
		{
			$createResult = (new PrivateChat())->add(['FROM_USER_ID' => $this->getId(), 'TO_USER_ID' => $userId]);
		}
		catch (SystemException $exception)
		{
			return null;
		}

		if (!$createResult->isSuccess())
		{
			return null;
		}

		return $createResult->getResult()['CHAT'];
	}

	final public function checkAccess(?int $idOtherUser = null): Result
	{
		$result = new Result();
		$idOtherUser ??= Locator::getContext()->getUserId();
		$otherUser = User::getInstance($idOtherUser);

		if (!$otherUser->isExist())
		{
			return $result->addError(new UserError(UserError::NOT_FOUND));
		}

		if ($this->getId() === $idOtherUser)
		{
			return $result;
		}

		if (isset($this->accessCache[$idOtherUser]))
		{
			return $this->accessCache[$idOtherUser];
		}

		$this->accessCache[$idOtherUser] = $this->checkAccessInternal($otherUser);

		return $this->accessCache[$idOtherUser];
	}

	protected function checkAccessInternal(self $otherUser): Result
	{
		$result = new Result();

		if (!static::$moduleManager::isModuleInstalled('intranet'))
		{
			if (!$this->hasAccessBySocialNetwork($otherUser->getId()))
			{
				$result->addError(new ChatError(ChatError::ACCESS_DENIED));
			}

			return $result;
		}

		if ($otherUser->isExtranet())
		{
			$inGroup = Extranet::isUserInGroup(
				$this->getId(),
				$otherUser->getId(),
				false
			);

			if (!$inGroup)
			{
				$result->addError(new ChatError(ChatError::ACCESS_DENIED));
			}

			return $result;
		}

		return $result;
	}

	final protected function hasAccessBySocialNetwork(int $idOtherUser): bool
	{
		$isContactPrivacy = (
			\CIMSettings::GetPrivacy(\CIMSettings::PRIVACY_MESSAGE) === \CIMSettings::PRIVACY_RESULT_CONTACT
			|| \CIMSettings::GetPrivacy(\CIMSettings::PRIVACY_MESSAGE, $this->getId()) === \CIMSettings::PRIVACY_RESULT_CONTACT
		);

		return !(
			$isContactPrivacy
			&& static::$loader::includeModule('socialnetwork')
			&& \CSocNetUser::IsFriendsAllowed()
			&& !\CSocNetUserRelations::IsFriends($this->getId(), $idOtherUser)
		);
	}

	protected function fillOnlineData(bool $withStatus = false): void
	{
		if ((!$withStatus && $this->isOnlineDataFilled)
		|| $this->isOnlineDataWithStatusFilled)
		{
			return;
		}

		$select = $withStatus ? self::ONLINE_DATA_SELECTED_FIELDS : self::ONLINE_DATA_SELECTED_FIELDS_WITHOUT_STATUS;
		$query = UserTable::query()
			->setSelect($select)
			->where('ID', $this->getId())
		;
		if ($withStatus)
		{
			$query->registerRuntimeField(
				new Reference(
					'STATUS',
					StatusTable::class,
					Join::on('this.ID', 'ref.USER_ID'),
					['join_type' => Join::TYPE_LEFT]
				)
			);
		}

		$statusData = $query->fetch() ?: [];
		$this->setOnlineData($statusData, $withStatus);
	}

	public function unsetOnlineData(): void
	{
		$this->isOnlineDataFilled = false;
		$this->isOnlineDataWithStatusFilled = false;
	}

	public function getId(): ?int
	{
		return isset($this->userData['ID']) ? (int)$this->userData['ID'] : null;
	}

	public static function getRestEntityName(): string
	{
		return 'user';
	}

	public function toRestFormat(array $option = []): array
	{
		if (isset($option['USER_SHORT_FORMAT']) && $option['USER_SHORT_FORMAT'] === true)
		{
			return [
				'id' => $this->getId(),
				'name' => $this->getName(),
				'avatar' => $this->getAvatar(),
				'color' => $this->getColor(),
				'type' => $this->getType()->value,
			];
		}

		$idle = false;
		$lastActivityDate = false;
		$mobileLastDate = false;
		$desktopLastDate = false;

		if (!isset($option['WITHOUT_ONLINE']) || $option['WITHOUT_ONLINE'] === false)
		{
			$idle = $this->getIdle() ? $this->getIdle()->format('c') : false;
			$lastActivityDate = $this->getLastActivityDate() ? $this->getLastActivityDate()->format('c') : false;
			$mobileLastDate = $this->getMobileLastDate() ? $this->getMobileLastDate()->format('c') : false;
			$desktopLastDate = $this->getDesktopLastDate() ? $this->getDesktopLastDate()->format('c') : false;
		}

		return [
			'id' => $this->getId(),
			'active' => $this->isActive(),
			'name' => $this->getName(),
			'firstName' => $this->getFirstName(),
			'lastName' => $this->getLastName(),
			'workPosition' => $this->getWorkPosition(),
			'color' => $this->getColor(),
			'avatar' => $this->getAvatar($option['FOR_REST'] ?? true),
			'avatarHr' => $this->getAvatarHr($option['FOR_REST'] ?? true),
			'gender' => $this->getGender(),
			'birthday' => (string)$this->getBirthday(),
			'extranet' => $this->isExtranet(),
			'network' => $this->isNetwork(),
			'bot' => $this->isBot(),
			'connector' => $this->isConnector(),
			'externalAuthId' => $this->getExternalAuthId(),
			'status' => $this->getStatus(),
			'idle' => $idle,
			'lastActivityDate' => $lastActivityDate,
			'mobileLastDate' => $mobileLastDate,
			'desktopLastDate' => $desktopLastDate,
			'absent' => $this->getAbsent() !== null ? $this->getAbsent()->format('c') : false,
			'departments' => $this->getDepartmentIds(),
			'phones' => empty($this->getPhones()) ? false : $this->getPhones(),
			'botData' => null,
			'type' => $this->getType()->value,
		];
	}

	public function getArray(array $option = []): array
	{
		$option['FOR_REST'] = false;
		$userData = $this->toRestFormat($option);

		if ($option['USER_SHORT_FORMAT'] ?? null)
		{
			return $userData;
		}

		$converter = new Converter(Converter::TO_SNAKE | Converter::TO_UPPER | Converter::KEYS);
		$userData = $converter->process($userData);

		if ($userData['PHONES'] ?? null)
		{
			$converter = new Converter(Converter::TO_LOWER | Converter::KEYS);
			$userData['PHONES'] = $converter->process($userData['PHONES']);
		}
		if (isset($userData['BOT_DATA']))
		{
			$converter = new Converter(Converter::TO_SNAKE | Converter::TO_LOWER | Converter::KEYS);
			$userData['BOT_DATA'] = $converter->process($userData['BOT_DATA']);
		}

		if (($option['JSON'] ?? 'N') === 'Y')
		{
			$userData = \Bitrix\Im\User::formatLegacyJson($userData);
		}

		return $userData;
	}

	//region Getters & setters

	public function isExist(): bool
	{
		return $this->getId() !== null;
	}

	public function setOnlineData(array $onlineData, bool $withStatus): void
	{
		$this->idle = $onlineData['IDLE'] ?? null;
		$this->lastActivityDate = $onlineData['LAST_ACTIVITY_DATE'] ?? null;
		$this->mobileLastDate = $onlineData['MOBILE_LAST_DATE'] ?? null;
		$this->desktopLastDate = $onlineData['DESKTOP_LAST_DATE'] ?? null;
		if ($withStatus)
		{
			$this->isOnlineDataWithStatusFilled = true;
		}
		else
		{
			$this->isOnlineDataFilled = true;
		}
	}

	public function getName(): ?string
	{
		return $this->userData['NAME'] ?? null;
	}

	public function getFirstName(): ?string
	{
		return $this->userData['FIRST_NAME'] ?? null;
	}

	public function getLastName(): ?string
	{
		return $this->userData['LAST_NAME'] ?? null;
	}

	public function getAvatar(bool $forRest = true): string
	{
		$avatar = $this->userData['AVATAR'] ?? '';

		return $forRest ? $this->prependPublicDomain($avatar) : $avatar;
	}

	public function getAvatarHr(bool $forRest = true): string
	{
		$avatarHr = $this->userData['AVATAR_HR'] ?? '';

		return $forRest ? $this->prependPublicDomain($avatarHr) : $avatarHr;
	}

	public function getBirthday(): string
	{
		return $this->userData['BIRTHDAY'] ?? '';
	}

	public function getAvatarId(): int
	{
		return $this->userData['AVATAR_ID'] ?? 0;
	}

	public function getWorkPosition(): ?string
	{
		return $this->userData['WORK_POSITION'] ?? null;
	}

	public function getGender(): string
	{
		return $this->userData['PERSONAL_GENDER'] === 'F' ? 'F' : 'M';
	}

	public function getExternalAuthId(): string
	{
		return $this->userData['EXTERNAL_AUTH_ID'] ?? 'default';
	}

	public function getWebsite(): string
	{
		return $this->userData['PERSONAL_WWW'] ?? '';
	}

	public function getEmail(): string
	{
		return $this->userData['EMAIL'] ?? '';
	}

	public function getPhones(): array
	{
		$result = [];

		foreach ([self::PHONE_MOBILE, self::PHONE_WORK, self::PHONE_INNER, self::PERSONAL_PHONE] as $phoneType)
		{
			if (isset($this->userData[$phoneType]) && $this->userData[$phoneType])
			{
				$result[mb_strtolower($phoneType)] = $this->userData[$phoneType];
			}
		}

		return $result;
	}

	public function getServices(): array
	{
		$result = [];

		if (isset($this->userData['UF_ZOOM']) && !empty($this->userData['UF_ZOOM']))
		{
			$result['zoom'] = $this->userData['UF_ZOOM'];
		}

		if (isset($this->userData['UF_SKYPE_LINK']) && !empty($this->userData['UF_SKYPE_LINK']))
		{
			$result['skype'] = $this->userData['UF_SKYPE_LINK'];
		}
		elseif (isset($this->userData['UF_SKYPE']) && !empty($this->userData['UF_SKYPE']))
		{
			$result['skype'] = 'skype://' . $this->userData['UF_SKYPE'];
		}

		return $result;
	}

	/**
	 * @deprecated
	 * @return bool
	 */
	public function getPhoneDevice(): bool
	{
		return Loader::includeModule('voximplant') && $this->userData['UF_VI_PHONE'] === 'Y';
	}

	public function getColor(): string
	{
		return $this->userData['COLOR'] ?? '';
	}

	public function getTzOffset(): string
	{
		return $this->userData['TIME_ZONE_OFFSET'] ?? '';
	}

	public function getLanguageId(): ?string
	{
		return $this->userData['LANGUAGE_ID'] ?? null;
	}

	public function isExtranet(): bool
	{
		return $this->userData['IS_EXTRANET'] ?? false;
	}

	public function isActive(): bool
	{
		return $this->userData['ACTIVE'] === 'Y';
	}

	public function getAbsent(): ?DateTime
	{
		return $this->userData['ABSENT'] ?? null;
	}

	public function isNetwork(): bool
	{
		return $this->userData['IS_NETWORK'] ?? false;
	}

	public function isBot(): bool
	{
		return $this->userData['IS_BOT'] ?? false;
	}

	public function isConnector(): bool
	{
		return $this->userData['IS_CONNECTOR'] ?? false;
	}

	public function getDepartmentIds(): array
	{
		return
			(isset($this->userData['UF_DEPARTMENT']) && is_array($this->userData['UF_DEPARTMENT']))
				? $this->userData['UF_DEPARTMENT']
				: []
			;
	}

	public function getDepartments(): Departments
	{
		return new Departments(...$this->getDepartmentIds());
	}

	public function isOnlineDataFilled(bool $withStatus): bool
	{
		return $withStatus ? $this->isOnlineDataWithStatusFilled : $this->isOnlineDataWithStatusFilled || $this->isOnlineDataFilled;
	}

	public function getStatus(bool $real = false): ?string
	{
		if ($real)
		{
			return $this->userData['STATUS'] ?? 'online';
		}

		return 'online';
	}

	public function getIdle(bool $real = false): ?DateTime
	{
		if ($real)
		{
			$this->fillOnlineData(true);
		}

		return $this->idle;
	}

	public function getLastActivityDate(): ?DateTime
	{
		$this->fillOnlineData();

		return $this->lastActivityDate;
	}

	public function getMobileLastDate(bool $real = false): ?DateTime
	{
		if ($real)
		{
			$this->fillOnlineData(true);
		}

		return $this->mobileLastDate;
	}

	public function getDesktopLastDate(bool $real = false): ?DateTime
	{
		if ($real)
		{
			$this->fillOnlineData(true);
		}

		return $this->desktopLastDate;
	}

	public function getType(): UserType
	{
		return UserType::USER;
	}

	public function isAdmin(): bool
	{
		if ($this->isAdmin !== null)
		{
			return $this->isAdmin;
		}

		global $USER;
		if (Loader::includeModule('bitrix24'))
		{
			if (
				$USER instanceof \CUser
				&& $USER->isAuthorized()
				&& $USER->isAdmin()
				&& (int)$USER->getId() === $this->getId()
			)
			{
				$this->isAdmin = true;

				return $this->isAdmin;
			}
			$this->isAdmin = \CBitrix24::isPortalAdmin($this->getId());

			return $this->isAdmin;
		}

		if (
			$USER instanceof \CUser
			&& $USER->isAuthorized()
			&& (int)$USER->getId() === $this->getId()
		)
		{
			$this->isAdmin = $USER->isAdmin();

			return $this->isAdmin;
		}

		$result = false;
		$groups = UserTable::getUserGroupIds($this->getId());
		foreach ($groups as $groupId)
		{
			if ((int)$groupId === 1)
			{
				$result = true;
				break;
			}
		}
		$this->isAdmin = $result;

		return $this->isAdmin;
	}

	public function isSuperAdmin(): bool
	{
		global $USER;
		if (!Loader::includeModule('socialnetwork') || (int)$USER->getId() !== $this->getId())
		{
			return false;
		}

		return $this->isAdmin() && \CSocNetUser::IsEnabledModuleAdmin();
	}

	//endregion

	private function prependPublicDomain(string $url): string
	{
		if ($url !== '' && mb_strpos($url, 'http') !== 0)
		{
			return Common::getPublicDomain() . $url;
		}

		return $url;
	}

	public static function getFirstAdmin(): int
	{
		$adminIds = [];

		if (Loader::includeModule('bitrix24'))
		{
			$adminIds = \CBitrix24::getAllAdminId();
		}
		else
		{
			$res = \CGroup::getGroupUserEx(1);
			while ($row = $res->fetch())
			{
				$adminIds[] = (int)$row["USER_ID"];
			}
		}

		$resultAdminIds = [];
		foreach ($adminIds as $adminId)
		{
			$user = User::getInstance((int)$adminId);
			if (!$user->isExtranet() && $user->isActive())
			{
				$resultAdminIds[] = (int)$adminId;
			}
		}

		return !empty($resultAdminIds) ? (int)min($resultAdminIds) : 0;
	}

	public static function clearStaticCache(int $id): void
	{
		unset(self::$userStaticCache[$id]);
	}
}
