<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Bitrix\Main\SiteTable;

use Bitrix\Sender\Entity\Letter;
use Bitrix\Sender\Message;
use Bitrix\Sender\Trigger;
use Bitrix\Sender\Runtime;

Loc::loadMessages(__FILE__);

/**
 * Class MailingChainTable
 * @package Bitrix\Sender
 * @internal
 */
class MailingChainTable extends Entity\DataManager
{

	const STATUS_NEW = 'N';
	const STATUS_SEND = 'S';
	const STATUS_PAUSE = 'P';
	const STATUS_WAIT = 'W';
	const STATUS_PLAN = 'T';
	const STATUS_END = 'Y';
	const STATUS_CANCEL = 'C';

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_mailing_chain';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'autocomplete' => true,
				'primary' => true,
			),
			'MAILING_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'required' => true,
			),
			'MESSAGE_CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => function ()
				{
					return Message\iBase::CODE_MAIL;
				},
			),
			'MESSAGE_ID' => array(
				'data_type' => 'integer',
			),
			'POSTING_ID' => array(
				'data_type' => 'integer',
			),
			'PARENT_ID' => array(
				'data_type' => 'integer',
			),
			'CREATED_BY' => array(
				'data_type' => 'integer',
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new Type\DateTime(),
			),
			'STATUS' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => static::STATUS_NEW,
			),
			'REITERATE' => array(
				'data_type' => 'string',
				'default_value' => 'N',
			),
			'LAST_EXECUTED' => array(
				'data_type' => 'datetime',
			),

			'TITLE' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('SENDER_ENTITY_MAILING_CHAIN_FIELD_TITLE_TITLE1'),
			),

			'EMAIL_FROM' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SENDER_ENTITY_MAILING_CHAIN_FIELD_TITLE_EMAIL_FROM1'),
				'validation' => array(__CLASS__, 'validateEmailForm'),
			),
			'SUBJECT' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SENDER_ENTITY_MAILING_CHAIN_FIELD_TITLE_SUBJECT')
			),
			'MESSAGE' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SENDER_ENTITY_MAILING_CHAIN_FIELD_TITLE_MESSAGE')
			),

			'TEMPLATE_TYPE' => array(
				'data_type' => 'string',
			),

			'TEMPLATE_ID' => array(
				'data_type' => 'string',
			),

			'IS_TRIGGER' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => 'N',
			),

			'TIME_SHIFT' => array(
				'data_type' => 'integer',
				'required' => true,
				'default_value' => 0,
			),

			'AUTO_SEND_TIME' => array(
				'data_type' => 'datetime',
			),

			'DAYS_OF_MONTH' => array(
				'data_type' => 'string',
			),
			'DAYS_OF_WEEK' => array(
				'data_type' => 'string',
			),
			'TIMES_OF_DAY' => array(
				'data_type' => 'string',
			),

			'PRIORITY' => array(
				'data_type' => 'string',
			),

			'LINK_PARAMS' => array(
				'data_type' => 'string',
			),

			'MAILING' => array(
				'data_type' => 'Bitrix\Sender\MailingTable',
				'reference' => array('=this.MAILING_ID' => 'ref.ID'),
			),
			'CURRENT_POSTING' => array(
				'data_type' => 'Bitrix\Sender\PostingTable',
				'reference' => array('=this.POSTING_ID' => 'ref.ID'),
			),
			'POSTING' => array(
				'data_type' => 'Bitrix\Sender\PostingTable',
				'reference' => array('=this.ID' => 'ref.MAILING_CHAIN_ID'),
			),
			'ATTACHMENT' => array(
				'data_type' => 'Bitrix\Sender\MailingAttachmentTable',
				'reference' => array('=this.ID' => 'ref.CHAIN_ID'),
			),
			'CREATED_BY_USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.CREATED_BY' => 'ref.ID'),
			),
		);
	}

	/**
	 * Returns validators for EMAIL_FROM field.
	 *
	 * @return array
	 */
	public static function validateEmailForm()
	{
		return array(
			new Entity\Validator\Length(null, 50),
			array(__CLASS__, 'checkEmail')
		);
	}

	/**
	 * @param string $value Value.
	 * @return mixed
	 */
	public static function checkEmail($value)
	{
		if(empty($value) || check_email($value))
			return true;
		else
			return Loc::getMessage('SENDER_ENTITY_MAILING_CHAIN_VALID_EMAIL_FROM');
	}

	/**
	 * Copy mailing chain.
	 *
	 * @param integer $id Chain id
	 * @return int|null Copied chain id
	 */
	public static function copy($id)
	{
		$dataDb = static::getList(array('filter' => array('ID' => $id)));
		if (!$data = $dataDb->fetch())
		{
			return null;
		}

		$copiedDb = static::add(array(
			'MAILING_ID' => $data['MAILING_ID'],
			'EMAIL_FROM' => $data['EMAIL_FROM'],
			'TITLE' => $data['TITLE'],
			'SUBJECT' => $data['SUBJECT'],
			'MESSAGE' => $data['MESSAGE'],
			'TEMPLATE_TYPE' => $data['TEMPLATE_TYPE'],
			'TEMPLATE_ID' => $data['TEMPLATE_ID'],
			'PRIORITY' => $data['PRIORITY'],
			'LINK_PARAMS' => $data['LINK_PARAMS'],
		));

		if (!$copiedDb->isSuccess())
		{
			return null;
		}
		$copiedId = $copiedDb->getId();

		$attachmentDb = MailingAttachmentTable::getList(array(
			'filter' => array('=CHAIN_ID' => $id)
		));
		while($attachment = $attachmentDb->fetch())
		{
			$copiedFileId = \CFile::copyFile($attachment['FILE_ID']);
			if (!$copiedFileId)
			{
				continue;
			}

			MailingAttachmentTable::add(array(
				'CHAIN_ID' => $copiedId,
				'FILE_ID' => $copiedFileId
			));
		}

		return $copiedId;
	}

	/**
	 * @param integer $mailingChainId
	 * @return int|null
	 */
	public static function initPosting($mailingChainId)
	{
		$postingId = null;
		$chainPrimary = array('ID' => $mailingChainId);
		$mailingChain = static::getRowById($chainPrimary);
		if(!$mailingChain)
		{
			return $postingId;
		}

		$needAddPosting = true;
		if(!empty($mailingChain['POSTING_ID']))
		{
			$posting = PostingTable::getRowById(array('ID' => $mailingChain['POSTING_ID']));
			if($posting)
			{
				if($posting['STATUS'] == PostingTable::STATUS_NEW)
				{
					$postingId = $mailingChain['POSTING_ID'];
					$needAddPosting = false;
				}
				/*
				elseif($arMailingChain['IS_TRIGGER'] == 'Y')
				{
					$postingId = $arMailingChain['POSTING_ID'];
					$needAddPosting = false;
				}
				*/
			}
		}

		if($needAddPosting)
		{
			$postingAddDb = PostingTable::add(array(
				'MAILING_ID' => $mailingChain['MAILING_ID'],
				'MAILING_CHAIN_ID' => $mailingChain['ID'],
			));
			if ($postingAddDb->isSuccess())
			{
				$postingId = $postingAddDb->getId();
				static::update($chainPrimary, array('POSTING_ID' => $postingId));
			}
		}

		if($postingId && $mailingChain['IS_TRIGGER'] != 'Y')
		{
			PostingTable::initGroupRecipients($postingId);
		}

		return $postingId;
	}


	/**
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onAfterAdd(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();

		/*
		// Commented because letter-segments is not set yet.
		if(!isset($data['fields']['IS_TRIGGER']) || $data['fields']['IS_TRIGGER'] != 'Y')
		{
			static::initPosting($data['primary']['ID']);
		}
		*/

		if(array_key_exists('STATUS', $data['fields']) || array_key_exists('AUTO_SEND_TIME', $data['fields']))
		{
			Runtime\Job::actualizeByLetterId($data['primary']['ID']);
		}

		if(isset($data['fields']['PARENT_ID']))
		{
			Trigger\Manager::actualizeHandlerForChild();
		}

		if(isset($data['fields']['IS_TRIGGER']) && $data['fields']['IS_TRIGGER'] == 'Y')
		{
			MailingTable::updateChainTrigger($data['fields']['CAMPAIGN_ID']);
		}

		return $result;
	}

	/**
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onBeforeUpdate(Entity\Event $event)
	{
		$result = new Entity\EventResult;

		Integration\EventHandler::onBeforeUpdateLetterTable($event, $result);

		return $result;
	}

	/**
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onAfterUpdate(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();
		if(array_key_exists('STATUS', $data['fields']) || array_key_exists('AUTO_SEND_TIME', $data['fields']))
		{
			if(array_key_exists('STATUS', $data['fields']) && $data['fields']['STATUS'] == static::STATUS_NEW)
			{
				static::initPosting($data['primary']['ID']);
			}

			Runtime\Job::actualizeByLetterId($data['primary']['ID']);
		}

		if(isset($data['fields']['PARENT_ID']))
		{
			Trigger\Manager::actualizeHandlerForChild();
		}

		return $result;
	}

	/**
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();

		$deleteIdList = array();
		if(!empty($data['primary']))
		{
			$itemDb = static::getList(array(
				'select' => array('ID'),
				'filter' => $data['primary']
			));
			while($item = $itemDb->fetch())
			{
				$deleteIdList[] = $item['ID'];
			}
		}

		foreach($deleteIdList as $chainId)
		{
			MailingAttachmentTable::delete(array('CHAIN_ID' => $chainId));
			MailingTriggerTable::delete(array('MAILING_CHAIN_ID' => $chainId));
			PostingTable::delete(array('MAILING_CHAIN_ID' => $chainId));
		}

		return $result;
	}

	/**
	 * @param Entity\Event $event
	 * @return void
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		Trigger\Manager::actualizeHandlerForChild();
	}

	/**
	 * @param $id
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function isReadyToSend($id)
	{
		$mailingChainDb = static::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=ID' => $id,
				'=MAILING.ACTIVE' => 'Y',
				'=STATUS' => array(static::STATUS_NEW, static::STATUS_PAUSE),
			),
		));
		$mailingChain = $mailingChainDb->fetch();

		return !empty($mailingChain);
	}

	/**
	 * @param $id
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function isManualSentPartly($id)
	{
		$mailingChainDb = static::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=ID' => $id,
				'=MAILING.ACTIVE' => 'Y',
				'=AUTO_SEND_TIME' => null,
				'!REITERATE' => 'Y',
				'=STATUS' => array(static::STATUS_SEND),
			),
		));
		$mailingChain = $mailingChainDb->fetch();

		return !empty($mailingChain);
	}

	/**
	 * Return true if chain will auto send.
	 *
	 * @param $id
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function isAutoSend($id)
	{
		$mailingChainDb = static::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=ID' => $id,
				'!AUTO_SEND_TIME' => null,
				'!REITERATE' => 'Y',
			),
		));
		$mailingChain = $mailingChainDb->fetch();

		return !empty($mailingChain);
	}

	/**
	 * Return true if chain can resend mails to recipients who have error sending
	 *
	 * @param $id
	 * @return bool
	 */
	public static function canReSendErrorRecipients($id)
	{
		$mailingChainDb = static::getList(array(
			'select' => array('POSTING_ID'),
			'filter' => array(
				'=ID' => $id,
				'!REITERATE' => 'Y',
				'!POSTING_ID' => null,
				'=STATUS' => static::STATUS_END,
			),
		));
		if($mailingChain = $mailingChainDb->fetch())
		{
			$errorRecipientDb = PostingRecipientTable::getList(array(
				'select' => array('ID'),
				'filter' => array(
					'=POSTING_ID' => $mailingChain['POSTING_ID'],
					'=STATUS' => PostingRecipientTable::SEND_RESULT_ERROR
				),
				'limit' => 1
			));
			if($errorRecipientDb->fetch())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Change status of recipients and mailing chain for resending mails to recipients who have error sending
	 *
	 * @param $id
	 * @return void
	 */
	public static function prepareReSendErrorRecipients($id)
	{
		if(!static::canReSendErrorRecipients($id))
		{
			return;
		}

		$mailingChain = static::getRowById(array('ID' => $id));
		$updateSql = 'UPDATE ' . PostingRecipientTable::getTableName() .
			" SET STATUS='" . PostingRecipientTable::SEND_RESULT_NONE . "'" .
			" WHERE POSTING_ID=" . intval($mailingChain['POSTING_ID']) .
			" AND STATUS='" . PostingRecipientTable::SEND_RESULT_ERROR . "'";
		Application::getConnection()->query($updateSql);
		PostingTable::update(array('ID' => $mailingChain['POSTING_ID']), array('STATUS' => PostingTable::STATUS_PART));
		static::update(array('ID' => $id), array('STATUS' => static::STATUS_SEND));
	}

	/**
	 * @param $mailingId
	 */
	public static function setStatusNew($mailingId)
	{
		static::update(array('MAILING_ID' => $mailingId), array('STATUS' => static::STATUS_NEW));
	}

	/**
	 * @return array
	 */
	public static function getStatusList()
	{
		return array(
			self::STATUS_NEW => Loc::getMessage('SENDER_CHAIN_STATUS_N'),
			self::STATUS_SEND => Loc::getMessage('SENDER_CHAIN_STATUS_S'),
			self::STATUS_PAUSE => Loc::getMessage('SENDER_CHAIN_STATUS_P'),
			self::STATUS_WAIT => Loc::getMessage('SENDER_CHAIN_STATUS_W'),
			self::STATUS_END => Loc::getMessage('SENDER_CHAIN_STATUS_Y'),
		);
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getDefaultEmailFromList()
	{
		$addressFromList = array();
		$siteEmailDb = SiteTable::getList(array('select'=>array('EMAIL')));
		while($siteEmail = $siteEmailDb->fetch())
		{
			$addressFromList[] = $siteEmail['EMAIL'];
		}

		try
		{
			$mainEmail = Option::get('main', 'email_from');
			if (!empty($mainEmail))
				$addressFromList[] = $mainEmail;

			$saleEmail = Option::get('sale', 'order_email');
			if(!empty($saleEmail))
				$addressFromList[] = $saleEmail;

			$addressFromList = array_unique($addressFromList);
			trimArr($addressFromList, true);

		}
		catch(\Exception $e)
		{

		}

		return $addressFromList;
	}

	/**
	 * @return array
	 */
	public static function getEmailFromList()
	{
		$addressFromList = static::getDefaultEmailFromList();
		$email = Option::get('sender', 'address_from');
		if(!empty($email))
		{
			$arEmail = explode(',', $email);
			$addressFromList = array_merge($arEmail, $addressFromList);
			$addressFromList = array_unique($addressFromList);
			trimArr($addressFromList, true);
		}

		return $addressFromList;
	}

	/**
	 * @param $email
	 */
	public static function setEmailFromToList($email)
	{
		$emailList = Option::get('sender', 'address_from');
		if(!empty($email))
		{
			$addressFromList = explode(',', $emailList);
			$addressFromList = array_merge(array($email), $addressFromList);
			$addressFromList = array_unique($addressFromList);
			trimArr($addressFromList, true);
			Option::set('sender', 'address_from', implode(',', $addressFromList));
		}
	}

	/**
	 * @return array
	 */
	public static function getEmailToMeList()
	{
		$addressToList = array();
		$email = Option::get('sender', 'address_send_to_me');
		if(!empty($email))
		{
			$addressToList = explode(',', $email);
			$addressToList = array_unique($addressToList);
			\TrimArr($addressToList, true);
		}

		return $addressToList;
	}

	/**
	 * @param $email
	 */
	public static function setEmailToMeList($email)
	{
		$emailList = Option::get('sender', 'address_send_to_me');
		if(!empty($email))
		{
			$addressToList = explode(',', $emailList);
			$addressToList = array_merge(array($email), $addressToList);
			$addressToList = array_unique($addressToList);
			trimArr($addressToList, true);
			Option::set('sender', 'address_send_to_me', implode(',', $addressToList));
		}
	}

	/**
	 * @param string|null $templateType Template type.
	 * @param string|null $templateId Template ID.
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function onPresetTemplateList($templateType = null, $templateId = null)
	{
		$resultList = array();

		if($templateType && $templateType !== 'MAILING')
		{
			return $resultList;
		}

		$filter = array();
		if($templateId)
		{
			$filter['ID'] = $templateId;
		}
		$templateDb = static::getList(array(
			'select' => array('ID', 'SUBJECT', 'MESSAGE'),
			'filter' => $filter,
			'order' => array('DATE_INSERT' => 'DESC'),
			'limit' => 15
		));
		while($template = $templateDb->fetch())
		{
			$resultList[] = array(
				'TYPE' => 'MAILING',
				'ID' => $template['ID'],
				'NAME' => $template['SUBJECT'],
				'ICON' => '',
				'HTML' => $template['MESSAGE']
			);
		}

		return $resultList;
	}

	/**
	 * Get message of mailing chain by ID.
	 *
	 * @param string $id ID of mailing chain
	 * @return null|string
	 * @internal
	 */
	public static function getMessageById($id)
	{
		$letter = new Letter($id);
		return $letter->getMessage()->getConfiguration()->get('BODY');
	}
}

class MailingAttachmentTable extends Entity\DataManager
{

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_mailing_attachment';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'CHAIN_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'FILE_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
		);
	}

}