<?php


namespace Bitrix\Im\Controller\Call;


use Bitrix\Im\Alias;
use Bitrix\Im\Dialog;
use Bitrix\Im\Text;
use Bitrix\Im\User;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Params;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Beta extends Controller
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

	public function createRoomAction(CurrentUser $currentUser, string $dialogId = "0", int $chatId = 0)
	{
		if ($dialogId > 0)
		{
			$chatId = Dialog::getChatId($dialogId, $currentUser->getId());
		}
		if ($chatId <= 0)
		{
			$this->addError(new Error("You must be specified chatId or dialogId"));
			return null;
		}

		$chat = Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			$this->addError(new Error("You must be a member of the chat to call it"));

			return null;
		}

		$roomId = $chatId.'_'. Alias::generateUnique();
		$link = 'https://demo-stage.webrtc-test.bitrix.info/?roomId='.$roomId;

		$text = Loc::getMessage("IM_CALL_BETA_INVITE", [
			'#LINK#' => '[URL='.$link.']'.Loc::getMessage('IM_CALL_BETA_INVITE_BUTTON').'[/URL]',
		]);

		$message = new Message();
		$message->setMessage($text)->markAsImportant(true);
		$message->getParams()
			->fill([
				Params::COMPONENT_ID => 'CallInviteMessage',
				Params::COMPONENT_PARAMS => [
					'LINK' => $link
				]
			])
			->save()
		;
		$chat->sendMessage($message);

		return true;
	}
}