<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Attribute;

use Attribute;
use Bitrix\Main\ArgumentException;
use Bitrix\Socialnetwork\Control\Mapper\Field\ValueMapperInterface;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Map
{
	protected string $fieldNewName;
	protected ?ValueMapperInterface $valueMapper = null;

	/**
	 * @throws ArgumentException
	 */
	public function __construct(
		string $fieldNewName,
		?string $valueMapperClass = null
	)
	{
		if ($valueMapperClass !== null)
		{
			if (!is_subclass_of($valueMapperClass, ValueMapperInterface::class))
			{
				throw new ArgumentException('Wrong value mapper class');
			}

			$this->valueMapper = new $valueMapperClass();
		}

		$this->fieldNewName = $fieldNewName;
	}

	public function getNameAndValue(mixed $propertyValue): array
	{
		if ($this->valueMapper !== null)
		{
			$propertyValue = $this->valueMapper->getValue($propertyValue);
		}

		return [$this->fieldNewName, $propertyValue];
	}
}