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
use Bitrix\Main\Type;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Integration\Seo\Ads\MessageMarketingFb;
use Bitrix\Sender\Posting\ThreadStrategy\IThreadStrategy;
use Bitrix\Sender\Posting\ThreadStrategy\ThreadStrategyContext;
use function MongoDB\BSON\fromPHP;

Loc::loadMessages(__FILE__);

/**
 * Class PostingManager
 * @package Bitrix\Sender
 */
class PostingManager
{
	const SEND_RESULT_ERROR    = false;
	const SEND_RESULT_SENT     = true;
	const SEND_RESULT_CONTINUE = 'CONTINUE';
	const SEND_RESULT_WAIT     = 'WAIT';
	const SEND_RESULT_WAITING_RECIPIENT     = 'WAITING_RECIPIENT';
	public static $threadId;

	/** @var int $checkStatusStep */
	protected static $checkStatusStep = 20;

	/** @var int $emailSentPerIteration */
	protected static $emailSentPerIteration = 0;

	/**
	 * Handler of read event.
	 *
	 * @param array $data Data.
	 *
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
	 * Do read actions.
	 *
	 * @param integer $recipientId Recipient ID.
	 *
	 * @return void
	 */
	public static function read($recipientId)
	{
		$postingContactPrimary = ['ID' => $recipientId];

		$row = PostingRecipientTable::getRowById($postingContactPrimary);
		if (!$row)
		{
			return;
		}

		if ($row['ID'])
		{
			PostingReadTable::add(
				[
					'POSTING_ID'   => $row['POSTING_ID'],
					'RECIPIENT_ID' => $row['ID'],
				]
			);
		}

		if ($row['CONTACT_ID'])
		{
			ContactTable::update(
				$row['CONTACT_ID'],
				[
					'IS_READ' => 'Y',
				]
			);
		}
	}

	/**
	 * Handler of click event.
	 *
	 * @param array $data Data.
	 *
	 * @return array
	 */
	public static function onMailEventMailClick(array $data)
	{
		$id  = intval($data['RECIPIENT_ID']);
		$url = $data['URL'];
		if ($id > 0 && $url <> '')
		{
			static::click($id, $url);
		}

		return $data;
	}

	/**
	 * Do click actions.
	 *
	 * @param integer $recipientId Recipient ID.
	 * @param string $url Url.
	 *
	 * @return void
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function click($recipientId, $url)
	{
		$postingContactPrimary = ['ID' => $recipientId];
		$row = PostingRecipientTable::getRowById($postingContactPrimary);
		if (!$row)
		{
			return;
		}

		if ($row['ID'])
		{
			$read = PostingReadTable::getRowById(
				[
					'POSTING_ID'   => $row['POSTING_ID'],
					'RECIPIENT_ID' => $row['ID']
				]
			);
			if ($read === null)
			{
				static::read($recipientId);
			}

			$postingDb = PostingTable::getList(
				[
					'select' => ['ID'],
					'filter' => ['=ID' => $row['POSTING_ID']],
				]
			);
			if ($postingDb->fetch())
			{
				$deleteParameters = ['bx_sender_conversion_id'];
				$letter           = Entity\Letter::createInstanceByPostingId($row['POSTING_ID']);
				$linkParams = $letter->getMessage()
									->getConfiguration()
									->get('LINK_PARAMS');
				if ($linkParams)
				{
					$parametersTmp = [];
					parse_str($linkParams, $parametersTmp);
					if (is_array($parametersTmp))
					{
						$parametersTmp    = array_keys($parametersTmp);
						$deleteParameters = array_merge($deleteParameters, $parametersTmp);
					}
				}

				$uri        = new \Bitrix\Main\Web\Uri($url);
				$fixedUrl = $uri->deleteParams($deleteParameters, false)
								->getUri();

				$fixedUrl   = urldecode($fixedUrl);

				if(mb_strpos($fixedUrl, 'pub/mail/unsubscribe.php') === false)
				{
					$addClickDb = PostingClickTable::add(
						[
							'POSTING_ID'   => $row['POSTING_ID'],
							'RECIPIENT_ID' => $row['ID'],
							'URL'          => $fixedUrl
						]
					);
				}

				if ($addClickDb && $addClickDb->isSuccess())
				{
					// send event
					$eventData = [
						'URL'       => $url,
						'URL_FIXED' => $fixedUrl,
						'CLICK_ID'  => $addClickDb->getId(),
						'RECIPIENT' => $row
					];
					$event = new Event('sender', 'OnAfterRecipientClick', [$eventData]);
					$event->send();
				}
			}
		}

		if ($row['CONTACT_ID'])
		{
			ContactTable::update(
				$row['CONTACT_ID'],
				[
					'IS_CLICK' => 'Y',
				]
			);
		}
	}

	/**
	 * Get chain list for resending.
	 *
	 * @param integer $mailingId Mailing ID.
	 *
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getChainReSend($mailingId)
	{
		$result      = [];
		$mailChainDb = MailingChainTable::getList(
			[
				'select' => ['ID'],
				'filter' => [
					'=MAILING.ID'           => $mailingId,
					'=MAILING.ACTIVE'       => 'Y',
					'=REITERATE'            => 'N',
					'=MAILING_CHAIN.STATUS' => MailingChainTable::STATUS_END,
				]
			]
		);
		while ($mailChain = $mailChainDb->fetch())
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
	 *
	 * @return bool
	 * @throws \Bitrix\Main\DB\Exception
	 */
	public static function sendToAddress($mailingChainId, $address)
	{
		$recipientEmail = $address;
		$emailParts     = explode('@', $recipientEmail);
		$recipientName  = $emailParts[0];

		global $USER;

		$mailingChain = MailingChainTable::getRowById(['ID' => $mailingChainId]);
		$fields       = [
			'NAME'              => $recipientName,
			'EMAIL_TO'          => $address,
			'USER_ID'           => $USER->GetID(),
			'SENDER_CHAIN_ID'   => $mailingChain["ID"],
			'SENDER_CHAIN_CODE' => 'sender_chain_item_'.$mailingChain["ID"]
		];

		$letter  = new Entity\Letter($mailingChainId);
		$message = $letter->getMessage();

		$siteId = MailingTable::getMailingSiteId($mailingChain['MAILING_ID']);

		$message->getReadTracker()
				->setModuleId('sender')
				->setFields(['RECIPIENT_ID' => 0])
				->setSiteId($siteId);

		$message->getClickTracker()
				->setModuleId('sender')
				->setFields(['RECIPIENT_ID' => 0])
				->setUriParameters(['bx_sender_conversion_id' => 0])
				->setSiteId($siteId);

		$message->getUnsubTracker()
				->setModuleId('sender')
				->setFields(
					[
						'MAILING_ID' => !empty($mailingChain) ? $mailingChain['MAILING_ID'] : 0,
						'EMAIL'      => $address,
						'TEST'       => 'Y'
					]
				)
				->setSiteId($siteId);

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
	 *
	 * @return bool|string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\Exception
	 */
	public static function send($id, $timeout = 0, $maxMailCount = 0)
	{
		$letter = Entity\Letter::createInstanceByPostingId($id);
		$sender = new Posting\Sender($letter);

		$sender->setThreadStrategy(
			MessageMarketingFb::checkSelf($sender->getMessage()->getCode()) ?
				ThreadStrategyContext::buildStrategy(
					IThreadStrategy::SINGLE
				): Runtime\Env::getThreadContext()
		)
		->setLimit($maxMailCount)
		->setTimeout($timeout)
		->send();

		static::$threadId = $sender->getThreadStrategy()->getThreadId();

		switch ($sender->getResultCode())
		{
			case Posting\Sender::RESULT_CONTINUE:
				$result = static::SEND_RESULT_CONTINUE;
				break;
			case Posting\Sender::RESULT_ERROR:
				$result = static::SEND_RESULT_ERROR;
				break;
			case Posting\Sender::RESULT_WAITING_RECIPIENT:
				$result = static::SEND_RESULT_WAITING_RECIPIENT;
				break;
			case Posting\Sender::RESULT_WAIT:
				$result = static::SEND_RESULT_WAIT;
				break;
			case Posting\Sender::RESULT_SENT:
			default:
				$result = static::SEND_RESULT_SENT;
				break;
		}

		$limiter = $sender->getExceededLimiter();
		if ($result === static::SEND_RESULT_CONTINUE && $limiter)
		{
			// update planned date only with timed limit;

			if ($limiter->getParameter('sendingStart'))
			{
				$currentTime = $limiter->getParameter('currentTime');
				$sendingStart = $limiter->getParameter('sendingStart');
				$sendingStartDate = (new \DateTime())->setTimestamp($sendingStart);

				$sendingStartDate = $currentTime > $sendingStart
					? $sendingStartDate->add(\DateInterval::createFromDateString('1 day'))
					: $sendingStartDate;

				$date = Type\DateTime::createFromPhp($sendingStartDate);
			}
			elseif ($limiter->getUnit())
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
	 * @param $threadId
	 *
	 * @return bool
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 * @deprecated
	 * @use \Bitrix\Sender\Posting\Sender::lock
	 */
	public static function lockPosting($id, $threadId)
	{
		return Posting\Sender::lock($id, $threadId);
	}

	/**
	 * UnLock posting that was locking for preventing double sending.
	 *
	 * @param integer $id ID.
	 * @param $threadId
	 *
	 * @return bool
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 * @deprecated
	 * @use \Bitrix\Sender\Posting\Sender::unlock
	 */
	public static function unlockPosting($id, $threadId)
	{
		return Posting\Sender::unlock($id, $threadId);
	}
}