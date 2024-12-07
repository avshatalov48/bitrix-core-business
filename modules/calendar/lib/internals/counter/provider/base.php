<?php

namespace Bitrix\Calendar\Internals\Counter\Provider;

interface Base
{
	public function getValue(): int;
}