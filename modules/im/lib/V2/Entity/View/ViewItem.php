<?php

namespace Bitrix\Im\V2\Entity\View;

use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Main\Type\DateTime;

class ViewItem implements RestEntity, PopupDataAggregatable
{
	use ContextCustomer;

	protected int $id;
	protected int $messageId;
	protected DateTime $dateView;
	protected int $userId;

	public function __construct(int $id, int $messageId, int $userId, DateTime $dateView)
	{
		$this->id = $id;
		$this->messageId = $messageId;
		$this->userId = $userId;
		$this->dateView = $dateView;
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		return (new PopupData([new UserPopupItem([$this->userId])], $excludedList));
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getMessageId(): int
	{
		return $this->messageId;
	}

	public function getDateView(): DateTime
	{
		return $this->dateView;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public static function getRestEntityName(): string
	{
		return 'view';
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'id' => $this->getId(),
			'messageId' => $this->getMessageId(),
			'userId' => $this->getUserId(),
			'dateView' => $this->getDateView(),
		];
	}
}