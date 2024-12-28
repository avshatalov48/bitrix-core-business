<?php

declare(strict_types=1);


namespace Bitrix\Socialnetwork\Collab\Controller\Filter;

use Bitrix\Intranet\ActionFilter\IntranetUser;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;

class IntranetUserFilter extends Base
{
	public function onBeforeAction(Event $event): ?EventResult
	{
		if (!Loader::includeModule('intranet'))
		{
			$this->addError(new Error('Intranet module is not installed', 'intranet_required'));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		$filter = new IntranetUser();

		$eventResult = $filter->onBeforeAction($event);

		$errors = $filter->getErrors();
		foreach ($errors as $error)
		{
			$this->addError($error);
		}

		return $eventResult;
	}
}