<?php

namespace Bitrix\Lists\Api\Response\ServiceFactory;

use Bitrix\Lists\Api\Response\Response;

class GetAverageIBlockTemplateDurationResponse extends Response
{
	public function getAverageDuration(): ?int
	{
		$averageTime = $this->data['averageDuration'] ?? null;

		return is_int($averageTime) ? $averageTime : null;
	}

	public function setAverageDuration(int $averageTime): static
	{
		$this->data['averageDuration'] = $averageTime;

		return $this;
	}
}
