<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\ICal\Parser\Calendar;
use Bitrix\Calendar\ICal\Parser\Parser;

class InboxAttachmentManager
{
	/**
	 * @var string|null
	 */
	private $fileContent;

	/**
	 * @var Parser
	 */
	private $parser;

	/**
	 * @param string $fileContent
	 * @return InboxAttachmentManager
	 */
	public static function createInstance(string $fileContent): InboxAttachmentManager
	{
		return new self($fileContent);
	}

	/**
	 * InboxAttachmentManager constructor.
	 * @param string $fileContent
	 */
	public function __construct(string $fileContent)
	{
		$this->fileContent = $fileContent;
	}

	/**
	 * @return $this
	 */
	public function parse(): InboxAttachmentManager
	{
		$this->parser = Parser::createInstance($this->fileContent)
			->parse();

		return $this;
	}

	/**
	 * @return Calendar|null
	 */
	public function getComponent(): ?Calendar
	{
		return $this->parser->getCalendarComponent();
	}
}