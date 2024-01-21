<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Recepients;

class Recepient
{
	public function __construct(private int $id)
	{}

	public function getId(): int
	{
		return $this->id;
	}
}