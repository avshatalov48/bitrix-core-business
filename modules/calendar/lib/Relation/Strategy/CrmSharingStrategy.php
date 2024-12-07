<?php

namespace Bitrix\Calendar\Relation\Strategy;

use Bitrix\Calendar\Integration\Crm\DealHandler;
use Bitrix\Calendar\Relation\Builder\Entity\EntityBuilderFromDeal;
use Bitrix\Calendar\Relation\Builder\Owner\OwnerBuilder;
use Bitrix\Calendar\Relation\Item\Relation;
use Bitrix\Calendar\Relation\Exception\RelationException;
use Bitrix\Calendar\Sharing\Link\CrmDealLink;
use Bitrix\Calendar\Sharing\Link\EventLink;
use Bitrix\Calendar\Sharing;
use Bitrix\Crm\EO_Deal;

class CrmSharingStrategy extends RelationStrategy
{
	/**
	 * @inheritdoc
	 */
	public function getRelation(): Relation
	{
		$relation = new Relation($this->event->getId());
		/** @var EventLink $eventLink */
		$eventLink = Sharing\Link\Factory::getInstance()->getEventLinkByEventId($this->event->getId(), false);
		if (!$eventLink)
		{
			throw new RelationException('Event sharing link not found');
		}

		/** @var CrmDealLink $crmDealLink */
		$crmDealLink = Sharing\Link\Factory::getInstance()->getLinkByHash($eventLink->getParentLinkHash());
		if (!$crmDealLink)
		{
			throw new RelationException('Deal sharing link not found');
		}

		/** @var EO_Deal $deal */
		$deal = DealHandler::getDeal($crmDealLink->getEntityId());
		if (!$deal)
		{
			throw new RelationException('Deal not found');
		}

		$entity = (new EntityBuilderFromDeal($deal))->build();
		$relation->setEntity($entity);

		$owner = (new OwnerBuilder($deal->getAssignedById()))->build();
		$relation->setOwner($owner);

		return $relation;
	}
}