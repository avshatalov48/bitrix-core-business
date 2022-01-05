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

	public const SYNC_STATUS = [
		'success' => 'success',
		'failed' => 'failed',
		'delete' => 'delete',
		'create' => 'create',
		'update' => 'update',
		'next' => 'next',
		'parent' => 'parent',
		'instance' => 'instance',
		'undefined' => 'undefined',
		'waiting' => 'waiting',
		'deleted' => 'deleted',
		'exdated' => 'exdated',
	];

	public const PUSH_STATUS_PROCESS = [
		'block' => 'B',
		'unprocessed' => 'U',
	];

	public const PUSH_TYPE = [
		'c' => 'CONNECTION',
		's' => 'SECTION',
	];
}