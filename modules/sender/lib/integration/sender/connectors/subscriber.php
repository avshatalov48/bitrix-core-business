<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Sender\Connectors;

use Bitrix\Sender\Connector\Base as ConnectorBase;
use Bitrix\Sender\MailingSubscriptionTable;

class Subscriber extends ConnectorBase
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Email-marketing - Subscriber';
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return "sender_subscriber";
	}

	/**
	 * @return \Bitrix\Main\DB\Result
	 */
	public function getData()
	{
		$mailingId = $this->getFieldValue('MAILING_ID', 0);

		return MailingSubscriptionTable::getSubscriptionList(array(
			'select' => array(
				'SENDER_CONTACT_ID' => 'CONTACT.ID',
				'NAME' => 'CONTACT.NAME',
				'EMAIL' => 'CONTACT.CODE',
				'USER_ID' => 'CONTACT.USER_ID'
			),
			'filter' => array(
				'MAILING_ID' => $mailingId,
			)
		));
	}

	/**
	 * @return string
	 */
	public function getForm()
	{
		return '';
	}
}
