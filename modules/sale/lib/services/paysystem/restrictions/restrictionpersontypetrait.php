<?php

namespace Bitrix\Sale\Services\PaySystem\Restrictions;

use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\PersonType;
use Bitrix\Sale\Services\Base\RestrictionInfo;
use Bitrix\Sale\Services\Base\RestrictionInfoCollection;

trait RestrictionPersonTypeTrait
{
	protected function getRestrictionEntityPersonType(RestrictionInfoCollection $collection): void
	{
		$this->getDomainRestrictions($collection, BusinessValue::ENTITY_DOMAIN);
	}

	protected function getRestrictionIndividualPersonType(RestrictionInfoCollection $collection): void
	{
		$this->getDomainRestrictions($collection, BusinessValue::INDIVIDUAL_DOMAIN);
	}

	private function getDomainRestrictions(RestrictionInfoCollection $collection, string $domain): void
	{
		$personTypes = PersonType::getIdsByDomain($domain);
		if (!empty($personTypes))
		{
			$personTypeRestrictionContainer = new RestrictionInfo(
				'PersonType',
				[
					'PERSON_TYPE_ID' => $personTypes,
				]
			);

			$collection->add($personTypeRestrictionContainer);
		}
	}
}