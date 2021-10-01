<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Im\Call\Call;
use Bitrix\Im\Call\Conference;
use Bitrix\Im\Chat;
use Bitrix\Intranet\Util;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Data\LocalStorage\SessionLocalStorage;
use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

class ImComponentJitsiConference extends CBitrixComponent
{
	public function executeComponent()
	{
		global $USER;
		if (!$USER->IsAuthorized())
		{
			$USER->LoginByCookies();
		}

		$jitsiServer = Option::get('im', 'jitsi_server', '');
		$this->arResult['JITSI_SERVER'] = $jitsiServer;
		$this->arResult['JITSI_EXTERNAL_API'] = "https://{$jitsiServer}/external_api.js";
		$this->arResult['ALIAS'] = $this->arParams['ALIAS'];
		$this->arResult['USER_NAME'] = \Bitrix\Im\User::getInstance()->getFullName();
		$this->arResult['USER_EMAIL'] = \Bitrix\Im\User::getInstance()->getEmail();
		$chatId = (int)$this->arParams['CHAT_ID'];
		if ($chatId > 0)
		{
			$chatData = \CIMChat::GetChatData([
				'ID' => $chatId,
				'USER_ID' => $USER->GetID()
			]);
			if ($chatData)
			{
				$chatFields = $chatData['chat'][$chatId];
				$this->arResult['CHAT_NAME'] = $chatFields['name'];
			}
		}

		$this->includeComponentTemplate();
	}

}