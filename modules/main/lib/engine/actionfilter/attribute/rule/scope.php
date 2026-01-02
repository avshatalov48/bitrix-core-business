<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\ActionFilter\Attribute\Rule;

use Attribute;
use Bitrix\Main\Engine\ActionFilter\Attribute\FilterAttributeInterface;
use Bitrix\Main\Engine\ActionFilter\FilterType;

#[Attribute(Attribute::TARGET_METHOD)]
final class Scope implements FilterAttributeInterface
{
	public function __construct(
		private readonly int $scopes,
		private readonly FilterType $type = FilterType::EnablePrefilter,
	)
	{

	}

	public function getFilters(): array
	{
		if ($this->type->isNegative())
		{
			return [\Bitrix\Main\Engine\ActionFilter\Scope::class];
		}

		return [new \Bitrix\Main\Engine\ActionFilter\Scope($this->scopes)];
	}

	public function getType(): FilterType
	{
		return $this->type;
	}
}