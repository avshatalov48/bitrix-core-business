<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\Common;
use Bitrix\Im\Recent;
use Bitrix\Im\V2\Chat\ChatFactory;
use Bitrix\Im\V2\Controller\Filter\CheckAvatarId;
use Bitrix\Im\V2\Controller\Filter\CheckAvatarIdInFields;
use Bitrix\Im\V2\Controller\Filter\CheckChatAccess;
use Bitrix\Im\V2\Controller\Filter\CheckChatAddParams;
use Bitrix\Im\V2\Controller\Filter\CheckChatManageUpdate;
use Bitrix\Im\V2\Controller\Filter\CheckChatOwner;
use Bitrix\Im\V2\Controller\Filter\CheckChatUpdate;
use Bitrix\Intranet\ActionFilter\IntranetUser;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Pull\Event;
use CFile;
use CRestUtil;

class Chat extends BaseController
{
	public function configureActions()
	{
		return [
			'add' => [
				'+prefilters' => [
					new IntranetUser(),
					new CheckChatAddParams(),
					new CheckAvatarIdInFields(),
				],
			],
			'update' => [
				'+prefilters' => [
					new IntranetUser(),
					new CheckChatAddParams(),
					new CheckAvatarIdInFields(),
				],
			],
			'setOwner' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
					new CheckChatOwner(),
				]
			],
			'setTitle' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'setDescription' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'setColor' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'setAvatarId' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
					new CheckAvatarId(),
				]
			],
			'setAvatar' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'addUsers' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'removeUsers' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'setManagers' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatUpdate(),
				]
			],
			'setManageUsers' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatManageUpdate(),
					new CheckChatUpdate(),
				]
			],
			'setManageUI' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatManageUpdate(),
					new CheckChatUpdate(),
				]
			],
			'setManageSettings' => [
				'+prefilters' => [
					new CheckChatAccess(),
					new CheckChatManageUpdate(),
					new CheckChatUpdate(),
				]
			],
		];
	}

	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			\Bitrix\Im\V2\Chat::class,
			'chat',
			function ($className, $id) {
				return \Bitrix\Im\V2\Chat::getInstance((int)$id);
			}
		);
	}

	/**
	 * @restMethod im.v2.Chat.read
	 */
	public function readAction(\Bitrix\Im\V2\Chat $chat, bool $onlyRecent = false): ?array
	{
		$result = $chat->read($onlyRecent);

		return $this->convertKeysToCamelCase($result->getResult());
	}

	/**
	 * @restMethod im.v2.Chat.readAll
	 */
	public function readAllAction(CurrentUser $user): ?array
	{
		\Bitrix\Im\V2\Chat::readAllChats((int)$user->getId());

		return [];
	}

	/**
	 * @restMethod im.v2.Chat.unread
	 */
	public function unreadAction(\Bitrix\Im\V2\Chat $chat): ?array
	{
		Recent::unread($chat->getDialogId(), true);

		return [];
	}

	/**
	 * @restMethod im.v2.Chat.add
	 */
	public function addAction(array $fields): ?array
	{
		$fields['type'] = ($fields['type'] === 'CHANNEL') ? \Bitrix\Im\V2\Chat::IM_TYPE_CHANNEL : \Bitrix\Im\V2\Chat::IM_TYPE_CHAT;

		$data = self::recursiveWhiteList($fields, \Bitrix\Im\V2\Chat::AVAILABLE_PARAMS);
		$result = ChatFactory::getInstance()->addChat($data);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
			return null;
		}

		return $this->convertKeysToCamelCase($result->getResult());
	}

	//region Update Chat
	/**
	 * @restMethod im.v2.Chat.update
	 */
	public function updateAction(\Bitrix\Im\V2\Chat $chat, array $fields)
	{
		$currentUser = $this->getCurrentUser();
		$userId = isset($currentUser) ? $currentUser->getId() : null;
		$relation = $chat->getSelfRelation();

		$changeSettings = false;
		$changeUI = false;
		if ($chat->getAuthorId() === (int)$userId)
		{
			$changeSettings = true;
			$changeUI = true;
		}
		elseif ($relation->getManager())
		{
			if ($chat->getManageSettings() === \Bitrix\Im\V2\Chat::MANAGE_RIGHTS_MANAGERS)
			{
				$changeSettings = true;
			}
			if ($chat->getManageUI() === \Bitrix\Im\V2\Chat::MANAGE_RIGHTS_MANAGERS)
			{
				$changeUI = true;
			}
		}
		else
		{
			if ($chat->getManageUI() === \Bitrix\Im\V2\Chat::MANAGE_RIGHTS_ALL)
			{
				$changeUI = true;
			}
		}

		if ($changeSettings)
		{
			if (isset($fields['entityType']))
			{
				$chat->setEntityType($fields['entityType']);
			}
			if (isset($fields['entityId']))
			{
				$chat->setEntityId($fields['entityId']);
			}
			if (isset($fields['entityData1']))
			{
				$chat->setEntityData1($fields['entityData1']);
			}
			if (isset($fields['entityData2']))
			{
				$chat->setEntityData2($fields['entityData2']);
			}
			if (isset($fields['entityData3']))
			{
				$chat->setEntityData3($fields['entityData3']);
			}
			if (isset($fields['ownerId']))
			{
				$chat->setAuthorId($fields['ownerId']);
			}
			if (isset($fields['manageUsers']))
			{
				$chat->setManageUsers($fields['manageUsers']);
			}
			if (isset($fields['manageUI']))
			{
				$chat->setManageUI($fields['manageUI']);
			}
			if (isset($fields['manageSettings']))
			{
				$chat->setManageSettings($fields['manageSettings']);
			}
			if (isset($fields['managers']))
			{
				$chat->setManagers($fields['managers']);
			}
		}

		if ($changeUI)
		{
			if (isset($fields['title']))
			{
				$chat->setTitle($fields['title']);
			}
			if (isset($fields['description']))
			{
				$chat->setDescription($fields['description']);
			}
			if (isset($fields['color']))
			{
				$chat->setColor($fields['color']);
			}
			if (isset($fields['avatar']) && $fields['avatar'])
			{
				if (is_numeric((string)$fields['avatar']))
				{
					$this->setAvatarIdAction($chat, $fields['avatar']);
				}
				else
				{
					$this->setAvatarAction($chat, $fields['avatar']);
				}
			}
		}

		$result = $chat->save();

		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	//region Manage Users
	/**
	 * @restMethod im.v2.Chat.addUsers
	 */
	public function addUsersAction(\Bitrix\Im\V2\Chat $chat, array $userIds)
	{
		$chat->addUsers($userIds);

		return true;
	}

	/**
	 * @restMethod im.v2.Chat.removeUsers
	 */
	public function removeUsersAction(\Bitrix\Im\V2\Chat $chat, array $userIds): bool
	{
		$chat->removeUsers($userIds);

		return true;
	}
	//endregion

	//region Manage UI
	/**
	 * @restMethod im.v2.Chat.setTitle
	 */
	public function setTitleAction(\Bitrix\Im\V2\Chat $chat, string $title)
	{
		$chat->setTitle($title);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setDescription
	 */
	public function setDescriptionAction(\Bitrix\Im\V2\Chat $chat, string $description)
	{
		$chat->setDescription($description);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setColor
	 */
	public function setColorAction(\Bitrix\Im\V2\Chat $chat, string $color)
	{
		$result = $chat->validateColor();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		$chat->setColor($color);
		$result = $chat->save();

		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setAvatarId
	 */
	public function setAvatarIdAction(\Bitrix\Im\V2\Chat $chat, int $avatarId)
	{
		$chat->setAvatarId($avatarId);
		$result = $chat->save();

		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		$avatarFile = CFile::ResizeImageGet(
			$avatarId,
			[],
			BX_RESIZE_IMAGE_EXACT,
			false,
			false,
			true
		);
		if (!empty($avatarFile['src']))
		{
			$imageUrl = $avatarFile['src'];

			Event::add($chat->getRelations()->getUserIds(), [
				'module_id' => 'im',
				'command' => 'chatAvatar',
				'params' => [
					'chatId' => $chat->getChatId(),
					'avatar' => $imageUrl,
				],
				'extra' => Common::getPullExtra()
			]);
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setAvatar
	 */
	public function setAvatarAction(\Bitrix\Im\V2\Chat $chat, string $avatarBase64)
	{
		if (isset($avatarBase64) && $avatarBase64)
		{
			$avatar = CRestUtil::saveFile($avatarBase64);
			$imageCheck = (new \Bitrix\Main\File\Image($avatar["tmp_name"]))->getInfo();
			if(
				!$imageCheck
				|| !$imageCheck->getWidth()
				|| $imageCheck->getWidth() > 5000
				|| !$imageCheck->getHeight()
				|| $imageCheck->getHeight() > 5000
			)
			{
				$avatar = null;
			}
			if (!$avatar || mb_strpos($avatar['type'], "image/") !== 0)
			{
				$avatarId = 0;
			}
			else
			{
				$avatar['MODULE_ID'] = 'im';
				$avatarId = CFile::saveFile($avatar, 'im');
			}
		}
		else
		{
			$avatarId = 0;
		}

		$chat->setAvatarId($avatarId);
		$result = $chat->save();

		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		$avatarFile = CFile::ResizeImageGet(
			$avatarId,
			[],
			BX_RESIZE_IMAGE_EXACT,
			false,
			false,
			true
		);
		if (!empty($avatarFile['src']))
		{
			$imageUrl = $avatarFile['src'];

			Event::add($chat->getRelations()->getUserIds(), [
				'module_id' => 'im',
				'command' => 'chatAvatar',
				'params' => [
					'chatId' => $chat->getChatId(),
					'avatar' => $imageUrl,
				],
				'extra' => Common::getPullExtra()
			]);
		}

		return $avatarId;
	}
	//endregion

	//region Manage Settings
	/**
	 * @restMethod im.v2.Chat.setOwner
	 */
	public function setOwnerAction(\Bitrix\Im\V2\Chat $chat, int $ownerId)
	{
		$chat->setAuthorId($ownerId);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setManagers
	 */
	public function setManagersAction(\Bitrix\Im\V2\Chat $chat, array $userIds)
	{
		$chat->setManagers($userIds);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();
	}

	/**
	 * @restMethod im.v2.Chat.setManageUsers
	 */
	public function setManageUsersAction(\Bitrix\Im\V2\Chat $chat, string $rightsLevel)
	{
		$chat->setManageUsers($rightsLevel);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();

	}

	/**
	 * @restMethod im.v2.Chat.setManageUI
	 */
	public function setManageUIAction(\Bitrix\Im\V2\Chat $chat, string $rightsLevel)
	{
		$chat->setManageUI($rightsLevel);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();

	}

	/**
	 * @restMethod im.v2.Chat.setManageSettings
	 */
	public function setManageSettingsAction(\Bitrix\Im\V2\Chat $chat, string $rightsLevel)
	{
		$chat->setManageSettings($rightsLevel);
		$result = $chat->save();
		if (!$result->isSuccess())
		{
			return $this->convertKeysToCamelCase($result->getErrors());
		}

		return $result->isSuccess();

	}
	//endregion
	//endregion
}
