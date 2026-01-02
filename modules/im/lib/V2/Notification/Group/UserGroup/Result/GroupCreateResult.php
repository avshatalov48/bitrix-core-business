<?php

namespace Bitrix\Im\V2\Notification\Group\UserGroup\Result;

use Bitrix\Main\Result;

class GroupCreateResult extends Result
{
	public function __construct(
		public readonly ?int $id = null,
	)
	{
		parent::__construct();
	}
}