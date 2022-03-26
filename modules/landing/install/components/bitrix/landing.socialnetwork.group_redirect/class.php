<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Connector;
use \Bitrix\Landing\Landing\UrlPreview;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingSocialnetworkGroupRedirectComponent extends LandingBaseComponent
{
	/**
	 * If for site id exists group, then returns group id.
	 * @param int $siteId Site id.
	 * @return int
	 */
	public static function getGroupIdBySiteId(int $siteId): ?int
	{
		return \Bitrix\Landing\Site\Scope\Group::getGroupIdBySiteId($siteId);
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent()
	{
		if ($this->init())
		{
			\Bitrix\Landing\Site\Type::setScope(
				\Bitrix\Landing\Site\Type::SCOPE_CODE_GROUP
			);
			$landingId = UrlPreview::resolveLandingId($this->getUriPath());

			if ($landingId)
			{
				$landing = Landing::createInstance($landingId, [
					'skip_blocks' => true
				]);
				if ($landing->exist())
				{
					$groupId = $this->getGroupIdBySiteId($landing->getSiteId());
					if ($groupId)
					{
						$groupPath = Connector\SocialNetwork::getTabUrl(
							$groupId,
							$landing->getPublicUrl(false, false)
						);
						if ($groupPath)
						{
							localRedirect($groupPath, true);
						}
					}
				}
			}

			parent::executeComponent();
		}
	}
}