<?php

namespace Bitrix\Calendar\Sharing\Link;

class Helper
{
	public const USER_SHARING_TYPE = 'user';
	public const EVENT_SHARING_TYPE = 'event';
	public const CRM_DEAL_SHARING_TYPE = 'crm_deal';
	public const MULTI_LINK_TYPE = 'multi';
	public const USER_CRM_DEAL_SHARING_TYPE = 'user_crm_deal';

	public const LIFETIME = [
		self::CRM_DEAL_SHARING_TYPE => '+7 days',
		self::EVENT_SHARING_TYPE => '+30 days', //after the end of an event
		self::MULTI_LINK_TYPE => '+21 days',
	];
}