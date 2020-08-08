<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Mail\Integration\Main\UISelector;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class Handler
{
	const ENTITY_TYPE_MAILCONTACTS = 'MAILCONTACTS';

	public static function OnUISelectorGetProviderByEntityType(Event $event)
	{
		$result = new EventResult(EventResult::UNDEFINED, null, 'mail');

		$entityType = $event->getParameter('entityType');

		switch($entityType)
		{
			case self::ENTITY_TYPE_MAILCONTACTS:
				$provider = new \Bitrix\Mail\Integration\Main\UISelector\MailContacts;
				break;
			default:
				$provider = false;
		}

		if ($provider)
		{
			$result = new EventResult(
				EventResult::SUCCESS,
				array(
					'result' => $provider
				),
				'mail'
			);
		}

		return $result;
	}

	public static function OnUISelectorFillLastDestination(Event $event)
	{
		$result = new EventResult(EventResult::UNDEFINED, null, 'mail');

		$params = $event->getParameter('params');
		$destSortData = $event->getParameter('destSortData');

		$lastDestinationList = [];

		$mailContactCounter = 0;

		if (is_array($destSortData))
		{
			$mailContactLimit = 10;

			foreach($destSortData as $code => $sortInfo)
			{
				if($mailContactCounter >= $mailContactLimit)
				{
					break;
				}

				if(preg_match('/^'.MailContacts::PREFIX.'(\d+)$/i', $code, $matches))
				{
					if($mailContactCounter >= $mailContactLimit)
					{
						continue;
					}
					if(!isset($lastDestinationList[self::ENTITY_TYPE_MAILCONTACTS]))
					{
						$lastDestinationList[self::ENTITY_TYPE_MAILCONTACTS] = [];
					}
					$lastDestinationList[self::ENTITY_TYPE_MAILCONTACTS][$code] = $code;
					$mailContactCounter++;
				}
			}

			$result = new EventResult(
				EventResult::SUCCESS,
				[
					'lastDestinationList' => $lastDestinationList
				],
				'mail'
			);
		}

		return $result;
	}
}
