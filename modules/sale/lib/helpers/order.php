<?php
namespace Bitrix\Sale\Helpers;

use Bitrix\Main\Config\Option,
	Bitrix\Sale,
	Bitrix\Main\Application,
	Bitrix\Main\SiteTable;

class Order
{
	/**
	 * Check ability to view order is not an authorized user
	 *
	 * @param Sale\Order $order
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function isAllowGuestView(Sale\Order $order)
	{
		$guestStatuses = Option::get("sale", "allow_guest_order_view_status", "");
		$guestStatuses = (strlen($guestStatuses) > 0) ?  unserialize($guestStatuses) : array();
		return (is_array($guestStatuses) && in_array($order->getField('STATUS_ID'), $guestStatuses) && Option::get("sale", "allow_guest_order_view") === 'Y');
	}

	/**
	 * Return link to order for an unauthorized users.
	 *
	 * @param Sale\Order $order
	 *
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function getPublicLink(Sale\Order $order)
	{
		$context = Application::getInstance()->getContext();
		$scheme = $context->getRequest()->isHttps() ? 'https' : 'http';
		$siteData = SiteTable::getList(array(
			'filter' => array('LID' => $order->getSiteId()),
		));
		$site = $siteData->fetch();

		$paths = unserialize(Option::get("sale", "allow_guest_order_view_paths"));
		$path =  htmlspecialcharsbx($paths[$site['LID']]);

		if (isset($path) && strpos($path, '#order_id#'))
		{
			$accountNumber = urlencode(urlencode($order->getField('ACCOUNT_NUMBER')));
			$path = str_replace('#order_id#', $accountNumber,$path);
			if (strpos($path, '/') !== 0)
			{
				$path = '/'.$path;
			}

			$path .= (strpos($path, '?')) ? '&' : "?";
			$path .= "access=".$order->getHash();
		}
		else
		{
			return "";
		}

		return $scheme.'://'.$site['SERVER_NAME'].$path;
	}
}