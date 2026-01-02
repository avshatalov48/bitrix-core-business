<?php

namespace Bitrix\Rest\V3\Interaction\Response;

use Bitrix\Main\Type\Contract\Arrayable;

abstract class Response implements Arrayable
{
	protected bool $showDebugInfo = true;
	
	protected bool $showRawData = false;

	public function toArray(): array
	{
		$reflection = new \ReflectionClass($this);
		$properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

		$result = [];
		foreach ($properties as $property)
		{
			$result[$property->getName()] = $property->getValue($this) instanceof Arrayable ? $property->getValue($this)->toArray() : $property->getValue($this);
		}

		return $result;
	}

	public function isShowDebugInfo(): bool
	{
		return $this->showDebugInfo;
	}

	public function setShowDebugInfo(bool $showDebugInfo): self
	{
		$this->showDebugInfo = $showDebugInfo;

		return $this;
	}

	public function isShowRawData(): bool
	{
		return $this->showRawData;
	}

	public function setShowRawData(bool $showRawData): self
	{
		$this->showRawData = $showRawData;

		return $this;
	}
}
