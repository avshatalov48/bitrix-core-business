<?php

namespace Bitrix\Forum\Comments\Service;

abstract class Base
{
	const TYPE = 'BASE';

	public function getType()
	{
		return static::TYPE;
	}

	abstract public function getText(): string;

	public function canDelete()
	{
		return true;
	}
}
