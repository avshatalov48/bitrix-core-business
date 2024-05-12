<?php

namespace Bitrix\Calendar\Core\Event\Properties;

use Bitrix\Calendar\Core\Base\BaseProperty;
use Serializable;

class MeetingDescription extends BaseProperty implements Serializable
{
	/**
	 * @var ?int
	 */
	private ?int $meetingCreator = null;
	/**
	 * @var bool
	 */
	private bool $isNotify = false;
	/**
	 * @var bool
	 */
	private bool $reInvite = false;
	/**
	 * @var bool
	 */
	private bool $allowInvite = false;
	/**
	 * @var bool
	 */
	private bool $hideGuests = false;
	/**
	 * @var ?string
	 */
	private ?string $hostName = null;
	/**
	 * @var ?string
	 */
	private ?string $languageId = null;
	/**
	 * @var ?string
	 */
	private ?string $mailFrom = null;

	/**
	 * @var ?int
	 */
	private ?int $chatId = null;

	/**
	 * @return array
	 */
	public function getFields(): array
	{
		return [
			'NOTIFY' => $this->isNotify ?? null,
			'MEETING_CREATOR' => $this->meetingCreator ?? null,
			'REINVITE' => $this->reInvite ?? null,
			'ALLOW_INVITE' => $this->allowInvite ?? null,
			'HIDE_GUESTS' => $this->hideGuests ?? null,
			'HOST_NAME' => $this->hostName ?? null,
			'LANGUAGE_ID' => $this->languageId ?? null,
			'MAIL_FROM' => $this->mailFrom ?? null,
			'CHAT_ID' => $this->chatId ?? null,
		];
	}

	/**
	 * @return string
	 */
	public function toString(): string
	{
		return $this->serialize() ?? '';
	}

	public function __serialize()
	{
		return $this->serialize();
	}

	public function __unserialize($data): void
	{
		$this->unserialize($data);
	}

	/**
	 * @return string|null
	 */
	public function serialize(): ?string
	{
		return serialize($this->getFields());
	}

	/**
	 * @param $data
	 * @return void
	 */
	public function unserialize($data)
	{
		$unserializedData = unserialize($data, ['allowed_classes' => false]);

		$this->isNotify = $unserializedData['NOTIFY'] ?? false;
		$this->meetingCreator = $unserializedData['HOST_NAME'];
		$this->reInvite = $unserializedData['REINVITE'] ?? false;
		$this->allowInvite = $unserializedData['ALLOW_INVITE'] ?? false;
		$this->hideGuests =  $unserializedData['HIDE_GUESTS'] ?? false;
		$this->hostName = $unserializedData['MEETING_CREATOR'];
		$this->languageId = $unserializedData['LANGUAGE_ID'];
		$this->mailFrom = $unserializedData['MAIL_FROM'];
	}

	/**
	 * @param ?int $meetingCreator
	 *
	 * @return MeetingDescription
	 */
	public function setMeetingCreator(?int $meetingCreator): MeetingDescription
	{
		$this->meetingCreator = $meetingCreator;

		return $this;
	}

	/**
	 * @param bool $isNotify
	 * @return MeetingDescription
	 */
	public function setIsNotify(bool $isNotify): MeetingDescription
	{
		$this->isNotify = $isNotify;

		return $this;
	}

	/**
	 * @param mixed $reInvite
	 * @return MeetingDescription
	 */
	public function setReInvite(bool $reInvite): MeetingDescription
	{
		$this->reInvite = $reInvite;

		return $this;
	}

	/**
	 * @param mixed $allowInvite
	 * @return MeetingDescription
	 */
	public function setAllowInvite(bool $allowInvite): MeetingDescription
	{
		$this->allowInvite = $allowInvite;

		return $this;
	}

	public function getHideGuests(): bool
	{
		return $this->hideGuests;
	}

	/**
	 * @param mixed $hideGuests
	 * @return MeetingDescription
	 */
	public function setHideGuests(bool $hideGuests): MeetingDescription
	{
		$this->hideGuests = $hideGuests;

		return $this;
	}

	/**
	 * @param mixed $hostName
	 * @return MeetingDescription
	 */
	public function setHostName(?string $hostName): MeetingDescription
	{
		$this->hostName = $hostName;

		return $this;
	}

	/**
	 * @param string|null $languageId
	 *
	 * @return $this
	 */
	public function setLanguageId(?string $languageId): MeetingDescription
	{
		$this->languageId = $languageId;

		return $this;
	}

	/**
	 * @param string|null $mailFrom
	 *
	 * @return $this
	 */
	public function setMailFrom(?string $mailFrom): MeetingDescription
	{
		$this->mailFrom = $mailFrom;

		return $this;
	}

	/**
	 * @param int|null $chatId
	 *
	 * @return $this
	 */
	public function setChatId(?int $chatId): MeetingDescription
	{
		$this->chatId = $chatId;

		return $this;
	}
}
