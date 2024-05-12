<?php

namespace Bitrix\Forum\Comments\Service;

class ServiceDefault extends Base
{
	const TYPE = 'DEFAULT';

	public function getText(string $text = '', array $params = []): string
	{
		return $text;
	}
}
