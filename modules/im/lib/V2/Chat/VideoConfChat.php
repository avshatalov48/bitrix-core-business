<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Alias;
use Bitrix\Im\Call\Conference;
use Bitrix\Im\Color;
use Bitrix\Im\Model\BlockUserTable;
use Bitrix\IM\Model\ConferenceTable;
use Bitrix\IM\Model\ConferenceUserRoleTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Relation\DeleteUserConfig;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Localization\Loc;
use CGlobalCounter;
use CIMMessageParamAttach;

class VideoConfChat extends GroupChat
{
	private const MAX_CONF_NUMBER = 999;

	public function getDefaultEntityType(): ?string
	{
		return self::ENTITY_TYPE_VIDEOCONF;
	}

	protected function needToSendGreetingMessages(): bool
	{
		return true;
	}

	public function add(array $params, ?Context $context = null): Result
	{
		$addResult = parent::add($params, $context);
		if (!$addResult->isSuccess() || !$addResult->hasResult())
		{
			return $addResult;
		}

		$chatResult = $addResult->getResult();
		/** @var Chat $chat */
		$chat = $chatResult['CHAT'];


		if (
			!isset($params['VIDEOCONF']['ALIAS_DATA'])
			|| !isset($params['VIDEOCONF']['ALIAS_DATA']['ID'])
			|| !isset($params['VIDEOCONF']['ALIAS_DATA']['LINK'])
		)
		{
			$aliasData = Alias::addUnique([
				"ENTITY_TYPE" => Alias::ENTITY_TYPE_VIDEOCONF,
				"ENTITY_ID" => $chat->getChatId(),
			]);
		}
		else
		{
			$aliasData = $params['VIDEOCONF']['ALIAS_DATA'];

			Alias::update($aliasData['ID'], ['ENTITY_ID' => $chat->getChatId()]);
		}

		$conferenceData = [
			'ALIAS_ID' => $aliasData['ID']
		];

		if (isset($params['VIDEOCONF']['PASSWORD']))
		{
			$conferenceData['PASSWORD'] = $params['VIDEOCONF']['PASSWORD'];
		}
		else
		{
			$conferenceData['PASSWORD'] = $params['CONFERENCE_PASSWORD'] ?? '';
		}

		if (isset($params['VIDEOCONF']['INVITATION']))
		{
			$conferenceData['INVITATION'] = $params['VIDEOCONF']['INVITATION'];
		}

		$conferenceData['IS_BROADCAST'] = isset($params['VIDEOCONF']['IS_BROADCAST']) && $params['VIDEOCONF']['IS_BROADCAST'] === 'Y'? 'Y': 'N';

		$creationResult = ConferenceTable::add($conferenceData);
		if (isset($params['VIDEOCONF']['PRESENTERS']))
		{
			foreach ($params['VIDEOCONF']['PRESENTERS'] as $presenter)
			{
				ConferenceUserRoleTable::add([
					'CONFERENCE_ID' => $creationResult->getId(),
					'USER_ID' => $presenter,
					'ROLE' => Conference::ROLE_PRESENTER
				]);
			}
		}

		$message = ''
			. GetMessage("IM_VIDEOCONF_LINK_TITLE") . ': [URL]' . $aliasData['LINK'] . '[/URL][BR]'
		;
		$attach = new CIMMessageParamAttach(null, Color::getColor($chat->getColor()));
		$attach->SetDescription(CIMMessageParamAttach::FIRST_MESSAGE);
		$attach->AddMessage($message);

		$keyboard = new \Bitrix\Im\Bot\Keyboard();
		$keyboard->addButton(
			[
				"TEXT" => GetMessage("IM_VIDEOCONF_COPY_LINK"),
				"ACTION" => "COPY",
				"ACTION_VALUE" => $aliasData['LINK'],
				"DISPLAY" => "LINE",
				"BG_COLOR" => "#A4C31E",
				"TEXT_COLOR" => "#FFF"
			]
		);

		\CIMChat::AddMessage([
			"TO_CHAT_ID" => $chat->getChatId(),
			"FROM_USER_ID" => $chat->getAuthorId(),
			"MESSAGE" => GetMessage('IM_VIDEOCONF_CREATE_WELCOME'),
			"SYSTEM" => 'Y',
			"ATTACH" => $attach,
			"KEYBOARD" => $keyboard,
			'PARAMS' => [
				'COMPONENT_ID' => 'ConferenceCreationMessage',
			],
			'SKIP_USER_CHECK' => 'Y',
		]);

		$addResult->setResult([
			'CHAT_ID' => $chat->getChatId(),
			'CHAT' => $chat,
			'ALIAS' => $aliasData['ALIAS'],
			'LINK' => $aliasData['LINK'],
		]);

		$chat->isFilledNonCachedData = false;

		return $addResult;
	}

	protected function getCodeGreetingMessage(\Bitrix\Im\V2\Entity\User\User $author): string
	{
		return 'IM_VIDEOCONF_CREATE_' . $author->getGender();
	}

	protected function prepareParams(array $params = []): Result
	{
		if (!isset($params['TITLE']))
		{
			$params['TITLE'] = $this->generateTitle();
		}

		if (isset($params['OWNER_ID']))
		{
			$params['OWNER_ID'] = (int)$params['OWNER_ID'];
		}

		if (!isset($params['VIDEOCONF']['PASSWORD']) && isset($params['CONFERENCE_PASSWORD']))
		{
			$params['PASSWORD'] = $params['CONFERENCE_PASSWORD'];
		}

		$params['SEARCHABLE'] = 'N';

		$params['MANAGE_UI'] = $params['MANAGE_UI'] ?? $this->getDefaultManageUI();
		$params['MANAGE_SETTINGS'] = $params['MANAGE_SETTINGS'] ?? $this->getDefaultManageSettings();
		$params['MANAGE_USERS_ADD'] = $params['MANAGE_USERS_ADD'] ?? $this->getDefaultManageUsersAdd();
		$params['MANAGE_USERS_DELETE'] = $params['MANAGE_USERS_DELETE'] ?? $this->getDefaultManageUsersDelete();
		$params['MANAGE_MESSAGES'] = $params['MANAGE_MESSAGES'] ?? $this->getDefaultManageMessages();

		$params = parent::prepareParams($params);
		if (!$params->isSuccess())
		{
			return $params;
		}

		$paramData = $params->getResult();

		//todo: drag method into this class
		$confParams = Conference::prepareParamsForAdd($paramData);
		if (!$confParams->isSuccess())
		{
			return $confParams;
		}
		$confParams = $confParams->getData()['FIELDS'];

		return $params->setResult(array_merge($paramData, $confParams));
	}

	public function generateTitle(): string
	{
		CGlobalCounter::Increment('im_videoconf_count', CGlobalCounter::ALL_SITES, false);
		$videoconfCount = CGlobalCounter::GetValue('im_videoconf_count', CGlobalCounter::ALL_SITES);

		if ($videoconfCount === self::MAX_CONF_NUMBER)
		{
			CGlobalCounter::Set('im_videoconf_count', 1, CGlobalCounter::ALL_SITES, '', false);
		}

		return Loc::GetMessage('IM_VIDEOCONF_NAME_FORMAT_NEW', [
			'#NUMBER#' => $videoconfCount
		]);
	}

	public function setExtranet(?bool $extranet): \Bitrix\Im\V2\Chat
	{
		return $this;
	}

	public function getExtranet(): ?bool
	{
		return false;
	}

	protected function updateStateAfterUsersAdd(array $usersToAdd): self
	{
		parent::updateStateAfterUsersAdd($usersToAdd);

		$wasUserBlocked = BlockUserTable::query()
			->setSelect(['ID'])
			->where('CHAT_ID', $this->getId())
			->whereIn('USER_ID', $usersToAdd)
			->fetchCollection()
			->getIdList()
		;

		if (empty($wasUserBlocked))
		{
			return $this;
		}

		BlockUserTable::deleteByFilter(['=USER_ID' => $wasUserBlocked]);

		return $this;
	}

	protected function updateStateAfterUserDelete(int $deletedUserId, DeleteUserConfig $config): Chat
	{
		parent::updateStateAfterUserDelete($deletedUserId, $config);

		$externalAuthId = User::getInstance($deletedUserId)->getExternalAuthId();
		if ($externalAuthId === 'call')
		{
			BlockUserTable::add(
				[
					'CHAT_ID' => $this->getId(),
					'USER_ID' => $deletedUserId,
					'BLOCK_DATE' => new SqlExpression(Application::getConnection()->getSqlHelper()->getCurrentDateTimeFunction())
				]
			);
		}

		return $this;
	}
}
