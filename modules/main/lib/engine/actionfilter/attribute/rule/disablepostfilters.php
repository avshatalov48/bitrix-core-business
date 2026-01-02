<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\ActionFilter\Attribute\Rule;

use Attribute;
use Bitrix\Main\Engine\ActionFilter\Attribute\FilterAttributeInterface;
use Bitrix\Main\Engine\ActionFilter\FilterType;

#[Attribute(Attribute::TARGET_METHOD)]
final class DisablePostfilters implements FilterAttributeInterface
{
	public function __construct(
		private readonly array $disablePostfilters,
	)
	{
	}

	public function getFilters(): array
	{
		return $this->disablePostfilters;
	}

	public function getType(): FilterType
	{
		return FilterType::DisablePostfilter;
	}
}