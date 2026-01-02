<?php

declare(strict_types=1);

namespace Bitrix\Rest\Internal\Integration\Main;

use Bitrix\Main\Application;

class License
{
	public function getRegion(): ?string
	{
		return Application::getInstance()->getLicense()->getRegion();
	}
}
