<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Decorator;

use Bitrix\Socialnetwork\Control\GroupService;

abstract class AbstractGroupServiceDecorator extends GroupService
{
	public function __construct(
		protected GroupService $source
	)
	{

	}
}