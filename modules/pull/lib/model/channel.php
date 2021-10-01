<?php

namespace Bitrix\Pull\Model;

use Bitrix\Main\Type\DateTime;

class Channel
{
	protected $id;
	protected $userId;
	protected $privateId;
	protected $publicId;
	protected $type = \CPullChannel::TYPE_PRIVATE;
	protected $dateCreate;

	public static function createWithTag(string $tag)
	{
		$instance = new static();
		$instance->privateId = \CPullChannel::GetNewChannelIdByTag($tag);
		$instance->publicId = \CPullChannel::GetNewChannelIdByTag($tag,'public');
		$instance->dateCreate = new DateTime();

		return $instance;
	}

	public static function createRandom()
	{
		$instance = new static();
		$instance->privateId = \CPullChannel::GetNewChannelId();
		$instance->publicId = \CPullChannel::GetNewChannelId('public');
		$instance->dateCreate = new DateTime();

		return $instance;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUserId(): ?int
	{
		return $this->userId;
	}

	public function getPrivateId(): string
	{
		return $this->privateId;
	}

	public function getPublicId(): string
	{
		return $this->publicId;
	}

	public function getSignedPublicId(): string
	{
		return \CPullChannel::SignPublicChannel($this->publicId);
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getDateCreate(): DateTime
	{
		return $this->dateCreate;
	}
}