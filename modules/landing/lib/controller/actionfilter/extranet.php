<?php
namespace Bitrix\Landing\Controller\ActionFilter;

use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class Extranet extends \Bitrix\Main\Engine\ActionFilter\Base
{
	/**
	 * Check that current user is intranet before action.
	 * @param Event $event Event instance.
	 * @return EventResult|null
	 */
	public function onBeforeAction(Event $event): ?EventResult
	{
		$user = \CUser::getList(
			'ID', 'ASC',
			['ID_EQUAL_EXACT' => \Bitrix\Landing\Manager::getUserId()],
			['FIELDS' => 'ID', 'SELECT' => ['UF_DEPARTMENT']]
		)->fetch();

		$isIntranet = ($user['UF_DEPARTMENT'][0] ?? 0) > 0;
		if (!$isIntranet)
		{
			$this->addError(new Error('Extranet site is not allowed.', 'EXTRANET_IS_NOT_ALLOWED'));
			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}
}