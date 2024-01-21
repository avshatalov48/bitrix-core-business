<?php
namespace Bitrix\Calendar\Sharing\Link\Rule;

use Bitrix\Calendar\Sharing\Link;

class Factory
{
	public function getRuleBySharingLink(Link\Joint\JointLink $sharingLink): ?Rule
	{
		$linkObjectRule = $this->getLinkObjectRuleByLink($sharingLink);

		if (!is_null($linkObjectRule))
		{
			return (new Mapper())->getFromLinkObjectRule($linkObjectRule);
		}

		return null;
	}

	public function getLinkObjectRuleByLink(Link\Joint\JointLink $sharingLink): ?LinkObjectRule
	{
		if ($sharingLink instanceof Link\CrmDealLink)
		{
			return (new Link\Rule\UserCrmDealRule($sharingLink->getOwnerId()))
				->setLinkId($sharingLink->getId());
		}

		if ($sharingLink instanceof Link\UserLink)
		{
			return (new Link\Rule\UserRule($sharingLink->getUserId()))
				->setLinkId($sharingLink->getId());
		}

		return null;
	}
}