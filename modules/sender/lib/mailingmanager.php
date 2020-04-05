<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\DB\Exception;
use Bitrix\Main\Type;

use Bitrix\Sender\Entity;
use Bitrix\Sender\Internals\Model;
use Bitrix\Sender\Dispatch\MethodSchedule;

class MailingManager
{
	/* @var Exception $error */
	protected static $error = null;

	/**
	 * @return Exception
	 */
	public static function getErrors()
	{
		return static::$error;
	}

	/**
	 * @return string
	 * @deprecated
	 */
	public static function getAgentNamePeriod()
	{
		return Runtime\ReiteratedJob::getAgentName();
	}

	/**
	 * @param $mailingChainId
	 * @return string
	 * @deprecated
	 */
	public static function getAgentName($mailingChainId)
	{
		return Runtime\SenderJob::getAgentName($mailingChainId);
	}

	/**
	 * @param null $mailingId
	 * @param null $mailingChainId
	 * @throws \Bitrix\Main\ArgumentException
	 * @deprecated
	 */
	public static function actualizeAgent($mailingId = null, $mailingChainId = null)
	{
		(new Runtime\SenderJob())
			->withCampaignId($mailingId)
			->withLetterId($mailingChainId)
			->actualize();

		(new Runtime\ReiteratedJob())->actualize();
	}

	/**
	 * Send letter.
	 *
	 * @param integer $letterId Letter ID.
	 * @return string
	 */
	public static function chainSend($letterId)
	{
		static::$error = null;

		$letter = Model\LetterTable::getRowById($letterId);
		if($letter && $letter['STATUS'] === Model\LetterTable::STATUS_PLAN)
		{
			$updateResult = Model\LetterTable::update($letterId, array('STATUS' => Model\LetterTable::STATUS_SEND));
			if ($updateResult->isSuccess())
			{
				$letter = Model\LetterTable::getRowById($letterId);
			}
		}
		if(!$letter || $letter['STATUS'] !== Model\LetterTable::STATUS_SEND)
		{
			return "";
		}


		$postingSendStatus = '';
		if(!empty($letter['POSTING_ID']))
		{
			try
			{
				$postingSendStatus = PostingManager::send(
					$letter['POSTING_ID'],
					Runtime\Env::getJobExecutionTimeout(),
					Runtime\Env::getJobExecutionItemLimit()
				);
			}
			catch (Exception $e)
			{
				static::$error = $e;
				$postingSendStatus = PostingManager::SEND_RESULT_ERROR;
			}
		}

		if(!empty(static::$error) || $postingSendStatus === PostingManager::SEND_RESULT_CONTINUE)
		{
			return static::getAgentName($letterId);
		}

		if ($letter['REITERATE'] !== 'Y')
		{
			Model\LetterTable::update($letterId, array('STATUS' => Model\LetterTable::STATUS_END));
			return "";
		}

		$isNeedUpdate = true;
		if($letter['IS_TRIGGER'] == 'Y')
		{
			$postingDb = PostingTable::getList(array(
				'select' => array('ID', 'DATE_CREATE'),
				'filter' => array(
					'STATUS' => PostingTable::STATUS_NEW,
					'MAILING_CHAIN_ID' => $letter['ID']
				),
				'order' => array('DATE_CREATE' => 'ASC'),
				'limit' => 1
			));
			if($posting = $postingDb->fetch())
			{
				$dateCreate = $posting['DATE_CREATE'];
				/** @var Type\DateTime $dateCreate|null */
				$updateFields = [
					'STATUS' => Model\LetterTable::STATUS_SEND,
					'AUTO_SEND_TIME' => $dateCreate ? $dateCreate->add($letter['TIME_SHIFT'].' minutes') : null,
					'POSTING_ID' => $posting['ID']
				];
				Model\LetterTable::update($letterId, $updateFields);
				$isNeedUpdate = false;
			}
		}

		if ($isNeedUpdate)
		{
			$letterInstance = new Entity\Letter();
			$letterInstance->loadByArray($letter);
			$letterInstance->wait();
		}

		$eventData = array(
			'MAILING_CHAIN' => $letter
		);
		$event = new \Bitrix\Main\Event('sender', 'OnAfterMailingChainSend', array($eventData));
		$event->send();

		return "";
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function checkSend()
	{
		if(\COption::GetOptionString("sender", "auto_method") !== 'cron')
			return;

		$mailingChainDb = MailingChainTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=STATUS' => array(MailingChainTable::STATUS_SEND, MailingChainTable::STATUS_PLAN),
				'=MAILING.ACTIVE' => 'Y',
				'<=AUTO_SEND_TIME' => new Type\DateTime(),
			)
		));
		while ($mailingChain = $mailingChainDb->fetch())
		{
			static::chainSend($mailingChain['ID']);
		}
	}

	/**
	 * Check period letters.
	 *
	 * @param bool $isAgentExec Is agent exec.
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function checkPeriod($isAgentExec = true)
	{
		$isAgentExecInSetting = !Runtime\Env::isReiteratedJobCron();
		if(($isAgentExec && !$isAgentExecInSetting) || (!$isAgentExec && $isAgentExecInSetting))
		{
				return "";
		}

		$dateTodayPhp = new \DateTime();
		$datetimeToday = Type\DateTime::createFromPhp(clone $dateTodayPhp);
		$dateToday = clone $dateTodayPhp;
		$dateToday = Type\Date::createFromPhp($dateToday->setTime(0,0,0));
		$dateTomorrow = clone $dateTodayPhp;
		$dateTomorrow = Type\Date::createFromPhp($dateTomorrow->setTime(0,0,0))->add('1 DAY');
		$arDateFilter = array($dateToday, $dateTomorrow);

		$chainDb = MailingChainTable::getList(array(
			'select' => array(
				'ID', 'LAST_EXECUTED', 'POSTING_ID',
				'DAYS_OF_MONTH', 'DAYS_OF_WEEK', 'TIMES_OF_DAY'
			),
			'filter' => array(
				'=REITERATE' => 'Y',
				'=MAILING.ACTIVE' => 'Y',
				'=IS_TRIGGER' => 'N',
				'=STATUS' => MailingChainTable::STATUS_WAIT,
				//'!><LAST_EXECUTED' => $arDateFilter,
			)
		));
		while($arMailingChain = $chainDb->fetch())
		{
			$lastExecuted = $arMailingChain['LAST_EXECUTED'];
			/* @var \Bitrix\Main\Type\DateTime $lastExecuted*/
			if($lastExecuted && $lastExecuted->getTimestamp() >= $dateToday->getTimestamp())
			{
				continue;
			}


			$timeOfExecute = static::getDateExecute(
				$dateTodayPhp,
				$arMailingChain["DAYS_OF_MONTH"],
				$arMailingChain["DAYS_OF_WEEK"],
				$arMailingChain["TIMES_OF_DAY"]
			);

			if($timeOfExecute)
			{
				$arUpdateMailChain = array('LAST_EXECUTED' => $datetimeToday);

				$postingDb = PostingTable::getList(array(
					'select' => array('ID'),
					'filter' => array(
						'=MAILING_CHAIN_ID' => $arMailingChain['ID'],
						'><DATE_CREATE' => $arDateFilter
					)
				));
				$arPosting = $postingDb->fetch();
				if(!$arPosting)
				{
					$postingId = MailingChainTable::initPosting($arMailingChain['ID']);
				}
				else
				{
					$postingId = $arPosting['ID'];
					$arUpdateMailChain['POSTING_ID'] = $postingId;
					PostingTable::initGroupRecipients($postingId);
				}

				if ($postingId)
				{
					$arUpdateMailChain['STATUS'] = MailingChainTable::STATUS_SEND;
					$arUpdateMailChain['AUTO_SEND_TIME'] = Type\DateTime::createFromPhp($timeOfExecute);
				}


				Model\LetterTable::update($arMailingChain['ID'], $arUpdateMailChain);
			}
		}

		(new Runtime\ReiteratedJob())->actualize();
		return '';
	}

	/**
	 * @param \DateTime $date
	 * @param $daysOfMonth
	 * @param $dayOfWeek
	 * @param $timesOfDay
	 * @return \DateTime|null
	 */
	protected static function getDateExecute(\DateTime $date, $daysOfMonth, $dayOfWeek, $timesOfDay)
	{
		$timeOfExecute = null;

		$arDay = MethodSchedule::parseDaysOfMonth($daysOfMonth);
		$arWeek = MethodSchedule::parseDaysOfWeek($dayOfWeek);
		$arTime = MethodSchedule::parseTimesOfDay($timesOfDay);
		if(!$arTime)
			$arTime = array(0,0);

		$day = $date->format('j');
		$week = $date->format('N');

		if( (!$arDay || in_array($day, $arDay)) && (!$arWeek || in_array($week, $arWeek)) )
			$timeOfExecute = $date->setTime($arTime[0], $arTime[1]);

		return $timeOfExecute;
	}
}
