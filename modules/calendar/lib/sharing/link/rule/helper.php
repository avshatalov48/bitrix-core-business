<?php

namespace Bitrix\Calendar\Sharing\Link\Rule;

use Bitrix\Calendar\Core\Base\SingletonTrait;
use Bitrix\Calendar\Sharing\Link\CrmDealLink;
use Bitrix\Calendar\Sharing\Link\Link;
use Bitrix\Calendar\Sharing\Link\UserLink;
use Bitrix\Main\Loader;

class Helper
{
	use SingletonTrait;
	
	/**
	 * @param string $linkHash
	 * @param array $ruleArray
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function saveLinkRule(string $linkHash,  array $ruleArray)
	{
		/** @var Link $link */
		$link = (new \Bitrix\Calendar\Sharing\Link\Factory())->getLinkByHash($linkHash);
		if (is_null($link))
		{
			return false;
		}

		if ($link instanceof UserLink && \CCalendar::GetCurUserId() !== $link->getUserId())
		{
			return false;
		}
		
		if ($this->isUserResponsibleForLink($link))
		{
			return false;
		}
		
		$linkObjectRule = (new Factory())->getLinkObjectRuleByLink($link);
		if (is_null($linkObjectRule))
		{
			return false;
		}
		
		$sharingRuleMapper = new Mapper();
		$rule = $sharingRuleMapper->buildRuleFromArray($ruleArray);
		$sharingRuleMapper->saveForLinkObject($rule, $linkObjectRule);
		
		return true;
	}
	
	/**
	 * @param Link $link
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function isUserResponsibleForLink(Link $link): bool
	{
		if (!Loader::includeModule('crm'))
		{
			return false;
		}
		
		if (!($link instanceof CrmDealLink))
		{
			return false;
		}
		
		$currentUserId = (new \Bitrix\Crm\Service\Context())->getUserId();
		
		return $this->getAssignedByIdCrmDeal($link) !== $currentUserId;
	}
	
	/**
	 * @param CrmDealLink $link
	 * @return int|null
	 */
	private function getAssignedByIdCrmDeal(CrmDealLink $link): ?int
	{
		$entityBroker = \Bitrix\Crm\Service\Container::getInstance()->getEntityBroker(\CCrmOwnerType::Deal);
		if (!$entityBroker)
		{
			return null;
		}
		
		$entity = $entityBroker->getById($link->getEntityId());
		if (!$entity)
		{
			return null;
		}
		
		return $entity->getAssignedById();
	}
}