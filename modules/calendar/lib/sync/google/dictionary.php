<?php


namespace Bitrix\Calendar\Sync\Google;


class Dictionary
{
	public const ACCESS_ROLE_TO_EXTERNAL_TYPE = [
		'reader' => 'google_readonly',
		'owner' => 'google',
		'writer' => 'google_write_read',
		'freeBusyOrder' => 'google_freebusy',
	];
}