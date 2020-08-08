<?php

namespace Bitrix\Im\Call\Integration;

use Bitrix\Im\Common,
	Bitrix\Im\Dialog;

use Bitrix\Main\Loader,
	Bitrix\Main\Result,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\LoaderException,
	Bitrix\Main\SystemException,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\ArgumentException,
	Bitrix\Main\ObjectPropertyException,
	Bitrix\Socialservices\ZoomMeetingTable;

class Zoom
{
	private const CONFERENCE_INSTANT_TYPE = 1;
	private const CONFERENCE_SCHEDULED_TYPE = 2;
	private const START_DATE_FORMAT = "Y-m-d\TH:i:s\Z";
	private const DEFAULT_DURATION_MINUTES = "30";

	private const PERSONAL_CHAT = 'dialog';
	private const GROUP_CHAT = 'chat';

	private $accessToken;
	private $userId;
	private $chatId;
	private $chatType;
	private $zoomChatName;
	private $zoomSocServ;

	/**
	 * Zoom constructor.
	 *
	 * @param int $userId
	 * @param string $chatId
	 * @throws LoaderException
	 */
	public function __construct(int $userId, string $chatId)
	{
		global $USER;

		if($userId === null)
		{
			if(is_object($USER) && $USER->IsAuthorized())
			{
				$this->userId = $USER->GetID();
			}
		}
		else
		{
			$this->userId = $userId;
		}

		if (\Bitrix\Im\Common::isChatId($chatId))
		{
			$this->chatType = self::GROUP_CHAT;
			$this->chatId = \Bitrix\Im\Dialog::getChatId($chatId);
		}
		else
		{
			$this->chatType = self::PERSONAL_CHAT;
			$this->chatId = $chatId;
		}

		$this->zoomChatName = $this->prepareZoomChatName($chatId);

		$accessToken = $this->getAccessToken();
		if ($accessToken)
		{
			$this->accessToken = $accessToken;
		}
	}

	/**
	 * Checks if zoom is active social service.
	 *
	 * @return bool
	 * @throws LoaderException
	 */
	public static function isActive(): bool
	{
		if (Loader::includeModule('socialservices'))
		{
			$authManager = new \CSocServAuthManager();
			$activeSocServ = $authManager->GetActiveAuthServices(array());
			if (isset($activeSocServ['zoom']))
			{
				return true;
			}
			return false;
		}

		return false;
	}

	/**
	 * Checks if zoom is connected to user profile.
	 *
	 * @param $userId
	 * @return bool
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function isConnected($userId): bool
	{
		if (!Loader::includeModule('socialservices'))
		{
			return false;
		}

		if (\CZoomInterface::isConnected($userId))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks if Zoom integration is available for this portal.
	 *
	 * @return bool
	 * @throws LoaderException
	 */
	public static function isAvailable(): bool
	{
		if (!Loader::includeModule('bitrix24'))
		{
			return true;
		}

		return \CBitrix24::IsLicensePaid() || \CBitrix24::IsNfrLicense() || \CBitrix24::IsDemoLicense();
	}

	/**
	 * Gets zoom access token.
	 *
	 * @return bool|mixed
	 * @throws LoaderException
	 */
	public function getAccessToken()
	{
		if (!Loader::includeModule('socialservices'))
		{
			return false;
		}

		$this->zoomSocServ = new \CSocServZoom($this->userId);
		$this->zoomSocServ->getUrl('');
		$accessToken = $this->zoomSocServ->getStorageToken();

		if (!empty($accessToken))
		{
			return $accessToken;
		}

		return false;
	}

	/**
	 * Gets URL to join Zoom conference by making request to Zoom, or getting it from DB (if it is not expired).
	 *
	 * @return string|null
	 */
	public function getImChatConferenceUrl(): ?string
	{
		$confUrl = null;
		$existedConf = $this->getExistedChatConference();

		if (!is_array($existedConf) || (!empty($this->accessToken) && $this->isConferenceExpired($existedConf)))
		{
			$newConf = $this->requestNewChatConference();
			if (is_array($newConf))
			{
				$confUrl = $newConf['join_url'];
			}
		}
		elseif (is_array($existedConf))
		{
			$confUrl = $existedConf['CONFERENCE_URL'];
		}

		return $confUrl;
	}

	private function getExistedChatConference(): ?array
	{
		if (Loader::includeModule('socialservices'))
		{
			$conf = ZoomMeetingTable::getList(array(
				'filter' => array(
					'=ENTITY_ID' => $this->chatId,
					'=ENTITY_TYPE_ID' => $this->chatType,
				),
				'select' => ['CONFERENCE_URL', 'CONFERENCE_EXTERNAL_ID'],
				'limit' => 1
			))->fetch();

			if (is_array($conf))
			{
				return $conf;
			}
		}

		return null;
	}

	private function requestNewChatConference()
	{
		$conference = null;

		$startTime = (new DateTime())
			->setTimeZone(new \DateTimeZone('UTC'))
			->add('1 MINUTE')
			->format(self::START_DATE_FORMAT);

		$randomSequence = new \Bitrix\Main\Type\RandomSequence($this->zoomChatName.$startTime);
		$password = $randomSequence->randString(10);

		$requestParams = [
			'topic' => $this->zoomChatName,
			'type' => self::CONFERENCE_SCHEDULED_TYPE,
			'start_time' => $startTime,
			'duration' => self::DEFAULT_DURATION_MINUTES,
			'password' => $password,
			'settings' => [
				'waiting_room' => 'false',
				'participant_video' => 'true',
				'host_video' => 'true',
				'join_before_host' => 'true',
				'approval_type' => "2",
			],
		];

		if ($this->zoomSocServ instanceof \CSocServZoom)
		{
			$conference = $this->zoomSocServ->createConference($requestParams);
			$conference['join_url'] = $this->attachPasswordToUrl($conference['join_url'], $conference['encrypted_password']);

			if (is_array($conference))
			{
				$result = ZoomMeetingTable::add([
					'ENTITY_TYPE_ID' => $this->chatType,
					'CONFERENCE_EXTERNAL_ID' => $conference['id'],
					'CONFERENCE_URL' => $conference['join_url'],
					'CONFERENCE_PASSWORD' => $conference['encrypted_password'],
					'ENTITY_ID' => $this->chatId,
					'CONFERENCE_CREATED' => (new DateTime()),
				]);

				if (!$result->isSuccess())
				{
					return false;
				}
			}
		}

		return $conference;
	}

	/**
	 * Gets Zoom conference information.
	 *
	 * @param int $confId External conference id.
	 * @return array|null
	 */
	public function requestConferenceById(int $confId): ?array
	{
		$conference = null;
		if ($this->zoomSocServ instanceof \CSocServZoom)
		{
			$conference = $this->zoomSocServ->getConferenceById($confId);
		}

		return $conference;
	}

	private function isConferenceExpired(array $confData): bool
	{
		$result = new Result();
		$confId = $confData['CONFERENCE_EXTERNAL_ID'];
		$conference = $this->requestConferenceById($confId);

		if (is_array($conference))
		{
			return false;
		}

		$orm = ZoomMeetingTable::getList([
			'filter' => [
				'=CONFERENCE_EXTERNAL_ID' => $confId,
				'=ENTITY_TYPE_ID' => $this->chatType,
			],
			'select' => ['ID'],
			'limit' => 1
		]);

		if ($conf = $orm->fetch())
		{
			$deleteResult = ZoomMeetingTable::delete($conf['ID']);
			if (!$deleteResult->isSuccess())
			{
				$result->addErrors($deleteResult->getErrors());
			}
		}

		return true;
	}

	/**
	 * Gets array of a message fields for IM to post a rich message with conference URL.
	 *
	 * @param string $chat Chat id.
	 * @param string $link URL to Zoom conference.
	 * @param integer $userId User Id who sends the message (private chat).
	 * @return array
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getRichMessageFields($chat, string $link, int $userId): array
	{
		$chatId = Dialog::getChatId($chat);
		$attach = new \CIMMessageParamAttach(null, \CIMMessageParamAttach::CHAT);

		$messageFields = [
			'SYSTEM' => 'Y',
			'URL_PREVIEW' => 'N',
			'MESSAGE' => '[B]'.Loc::getMessage('IM_ZOOM_MESSAGE_CONFERENCE_CREATED').'[/B]',
		];

		//chat
		if (Common::isChatId($chat))
		{
			$chatData = \Bitrix\Im\Chat::getById($chatId);
			$richChatData = [
				'NAME' => $chatData['NAME'],
				'CHAT_ID' => $chatId
			];
			$attach->AddChat($richChatData);
			$attach->AddDelimiter(['SIZE' => 300, 'COLOR' => '#c6c6c6']);

			$messageFields['FROM_USER_ID'] = 0;
			$messageFields['DIALOG_ID'] = $chat;
			$messageFields['MESSAGE_TYPE'] = IM_MESSAGE_CHAT;
		}
		else //dialog
		{
			$messageFields['FROM_USER_ID'] = $userId;
			$messageFields['TO_USER_ID'] = $chat;
			$messageFields['TO_CHAT_ID'] = $chatId;
			$messageFields['MESSAGE_TYPE'] = IM_MESSAGE_PRIVATE;
		}

		$attach->AddMessage(Loc::getMessage('IM_ZOOM_MESSAGE_JOIN_LINK'));
		$attach->AddLink(['LINK' => $link]);
		$messageFields['ATTACH'] = $attach;

		return $messageFields;
	}

	private function prepareZoomChatName($chatId): string
	{
		//chat
		if (\Bitrix\Im\Common::isChatId($chatId))
		{
			$chatInfo = \Bitrix\Im\Chat::getById($this->chatId);
			$zoomChatName = "Bitrix24: " . $chatInfo['NAME'];
		}
		else //dialog
		{
			$chatUsers = \Bitrix\Im\Chat::getUsers(Dialog::getChatId($chatId));
			foreach ($chatUsers as $chatUser)
			{
				$usersLastNames[] = $chatUser["last_name"];
			}

			if (isset($usersLastNames))
			{
				$usersLastNames = implode(" <-> ", $usersLastNames);
				$zoomChatName = "Bitrix24: ".$usersLastNames;
			}
			else
			{
				$zoomChatName = "Bitrix24";
			}
		}

		return $zoomChatName;
	}

	private function attachPasswordToUrl(string $conferenceUrl, string $password): string
	{
		$url = new \Bitrix\Main\Web\Uri($conferenceUrl);
		$queryParams = $url->getQuery();
		$parsedParams = [];
		parse_str($queryParams, $parsedParams);
		if (!isset($parsedParams['pwd']))
		{
			$url->addParams(['pwd' => $password]);
			$conferenceUrl = $url->getUri();
		}

		return $conferenceUrl;
	}
}