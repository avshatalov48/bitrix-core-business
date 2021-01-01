<?php

namespace Bitrix\Im\Controller\Call\Filter;

use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\EventResult;

class Authorization extends Base
{
	private const AUTH_TYPE = 'call';

	public function onBeforeAction(Event $event)
	{
		$authCode = Context::getCurrent()->getRequest()->getHeader('call-auth-id');
		if (!$authCode)
		{
			return null;
		}

		if (!preg_match("/^[a-fA-F0-9]{32}$/i", $authCode))
		{
			$this->addError(new Error('Call: user auth failed [code is not correct]'));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		$xmlId = self::AUTH_TYPE."|".$authCode;

		global $USER;
		if ($USER->IsAuthorized())
		{
			if ($USER->GetParam('EXTERNAL_AUTH_ID') === 'call')
			{
				if ($USER->GetParam('XML_ID') === $xmlId)
				{
					\CUser::SetLastActivityDate($USER->GetID(), true);

					return null;
				}

				$this->addError(new Error('Call: you are authorized with a different user [2]'));

				return new EventResult(EventResult::ERROR, null, null, $this);
			}
			$this->addError(new Error('Call: you are authorized with a portal user [2]'));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		$userData = \Bitrix\Main\UserTable::getList([
			'select' => ['ID', 'EXTERNAL_AUTH_ID'],
			'filter' => ['=XML_ID' => $xmlId]
		])->fetch();

		if ($userData && $userData['EXTERNAL_AUTH_ID'] === 'call')
		{
			\Bitrix\Im\Call\Auth::authorizeById($userData['ID']);
			\CUser::SetLastActivityDate($USER->GetID(), true);

			return null;
		}

		return null;
	}
}