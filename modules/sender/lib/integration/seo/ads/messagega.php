<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Seo\Ads;

use Bitrix\Main\Localization\Loc;

/**
 * Class MessageGa
 * @package Bitrix\Sender\Integration\Seo\Ads
 */
class MessageGa extends MessageBase
{
	const CODE = self::CODE_ADS_GA;

	/**
	 * Get name.
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_NAME_ADS_GA');
	}
}