<?php

namespace Bitrix\Pull\Model;

use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

class Channel
{
	protected $id;
	protected $userId;
	protected $privateId;
	protected $publicId;
	protected $type = \CPullChannel::TYPE_PRIVATE;
	protected $dateCreate;

	public static function createWithTag(string $tag): Channel
	{
		$instance = new static();
		$instance->privateId = \CPullChannel::GetNewChannelIdByTag($tag);
		$instance->publicId = \CPullChannel::GetNewChannelIdByTag($tag,'public');
		$instance->dateCreate = new DateTime();

		return $instance;
	}

	public static function createRandom(): Channel
	{
		$instance = new static();
		$instance->privateId = \CPullChannel::GetNewChannelId();
		$instance->publicId = \CPullChannel::GetNewChannelId('public');
		$instance->dateCreate = new DateTime();

		return $instance;
	}

	/**
	 * Returns Channel instance, suitable for sending to the shared channel.
	 * @return Channel
	 * @throws SystemException
	 */
	public static function getShared(): Channel
	{
		$fields = \CPullChannel::GetShared();
		if (!$fields)
		{
			throw new SystemException("Public channel is empty");
		}

		return static::createWithFields($fields);
	}

	public static function createWithFields(array $fields): Channel
	{
		$instance = new static();
		if (isset($fields['CHANNEL_ID']))
		{
			$instance->privateId = $fields['CHANNEL_ID'];
		}
		if (isset($fields['CHANNEL_PUBLIC_ID']))
		{
			$instance->publicId = $fields['CHANNEL_PUBLIC_ID'];
		}
		if (isset($fields['CHANNEL_TYPE']))
		{
			$instance->type = $fields['CHANNEL_TYPE'];
		}
		if (isset($fields['CHANNEL_DT']))
		{
			$instance->dateCreate = DateTime::createFromTimestamp($fields['CHANNEL_DT']);
		}

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