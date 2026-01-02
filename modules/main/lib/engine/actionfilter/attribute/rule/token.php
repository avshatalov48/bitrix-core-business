<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\ActionFilter\Attribute\Rule;

use Attribute;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\ActionFilter\Attribute\FilterAttributeInterface;
use Bitrix\Main\Engine\ActionFilter\FilterType;
use Closure;

#[Attribute(Attribute::TARGET_METHOD)]
final class Token implements FilterAttributeInterface
{
	public function __construct(
		private readonly array $getEntityCallback,
		private readonly FilterType $type = FilterType::EnablePrefilter,
	)
	{

	}

	public function getFilters(): array
	{
		if ($this->type->isNegative())
		{
			return [\Bitrix\Main\Engine\ActionFilter\Token::class];
		}

		if (!is_callable($this->getEntityCallback))
		{
			throw new ArgumentException('Not valid closure');
		}

		$closure = Closure::fromCallable($this->getEntityCallback);

		return [new \Bitrix\Main\Engine\ActionFilter\Token($closure)];
	}

	public function getType(): FilterType
	{
		return $this->type;
	}
}