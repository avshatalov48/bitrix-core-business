<?php

namespace Bitrix\Catalog\Component\Preset;

interface Preset
{
	public function enable();
	public function disable();
	public function isOn(): bool;
}