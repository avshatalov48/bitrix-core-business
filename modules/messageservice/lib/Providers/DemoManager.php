<?php

namespace Bitrix\MessageService\Providers;

interface DemoManager
{
	public const IS_DEMO = 'is_demo';

	public function isDemo(): bool;
	public function disableDemo(): self;
	public function enableDemo(): self;
}