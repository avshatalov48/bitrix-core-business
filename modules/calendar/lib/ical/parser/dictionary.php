<?php


namespace Bitrix\Calendar\ICal\Parser;


class Dictionary
{
	public const ATTENDEES_STATUS = [
		'NEEDS-ACTION' => 'Q',
		'ACCEPTED' => 'Y',
		'DECLINED' => 'N',
		'TENTATIVE' => 'Y',
		'DELEGATED' => 'Y',
		'iana-token' => 'Q',
	];

	public const METHOD = [
		'request' => 'REQUEST',
		'reply' => 'REPLY',
		'cancel' => 'CANCEL',
	];

	public const RRULE_FREQUENCY = [
		'daily' => 'DAILY',
		'weekly' => 'WEEKLY',
		'monthly' => 'MONTHLY',
		'yearly' => "YEARLY"
	];
}