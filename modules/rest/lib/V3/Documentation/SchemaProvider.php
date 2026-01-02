<?php

namespace Bitrix\Rest\V3\Documentation;

abstract class SchemaProvider
{
	protected const DEFAULT_TYPE = 'object';

	protected function getType(): string
	{
		return static::DEFAULT_TYPE;
	}

	abstract protected function getProperties(): array;

	public function getDocumentation(): array
	{
		return [
			'type' => $this->getType(),
			'properties' => $this->getProperties(),
		];
	}
}
