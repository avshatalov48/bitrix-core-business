<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Integration\Im;

use Bitrix\Im\V2\Chat;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\Item\Workgroup\Type;
use CSite;

class ChatFactory
{
	private const CHAT_ENTITY_TYPE = 'SONET_GROUP';

	private Workgroup $group;

	public static function createChat(Workgroup $group): Result
	{
		$result = new Result();

		if (!Loader::includeModule('im'))
		{
			$result->addError(new Error('IM is not installed'));

			return $result;
		}

		$factory = new static($group);

		$fields = match ($factory->group->getType())
		{
			Type::Project, Type::Scrum => $factory->getProjectChatFields(),
			Type::Collab => $factory->getCollabChatFields(),
			default => $factory->getGroupChatFields(),
		};

		$chatResult = Chat\ChatFactory::getInstance()->addChat($fields);

		$result->addErrors($chatResult->getErrors());

		return $result;
	}

	public static function getChatTitle(string $groupTitle, ?Type $groupType): string
	{
		return match ($groupType)
		{
			Type::Project, Type::Scrum => Loc::getMessage('SOCIALNETWORK_WORKGROUP_CHAT_FACTORY_TITLE_PROJECT', [
				'#GROUP_NAME#' => $groupTitle,
			], static::getLanguageId()),

			Type::Collab => $groupTitle,

			default => Loc::getMessage('SOCIALNETWORK_WORKGROUP_CHAT_FACTORY_TITLE', [
				'#GROUP_NAME#' => $groupTitle,
			], static::getLanguageId()),
		};
	}

	private function __construct(Workgroup $group)
	{
		$this->group = $group;
	}

	private static function getLanguageId(): string
	{
		$currentSite = CSite::getById(SITE_ID)->fetch();

		return (string)($currentSite ? $currentSite['LANGUAGE_ID'] : LANGUAGE_ID);
	}

	private function getCollabChatFields(): array
	{
		$fields = $this->getCommonFields();

		$fields['TYPE'] = Chat::IM_TYPE_COLLAB;
		$fields['SKIP_ADD_MESSAGE'] = 'N';
		$fields['MANAGE_USERS_DELETE'] = Chat::MANAGE_RIGHTS_OWNER;
		$fields['MANAGE_UI'] = Chat::MANAGE_RIGHTS_OWNER;

		return $fields;
	}

	private function getGroupChatFields(): array
	{
		$fields = $this->getCommonFields();

		$fields['TYPE'] = IM_MESSAGE_CHAT;

		return $fields;
	}

	private function getProjectChatFields(): array
	{
		$fields = $this->getCommonFields();

		$fields['TYPE'] = IM_MESSAGE_CHAT;

		return $fields;
	}

	private function getCommonFields(): array
	{
		$fields = [
			'TITLE' => $fields['TITLE'] = static::getChatTitle($this->group->getName(), $this->group->getType()),
			'DESCRIPTION' => $this->group->getDescription(),
			'ENTITY_TYPE' => self::CHAT_ENTITY_TYPE,
			'ENTITY_ID' => $this->group->getId(),
			'SKIP_ADD_MESSAGE' => 'Y',
			'AUTHOR_ID' => $this->group->getOwnerId(),
			'USERS' => $this->getMembers(),
			'USER_ID' => 0,
		];

		if ($this->group->getImageId() > 0)
		{
			$fields['AVATAR_ID'] = $this->group->getImageId();
			$fields['AVATAR'] = $this->group->getImageId();
		}

		return $fields;
	}

	private function getMembers(): array
	{
		$members = $this->group->getUserMemberIds();
		if (empty($members))
		{
			$members[] = $this->group->getOwnerId();
		}

		return $members;
	}
}