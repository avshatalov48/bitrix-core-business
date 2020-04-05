<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm\ReturnCustomer;

use Bitrix\Main\Loader;

use Bitrix\Sender\Recipient;
use Bitrix\Sender\Integration;

use Bitrix\Crm;

/**
 * Class Service
 * @package Bitrix\Sender\Integration\Crm\ReturnCustomer
 */
class Service
{
	/**
	 * Return true if can use.
	 *
	 * @return bool
	 */
	public static function canUse()
	{
		return (Loader::includeModule('crm'));
	}

	/**
	 * Service can be used, but is not available because of plan.
	 *
	 * @return bool
	 */
	public static function isAvailable()
	{
		return self::canUse() && Integration\Bitrix24\Service::isRcAvailable();
	}

	/**
	 * Return true if current user can use.
	 *
	 * @return bool
	 */
	public static function canCurrentUserUse()
	{
		//TODO: add Security\Access::current()->canModifyRc()
		return self::canUse();
	}



	/**
	 * Return true if lead enabled.
	 *
	 * @return bool
	 */
	public static function isLeadEnabled()
	{
		return Crm\Settings\LeadSettings::isEnabled();
	}

	/**
	 * Get crm entity type ID by recipient type.
	 *
	 * @param string $recipientType Recipient type.
	 * @return bool
	 */
	public static function getTypeIdByRecipientType($recipientType)
	{
		return self::getTypeIdByRecipientTypeId(Recipient\Type::getId($recipientType));
	}

	/**
	 * Get crm entity type ID by recipient type ID.
	 *
	 * @param int $recipientTypeId Recipient type ID.
	 * @return int|null
	 */
	public static function getTypeIdByRecipientTypeId($recipientTypeId)
	{
		$map = [
			Recipient\Type::CRM_CONTACT_ID => \CCrmOwnerType::Contact,
			Recipient\Type::CRM_COMPANY_ID => \CCrmOwnerType::Company,
		];

		return isset($map[$recipientTypeId]) ? $map[$recipientTypeId] : null;
	}
}