<?php

namespace Bitrix\Calendar\Sync\Managers;


interface ServiceInterface
{
	/**
	 * @return string
	 */
	public function getServiceName(): string;
}
