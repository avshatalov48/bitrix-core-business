<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Main;
use Bitrix\Mail;
use Bitrix\Mail\Internals\MailContactTable;

class ContactsStepper extends Main\Update\Stepper
{
	protected static $moduleId = 'mail';

	public function execute(array &$option)
	{
		$option['steps'] = Mail\MailMessageTable::getCount(array(
			'<=ID' => $option['lastId'] > 0 ? $option['lastId'] : 0,
		));
		$option['count'] = Mail\MailMessageTable::getCount();

		if ($option['steps'] >= $option['count'])
		{
			return false;
		}

		$res = Mail\MailMessageTable::getList(array(
			'select' => array(
				'ID',
				'FIELD_FROM', 'FIELD_REPLY_TO',
				'FIELD_TO', 'FIELD_CC', 'FIELD_BCC',
				'MAILBOX_USER_ID' => 'MAILBOX.USER_ID',
			),
			'filter' => array(
				'>ID' => $option['lastId'] > 0 ? $option['lastId'] : 0,
			),
			'order' => array('ID' => 'ASC'),
			'limit' => 1000,
		));


		$contacts = array();
		while ($item = $res->fetch())
		{
			$option['steps']++;
			$option['lastId'] = $item['ID'];

			@array_push(
				$contacts,
				...MailContact::getContactsData($item['FIELD_FROM'], $item['MAILBOX_USER_ID'], MailContactTable::ADDED_TYPE_FROM),
				...MailContact::getContactsData($item['FIELD_REPLY_TO'], $item['MAILBOX_USER_ID'], MailContactTable::ADDED_TYPE_REPLY_TO),
				...MailContact::getContactsData($item['FIELD_TO'], $item['MAILBOX_USER_ID'], MailContactTable::ADDED_TYPE_TO),
				...MailContact::getContactsData($item['FIELD_CC'], $item['MAILBOX_USER_ID'], MailContactTable::ADDED_TYPE_CC),
				...MailContact::getContactsData($item['FIELD_BCC'], $item['MAILBOX_USER_ID'], MailContactTable::ADDED_TYPE_BCC)
			);

			if (count($contacts) >= 100)
			{
				MailContactTable::addContactsBatch($contacts);

				$contacts = array();
			}
		}

		MailContactTable::addContactsBatch($contacts);

		return true;
	}

}
