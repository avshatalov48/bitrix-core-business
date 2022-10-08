<?php

namespace Bitrix\MessageService\Providers;

interface SupportChecker
{
	public function isSupported(): bool;
	public function canUse(): bool;
}