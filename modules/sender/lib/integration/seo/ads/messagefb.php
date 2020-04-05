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
 * Class MessageFb
 * @package Bitrix\Sender\Integration\Seo\Ads
 */
class MessageFb extends MessageBase
{
	const CODE = self::CODE_ADS_FB;

	/**
	 * Get name.
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_NAME_ADS_FB');
	}
}