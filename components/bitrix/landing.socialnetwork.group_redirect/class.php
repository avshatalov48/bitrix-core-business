<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Connector;
use \Bitrix\Landing\Landing\UrlPreview;
use \Bitrix\Landing\Internals\BindingTable;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

\CBitrixComponent::includeComponentClass('bitrix:landing.base');

class LandingSocialnetworkGroupRedirectComponent extends LandingBaseComponent
{
	/**
	 * If for site id exist group, then returns group id.
	 * @param int $siteId Site id.
	 * @return int
	 */
	public static function getGroupIdBySiteId($siteId)
	{
		$res = BindingTable::getList([
			'select' => [
				'BINDING_ID'
			],
			'filter' => [
				'=ENTITY_TYPE' => BindingTable::ENTITY_TYPE_SITE,
				'=BINDING_TYPE' => 'G',
				'ENTITY_ID' => $siteId
			]
		]);
		if ($row = $res->fetch())
		{
			return (int) $row['BINDING_ID'];
		}

		return null;
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
			$realFileDir = dirname($this->getRealFile());
			$sitePath = mb_substr($this->getUriPath(), mb_strlen($realFileDir));
			$landingId = UrlPreview::getPreviewByCode($sitePath);

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