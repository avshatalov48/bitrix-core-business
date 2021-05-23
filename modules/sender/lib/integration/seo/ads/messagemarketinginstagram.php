<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Seo\Ads;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Message\iMarketing;

/**
 * Class MessageMarketingInstagram
 * @package Bitrix\Sender\Integration\Seo\Ads
 */
class MessageMarketingInstagram extends MessageMarketingFb
{
	const CODE = iMarketing::CODE_INSTAGRAM;
	public function getName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_SEO_MESSAGE_NAME_ADS_INSTAGRAM');
	}
}