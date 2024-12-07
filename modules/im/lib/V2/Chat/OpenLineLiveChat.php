<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\V2\Chat;

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

	protected function sendMessageAuthorChange(\Bitrix\Im\V2\Entity\User\User $author): void
	{
		return;
	}

	protected function needToSendMessageUserDelete(): bool
	{
		return true;
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