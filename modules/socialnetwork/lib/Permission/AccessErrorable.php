<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Permission;

interface AccessErrorable
{
	public function getErrors(): array;
	public function addError(string $class, string $message): void;
}