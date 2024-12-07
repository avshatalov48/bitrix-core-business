<?php

namespace Bitrix\Calendar\Relation\Builder\Entity;

use Bitrix\Crm\EO_Deal;

class EntityBuilderFromDeal extends EntityBuilder
{

	/** @var EO_Deal $deal */
	public function __construct(private $deal)
	{}

	protected function getEntityId(): int
	{
		return $this->deal->getId();
	}

	protected function getEntityType(): string
	{
		return 'deal';
	}

	protected function getLink(): string
	{
		return \CCrmOwnerType::getEntityShowPath(\CCrmOwnerType::Deal, $this->getEntityId());
	}
}