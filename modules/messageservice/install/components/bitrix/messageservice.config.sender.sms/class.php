<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\MessageService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class MessageServiceConfigSenderSmsComponent extends CBitrixComponent
{
	public function executeComponent()
	{
		if (!Main\Loader::includeModule('messageservice'))
		{
			ShowError(Loc::getMessage('MESSAGESERVICE_MODULE_NOT_INSTALLED'));
			return;
		}

		if (!\Bitrix\MessageService\Context\User::isAdmin())
		{
			ShowError(Loc::getMessage('MESSAGESERVICE_PERMISSION_DENIED'));
			return;
		}

		$senderId = $this->getSenderId();

		/** @var MessageService\Sender\BaseConfigurable $sender */
		if ($senderId)
		{
			$sender = MessageService\Sender\SmsManager::getSenderById($senderId);
		}
		else
		{
			$sender = MessageService\Sender\SmsManager::getDefaultSender();
		}

		if (!$sender || !$sender->isConfigurable())
		{
			ShowError(Loc::getMessage('MESSAGESERVICE_SENDER_NOT_FOUND'));
			return;
		}

		//Sync sender remote state.
		$sender->sync();

		$this->arResult['sender'] = $sender;
		$this->IncludeComponentTemplate($sender->getConfigComponentTemplatePageName());
	}

	protected function getSenderId()
	{
		return isset($this->arParams['SENDER_ID']) ? (string) $this->arParams['SENDER_ID'] : '';
	}
}