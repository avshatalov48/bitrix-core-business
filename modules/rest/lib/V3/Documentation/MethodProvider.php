<?php

namespace Bitrix\Rest\V3\Documentation;

abstract class MethodProvider
{
	protected const DEPRECATED = false;

	abstract protected function getTags(): array;
	abstract protected function getRequestBody(): array;
	abstract protected function getResponses(): array;

	public function getDocumentation(): array
	{
		$result = [
			'tags' => $this->getTags(),
			'requestBody' => $this->getRequestBody(),
			'responses' => $this->getResponses(),
		];

		if (static::DEPRECATED)
		{
			$result['deprecated'] = true;
		}

		return $result;
	}
}
