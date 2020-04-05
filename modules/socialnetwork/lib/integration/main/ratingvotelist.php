<?php
/**
* Bitrix Framework
* @package bitrix
* @subpackage socialnetwork
* @copyright 2001-2017 Bitrix
*/
namespace Bitrix\Socialnetwork\Integration\Main;

use Bitrix\Socialnetwork\Livefeed\Provider;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class RatingVoteList
{
	public static function onViewed(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'socialnetwork'
		);

		$entityTypeId = $event->getParameter('entityTypeId');
		$entityId = $event->getParameter('entityId');
		$userId = $event->getParameter('userId');

		if (
			empty($entityTypeId)
			|| intval($entityId) <= 0
			|| intval($userId) <= 0
		)
		{
			return $result;
		}

		if ($liveFeedEntity = Provider::init(array(
			'ENTITY_TYPE' => Provider::DATA_ENTITY_TYPE_RATING_LIST,
			'ENTITY_ID' => $entityTypeId.'|'.$entityId
		)))
		{
			$liveFeedEntity->setContentView(array(
				"userId" => $userId
			));
		}

		$result = new EventResult(
			EventResult::SUCCESS,
			array(),
			'socialnetwork'
		);

		return $result;
	}
}
?>