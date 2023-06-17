<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Call\Conference;
use Bitrix\Im\Color;
use Bitrix\IM\Model\AliasTable;
use Bitrix\IM\Model\ConferenceTable;
use Bitrix\IM\Model\ConferenceUserRoleTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
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

	public function add(array $params, ?Context $context = null): Result
	{
		$result = new Result();

		$paramsResult = $this->prepareParams($params);
		if ($paramsResult->isSuccess())
		{
			$params = $paramsResult->getResult();
		}
		else
		{
			return $result->addErrors($paramsResult->getErrors());
		}

		if (!$params['TITLE'])
		{
			$params['TITLE'] = $this->generateTitle();
		}

		$addResult = parent::add($params, $context);
		if (!$addResult->isSuccess() || !$addResult->hasResult())
		{
			return $addResult;
		}

		$chatResult = $addResult->getResult();
		/** @var Chat $chat */
		$chat = $chatResult['CHAT'];

		$aliasData = $params['VIDEOCONF']['ALIAS_DATA'];
		AliasTable::update($aliasData['ID'], [
			'ENTITY_ID' => $chat->getChatId()
		]);

		$conferenceData = [
			'ALIAS_ID' => $aliasData['ID']
		];

		if (isset($params['VIDEOCONF']['PASSWORD']))
		{
			$conferenceData['PASSWORD'] = $params['VIDEOCONF']['PASSWORD'];
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
				//'COMPONENT_ID' => 'VideoconfCreationMessage',
			]
		]);

		return $addResult;
	}

	protected function prepareParams(array $params = []): Result
	{
		$result = new Result();

		if (
			!isset($params['VIDEOCONF'])
			|| !isset($params['VIDEOCONF']['ALIAS_DATA'])
			|| !isset($params['VIDEOCONF']['ALIAS_DATA']['ID'])
			|| !isset($params['VIDEOCONF']['ALIAS_DATA']['LINK'])
		)
		{
			return $result->addError(new ChatError(ChatError::WRONG_PARAMETER));
		}

		if (!$params['TITLE'])
		{
			$params['TITLE'] = $this->generateTitle();
		}

		return parent::prepareParams($params);
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
}
