<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
use Bitrix\Main\Type;

use Bitrix\Sender\Entity;
use Bitrix\Sender\Internals\Model\LetterTable;
use Bitrix\Sender\Recipient;

Loc::loadMessages(__FILE__);

/**
 * Class PostingManager
 * @package Bitrix\Sender
 */
class PostingManager
{
	const SEND_RESULT_ERROR = false;
	const SEND_RESULT_SENT = true;
	const SEND_RESULT_CONTINUE = 'CONTINUE';

	/** @var int $checkStatusStep */
	protected static $checkStatusStep = 20;

	/** @var int $emailSentPerIteration */
	protected static $emailSentPerIteration = 0;

	/**
	 * Handler of read event.
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public static function onMailEventMailRead(array $data)
	{
		$id = intval($data['RECIPIENT_ID']);
		if ($id > 0)
		{
			static::read($id);
		}

		return $data;
	}

	/**
	 * Handler of click event.
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public static function onMailEventMailClick(array $data)
	{
		$id = intval($data['RECIPIENT_ID']);
		$url = $data['URL'];
		if ($id > 0 && strlen($url) > 0)
		{
			static::click($id, $url);
		}

		return $data;
	}

	/**
	 * Do read actions.
	 *
	 * @param integer $recipientId Recipient ID.
	 * @return void
	 */
	public static function read($recipientId)
	{
		$postingContactPrimary = array('ID' => $recipientId);
		$row = PostingRecipientTable::getRowById($postingContactPrimary);
		if (!$row)
		{
			return;
		}

		if ($row['ID'])
		{
			PostingReadTable::add(array(
				'POSTING_ID' => $row['POSTING_ID'],
				'RECIPIENT_ID' => $row['ID'],
			));
		}

		if ($row['CONTACT_ID'])
		{
			ContactTable::update(
				$row['CONTACT_ID'],
				array(
					'IS_READ' => 'Y',
					'AGENT' => Recipient\Agent::detect(),
					'IP' => Context::getCurrent()->getRequest()->getRemoteAddress()
				)
			);
		}
	}

	/**
	 * Do click actions.
	 *
	 * @param integer $recipientId Recipient ID.
	 * @param string $url Url.
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function click($recipientId, $url)
	{

		$postingContactPrimary = array('ID' => $recipientId);
		$row = PostingRecipientTable::getRowById($postingContactPrimary);
		if (!$row)
		{
			return;
		}

		if ($row['ID'])
		{
			$read = PostingReadTable::getRowById(array(
				'POSTING_ID' => $row['POSTING_ID'],
				'RECIPIENT_ID' => $row['ID']
			));
			if ($read === null)
			{
				static::read($recipientId);
			}

			$postingDb = PostingTable::getList(array(
				'select' => array('ID'),
				'filter' => array('=ID' => $row['POSTING_ID']),
			));
			if ($postingDb->fetch())
			{
				$deleteParameters = ['bx_sender_conversion_id'];
				$letter = Entity\Letter::createInstanceByPostingId($row['POSTING_ID']);
				$linkParams = $letter->getMessage()->getConfiguration()->get('LINK_PARAMS');
				if ($linkParams)
				{
					$parametersTmp = [];
					parse_str($linkParams, $parametersTmp);
					if (is_array($parametersTmp))
					{
						$parametersTmp = array_keys($parametersTmp);
						$deleteParameters = array_merge($deleteParameters, $parametersTmp);
					}
				}

				$uri = new \Bitrix\Main\Web\Uri($url);
				$fixedUrl = $uri->deleteParams($deleteParameters)->getLocator();
				$addClickDb = PostingClickTable::add(array(
					'POSTING_ID' => $row['POSTING_ID'],
					'RECIPIENT_ID' => $row['ID'],
					'URL' => $fixedUrl
				));
				if($addClickDb->isSuccess())
				{
					// send event
					$eventData = array(
						'URL' => $url,
						'URL_FIXED' => $fixedUrl,
						'CLICK_ID' => $addClickDb->getId(),
						'RECIPIENT' => $row
					);
					$event = new Event('sender', 'OnAfterRecipientClick', array($eventData));
					$event->send();
				}
			}
		}

		if ($row['CONTACT_ID'])
		{
			ContactTable::update(
				$row['CONTACT_ID'],
				array(
					'IS_CLICK' => 'Y',
					'IP' => Context::getCurrent()->getRequest()->getRemoteAddress()
				)
			);
		}
	}


	/**
	 * Get chain list for resending.
	 *
	 * @param integer $mailingId Mailing ID.
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getChainReSend($mailingId)
	{
		$result = array();
		$mailChainDb = MailingChainTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=MAILING.ID' => $mailingId,
				'=MAILING.ACTIVE' => 'Y',
				'=REITERATE' => 'N',
				'=MAILING_CHAIN.STATUS' => MailingChainTable::STATUS_END,
			)
		));
		while($mailChain = $mailChainDb->fetch())
		{
			$result[] = $mailChain['ID'];
		}

		return (empty($result) ? null : $result);
	}

	/**
	 * Send letter by message from posting to address.
	 *
	 * @param integer $mailingChainId Chain ID.
	 * @param string $address Address.
	 * @return bool
	 * @throws \Bitrix\Main\DB\Exception
	 */
	public static function sendToAddress($mailingChainId, $address)
	{
		$recipientEmail = $address;
		$emailParts = explode('@', $recipientEmail);
		$recipientName = $emailParts[0];

		global $USER;

		$mailingChain = MailingChainTable::getRowById(array('ID' => $mailingChainId));
		$fields = array(
			'NAME' => $recipientName,
			'EMAIL_TO' => $address,
			'USER_ID' => $USER->GetID(),
			'SENDER_CHAIN_ID' => $mailingChain["ID"],
			'SENDER_CHAIN_CODE' => 'sender_chain_item_' . $mailingChain["ID"]
		);

		$letter = new Entity\Letter($mailingChainId);
		$message = $letter->getMessage();

		$message->getReadTracker()
			->setModuleId('sender')
			->setFields(array('RECIPIENT_ID' => 0));
		$message->getClickTracker()
			->setModuleId('sender')
			->setFields(array('RECIPIENT_ID' => 0))
			->setUriParameters(array('bx_sender_conversion_id' => 0));
		$message->getUnsubTracker()
			->setModuleId('sender')
			->setFields(array(
				'MAILING_ID' => !empty($mailingChain) ? $mailingChain['MAILING_ID'] : 0,
				'EMAIL' => $address,
				'TEST' => 'Y'
			));
		$message->getUnsubTracker()
			->setHandlerUri(Option::get('sender', 'unsub_link'));

		$message->setFields($fields);
		$result = $message->send();

		return $result ? static::SEND_RESULT_SENT : static::SEND_RESULT_ERROR;
	}

	/**
	 * Send posting.
	 *
	 * @param integer $id Posting ID.
	 * @param int $timeout Timeout.
	 * @param int $maxMailCount Max mail count.
	 * @return bool|string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\Exception
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Exception
	 */
	public static function send($id, $timeout=0, $maxMailCount=0)
	{
		$letter = Entity\Letter::createInstanceByPostingId($id);
		$sender = new Posting\Sender($letter);
		$sender->setLimit($maxMailCount)->setTimeout($timeout)->send();

		switch ($sender->getResultCode())
		{
			case Posting\Sender::RESULT_CONTINUE:
				$result = static::SEND_RESULT_CONTINUE;
				break;
			case Posting\Sender::RESULT_ERROR:
				$result = static::SEND_RESULT_ERROR;
				break;

			case Posting\Sender::RESULT_SENT:
			default:
				$result = static::SEND_RESULT_SENT;
				break;
		}

		if ($result === static::SEND_RESULT_CONTINUE && $sender->isTransportLimitsExceeded())
		{
			// update planned date only with timed limit
			$limiters = $letter->getMessage()->getTransport()->getLimiters($letter->getMessage());
			if (!empty($limiters) && current($limiters)->getUnit())
			{
				$date = new Type\Date();
				$date->add('1 day');
			}
			else
			{
				$date = new Type\DateTime();
				$date->add('2 minute');
			}
			$letter->getState()->updatePlannedDateSend($date);
		}

		return $result;
	}

	/**
	 * Lock posting for preventing double sending.
	 *
	 * @param integer $id ID.
	 * @return bool
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Exception
	 * @deprecated
	 * @use \Bitrix\Sender\Posting\Sender::lock
	 */
	public static function lockPosting($id)
	{
		return Posting\Sender::lock($id);
	}

	/**
	 * UnLock posting that was locking for preventing double sending.
	 *
	 * @param integer $id ID.
	 * @return bool
	 * @deprecated
	 * @use \Bitrix\Sender\Posting\Sender::unlock
	 */
	public static function unlockPosting($id)
	{
		return Posting\Sender::unlock($id);
	}
}