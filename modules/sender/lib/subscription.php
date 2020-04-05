<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Event;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Mail\Tracking;
use Bitrix\Main\Application;
use Bitrix\Main\EventResult;
use Bitrix\Sender\Internals\Model\AbuseTable;
use Bitrix\Sender\Recipient;
use Bitrix\Sender\Integration;

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
		return Tracking::getLinkUnsub(static::MODULE_ID, $fields, Option::get('sender', 'unsub_link'));
	}

	/**
	 * Return link to confirmation subscription page for subscriber.
	 *
	 * @param array $fields Fields.
	 * @return string
	 */
	public static function getLinkSub(array $fields)
	{
		$tag = Tracking::getSignedTag(static::MODULE_ID, $fields);
		$urlPage = Option::get('sender', 'sub_link');
		if($urlPage == "")
		{
			$bitrixDirectory = Application::getInstance()->getPersonalRoot();
			$result = $bitrixDirectory.'/tools/sender_sub_confirm.php?sender_subscription=confirm&tag='.urlencode($tag);
		}
		else
		{
			$result = $urlPage.(strpos($urlPage, "?")===false ? "?" : "&").'sender_subscription=confirm&tag='.urlencode($tag);
		}

		return $result;
	}

	/**
	 * Event handler.
	 *
	 * @param array $data Data.
	 * @return mixed
	 */
	public static function onMailEventSubscriptionList($data)
	{
		$data['LIST'] = static::getSubscriptions($data);

		return $data;
	}

	protected static function getSubscriptions($data)
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

		if(!$data['RECIPIENT_ID'])
		{
			return array();
		}

		$recipient = PostingRecipientTable::getRowById(array('ID' => $data['RECIPIENT_ID']));
		if (!$recipient || !$recipient['CONTACT_ID'])
		{
			return array();
		}
		$contactId = $recipient['CONTACT_ID'];

		$contactData = ContactTable::getRowById($contactId);
		if ($contactData && $contactData['BLACKLISTED'] === 'Y')
		{
			return array();
		}

		$mailingUnsub = array();
		$unSubDb = MailingSubscriptionTable::getUnSubscriptionList(array(
			'select' => array('MAILING_ID'),
			'filter' => array(
				'=CONTACT.ID' => $contactId,
				'=MAILING.SITE_ID' => $mailing['SITE_ID']
			)
		));
		while($unSub = $unSubDb->fetch())
		{
			$mailingUnsub[] = $unSub['MAILING_ID'];
		}

		$mailingList = array();
		// all receives mailings
		$mailingDb = PostingRecipientTable::getList(array(
			'select' => array('MAILING_ID' => 'POSTING.MAILING.ID'),
			'filter' => array(
				'=CONTACT_ID' => $contactId,
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
				'=CONTACT.ID' => $contactId,
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
	 * Event handler.
	 *
	 * @param array $data Data.
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
	 * Event handler.
	 *
	 * @param array $data Data.
	 * @return EventResult
	 */
	public static function onMailEventSubscriptionDisable($data)
	{
		$data['SUCCESS'] = static::unsubscribeRecipient($data);
		if($data['SUCCESS'])
			$result = EventResult::SUCCESS;
		else
			$result = EventResult::ERROR;

		return new EventResult($result, $data, static::MODULE_ID);
	}

	protected static function unsubscribeRecipient($data)
	{
		if(isset($data['TEST']) && $data['TEST'] == 'Y')
		{
			return true;
		}

		if(!$data['RECIPIENT_ID'])
		{
			return true;
		}

		$data['ABUSE'] = isset($data['ABUSE']) ? (bool) $data['ABUSE'] : false;
		$data['ABUSE_TEXT'] = isset($data['ABUSE_TEXT']) ? $data['ABUSE_TEXT'] : null;

		$result = false;
		$recipient = PostingRecipientTable::getList(array(
			'select' => array(
				'ID', 'CONTACT_ID', 'CONTACT_CODE' => 'CONTACT.CODE', 'CONTACT_TYPE_ID' => 'CONTACT.TYPE_ID',
				'POSTING_ID', 'POSTING_MAILING_ID' => 'POSTING.MAILING_ID'
			),
			'filter' => array('=ID' => $data['RECIPIENT_ID']),
			'limit' => 1
		))->fetch();
		if(!$recipient || !$recipient['CONTACT_ID'])
		{
			return true;
		}
		$contactId = $recipient['CONTACT_ID'];

		$mailingDb = MailingTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=ID' => $data['UNSUBSCRIBE_LIST'],
			)
		));
		while($mailing = $mailingDb->fetch())
		{
			$primary = null;
			if($recipient['POSTING_MAILING_ID'] == $mailing['ID'])
			{
				$primary = array(
					'POSTING_ID' => $recipient['POSTING_ID'],
					'RECIPIENT_ID' => $recipient['ID'],
				);
			}
			else
			{
				$mailingPostingDb = PostingRecipientTable::getList(array(
					'select' => array('RECIPIENT_ID' => 'ID', 'POSTING_ID'),
					'filter' => array(
						'=POSTING.MAILING_ID' => $mailing['ID'],
						'=CONTACT_ID' => $contactId
					)
				));
				if($mailingPosting = $mailingPostingDb->fetch())
				{
					$primary = $mailingPosting;
				}
			}

			// add mark in statistic if there is no previous mark
			if(!empty($primary))
			{
				$unsubExists = PostingUnsubTable::getRowById($primary);
				if(!$unsubExists)
				{
					$unsubResult = PostingUnsubTable::add($primary);
					if($unsubResult->isSuccess())
					{
						$eventData = array(
							'ABUSE' => $data['ABUSE'],
							'ABUSE_TEXT' => $data['ABUSE_TEXT'],
							'MAILING_ID' => $mailing['ID'],
							'RECIPIENT_ID' => $primary['RECIPIENT_ID'],
							'CONTACT_ID' => $contactId,
							'EMAIL' => $data['EMAIL'],
						);
						$event = new Event('sender', 'OnAfterRecipientUnsub', array($eventData));
						$event->send();

						if ($data['ABUSE'])
						{
							AbuseTable::add(array(
								'TEXT' => $data['ABUSE_TEXT'],
								'CONTACT_ID' => $contactId,
								'CONTACT_CODE' => $recipient['CONTACT_CODE'],
								'CONTACT_TYPE_ID' => $recipient['CONTACT_TYPE_ID'],
							))->isSuccess();
						}

						Integration\EventHandler::onAfterPostingRecipientUnsubscribe($eventData);
					}
				}

				$result = true;
			}

			MailingSubscriptionTable::addUnSubscription(array(
				'MAILING_ID' => $mailing['ID'],
				'CONTACT_ID' => $contactId
			));

			if ($contactId && $data['ABUSE'])
			{
				ContactTable::update($contactId, array('BLACKLISTED' => 'Y'));
			}
		}

		return $result;
	}

	/**
	 * Return list of subscriptions on mailings for subscriber.
	 *
	 * @param array $data Data.
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @deprecated
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
		$receiveMailingDb = PostingRecipientTable::getList(array(
			'select' => array('MAILING_ID' => 'POSTING.MAILING.ID'),
			'filter' => array(
				'=EMAIL' => trim(strtolower($data['EMAIL'])),
				'=POSTING.MAILING.ACTIVE' => 'Y',
				'=POSTING.MAILING.SITE_ID' => $mailing['SITE_ID']
			),
			'group' => array('MAILING_ID')
		));
		while ($receiveMailing = $receiveMailingDb->fetch())
		{
			$mailingList[] = $receiveMailing['MAILING_ID'];
		}

		// all subscribed mailings
		$subscribedMailingDb = MailingSubscriptionTable::getSubscriptionList(array(
			'select' => array('MAILING_ID'),
			'filter' => array(
				'=CONTACT.EMAIL' => trim(strtolower($data['EMAIL'])),
				'=MAILING.ACTIVE' => 'Y',
				'=MAILING.SITE_ID' => $mailing['SITE_ID']
			)
		));
		while ($subscribedMailing = $subscribedMailingDb->fetch())
		{
			$mailingList[] = $subscribedMailing['MAILING_ID'];
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
	 * Subscribe email for mailings.
	 *
	 * @param array $data Data.
	 * @return bool
	 */
	public static function subscribe(array $data)
	{
		$id = static::add($data['EMAIL'], $data['SUBSCRIBE_LIST']);
		if($id)
		{
			return true;
		}

		return false;
	}

	/**
	 * Unsubscribe email from mailing.
	 *
	 * @param array $data Data.
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @deprecated
	 */
	public static function unsubscribe($data)
	{
		$result = false;

		if(isset($data['TEST']) && $data['TEST'] == 'Y')
			return true;

		$data['ABUSE'] = isset($data['ABUSE']) ? (bool) $data['ABUSE'] : false;
		$data['ABUSE_TEXT'] = isset($data['ABUSE_TEXT']) ? $data['ABUSE_TEXT'] : null;

		$posting = null;
		if($data['RECIPIENT_ID'])
		{
			$postingDb = PostingRecipientTable::getList(array(
				'select' => array('POSTING_ID', 'POSTING_MAILING_ID' => 'POSTING.MAILING_ID'),
				'filter' => array(
					'=ID' => $data['RECIPIENT_ID'],
					'=CONTACT.CODE' => $data['EMAIL'],
					'=CONTACT.TYPE_ID' => Recipient\Type::EMAIL,
				)
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
					'CONTACT_ID' => isset($data['CONTACT_ID']) ? (int) $data['CONTACT_ID'] : null,
				);
			}
			else
			{
				$mailingPostingDb = PostingRecipientTable::getList(array(
					'select' => array('RECIPIENT_ID' => 'ID', 'CONTACT_ID', 'POSTING_ID'),
					'filter' => array(
						'=POSTING.MAILING_ID' => $mailing['ID'],
						'=CONTACT.CODE' => $data['EMAIL'],
						'=CONTACT.TYPE_ID' => Recipient\Type::EMAIL,
					),
					'limit' => 1
				));
				if($mailingPosting = $mailingPostingDb->fetch())
				{
					$unsub = $mailingPosting;
				}
			}

			// add mark in statistic if there is no previous mark
			if(!empty($unsub))
			{
				if ($unsub['CONTACT_ID'] && $data['ABUSE'])
				{
					ContactTable::update($unsub['CONTACT_ID'], array('BLACKLISTED' => 'Y'));
				}

				$unsubExists = PostingUnsubTable::getRowById($unsub);
				if(!$unsubExists)
				{
					$unsubResult = PostingUnsubTable::add($unsub);
					if($unsubResult->isSuccess())
					{
						$eventData = array(
							'ABUSE' => $data['ABUSE'],
							'ABUSE_TEXT' => $data['ABUSE_TEXT'],
							'CAMPAIGN_ID' => $mailing['ID'],
							'MAILING_ID' => $mailing['ID'],
							'RECIPIENT_ID' => $unsub['RECIPIENT_ID'],
							'EMAIL' => $data['EMAIL'],
						);
						$event = new Event('sender', 'OnAfterRecipientUnsub', array($eventData));
						$event->send();

						if ($data['ABUSE'])
						{
							AbuseTable::add(array(
								'TEXT' => $data['ABUSE_TEXT'],
								'CONTACT_ID' => $unsub['CONTACT_ID'],
								'CONTACT_CODE' => $data['EMAIL'],
								'CONTACT_TYPE_ID' => Recipient\Type::EMAIL,
							));
						}

						Integration\EventHandler::onAfterPostingRecipientUnsubscribe($eventData);
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
	 * Subscribe email for mailings and returns subscription id.
	 *
	 * @param string $code Code.
	 * @param array $mailingIdList Mailing list.
	 * @return integer|null
	 */
	public static function add($code,  array $mailingIdList)
	{
		$contactId = null;

		$typeId = Recipient\Type::detect($code);
		$code = Recipient\Normalizer::normalize($code, $typeId);
		$contact = ContactTable::getRow([
			'select' => ['ID'],
			'filter' => [
				'=CODE' => $code,
				'=TYPE_ID' => $typeId,
			]
		]);
		if($contact)
		{
			$contactId = $contact['ID'];
		}
		else
		{
			$contactAddDb = ContactTable::add(['TYPE_ID' => $typeId, 'CODE' => $code]);
			if($contactAddDb->isSuccess())
			{
				$contactId = $contactAddDb->getId();
			}
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

		return $contactId;
	}

	/**
	 * Get mailing list allowed for subscription.
	 *
	 * @param array $params Parameters.
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
	 * Send email with link for confirmation of subscription.
	 *
	 * @param string $email Email.
	 * @param array $mailingIdList Mailing List.
	 * @param string $siteId Site ID.
	 * @return void
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
		$date = new DateTime;
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
	 * Return true if email address was unsubscribed.
	 *
	 * @param int $mailingId Campaign ID.
	 * @param string $code Recipient code.
	 * @param int $typeId Recipient type ID.
	 * @return bool
	 */
	public static function isUnsubscibed($mailingId, $code, $typeId = Recipient\Type::EMAIL)
	{
		$code = Recipient\Normalizer::normalize($code, $typeId);
		$unSubDb = MailingSubscriptionTable::getUnSubscriptionList(array(
			'select' => array('MAILING_ID'),
			'filter' => array(
				'=MAILING_ID' => $mailingId,
				'=CONTACT.CODE' => $code,
				'=CONTACT.TYPE_ID' => $typeId,
			)
		));
		if($unSubDb->fetch())
		{
			return true;
		}

		return false;
	}
}
