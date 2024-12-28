<?php

declare(strict_types=1);


namespace Bitrix\Socialnetwork\Internals\Registry\Event;

use Bitrix\Main\Event;
use Bitrix\Socialnetwork\Item\Workgroup;

class GroupLoadedEvent extends Event
{
	public function __construct(Workgroup $group)
	{
		$parameters = [
			'group' => $group,
		];

		parent::__construct('socialnetwork', 'OnGroupLoaded', $parameters);
	}

	public function getGroup(): Workgroup
	{
		return $this->parameters['group'];
	}
}