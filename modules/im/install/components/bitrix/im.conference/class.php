<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Context;
use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc;

class ImComponentConference extends CBitrixComponent
{
	private $chatId = 0;
	private $userId = 0;
	private $userCount = 0;
	private $userLimit = 0;
	private $startupErrorCode = '';
	private $isIntranetOrExtranet = false;
	private $language = 'en';

	protected function checkModules()
	{
		if (!Loader::includeModule('im'))
		{
			\ShowError(Loc::getMessage('IM_COMPONENT_MODULE_IM_NOT_INSTALLED'));
			return false;
		}
		return true;
	}

	protected function addUserToChat()
	{
		global $USER;

		if ($this->userCount + 1 > $this->userLimit)
		{
			$this->startupErrorCode = \Bitrix\Im\Call\Conference::ERROR_USER_LIMIT_REACHED;
		}
		else
		{
			$chat = new \CIMChat(0);
			$chat->AddUser($this->chatId, $USER->GetID());
			$this->userCount++;
		}
	}

	public function executeComponent()
	{
		global $USER;

		$this->includeComponentLang('class.php');

		if (!$this->checkModules())
		{
			return false;
		}

		$this->chatId = (int)$this->arParams['CHAT_ID'];
		if (!$this->chatId)
		{
			return false;
		}

		\Bitrix\Main\UI\Extension::load("im.application.call");

		$this->arResult['ALIAS'] = $this->arParams['ALIAS'];
		$this->arResult['CHAT_ID'] = $this->chatId;
		$this->arResult['SITE_ID'] = Context::getCurrent()->getSite();

		$this->language = \Bitrix\Main\Localization\Loc::getCurrentLang();
		$this->userCount = CIMChat::getUserCount($this->chatId);

		if (\Bitrix\Im\Call\Call::isCallServerEnabled())
		{
			$this->userLimit = \Bitrix\Im\Call\Call::getMaxCallServerParticipants();
		}
		else
		{
			$this->userLimit = \Bitrix\Main\Config\Option::get('im', 'turn_server_max_users');
		}

		if (!\Bitrix\Main\Loader::includeModule('intranet'))
		{
			$this->startupErrorCode = \Bitrix\Im\Call\Conference::ERROR_BITRIX24_ONLY;
		}
		else
		{
			if ($USER->IsAuthorized())
			{
				$wasKickedFromChat = \Bitrix\Im\Chat::isUserKickedFromChat($this->chatId);
				if ($wasKickedFromChat)
				{
					$this->startupErrorCode = \Bitrix\Im\Call\Conference::ERROR_KICKED_FROM_CALL;
				}
				else
				{
					if (\Bitrix\Intranet\Util::isIntranetUser() || \Bitrix\Intranet\Util::isExtranetUser())
					{
						$this->userId = $USER->GetID();
						$this->isIntranetOrExtranet = true;

						$isUserInChat = \Bitrix\Im\Chat::isUserInChat($this->chatId);
						if (!$isUserInChat)
						{
							$this->addUserToChat();
						}
					}
					else if (!\Bitrix\Intranet\Util::isIntranetUser() &&
						!\Bitrix\Intranet\Util::isExtranetUser() &&
						$USER->GetParam('EXTERNAL_AUTH_ID') !== 'call')
					{
						$USER->Logout();
					}
					else
					{
						//it is authorized guest
						$this->userId = $USER->GetID();

						$isUserInChat = \Bitrix\Im\Chat::isUserInChat($this->chatId);
						if (!$isUserInChat)
						{
							$this->addUserToChat();
						}
					}
				}
			}
			else
			{
				$guest_cookie = isset($_COOKIE['VIDEOCONF_GUEST']);
				$cookie_prefix = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM');
				$cookie_login = (string)$_COOKIE[$cookie_prefix.'_UIDL'];
				if ($cookie_login === '')
				{
					$cookie_login = (string)$_COOKIE[$cookie_prefix.'_LOGIN'];
				}
				if (!$guest_cookie && $cookie_login !== '' && mb_strpos($cookie_login, 'im_call') !== 0)
				{
					$USER->LoginByCookies();

					if ($USER->GetID() <= 0)
					{
						$this->startupErrorCode = \Bitrix\Im\Call\Conference::ERROR_DETECT_INTRANET_USER;
					}
					else
					{
						if (\Bitrix\Intranet\Util::isIntranetUser() || \Bitrix\Intranet\Util::isExtranetUser())
						{
							$this->isIntranetOrExtranet = true;
						}

						if (!\Bitrix\Intranet\Util::isIntranetUser() &&
							!\Bitrix\Intranet\Util::isExtranetUser() &&
							$USER->GetParam('EXTERNAL_AUTH_ID') !== 'call')
						{
							$USER->Logout();
						}
						else
						{
							$this->userId = $USER->GetID();
						}
					}
				}
				else
				{
					//it is new user
					if ($this->userCount + 1 > $this->userLimit)
					{
						$this->startupErrorCode = \Bitrix\Im\Call\Conference::ERROR_USER_LIMIT_REACHED;
					}
				}
			}
		}

		$this->arResult['USER_ID'] = $this->userId;
		$this->arResult['USER_COUNT'] = $this->userCount;
		$this->arResult['STARTUP_ERROR_CODE'] = $this->startupErrorCode;
		$this->arResult['IS_INTRANET_OR_EXTRANET'] = $this->isIntranetOrExtranet;
		$this->arResult['LANGUAGE'] = $this->language;


		$chatName = Loc::getMessage('IM_COMPONENT_DEFAULT_OG_TITLE');
		$chatData = \Bitrix\Im\Chat::getById($this->chatId);
		if ($chatData && $chatData['NAME'])
		{
			$chatName = $chatData['NAME'];
		}

		\Bitrix\Main\Page\Asset::getInstance()->addString(
			'<meta name="viewport" content="width=device-width, initial-scale=0.6"/>'
		);
		\Bitrix\Main\Page\Asset::getInstance()->addString(
			'<meta property="og:title" content="' . htmlspecialcharsbx($chatName) . '" />'
		);
		\Bitrix\Main\Page\Asset::getInstance()->addString(
			'<meta property="og:description" content="' . Loc::getMessage('IM_COMPONENT_OG_DESCRIPTION') . '" />'
		);

		$imagePath = $this->getPath() . '/images/og_image.jpg';
		\Bitrix\Main\Page\Asset::getInstance()->addString(
			'<meta property="og:image" content="' . $imagePath . '" />'
		);

		\Bitrix\Main\Page\Asset::getInstance()->addString(
			'<meta name="robots" content="noindex, nofollow" />'
		);

		$this->includeComponentTemplate();

		return true;
	}
};