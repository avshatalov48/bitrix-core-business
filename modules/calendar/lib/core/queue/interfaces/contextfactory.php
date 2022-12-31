<?php

namespace Bitrix\Calendar\Core\Queue\Interfaces;

interface ContextFactory
{
	public function createContext(): Context;
}