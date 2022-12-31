<?php

namespace Bitrix\Calendar\Core\Queue\Interfaces;

interface Listener
{
	public function handle();
}