<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\ActionFilter\Attribute\Rule;

use Attribute;
use Bitrix\Main\Engine\ActionFilter\Attribute\FilterAttributeInterface;
use Bitrix\Main\Engine\ActionFilter\FilterType;

#[Attribute(Attribute::TARGET_METHOD)]
final class Authentication implements FilterAttributeInterface
{
	public function __construct(
		private readonly bool $enableRedirect = false,
		private readonly FilterType $type = FilterType::EnablePrefilter,
	)
	{

	}

	public function getFilters(): array
	{
		if ($this->type->isNegative())
		{
			return [\Bitrix\Main\Engine\ActionFilter\Authentication::class];
		}

		return [new \Bitrix\Main\Engine\ActionFilter\Authentication($this->enableRedirect)];
	}

	public function getType(): FilterType
	{
		return $this->type;
	}
}