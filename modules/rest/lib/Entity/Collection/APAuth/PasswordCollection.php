<?php

declare(strict_types=1);

namespace Bitrix\Rest\Entity\Collection\APAuth;

use Bitrix\Rest\Entity\APAuth\Password;
use Bitrix\Rest\Entity\Collection\BaseCollection;

/**
 * @template-extends BaseCollection<Password>
 */
class PasswordCollection extends BaseCollection
{
	/**
	 * @inheritDoc
	 */
	protected static function getItemClassName(): string
	{
		return Password::class;
	}
}
