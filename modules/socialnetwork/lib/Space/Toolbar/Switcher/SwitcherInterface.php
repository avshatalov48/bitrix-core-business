<?php

namespace Bitrix\Socialnetwork\Space\Toolbar\Switcher;

use Bitrix\Main\Result;

interface SwitcherInterface
{
	public function enable(): Result;
	public function disable(): Result;
	public function switch(): Result;
	public function isEnabled(): bool;
}