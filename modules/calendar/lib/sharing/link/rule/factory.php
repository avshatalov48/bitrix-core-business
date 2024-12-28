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
		$rule = match (true)
		{
			$sharingLink instanceof Link\CrmDealLink => new Link\Rule\UserCrmDealRule($sharingLink->getOwnerId()),
			$sharingLink instanceof Link\UserLink => new Link\Rule\UserRule($sharingLink->getUserId()),
			$sharingLink instanceof Link\GroupLink => new Link\Rule\GroupRule($sharingLink->getGroupId()),
			default => null,
		};

		return $rule?->setLinkId($sharingLink->getId());
	}
}
