<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\EventResult;

class Subscription
{
	const MODULE_ID = 'sender';

	/**
	 * Return link to unsubsribe page for subscriber
	 *
	 * @param array $fields
	 * @return string
	 */
	public static function getLinkUnsub(array $fields)
	{
		return \Bitrix\Main\Mail\Tracking::getLinkUnsub(static::MODULE_ID, $fields, \Bitrix\Main\Config\Option::get('sender', 'unsub_link'));
	}

	/**
	 * Return link to confirmation subscription page for subscriber
	 *
	 * @param array $fields
	 * @return string
	 */
	public static function getLinkSub(array $fields)
	{
		$tag = \Bitrix\Main\Mail\Tracking::getSignedTag(static::MODULE_ID, $fields);
		$urlPage = \Bitrix\Main\Config\Option::get('sender', 'sub_link');
		if($urlPage == "")
		{
			$bitrixDirectory = \Bitrix\Main\Application::getInstance()->getPersonalRoot();
			$result = $bitrixDirectory.'/tools/sender_sub_confirm.php?sender_subscription=confirm&tag='.urlencode($tag);
		}
		else
		{
			$result = $urlPage.(strpos($urlPage, "?")===false ? "?" : "&").'sender_subscription=confirm&tag='.urlencode($tag);
		}

		return $result;
	}

	/**
	 * Event handler
	 *
	 * @param $data
	 * @return mixed
	 */
	public static function onMailEventSubscriptionList($data)
	{
		$data['LIST'] = static::getList($data);

		return $data;
	}

	/**
	 * Event handler
	 *
	 * @param $data
	 * @return EventResult
	 */
	public static function onMailEventSubscriptionEnable($data)
	{
		$data['SUCCESS'] = static::subscribe($data);
		if($data['SUCCESS'])
			$result = EventResult::SUCCESS;
		else
			$result = EventResult::ERROR;

		return new EventResult($result, $data, static::MODULE_ID);
	}

	/**
	 * Event handler
	 *
	 * @param $data
	 * @return EventResult
	 */
	public static function onMailEventSubscriptionDisable($data)
	{
		$data['SUCCESS'] = static::unsubscribe($data);
		if($data['SUCCESS'])
			$result = EventResult::SUCCESS;
		else
			$result = EventResult::ERROR;

		return new EventResult($result, $data, static::MODULE_ID);
	}

	/**
	 * Return list of subscriptions on mailings for subscriber
	 *
	 * @param $data
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getList($data)
	{
		$resultMailingList = array();

		$mailing = MailingTable::getRowById(array('ID' => $data['MAILING_ID']));
		if(isset($data['TEST']) && $data['TEST'] == 'Y')
		{
			$resultMailingList[] = array(
				'ID' => $mailing['ID'],
				'NAME' => $mailing['NAME'],
				'DESC' => $mailing['DESCRIPTION'],
				'SELECTED' => true,
			);

			return $resultMailingList;
		}

		$mailingUnsub = array();
		$unSubDb = MailingSubscriptionTable::getUnSubscriptionList(array(
			'select' => array('MAILING_ID'),
			'filter' => array(
				'=CONTACT.EMAIL' => trim(strtolower($data['EMAIL'])),
				'=MAILING.SITE_ID' => $mailing['SITE_ID']
			)
		));
		while($unSub = $unSubDb->fetch())
			$mailingUnsub[] = $unSub['MAILING_ID'];

		$mailingList = array();
		// all receives mailings
		$mailingDb = PostingRecipientTable::getList(array(
			'select' => array('MAILING_ID' => 'POSTING.MAILING.ID'),
			'filter' => array(
				'=EMAIL' => trim(strtolower($data['EMAIL'])),
				'=POSTING.MAILING.ACTIVE' => 'Y',
				'=POSTING.MAILING.SITE_ID' => $mailing['SITE_ID']
			),
			'group' => array('MAILING_ID')
		));
		while ($mailing = $mailingDb->fetch())
		{
			$mailingList[] = $mailing['MAILING_ID'];
		}

		// all subscribed mailings
		$mailingDb = MailingSubscriptionTable::getSubscriptionList(array(
			'select' => array('MAILING_ID'),
			'filter' => array(
				'=CONTACT.EMAIL' => trim(strtolower($data['EMAIL'])),
				'=MAILING.ACTIVE' => 'Y',
				'=MAILING.SITE_ID' => $mailing['SITE_ID']
			)
		));
		while ($mailing = $mailingDb->fetch())
		{
			$mailingList[] = $mailing['MAILING_ID'];
		}

		$mailingList = array_unique($mailingList);
		foreach($mailingList as $mailingId)
		{
			if(!in_array($mailingId, $mailingUnsub))
			{
				$mailingDesc = MailingTable::getRowById($mailingId);
				if($mailingDesc)
				{
					$resultMailingList[] = array(
						'ID' => $mailingDesc['ID'],
						'NAME' => $mailingDesc['NAME'],
						'DESC' => $mailingDesc['DESCRIPTION'],
						'SELECTED' => in_array($mailingDesc['ID'], array($data['MAILING_ID'])),
					);
				}
			}
		}

		return $resultMailingList;
	}

	/**
	 * Subscribe email for mailings
	 *
	 * @param $data
	 * @return bool
	 */
	public static function subscribe($data)
	{
		$id = static::add($data['EMAIL'], $data['SUBSCRIBE_LIST']);
		if($id)
		{
			return true;
		}

		return false;
	}

	/**
	 * Unsubscribe email from mailing
	 *
	 * @param array $data
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function unsubscribe($data)
	{
		$result = false;

		if(isset($data['TEST']) && $data['TEST'] == 'Y')
			return true;

		$posting = null;
		if($data['RECIPIENT_ID'])
		{
			$postingDb = PostingRecipientTable::getList(array(
				'select' => array('POSTING_ID', 'POSTING_MAILING_ID' => 'POSTING.MAILING_ID'),
				'filter' => array('=ID' => $data['RECIPIENT_ID'], '=EMAIL' => $data['EMAIL'])
			));
			$posting = $postingDb->fetch();
		}

		$mailingDb = MailingTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=ID' => $data['UNSUBSCRIBE_LIST'],
			)
		));
		while($mailing = $mailingDb->fetch())
		{
			$unsub = null;

			if($posting && $posting['POSTING_MAILING_ID'] == $mailing['ID'])
			{
				$unsub = array(
					'POSTING_ID' => $posting['POSTING_ID'],
					'RECIPIENT_ID' => $data['RECIPIENT_ID'],
				);
			}
			else
			{
				$mailingPostingDb = PostingRecipientTable::getList(array(
					'select' => array('RECIPIENT_ID' => 'ID', 'POSTING_ID'),
					'filter' => array('=POSTING.MAILING_ID' => $mailing['ID'], '=EMAIL' => $data['EMAIL'])
				));
				if($mailingPosting = $mailingPostingDb->fetch())
				{
					$unsub = $mailingPosting;
				}
			}

			// add mark in statistic if there is no previous mark
			if(!empty($unsub))
			{
				$unsubExists = PostingUnsubTable::getRowById($unsub);
				if(!$unsubExists)
				{
					$unsubResult = PostingUnsubTable::add($unsub);
					if($unsubResult->isSuccess())
					{
						$eventData = array(
							'MAILING_ID' => $mailing['ID'],
							'RECIPIENT_ID' => $unsub['RECIPIENT_ID'],
							'EMAIL' => $data['EMAIL'],
						);
						$event = new \Bitrix\Main\Event('sender', 'OnAfterRecipientUnsub', array($eventData));
						$event->send();
					}
				}

				$result = true;
			}

			// add row to unsubscribe list
			$contactId = ContactTable::addIfNotExist(array('EMAIL' => $data['EMAIL']));
			if($contactId)
			{
				MailingSubscriptionTable::addUnSubscription(array('MAILING_ID' => $mailing['ID'], 'CONTACT_ID' => $contactId));
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * Subscribe email for mailings and returns subscription id
	 *
	 * @param string $email
	 * @param array $mailingIdList
	 * @return integer|null
	 */
	public static function add($email,  array $mailingIdList)
	{
		$contactId = null;

		$email = strtolower($email);
		$contactDb = ContactTable::getList(array('filter' => array('=EMAIL' => $email)));
		if($contact = $contactDb->fetch())
		{
			$contactId = $contact['ID'];
		}
		else
		{
			$contactAddDb = ContactTable::add(array('EMAIL' => $email));
			if($contactAddDb->isSuccess())
				$contactId = $contactAddDb->getId();
		}

		if(!empty($contactId))
		{
			foreach ($mailingIdList as $mailingId)
			{
				MailingSubscriptionTable::addSubscription(array(
					'MAILING_ID' => $mailingId, 'CONTACT_ID' => $contactId
				));
			}
		}
		else
		{

		}

		return $contactId;
	}

	/**
	 * Get mailing list allowed for subscription
	 *
	 * @param array $params
	 * @return array
	 */
	public static function getMailingList($params)
	{
		$filter = array("ACTIVE" => "Y", "IS_TRIGGER" => "N");
		if(isset($params["SITE_ID"]))
			$filter["SITE_ID"] = $params["SITE_ID"];
		if(isset($params["IS_PUBLIC"]))
			$filter["IS_PUBLIC"] = $params["IS_PUBLIC"];
		if(isset($params["ACTIVE"]))
			$filter["ACTIVE"] = $params["ACTIVE"];
		if(isset($params["ID"]))
			$filter["ID"] = $params["ID"];

		$mailingList = array();
		$mailingDb = MailingTable::getList(array(
			'select' => array('ID', 'NAME', 'DESCRIPTION', 'IS_PUBLIC'),
			'filter' => $filter,
			'order' => array('SORT' => 'ASC', 'NAME' => 'ASC'),
		));
		while($mailing = $mailingDb->fetch())
		{
			$mailingList[] = $mailing;
		}

		return $mailingList;
	}

	/**
	 * Send email with link for confirmation of subscription
	 *
	 * @param string $email
	 * @param array $mailingList
	 * @param string $siteId
	 * @return void
	 * //send email with url to confirmation of subscription
	 */
	public static function sendEventConfirm($email, array $mailingIdList, $siteId)
	{
		$mailingNameList = array();
		$mailingDb = MailingTable::getList(array('select' => array('NAME'), 'filter' => array("IS_TRIGGER" => "N", 'ID' => $mailingIdList)));
		while($mailing = $mailingDb->fetch())
		{
			$mailingNameList[] = $mailing['NAME'];
		}

		$subscription = array(
			'EMAIL' => $email,
			'SITE_ID' => $siteId,
			'MAILING_LIST' => $mailingIdList,
		);
		$confirmUrl = static::getLinkSub($subscription);
		$date = new \Bitrix\Main\Type\DateTime;
		$eventSendFields = array(
			"EVENT_NAME" => "SENDER_SUBSCRIBE_CONFIRM",
			"C_FIELDS" => array(
				"EMAIL" => $email,
				"DATE" => $date->toString(),
				"CONFIRM_URL" => $confirmUrl,
				"MAILING_LIST" => implode("\r\n",$mailingNameList),
			),
			"LID" => is_array($siteId)? implode(",", $siteId): $siteId,
		);
		\Bitrix\Main\Mail\Event::send($eventSendFields);
	}

	/**
	 * Return true if email address was unsubscribed
	 *
	 * @param int $mailingId
	 * @param string $email
	 * @return bool
	 */
	public static function isUnsubscibed($mailingId, $email)
	{
		$email = strtolower($email);
		$unSubDb = MailingSubscriptionTable::getUnSubscriptionList(array(
			'select' => array('MAILING_ID'),
			'filter' => array(
				'=MAILING_ID' => $mailingId,
				'=CONTACT.EMAIL' => $email
			)
		));
		if($unSubDb->fetch())
		{
			return true;
		}

		return false;
	}
}
