<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\ActionFilter\Attribute\Rule;

use Attribute;
use Bitrix\Main\Engine\ActionFilter\Attribute\FilterAttributeInterface;
use Bitrix\Main\Engine\ActionFilter\FilterType;

#[Attribute(Attribute::TARGET_METHOD)]
final class HttpMethod implements FilterAttributeInterface
{
	public function __construct(
		private readonly array $allowedMethods = [\Bitrix\Main\Engine\ActionFilter\HttpMethod::METHOD_GET],
		private readonly FilterType $type = FilterType::EnablePrefilter,
	)
	{

	}

	public function getFilters(): array
	{
		if ($this->type->isNegative())
		{
			return [\Bitrix\Main\Engine\ActionFilter\HttpMethod::class];
		}

		return [new \Bitrix\Main\Engine\ActionFilter\HttpMethod($this->allowedMethods)];
	}

	public function getType(): FilterType
	{
		return $this->type;
	}
}