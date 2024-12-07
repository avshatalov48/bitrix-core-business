<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Attribute;

use Attribute;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\Field\FieldInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MapOne implements MapInterface
{
	public function __construct(
		private readonly FieldInterface $mapper
	)
	{
	}

	public function map(array &$fields, mixed $value): void
	{
		$fields[$this->mapper->getFieldName()] = $this->mapper->getValue($value);
	}
}