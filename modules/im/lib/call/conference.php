<?php

namespace Bitrix\Im\Call;

use Bitrix\Im\Alias;
use Bitrix\Im\Chat;
use Bitrix\Im\Common;
use Bitrix\Im\Model\AliasTable;
use Bitrix\Im\Model\ConferenceUserRoleTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\Settings;
use Bitrix\Im\V2\Chat\ChatFactory;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\ArrayResult;
use Bitrix\Main\Entity;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Im\Model\ConferenceTable;
use CBitrix24;

class Conference
{
	/* codes sync with im/install/js/im/const/src/call.js:25 */
	public const ERROR_USER_LIMIT_REACHED = "userLimitReached";
	public const ERROR_BITRIX24_ONLY = "bitrix24only";
	public const ERROR_DETECT_INTRANET_USER = "detectIntranetUser";
	public const ERROR_KICKED_FROM_CALL = "kickedFromCall";
	public const ERROR_WRONG_ALIAS = "wrongAlias";

	public const STATE_NOT_STARTED = "notStarted";
	public const STATE_ACTIVE = "active";
	public const STATE_FINISHED = "finished";

	public const ALIAS_TYPE = 'VIDEOCONF';
	public const BROADCAST_MODE = 'BROADCAST';

	public const PRESENTERS_LIMIT = 4;
	public const BROADCAST_USER_LIMIT = 500;
	public const ROLE_PRESENTER = 'presenter';

	protected $id;
	protected $alias;
	protected $aliasId;
	protected $chatId;
	protected $password;
	protected $invitation;
	protected $startDate;
	protected $chatName;
	protected $hostName;
	protected $hostId;
	protected $users;
	protected $broadcastMode;
	protected $speakers;

	protected function __construct()
	{
	}

	public function getId()
	{
		return $this->id;
	}

	public function getAliasId()
	{
		return $this->aliasId;
	}

	public function getAlias()
	{
		return $this->alias;
	}

	public function getStartDate()
	{
		return $this->startDate;
	}

	public function getChatId()
	{
		return $this->chatId;
	}

	public function getChatName()
	{
		return $this->chatName;
	}

	public function getHostName()
	{
		return $this->hostName;
	}

	public function getHostId()
	{
		return $this->hostId;
	}

	public function isPasswordRequired(): bool
	{
		return $this->password !== '';
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getInvitation()
	{
		return $this->invitation;
	}

	public function getUsers(): array
	{
		$users = Chat::getUsers($this->getChatId(), ['SKIP_EXTERNAL' => true]);

		return array_map(static function($user){
			return [
				'id' => $user['id'],
				'title' => $user['name'],
				'avatar' => $user['avatar']
			];
		}, $users);
	}

	public function getUserLimit(): int
	{
		if ($this->isBroadcast())
		{
			return self::BROADCAST_USER_LIMIT;
		}
		else if (Call::isCallServerEnabled())
		{
			return Call::getMaxCallServerParticipants();
		}
		else
		{
			 return (int)Option::get('im', 'turn_server_max_users');
		}
	}

	public function isBroadcast(): bool
	{
		return $this->broadcastMode;
	}

	public function getPresentersList(): array
	{
		$result = [];

		$presenters = \Bitrix\Im\Model\ConferenceUserRoleTable::getList(
			[
				'select' => ['USER_ID'],
				'filter' => [
					'=CONFERENCE_ID' => $this->getId(),
					'=ROLE' => self::ROLE_PRESENTER
				]
			]
		)->fetchAll();

		foreach ($presenters as $presenter)
		{
			$result[] = (int)$presenter['USER_ID'];
		}

		return $result;
	}

	public function getPresentersInfo(): array
	{
		$result = [];
		$presenters = $this->getPresentersList();

		foreach ($presenters as $presenter)
		{
			$presenterInfo =  \Bitrix\Im\User::getInstance($presenter)->getArray();
			$result[] = array_change_key_case($presenterInfo, CASE_LOWER);
		}

		return $result;
	}

	public function isPresenter(int $userId): bool
	{
		$presenters = $this->getPresentersList();

		return in_array($userId, $presenters, true);
	}

	public function makePresenter(int $userId): \Bitrix\Main\ORM\Data\AddResult
	{
		return \Bitrix\Im\Model\ConferenceUserRoleTable::add(
			[
				'CONFERENCE_ID' => $this->getId(),
				'USER_ID' => $userId,
				'ROLE' => self::ROLE_PRESENTER
			]
		);
	}

	public function deletePresenter(int $userId): \Bitrix\Main\ORM\Data\DeleteResult
	{
		return \Bitrix\Im\Model\ConferenceUserRoleTable::delete(
			[
				'CONFERENCE_ID' => $this->getId(),
				'USER_ID' => $userId
			]
		);
	}

	public function isActive(): bool
	{
		//TODO
		return true;
	}

	public function isFinished(): bool
	{
		return $this->getStatus() === static::STATE_FINISHED;
	}

	public function getStatus(): string
	{
		//todo
		if (!($this->startDate instanceof DateTime))
		{
			return self::STATE_FINISHED;
		}

		$now = time();
		$startTimestamp = $this->startDate->getTimestamp();

		//TODO: active and finished
		if ($startTimestamp > $now)
		{
			return self::STATE_NOT_STARTED;
		}

		return self::STATE_FINISHED;
	}

	public function getPublicLink(): string
	{
		return Common::getPublicDomain().'/video/'.$this->alias;
	}

	public function canUserEdit($userId): bool
	{
		if (Loader::includeModule('bitrix24'))
		{
			$isAdmin = CBitrix24::IsPortalAdmin($userId);
		}
		else
		{
			$user = new \CUser();
			$arGroups = $user::GetUserGroup($userId);
			$isAdmin = in_array(1, $arGroups, true);
		}

//		return ($this->getStatus() !== static::STATE_FINISHED) &&
		return ($isAdmin || $this->getHostId() === $userId);
	}

	public function canUserDelete($userId): bool
	{
		if (Loader::includeModule('bitrix24'))
		{
			$isAdmin = CBitrix24::IsPortalAdmin($userId);
		}
		else
		{
			$user = new \CUser();
			$arGroups = $user::GetUserGroup($userId);
			$isAdmin = in_array(1, $arGroups, true);
		}

		return $isAdmin || $this->getHostId() === $userId;
	}

	protected function setFields(array $fields): bool
	{
		//set instance fields after update
		return true;
	}

	protected function getChangedFields(array $fields): array
	{
		$result = [];

		if (isset($fields['TITLE']) && $fields['TITLE'] !== $this->chatName)
		{
			$result['TITLE'] = $fields['TITLE'];
		}

		if (isset($fields['VIDEOCONF']['PASSWORD']) && $fields['VIDEOCONF']['PASSWORD'] !== $this->getPassword())
		{
			$result['VIDEOCONF']['PASSWORD'] = $fields['VIDEOCONF']['PASSWORD'];
		}

		if (isset($fields['VIDEOCONF']['INVITATION']) && $fields['VIDEOCONF']['INVITATION'] !== $this->getInvitation())
		{
			$result['VIDEOCONF']['INVITATION'] = $fields['VIDEOCONF']['INVITATION'];
		}

		$newBroadcastMode = isset($fields['VIDEOCONF']['PRESENTERS']) && count($fields['VIDEOCONF']['PRESENTERS']) > 0;
		if ($this->isBroadcast() !== $newBroadcastMode)
		{
			$result['VIDEOCONF']['IS_BROADCAST'] = $newBroadcastMode === true ? 'Y' : 'N';
		}

		if ($newBroadcastMode)
		{
			$currentPresenters = $this->getPresentersList();
			$result['NEW_PRESENTERS'] = array_diff($fields['VIDEOCONF']['PRESENTERS'], $currentPresenters);
			$result['DELETED_PRESENTERS'] = array_diff($currentPresenters, $fields['VIDEOCONF']['PRESENTERS']);
		}

		if (isset($fields['USERS']))
		{
			$currentUsers = array_map(static function($user){
				return $user['id'];
			}, $this->users);

			$result['NEW_USERS'] = array_diff($fields['USERS'], $currentUsers);
			$result['DELETED_USERS'] = array_diff($currentUsers, $fields['USERS']);
		}

		return $result;
	}

	public function update(array $fields = []): Result
	{
		$result = new Result();

		if (!static::isEnvironmentConfigured())
		{
			return $result->addError(
				new Error(
					Loc::getMessage('IM_CALL_CONFERENCE_ERROR_ENVIRONMENT_CONFIG'),
					'ENVIRONMENT_CONFIG_ERROR'
				)
			);
		}

		$validationResult = static::validateFields($fields);
		if (!$validationResult->isSuccess())
		{
			return $result->addErrors($validationResult->getErrors());
		}
		$updateData = $validationResult->getData()['FIELDS'];

		if (!isset($fields['ID']))
		{
			return $result->addError(
				new Error(
					Loc::getMessage('IM_CALL_CONFERENCE_ERROR_ID_NOT_PROVIDED'),
					'CONFERENCE_ID_EMPTY'
				)
			);
		}

		$updateData = $this->getChangedFields($updateData);
		if (empty($updateData))
		{
			return $result;
		}
		$updateData['ID'] = $fields['ID'];

		if (!isset($fields['PASSWORD']))
		{
			unset($updateData['VIDEOCONF']['PASSWORD']);
		}

		global $USER;
		$chat = new \CIMChat($USER->GetID());

		//Chat update
		if ($updateData['TITLE'])
		{
			$renameResult = $chat->Rename($this->getChatId(), $updateData['TITLE']);

			if (!$renameResult)
			{
				return $result->addError(
					new Error(
						Loc::getMessage('IM_CALL_CONFERENCE_ERROR_RENAMING_CHAT'),
						'CONFERENCE_RENAMING_ERROR'
					)
				);
			}

			$this->chatName = $updateData['TITLE'];
		}

		//Adding users
		if (isset($updateData['NEW_USERS']))
		{
			//check user count
			$userLimit = $this->getUserLimit();

			$currentUserCount = \CIMChat::getUserCount($this->chatId);
			$newUserCount = $currentUserCount + count($updateData['NEW_USERS']);
			if (isset($updateData['DELETED_USERS']))
			{
				$newUserCount -= count($updateData['DELETED_USERS']);
			}

			if ($newUserCount > $userLimit)
			{
				return $result->addError(
					new Error(
						Loc::getMessage('IM_CALL_CONFERENCE_ERROR_MAX_USERS'),
						'USER_LIMIT_ERROR'
					)
				);
			}

			foreach ($updateData['NEW_USERS'] as $newUser)
			{
				$addingResult = $chat->AddUser($this->getChatId(), $newUser);

				if (!$addingResult)
				{
					return $result->addError(
						new Error(
							Loc::getMessage('IM_CALL_CONFERENCE_ERROR_ADDING_USERS'),
							'ADDING_USER_ERROR'
						)
					);
				}
			}
		}

		//Deleting users
		if (isset($updateData['DELETED_USERS']))
		{
			foreach ($updateData['DELETED_USERS'] as $deletedUser)
			{
				$addingResult = $chat->DeleteUser($this->getChatId(), $deletedUser);

				if (!$addingResult)
				{
					return $result->addError(
						new Error(
							Loc::getMessage('IM_CALL_CONFERENCE_ERROR_DELETING_USERS'),
							'DELETING_USER_ERROR'
						)
					);
				}
			}
		}

		//Conference update
		if (isset($updateData['VIDEOCONF']))
		{
			if (isset($updateData['VIDEOCONF']['IS_BROADCAST']))
			{
				\CIMChat::SetChatParams($this->getChatId(), [
					'ENTITY_DATA_1' => $updateData['VIDEOCONF']['IS_BROADCAST'] === 'Y'? self::BROADCAST_MODE: ''
				]);
			}

			$updateResult = ConferenceTable::update($updateData['ID'], $updateData['VIDEOCONF']);

			if (!$updateResult->isSuccess())
			{
				return $result->addErrors($updateResult->getErrors());
			}
		}

		//update presenters
		if (isset($updateData['NEW_PRESENTERS']) && !empty($updateData['NEW_PRESENTERS']))
		{
			$setManagers = [];
			foreach ($updateData['NEW_PRESENTERS'] as $newPresenter)
			{
				$this->makePresenter($newPresenter);
				$setManagers[$newPresenter] = true;
			}
			$chat->SetManagers($this->getChatId(), $setManagers, false);
		}

		if (isset($updateData['DELETED_PRESENTERS']) && !empty($updateData['DELETED_PRESENTERS']))
		{
			$removeManagers = [];
			foreach ($updateData['DELETED_PRESENTERS'] as $deletedPresenter)
			{
				$this->deletePresenter($deletedPresenter);
				$removeManagers[$deletedPresenter] = false;
			}
			$chat->SetManagers($this->getChatId(), $removeManagers, false);
		}

		// delete presenters if we change mode to normal
		if (isset($updateData['VIDEOCONF']['IS_BROADCAST']) && $updateData['VIDEOCONF']['IS_BROADCAST'] === 'N')
		{
			$presentersList = $this->getPresentersList();
			foreach ($presentersList as $presenter)
			{
				$this->deletePresenter($presenter);
			}
		}

		// send pull
		$isPullNeeded = isset($updateData['VIDEOCONF']['IS_BROADCAST']) || isset($updateData['NEW_PRESENTERS']) || isset($updateData['DELETED_PRESENTERS']);
		if ($isPullNeeded && Loader::includeModule("pull"))
		{
			$relations = \CIMChat::GetRelationById($this->getChatId(), false, true, false);
			$pushMessage = [
				'module_id' => 'im',
				'command' => 'conferenceUpdate',
				'params' => [
					'chatId' => $this->getChatId(),
					'isBroadcast' => isset($updateData['VIDEOCONF']['IS_BROADCAST']) ? $updateData['VIDEOCONF']['IS_BROADCAST'] === 'Y' : '',
					'presenters' => $this->getPresentersList()
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			];
			\Bitrix\Pull\Event::add(array_keys($relations), $pushMessage);
		}

		return $result;
	}

	public function delete(): Result
	{
		$result = new Result();

		//hide chat
		\CIMChat::hide($this->getChatId());

		//delete relations
		RelationTable::deleteBatch(
			['=CHAT_ID' => $this->getChatId()]
		);

		//delete roles
		$presenters = $this->getPresentersList();
		foreach ($presenters as $presenter)
		{
			$deleteRolesResult = ConferenceUserRoleTable::delete(
				[
					'CONFERENCE_ID' => $this->getId(),
					'USER_ID' => $presenter
				]
			);

			if (!$deleteRolesResult->isSuccess())
			{
				return $result->addErrors($deleteRolesResult->getErrors());
			}
		}

		//delete conference
		$deleteConferenceResult = ConferenceTable::delete($this->getId());
		if (!$deleteConferenceResult->isSuccess())
		{
			return $result->addErrors($deleteConferenceResult->getErrors());
		}

		//delete alias
		$deleteAliasResult = AliasTable::delete($this->getAliasId());
		if (!$deleteAliasResult->isSuccess())
		{
			return $result->addErrors($deleteAliasResult->getErrors());
		}

		//delete access codes
		$accessProvider = new \Bitrix\Im\Access\ChatAuthProvider;
		$accessProvider->deleteChatCodes((int)$this->getChatId());

		return $result;
	}

	protected static function validateFields(array $fields): Result
	{
		$result = new Result();
		$validatedFields = [];

		$fields = array_change_key_case($fields, CASE_UPPER);

		if (isset($fields['TITLE']) && is_string($fields['TITLE']))
		{
			$fields['TITLE'] = trim($fields['TITLE']);
			$validatedFields['TITLE'] = $fields['TITLE'];
		}

		if (isset($fields['PASSWORD']) && is_string($fields['PASSWORD']) && $fields['PASSWORD'] !== '')
		{
			$fields['PASSWORD'] = trim($fields['PASSWORD']);

			if (strlen($fields['PASSWORD']) < 3)
			{
				return $result->addError(
					new Error(
						Loc::getMessage('IM_CALL_CONFERENCE_ERROR_PASSWORD_LENGTH_NEW'),
						'PASSWORD_SHORT_ERROR'
					)
				);
			}

			$validatedFields['VIDEOCONF']['PASSWORD'] = $fields['PASSWORD'];
		}
		else
		{
			$validatedFields['VIDEOCONF']['PASSWORD'] = '';
		}

		if (isset($fields['INVITATION']) && is_string($fields['INVITATION']))
		{
			$fields['INVITATION'] = trim($fields['INVITATION']);

			if (strlen($fields['INVITATION']) > 255)
			{
				return $result->addError(
					new Error(
						Loc::getMessage('IM_CALL_CONFERENCE_ERROR_INVITATION_LENGTH'),
						'INVITATION_LONG_ERROR'
					)
				);
			}

			$validatedFields['VIDEOCONF']['INVITATION'] = $fields['INVITATION'];
		}

		if (isset($fields['USERS']) && is_array($fields['USERS']))
		{
			$validatedFields['USERS'] = [];
			foreach ($fields['USERS'] as $userId)
			{
				$validatedFields['USERS'][] = (int)$userId;
			}
		}

		if (isset($fields['BROADCAST_MODE']) && $fields['BROADCAST_MODE'] === true && Settings::isBroadcastingEnabled())
		{
			if (count($fields['PRESENTERS']) === 0)
			{
				return $result->addError(
					new Error(
						Loc::getMessage('IM_CALL_CONFERENCE_ERROR_NO_PRESENTERS'),
						'PRESENTERS_EMPTY_ERROR'
					)
				);
			}

			if (count($fields['PRESENTERS']) > self::PRESENTERS_LIMIT)
			{
				return $result->addError(
					new Error(
						Loc::getMessage('IM_CALL_CONFERENCE_ERROR_TOO_MANY_PRESENTERS'),
						'PRESENTERS_TOO_MANY_ERROR'
					)
				);
			}

			$validatedFields['VIDEOCONF']['IS_BROADCAST'] = 'Y';
			$validatedFields['VIDEOCONF']['PRESENTERS'] = [];
			foreach ($fields['PRESENTERS'] as $userId)
			{
				$validatedFields['USERS'][] = (int)$userId;
				$validatedFields['VIDEOCONF']['PRESENTERS'][] = (int)$userId;
			}
		}
		else
		{
			$validatedFields['VIDEOCONF']['IS_BROADCAST'] = 'N';
		}

		if (isset($fields['ALIAS_DATA']))
		{
			if (static::isAliasCorrect($fields['ALIAS_DATA']))
			{
				$validatedFields['VIDEOCONF']['ALIAS_DATA'] = $fields['ALIAS_DATA'];
			}
			else
			{
				return $result->addError(
					new Error(
						Loc::getMessage('IM_CALL_CONFERENCE_ERROR_ALIAS'),
						'WRONG_ALIAS_ERROR'
					)
				);
			}
		}
		else
		{
			$validatedFields['VIDEOCONF']['ALIAS_DATA'] = Alias::addUnique([
				"ENTITY_TYPE" => Alias::ENTITY_TYPE_VIDEOCONF,
				"ENTITY_ID" => 0
			]);
		}

		$result->setData(['FIELDS' => $validatedFields]);

		return $result;
	}

	public static function add(array $fields = []): Result
	{
		$result = new Result();

		if (!static::isEnvironmentConfigured())
		{
			return $result->addError(
				new Error(
					Loc::getMessage('IM_CALL_CONFERENCE_ERROR_ENVIRONMENT_CONFIG'),
					'ENVIRONMENT_CONFIG_ERROR'
				)
			);
		}

		$validationResult = static::validateFields($fields);
		if (!$validationResult->isSuccess())
		{
			return $result->addErrors($validationResult->getErrors());
		}

		$addData = $validationResult->getData()['FIELDS'];
		$addData['ENTITY_TYPE'] = static::ALIAS_TYPE;
		$addData['ENTITY_DATA_1'] = $addData['VIDEOCONF']['IS_BROADCAST'] === 'Y'? static::BROADCAST_MODE: '';

		$currentUser = \Bitrix\Im\User::getInstance();
		$addData['AUTHOR_ID'] = $currentUser->getId();

		$addData['MANAGERS'] = [];
		if ($addData['VIDEOCONF']['IS_BROADCAST'] === 'Y')
		{
			foreach ($addData['VIDEOCONF']['PRESENTERS'] as $presenter)
			{
				$addData['MANAGERS'][$presenter] = true;
			}
		}

		$result = ChatFactory::getInstance()->addChat($addData);
		if (!$result->isSuccess() || !$result->hasResult())
		{
			return $result->addError(
				new Error(
					Loc::getMessage('IM_CALL_CONFERENCE_ERROR_CREATING'),
					'CREATION_ERROR'
				)
			);
		}

		$chatResult = $result->getResult();
		return $result->setData([
			'CHAT_ID' => $chatResult['CHAT_ID'],
			'ALIAS_DATA' => $addData['VIDEOCONF']['ALIAS_DATA']
		]);
	}

	public static function getByAlias(string $alias)
	{
		$conferenceFields = ConferenceTable::getRow(
			[
				'select' => self::getDefaultSelectFields(),
				'runtime' => self::getRuntimeChatField(),
				'filter' => ['=ALIAS.ALIAS' => $alias, '=ALIAS.ENTITY_TYPE' => static::ALIAS_TYPE]
			]
		);

		if (!$conferenceFields)
		{
			return false;
		}

		return static::createWithArray($conferenceFields);
	}

	public static function getById(int $id): ?Conference
	{
		$conferenceFields = ConferenceTable::getRow(
			[
				'select' => self::getDefaultSelectFields(),
				'runtime' => self::getRuntimeChatField(),
				'filter' => ['=ID' => $id, '=ALIAS.ENTITY_TYPE' => static::ALIAS_TYPE]
			]
		);

		if (!$conferenceFields)
		{
			return null;
		}

		return static::createWithArray($conferenceFields);
	}

	public static function createWithArray(array $fields): Conference
	{
		$instance = new static();

		$instance->id = (int)$fields['ID'];
		$instance->alias = $fields['ALIAS_CODE'];
		$instance->aliasId = $fields['ALIAS_PRIMARY'];
		$instance->chatId = (int)$fields['CHAT_ID'];
		$instance->password = $fields['PASSWORD'];
		$instance->invitation = $fields['INVITATION'];
		$instance->startDate = $fields['CONFERENCE_START'];
		$instance->chatName = $fields['CHAT_NAME'];
		$instance->hostName = $fields['HOST_NAME']." ".$fields['HOST_LAST_NAME'];
		$instance->hostId = $fields['HOST'];
		$instance->broadcastMode = $fields['IS_BROADCAST'] === 'Y';

		$instance->users = $instance->getUsers();

		return $instance;
	}

	public static function getAll(array $queryParams): ArrayResult
	{
		$result = [];
		$list = ConferenceTable::getList($queryParams);

		while ($item = $list->fetch())
		{
			$result[] = $item;
		}

		$dbResult = new ArrayResult($result);
		$dbResult->setCount($list->getCount());

		return $dbResult;
	}

	public static function getStatusList(): array
	{
		return [static::STATE_NOT_STARTED, static::STATE_ACTIVE, static::STATE_FINISHED];
	}

	public static function getDefaultSelectFields(): array
	{
		return [
			'ID',
			'CONFERENCE_START',
			'PASSWORD',
			'INVITATION',
			'IS_BROADCAST',
			'ALIAS_PRIMARY' => 'ALIAS.ID',
			'ALIAS_CODE' => 'ALIAS.ALIAS',
			'CHAT_ID' => 'ALIAS.ENTITY_ID',
			'HOST' => 'CHAT.AUTHOR.ID',
			'HOST_NAME' => 'CHAT.AUTHOR.NAME',
			'HOST_LAST_NAME' => 'CHAT.AUTHOR.LAST_NAME',
			'CHAT_NAME' => 'CHAT.TITLE'
		];
	}

	public static function getRuntimeChatField(): array
	{
		return [
			new Entity\ReferenceField(
				'CHAT', 'Bitrix\Im\Model\ChatTable', ['=this.CHAT_ID' => 'ref.ID']
			),
			new Entity\ReferenceField(
				'RELATION', 'Bitrix\Im\Model\RelationTable', ['=this.CHAT_ID' => 'ref.CHAT_ID'], ['join_type' => 'inner']
			)
		];
	}

	public static function removeTemporaryAliases()
	{
		AliasTable::deleteBatch(
			[
				'=ENTITY_TYPE' => Alias::ENTITY_TYPE_VIDEOCONF,
				'=ENTITY_ID' => 0
			],
			1000
		);

		return '\Bitrix\Im\Call\Conference::removeTemporaryAliases();';
	}

	private static function isAliasCorrect($aliasData): bool
	{
		return isset($aliasData['ID'], $aliasData['ALIAS']) && Alias::getByIdAndCode($aliasData['ID'], $aliasData['ALIAS']);
	}

	private static function isEnvironmentConfigured(): bool
	{
		return (
			\Bitrix\Main\Loader::includeModule('pull')
			&& \CPullOptions::GetPublishWebEnabled()
			&& \Bitrix\Im\Call\Call::isCallServerEnabled()
		);
	}
}