<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Result;

class OpenLineLiveChat extends EntityChat
{
	protected $entityMap = [
		'entityId' => [
			'connectorId',
			'lineId',
		],
		'entityData1' => [
			'readed',
			'readedId',
			'readedTime',
			'sessionId',
			'showForm',
			'welcomeFormNeeded',
			'welcomeFormSent'
		],
		'entityData2' => [],
		'entityData3' => [],
	];

	protected function validateAuthorId(int $authorId): Result
	{
		if ($authorId === 0)
		{
			return new Result();
		}

		return parent::validateAuthorId($authorId);
	}

	protected function sendMessageAuthorChange(\Bitrix\Im\V2\Entity\User\User $author): void
	{
		return;
	}

	protected function getDefaultEntityType(): string
	{
		return self::ENTITY_TYPE_LIVECHAT;
	}

	public function setEntityMap(array $entityMap): EntityChat
	{
		return $this;
	}

	public function setExtranet(?bool $extranet): \Bitrix\Im\V2\Chat
	{
		return $this;
	}

	public function getExtranet(): ?bool
	{
		return false;
	}

	protected function updateIndex(): \Bitrix\Im\V2\Chat
	{
		return $this;
	}

	protected function addIndex(): Chat
	{
		return $this;
	}
}
