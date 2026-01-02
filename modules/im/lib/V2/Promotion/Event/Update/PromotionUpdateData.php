<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Promotion\Event\Update;

use Bitrix\Im\V2\Promotion\Entity\PromotionList;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Validation\Rule\Recursive\Validatable;

class PromotionUpdateData implements RestConvertible
{
	#[Validatable]
	private PromotionList $addedPromotions;
	#[Validatable]
	private PromotionList $deletedPromotions;

	public function __construct(PromotionList $addedPromotions, ?PromotionList $deletedPromotions = null)
	{
		$this->addedPromotions = $addedPromotions;
		$this->deletedPromotions = $deletedPromotions ?? new PromotionList();
	}

	public static function getRestEntityName(): string
	{
		return 'promotionUpdateData';
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'addedPromotions' => $this->addedPromotions->toRestFormat(),
			'deletedPromotions' => $this->deletedPromotions->toRestFormat(),
		];
	}
}
