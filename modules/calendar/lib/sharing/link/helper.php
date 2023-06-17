<?php

namespace Bitrix\Calendar\Sharing\Link;

class Helper
{
	public const USER_SHARING_TYPE = 'user';
	public const EVENT_SHARING_TYPE = 'event';
	public const CRM_DEAL_SHARING_TYPE = 'crm_deal';

	public const LIFETIME_AFTER_NEED = [
		self::CRM_DEAL_SHARING_TYPE => '+7 days',
		self::EVENT_SHARING_TYPE => '+30 days',
	];
}