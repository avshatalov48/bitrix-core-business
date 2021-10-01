<?php


namespace Bitrix\Im\Controller;


use Bitrix\Im\Alias;
use Bitrix\Im\Dialog;
use Bitrix\Im\User;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class JitsiLite extends Controller
{
	protected function processBeforeAction(Action $action): bool
	{
		if (!Loader::includeModule('im'))
		{
			$this->addError(new Error("Module IM is not installed"));

			return false;
		}

		if (User::getInstance()->isExtranet())
		{
			$this->addError(new Error("You dont have access to this action"));

			return false;
		}

		return true;
	}

	public function createConferenceAction(string $dialogId, CurrentUser $currentUser)
	{
		if (!\Bitrix\Im\Dialog::hasAccess($dialogId, $currentUser->getId()))
		{
			$this->addError(new Error("You must be a member of the chat to call it"));

			return null;
		}

		$chatId = Dialog::getChatId($dialogId);

		$aliasData = Alias::addUnique([
			"ENTITY_TYPE" => Alias::ENTITY_TYPE_JITSICONF,
			"ENTITY_ID" => $chatId
		]);

		$message = Loc::getMessage("IM_JITSI_LITE_INVITE_TO_CONF");

		$keyboard = new \Bitrix\Im\Bot\Keyboard();
		$keyboard->addButton([
			"TEXT" => Loc::getMessage("IM_JITSI_LITE_OPEN_CONF"),
			"FUNCTION" => "if ('BXIM' in window) {BXIM.openVideoconf('{$aliasData['ALIAS']}')} else {ChatUtils.openVideoconf('{$aliasData['ALIAS']}')};",
			"BG_COLOR" => "#29619b",
			"TEXT_COLOR" => "#fff",
			"DISPLAY" => "LINE",
		]);

		\CIMChat::AddMessage([
			"FROM_USER_ID" => $currentUser->getId(),
			"TO_CHAT_ID" => $chatId,
			"MESSAGE" => $message,
			"SYSTEM" => 'Y',
			"KEYBOARD" => $keyboard,
		]);

		return [
			'ALIAS_DATA' => $aliasData
		];
	}
}