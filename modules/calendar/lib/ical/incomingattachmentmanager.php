<?php


namespace Bitrix\Calendar\ICal;


use Bitrix\Calendar\ICal\Basic\AttachmentManager;
use Bitrix\Calendar\ICal\Parser\ParserManager;

class IncomingAttachmentManager extends AttachmentManager
{
	private $data;
	/**
	 * @var array
	 */
	private $events;
	private $method;

	public static function getInstance(array $params)
	{
		return new self($params);
	}

	public function __construct(array $params)
	{
		$this->data = $params['data'];
	}

	public function getAttachment()
	{
		return $this->events;
	}

	public function prepareEventAttachment(): IncomingAttachmentManager
	{
		$manager = ParserManager::getInstance($this->data);
		$this->events = $manager->getProcessedEvents();
		$this->method = $manager->getMethod();

		return $this;
	}

	public function convertEventFields()
	{

	}

	public function getEvent()
	{
		return !empty($this->events)
			? $this->events[0]
			: [];
	}

	public function getMethod()
	{
		return $this->method;
	}
}