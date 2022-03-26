<?php

namespace Bitrix\Mail\Item;

class Message extends Base
{

	private const FIELD_ID = 'ID';
	private const FIELD_MSG_ID = 'MSG_ID';
	private const FIELD_MAILBOX_ID = 'MAILBOX_ID';
	private const FIELD_SUBJECT = 'SUBJECT';
	private const FIELD_BODY = 'BODY';
	private const FIELD_BODY_HTML = 'BODY_HTML';
	private const FIELD_FROM = 'FIELD_FROM';
	private const FIELD_TO = 'FIELD_TO';
	private const FIELD_DATE = 'FIELD_DATE';

	/** @var int */
	private $id;
	/** @var string */
	private $msgId;
	/** @var int */
	private $mailboxId;
	/** @var string */
	private $subject;
	/** @var string */
	private $body;
	/** @var string */
	private $bodyHtml;
	/** @var string */
	private $from;
	/** @var string */
	private $to;
	/** @var \Bitrix\Main\Type\DateTime */
	private $date;

	protected function __construct(int $id, int $mailboxId)
	{
		$this->id = $id;
		$this->mailboxId = $mailboxId;
	}

	public static function fromArray(array $array): self
	{
		if (!isset($array[self::FIELD_ID], $array[self::FIELD_MAILBOX_ID]))
		{
			throw new \Bitrix\Main\SystemException('message field error');
		}

		$item = new self((int)$array[self::FIELD_ID], (int)$array[self::FIELD_MAILBOX_ID]);

		$item->msgId = $array[self::FIELD_MSG_ID] ?? '';
		$item->subject = $array[self::FIELD_SUBJECT] ?? '';
		$item->body = $array[self::FIELD_BODY] ?? '';
		$item->bodyHtml = $array[self::FIELD_BODY_HTML] ?? '';
		$item->from = $array[self::FIELD_FROM] ?? '';
		$item->to = $array[self::FIELD_TO] ?? '';
		$date = $array[self::FIELD_DATE] ?? '';
		$item->date = $date instanceof \Bitrix\Main\Type\DateTime
			? $date
			: new \Bitrix\Main\Type\DateTime();

		return $item;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	public function getMsgId(): string
	{
		return $this->msgId;
	}

	public function getMailboxId(): int
	{
		return $this->mailboxId;
	}

	/**
	 * @return string
	 */
	public function getFrom(): string
	{
		return $this->from;
	}

	/**
	 * @return string
	 */
	public function getTo(): string
	{
		return $this->to;
	}

	/**
	 * @return string
	 */
	public function getSubject(): string
	{
		return $this->subject;
	}

	public function getBody(): string
	{
		return $this->body;
	}

	public function getBodyHtml(): string
	{
		return $this->bodyHtml;
	}

	/**
	 * @return \Bitrix\Main\Type\DateTime
	 */
	public function getDate(): \Bitrix\Main\Type\DateTime
	{
		return $this->date;
	}

	public function toArray(): array
	{
		return [
			self::FIELD_ID => $this->getId(),
			self::FIELD_MSG_ID => $this->getMsgId(),
			self::FIELD_MAILBOX_ID => $this->getMailboxId(),
			self::FIELD_SUBJECT => $this->getSubject(),
			self::FIELD_BODY => $this->getBody(),
			self::FIELD_BODY_HTML => $this->getBodyHtml(),
			self::FIELD_FROM => $this->getFrom(),
			self::FIELD_TO => $this->getTo(),
			self::FIELD_DATE => $this->getDate()->format('Y-m-d H:i:s'),
		];
	}

}