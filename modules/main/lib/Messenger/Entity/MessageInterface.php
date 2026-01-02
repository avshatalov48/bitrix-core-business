<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Entity;

/**
 * @internal
 */
interface MessageInterface extends \JsonSerializable
{
	public static function createFromData(array $data): MessageInterface;
}
