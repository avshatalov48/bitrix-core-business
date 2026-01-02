<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\ActionFilter\Attribute\Rule;

use Attribute;
use Bitrix\Main\Engine\ActionFilter\Attribute\FilterAttributeInterface;
use Bitrix\Main\Engine\ActionFilter\FilterType;

#[Attribute(Attribute::TARGET_METHOD)]
final class Prefilters implements FilterAttributeInterface
{
	public function __construct(
		private readonly array $prefilters,
	)
	{
	}

	public function getFilters(): array
	{
		return $this->prefilters;
	}

	public function getType(): FilterType
	{
		return FilterType::Prefilter;
	}
}