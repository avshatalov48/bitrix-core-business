<?php

namespace Bitrix\Im\V2\Controller\Chat;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\InputAction\Action;
use Bitrix\Im\V2\Chat\InputAction\Type;
use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Main\Engine\AutoWire\ExactParameter;

class InputAction extends BaseController
{
	public function getAutoWiredParameters()
	{
		return array_merge(parent::getAutoWiredParameters(), [
			new ExactParameter(
				Type::class,
				'actionType',
				function($className, string $type) {
					return Type::tryFrom($type) ?? Type::Writing;
				}
			),
		]);
	}

	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			Action::class,
			'action',
			function ($className, Chat $chat, Type $actionType) {
				return new Action($chat, $actionType);
			}
		);
	}

	/**
	 * @restMethod im.v2.Chat.InputAction.notify
	 *
	 * The second parameter is needed for correct rights checking. @see \Bitrix\Im\V2\Controller\Filter\CheckChatAccess
	 */
	public function notifyAction(Action $action, Chat $chat): ?array
	{
		$result = $action->notify();

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return ['result' => true];
	}
}
