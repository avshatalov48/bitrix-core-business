<?php

namespace Bitrix\Im\V2\Entity\User;

use Bitrix\Im\Common;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\Model\StatusTable;
use Bitrix\Im\V2\Chat\FavoriteChat;
use Bitrix\Im\V2\Chat\PrivateChat;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

class User implements RestEntity
{
	public const PHONE_ANY = 'PHONE_ANY';
	public const PHONE_MOBILE = 'PERSONAL_MOBILE';
	public const PHONE_PERSONAL = 'PERSONAL_PHONE';
	public const PHONE_WORK = 'WORK_PHONE';

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
	protected ?bool $isAdmin = null;

	protected ?string $status = null;
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
	public function getChatWith(int $userId): ?PrivateChat
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

	final public function hasAccess(?int $idOtherUser = null): bool
	{
		$idOtherUser = $idOtherUser ?? Locator::getContext()->getUserId();

		$otherUser = User::getInstance($idOtherUser);

		if (!$otherUser->isExist())
		{
			return false;
		}

		if ($this->getId() === $idOtherUser)
		{
			return true;
		}

		if (isset($this->accessCache[$idOtherUser]))
		{
			return $this->accessCache[$idOtherUser];
		}

		$this->accessCache[$idOtherUser] = $this->checkAccessWithoutCaching($otherUser);

		return $this->accessCache[$idOtherUser];
	}

	protected function checkAccessWithoutCaching(self $otherUser): bool
	{
		if (!static::$moduleManager::isModuleInstalled('intranet'))
		{
			return $this->hasAccessBySocialNetwork($otherUser->getId());
		}

		if ($otherUser->isExtranet())
		{
			$inGroup = \Bitrix\Im\Integration\Socialnetwork\Extranet::isUserInGroup($this->getId(), $otherUser->getId());
			if ($inGroup)
			{
				return true;
			}

			return false;
		}

		if ($this->isNetwork())
		{
			return true;
		}

		return true;
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

	protected function fillOnlineData(): void
	{
		if ($this->isOnlineDataFilled)
		{
			return;
		}

		$select = ['USER_ID', 'STATUS', 'IDLE', 'MOBILE_LAST_DATE', 'DESKTOP_LAST_DATE', 'LAST_ACTIVITY_DATE' => 'USER.LAST_ACTIVITY_DATE'];
		$statusData = StatusTable::query()
			->setSelect($select)
			->where('USER_ID', $this->getId())
			->fetch() ?: []
		;

		$this->setOnlineData($statusData);
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
			];
		}

		return [
			'id' => $this->getId(),
			'active' => $this->isActive(),
			'name' => $this->getName(),
			'firstName' => $this->getFirstName(),
			'lastName' => $this->getLastName(),
			'workPosition' => $this->getWorkPosition(),
			'color' => $this->getColor(),
			'avatar' => $this->getAvatar(),
			'avatarHr' => $this->getAvatarHr(),
			'gender' => $this->getGender(),
			'birthday' => (string)$this->getBirthday(),
			'extranet' => $this->isExtranet(),
			'network' => $this->isNetwork(),
			'bot' => $this->isBot(),
			'connector' => $this->isConnector(),
			'externalAuthId' => $this->getExternalAuthId(),
			'status' => $this->getStatus(),
			'idle' => $this->getIdle() ? $this->getIdle()->format('c') : false,
			'lastActivityDate' => $this->getLastActivityDate() ? $this->getLastActivityDate()->format('c') : false,
			'mobileLastDate' => $this->getMobileLastDate() ? $this->getMobileLastDate()->format('c') : false,
			'desktopLastDate' => $this->getDesktopLastDate() ? $this->getDesktopLastDate()->format('c') : false,
			'absent' => $this->getAbsent() !== null ? $this->getAbsent()->format('c') : false,
			'departments' => $this->getDepartments(),
			'phones' => empty($this->getPhones()) ? false : $this->getPhones(),
		];
	}

	//region Getters & setters

	public function isExist(): bool
	{
		return $this->getId() !== null;
	}

	public function setOnlineData(array $onlineData): void
	{
		$this->status = $onlineData['STATUS'] ?? null;
		$this->idle = $onlineData['IDLE'] ?? null;
		$this->lastActivityDate = $onlineData['LAST_ACTIVITY_DATE'] ?? null;
		$this->mobileLastDate = $onlineData['MOBILE_LAST_DATE'] ?? null;
		$this->desktopLastDate = $onlineData['DESKTOP_LAST_DATE'] ?? null;
		$this->isOnlineDataFilled = true;
	}

	public function getName(): string
	{
		return $this->userData['NAME'] ?? '';
	}

	public function getFirstName(): string
	{
		return $this->userData['FIRST_NAME'] ?? '';
	}

	public function getLastName(): string
	{
		return $this->userData['LAST_NAME'] ?? '';
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

		foreach ([self::PHONE_MOBILE, self::PHONE_PERSONAL, self::PHONE_WORK] as $phoneType)
		{
			if (isset($this->userData[$phoneType]) && $this->userData[$phoneType])
			{
				$result[$phoneType] = $this->userData[$phoneType];
			}
		}

		return $result;
	}

	public function getColor(): string
	{
		return $this->userData['COLOR'] ?? '';
	}

	public function getTzOffset(): string
	{
		return $this->userData['TIME_ZONE_OFFSET'] ?? '';
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

	public function getDepartments(): array
	{
		return
			(isset($this->userData['UF_DEPARTMENT']) && is_array($this->userData['UF_DEPARTMENT']))
				? $this->userData['UF_DEPARTMENT']
				: []
			;
	}

	public function isOnlineDataFilled(): bool
	{
		return $this->isOnlineDataFilled;
	}

	public function getStatus(): ?string
	{
		$this->fillOnlineData();

		return $this->status;
	}

	public function getIdle(): ?DateTime
	{
		$this->fillOnlineData();

		return $this->idle;
	}

	public function getLastActivityDate(): ?DateTime
	{
		$this->fillOnlineData();

		return $this->lastActivityDate;
	}

	public function getMobileLastDate(): ?DateTime
	{
		$this->fillOnlineData();

		return $this->mobileLastDate;
	}

	public function getDesktopLastDate(): ?DateTime
	{
		$this->fillOnlineData();

		return $this->desktopLastDate;
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

	//endregion

	private function prependPublicDomain(string $url): string
	{
		if ($url !== '' && mb_strpos($url, 'http') !== 0)
		{
			return Common::getPublicDomain() . $url;
		}

		return $url;
	}
}