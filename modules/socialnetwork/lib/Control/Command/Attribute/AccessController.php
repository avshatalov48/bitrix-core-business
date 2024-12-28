<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command\Attribute;

use Attribute;
use Bitrix\Main\ArgumentException;
use Bitrix\Socialnetwork\Permission\AbstractAccessController;

#[Attribute(Attribute::TARGET_CLASS)]
class AccessController
{
	/**
	 * @throws ArgumentException
	 */
	public function __construct(
		public readonly string $class
	)
	{
		if (!is_subclass_of($class, AbstractAccessController::class))
		{
			throw new ArgumentException('Controller class must be subclass of '. AbstractAccessController::class);
		}
	}
}