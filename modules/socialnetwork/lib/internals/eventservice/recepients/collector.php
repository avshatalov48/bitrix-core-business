<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Recepients;

interface Collector
{
	public function fetch(int $limit, int $offset): RecepientCollection;
}