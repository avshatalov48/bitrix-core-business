<?php
namespace Bitrix\Landing\Controller\ActionFilter;

use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class Extranet extends \Bitrix\Main\Engine\ActionFilter\Base
{
	/**
	 * Check that current site is not extranet before action.
	 * @param Event $event Event instance.
	 * @return EventResult|null
	 */
	public function onBeforeAction(Event $event): ?EventResult
	{
		if (\Bitrix\Main\Loader::includeModule('extranet'))
		{
			$isIntranet = \CExtranet::isIntranetUser(
				\CExtranet::getExtranetSiteID(),
				\Bitrix\Landing\Manager::getUserId()
			);
			if (!$isIntranet)
			{
				$this->addError(new Error('Extranet site is not allowed.', 'EXTRANET_IS_NOT_ALLOWED'));
				return new EventResult(EventResult::ERROR, null, null, $this);
			}
		}

		return null;
	}
}