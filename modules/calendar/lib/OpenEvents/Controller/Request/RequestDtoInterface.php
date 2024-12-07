<?php

namespace Bitrix\Calendar\OpenEvents\Controller\Request;

interface RequestDtoInterface
{
	public static function fromRequest(array $request): self;
}
