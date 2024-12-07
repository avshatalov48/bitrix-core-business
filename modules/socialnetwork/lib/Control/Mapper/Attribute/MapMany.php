<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Attribute;

use Attribute;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\Field\FieldInterface;

#[Attribute]
class MapMany implements MapInterface
{
	/** @var FieldInterface[]  */
	private array $fieldMappers;

	public function __construct(
		FieldInterface ...$fieldMappers,
	)
	{
		$this->fieldMappers = $fieldMappers;
	}

	public function map(array &$fields, mixed $value): void
	{
		foreach ($this->fieldMappers as $mapper)
		{
			$fields[$mapper->getFieldName()] = $mapper->getValue($value);
		}
	}
}