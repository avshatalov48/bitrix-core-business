<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Type;

class RequestQuotaDto
{
	public string $connectorClass;
	public int $requestCount;

	public function __construct(string $connectorClass, int $requestCount)
	{
		$this->connectorClass = $connectorClass;
		$this->requestCount = $requestCount;
	}
}