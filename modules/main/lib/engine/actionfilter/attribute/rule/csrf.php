<?php

declare(strict_types=1);

namespace Bitrix\Main\Engine\ActionFilter\Attribute\Rule;

use Attribute;
use Bitrix\Main\Engine\ActionFilter\Attribute\FilterAttributeInterface;
use Bitrix\Main\Engine\ActionFilter\FilterType;

#[Attribute(Attribute::TARGET_METHOD)]
final class Csrf implements FilterAttributeInterface
{
	public function __construct(
		private readonly bool $enabled = true,
		private readonly string $tokenName = 'sessid',
		private readonly bool $returnNew = true,
		private readonly FilterType $type = FilterType::EnablePrefilter,
	)
	{

	}

	public function getFilters(): array
	{
		if ($this->type->isNegative())
		{
			return [\Bitrix\Main\Engine\ActionFilter\Csrf::class];
		}

		return [new \Bitrix\Main\Engine\ActionFilter\Csrf($this->enabled, $this->tokenName, $this->returnNew)];
	}

	public function getType(): FilterType
	{
		return $this->type;
	}
}