<?php

namespace Bitrix\MessageService\Providers;

interface StatusResolver
{
	public function resolveStatus(string $serviceStatus): ?int;
}