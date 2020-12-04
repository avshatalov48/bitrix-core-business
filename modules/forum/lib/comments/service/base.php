<?php

namespace Bitrix\Forum\Comments\Service;

abstract class Base
{
	const TYPE = 'BASE';

	public function getType()
	{
		return static::TYPE;
	}

	public function getText()
	{
		return '';
	}

	public function canDelete()
	{
		return true;
	}
}