<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Promotion\Entity;

use ArrayIterator;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Validation\Rule\ElementsType;
use Bitrix\Main\Validation\Rule\Recursive\Validatable;
use IteratorAggregate;

class PromotionList implements RestConvertible, IteratorAggregate
{
	#[ElementsType(className: Promotion::class)]
	#[Validatable(true)]
	private array $promotions;

	public function __construct(Promotion ...$promotions)
	{
		$this->promotions = $promotions ?: [];
	}

	public function add(Promotion ...$promotions): self
	{
		$this->promotions = array_merge($this->promotions, $promotions);

		return $this;
	}

	public function merge(self $promotionList): self
	{
		$this->promotions = array_merge($this->promotions, $promotionList->promotions);

		return $this;
	}
	public function toIdList(): array
	{
		return array_map(
			static fn(Promotion $promotion) => $promotion->getId(),
			$this->promotions
		);
	}

	public static function getRestEntityName(): string
	{
		return 'promotions';
	}

	public function toRestFormat(array $option = []): array
	{
		return array_map(
			static fn(Promotion $promotion) => $promotion->toRestFormat(),
			$this->promotions
		);
	}

	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->promotions);
	}
}
