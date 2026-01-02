<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Common\Collection;

use Throwable;

trait GroupActionTrait
{
	abstract public static function getCollectionElementClass(): string;

	public function __call(string $name, array $args = [])
	{
		[$property, $operation] = $this->getPropertyAndOperationWithType($name);

		if ($operation === 'get')
		{
			return $this->callGetMethod($property);
		}

		if ($operation === 'set')
		{
			return $this->callSetMethod($property, $args);
		}

		return null;
	}

	private function getPropertyAndOperationWithType(string $name): array
	{
		$property = lcfirst(substr($name, 3));
		$operation = substr($name, 0, 3);
		$isList = lcfirst(substr($property, -4)) === 'list';
		$property = $isList ? substr($property, 0, -4) : $property;

		return [$property, $operation];
	}

	private function callGetMethod(string $property): ?array
	{
		$getter = 'get' . $property;
		if (!method_exists(self::getCollectionElementClass(), $getter))
		{
			return null;
		}

		$data = [];
		foreach ($this as $item)
		{
			try
			{
				$data[] = $item->$getter();
			}
			catch (Throwable)
			{

			}
		}

		return $data;
	}

	private function callSetMethod(string $property, array $args): self
	{
		$setter = 'set' . $property;
		if (!method_exists(self::getCollectionElementClass(), $setter))
		{
			return $this;
		}

		foreach ($this as $item)
		{
			try
			{
				$item->$setter(...$args);
			}
			catch (Throwable)
			{

			}
		}

		return $this;
	}
}