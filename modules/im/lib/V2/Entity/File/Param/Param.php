<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Entity\File\Param;

interface Param
{
	public function getFileId(): int;
	public function getValue(): string|int|bool;
	public function toArray(): array;
}
