<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Crm\ReturnCustomer;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class TransportDeal
 * @package Bitrix\Sender\Integration\Crm\ReturnCustomer
 */
class TransportDeal extends TransportBase
{
	const CODE = self::CODE_RC_DEAL;
}