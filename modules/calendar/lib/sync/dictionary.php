<?php


namespace Bitrix\Calendar\Sync;

class Dictionary
{
	public const RECURRENCE_SYNC_MODE = [
		'exception' => 0b100,
		'oldMaster' => 0b011,
		'newMaster' => 0b010,
		'deleteInstance' => 0b001,
		'single' => 0b000,
	];
	public const SYNC_STATUS = [
		'success'   => 'success',
		'failed'    => 'failed',
		'delete'    => 'delete',
		'create'    => 'create',
		'recreate' => 'recreate',
		'update'    => 'update',
		'next'      => 'next',
		'parent'    => 'parent',
		'instance'  => 'instance',
		'undefined' => 'undefined',
		'waiting'   => 'waiting',
		'deleted'   => 'deleted',
		'exdated'   => 'exdated',
		'inactive'  => 'inactive',
	];

	public const PUSH_STATUS_PROCESS = [
		'block'       => 'B',
		'unprocessed' => 'U',
		'unblocked' => 'N',
		'process' => 'Y',
	];

	public const SYNC_SECTION_ACTION = [
		'create' => 'create',
		'update' => 'update',
		'delete' => 'delete',
		'success' => 'success',
	];

	public const SYNC_EVENT_ACTION = [
		'create' => 'create',
		'recreate' => 'recreate',
		'update' => 'update',
		'delete' => 'delete',
		'success' => 'success',
	];

	public const FIRST_SYNC_FLAG_NAME = 'IsFirstSynchronization';
}
