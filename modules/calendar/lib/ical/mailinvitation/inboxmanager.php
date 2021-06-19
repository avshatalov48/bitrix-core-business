<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\ICal\Parser\Calendar;

class InboxManager
{
	/**
	 * @var string
	 */
	private $content;
	/**
	 * @var InboxAttachmentManager
	 */
	private $attachmentManager;

	/**
	 * @param string $content
	 * @return InboxManager
	 */
	public static function createInstance(string $content): InboxManager
	{
		return new self($content);
	}

	/**
	 * InboxManager constructor.
	 * @param string $content
	 */
	public function __construct(string $content)
	{
		$this->content = $content;
	}

	/**
	 * @return InboxManager
	 */
	public function parseContent(): InboxManager
	{
		$this->attachmentManager = InboxAttachmentManager::createInstance($this->content)
			->parse();

		return $this;
	}


	/**
	 * @return Calendar|null
	 */
	public function getComponent(): ?Calendar
	{
		return $this->attachmentManager->getComponent();
	}


}