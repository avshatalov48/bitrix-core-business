<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main\Sms;

use Bitrix\Main;
use Bitrix\Main\ORM\Query\Query;

class Event
{
	const ERR_SITE = 'site';
	const ERR_TEMPLATES = 'templates';
	const ERR_MODULE = 'module';

	protected $eventName;
	protected $fields;
	protected $siteId;
	protected $languageId;
	protected $templateId;

	/**
	 * Message constructor.
	 * @param string $eventName Name of a SMS template
	 * @param array $fields Data fields to insert into the message
	 */
	public function __construct($eventName, array $fields = [])
	{
		$this->eventName = $eventName;
		$this->fields = $fields;
	}

	/**
	 * @param string $siteId
	 * @return $this
	 */
	public function setSite($siteId)
	{
		$this->siteId = $siteId;
		return $this;
	}

	/**
	 * @param string $languageId
	 * @return $this
	 */
	public function setLanguage($languageId)
	{
		$this->languageId = $languageId;
		return $this;
	}

	/**
	 * @param int $templateId
	 * @return $this
	 */
	public function setTemplate($templateId)
	{
		$this->templateId = $templateId;
		return $this;
	}

	/**
	 * @param bool $directly True - send directly, False - send via queue
	 * @return Main\Result
	 */
	public function send($directly = false)
	{
		$result = new Main\Result();

		if(!Main\Loader::includeModule("messageservice"))
		{
			$result->addError(new Main\Error("Module messageservice is not installed.", self::ERR_MODULE));
			return $result;
		}

		$senderId = Main\Config\Option::get("main", "sms_default_service");
		if($senderId == '')
		{
			//messageservice will try to use any available sender
			$senderId = null;
		}

		$messageListResult = $this->createMessageList();
		if (!$messageListResult->isSuccess())
		{
			return $result->addErrors($messageListResult->getErrors());
		}
		$messageList = $messageListResult->getData();

		foreach($messageList as $message)
		{
			$smsMessage = \Bitrix\MessageService\Sender\SmsManager::createMessage([
				'SENDER_ID' => $senderId,
				'MESSAGE_FROM' => $message->getSender(),
				'MESSAGE_TO' => $message->getReceiver(),
				'MESSAGE_BODY' => $message->getText(),
			]);

			$event = new Main\Event('main', 'onBeforeSendSms', [
				'message' => $smsMessage,
				'template' => $message->getTemplate()
			]);
			$event->send();
			foreach($event->getResults() as $evenResult)
			{
				if($evenResult->getType() === Main\EventResult::ERROR)
				{
					continue 2;
				}
			}

			if($directly)
			{
				$smsResult = $smsMessage->sendDirectly();
			}
			else
			{
				$smsResult = $smsMessage->send();
			}

			if(!$smsResult->isSuccess())
			{
				$result->addErrors($smsResult->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return Main\Result<int, Message>
	 */
	public function createMessageList(): Main\Result
	{
		$result = new Main\Result();
		$context = Main\Context::getCurrent();

		if($this->siteId === null)
		{
			$this->siteId = $context->getSite();
			if($this->siteId === null)
			{
				$result->addError(new Main\Error("Can't filter templates, the siteId is not set.", self::ERR_SITE));
				return $result;
			}
		}

		if($this->languageId === null)
		{
			$this->languageId = $context->getLanguage();
		}

		$templates = $this->fetchTemplates();

		if(empty($templates))
		{
			$result->addError(new Main\Error("Templates not found.", self::ERR_TEMPLATES));
			return $result;
		}

		$messageList = [];
		foreach($templates as $template)
		{
			$messageList[] = Message::createFromTemplate($template, $this->fields);
		}
		$result->setData($messageList);

		return $result;
	}

	protected function fetchTemplates()
	{
		$filter = Query::filter()
			->where("ACTIVE", "Y")
			->where("SITES.LID", $this->siteId);

		if($this->templateId !== null)
		{
			//specific template was supplied
			$filter->where("ID", $this->templateId);
		}
		else
		{
			//select templates by conditions
			$filter->where("EVENT_NAME", $this->eventName);

			if($this->languageId !== null)
			{
				$filter->where(Query::filter()
					->logic('or')
					->where('LANGUAGE_ID', $this->languageId)
					->where('LANGUAGE_ID', '')
					->whereNull('LANGUAGE_ID')
				);
			}
		}

		$res = TemplateTable::getList([
			'select' => ['*', 'SITES.SITE_NAME', 'SITES.SERVER_NAME', 'SITES.LID'],
			'filter' => $filter,
		]);

		return $res->fetchCollection();

	}
}
