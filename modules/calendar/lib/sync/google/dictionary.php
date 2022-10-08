<?php

namespace Bitrix\Calendar\Sync\Google;

class Dictionary extends \Bitrix\Calendar\Sync\Dictionary
{
	/** @var array  */
	public const ACCESS_ROLE_TO_EXTERNAL_TYPE = [
		'reader' => 'google_readonly',
		'owner' => 'google',
		'writer' => 'google_write_read',
		'freeBusyOrder' => 'google_freebusy',
	];

	/** @var array  */
	public const SYNC_ACTION = [
		'confirmed' => 'save',
		'cancelled' => 'delete',
		'tentative' => 'save',
	];

	public const PUSH_CHANNEL_TYPES = [
		'connection' => 'BX_CONNECTION',
		'sectionConnection' => 'BX_SC',
	];
}
