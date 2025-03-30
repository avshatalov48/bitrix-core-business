<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Entity\User\NullUser;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\Send\PushService;
use Bitrix\Im\V2\Message\Send\SendingConfig;
use Bitrix\Im\V2\Message\Send\SendResult;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Permission\Action;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Im\V2\Result;

class NullChat extends Chat
{
	private $preparedParams = [];

	protected function getDefaultType(): string
	{
		return '';
	}

	public function setPreparedParams(array $params): self
	{
		$this->preparedParams = $params;

		return $this;
	}

	public function getAuthor(): User
	{
		return new NullUser();
	}

	protected function checkAccessInternal(int $userId): Result
	{
		return (new Result())->addError(new ChatError(ChatError::ACCESS_DENIED));
	}

	public function getStartId(?int $userId = null): int
	{
		return 0;
	}

	public function isExist(): bool
	{
		return false;
	}

	/**
	 * Allows to send mention notification.
	 * @return bool
	 */
	public function allowMention(): bool
	{
		return false;
	}

	public function getRelations(): RelationCollection
	{
		return new RelationCollection();
	}

	public function fillActual(array $fieldsToFill): self
	{
		return $this;
	}

	public function readMessages(?MessageCollection $messages, bool $byEvent = false): Result
	{
		return new Result();
	}

	public function getSelfRelation(): ?Relation
	{
		return null;
	}

	public function createChatIfNotExists(array $params): self
	{
		$params = array_merge($this->preparedParams, $params);

		$addResult = ChatFactory::getInstance()->addChat($params);
		if (!$addResult->isSuccess() || !$addResult->hasResult())
		{
			return $this;
		}

		return $addResult->getResult()['CHAT'];
	}

	/**
	 * Does nothing.
	 * @inheritdoc
	 */
	public function sendMessage($message, $sendingConfig = null): SendResult
	{
		return (new SendResult())->addError(new ChatError(ChatError::WRONG_TARGET_CHAT));
	}

	/**
	 * Does nothing.
	 * @inheritdoc
	 */
	public function validateMessage(Message $message, SendingConfig $sendingServiceConfig): Result
	{
		return (new Result)->addError(new ChatError(ChatError::WRONG_TARGET_CHAT));
	}

	/**
	 * Does nothing.
	 * @inheritdoc
	 */
	public function save(): Result
	{
		return (new Result)->addError(new ChatError(ChatError::CREATION_ERROR));
	}

	/**
	 * Does nothing.
	 * @inheritdoc
	 */
	public function updateMessage(Message $message): Result
	{
		return (new Result)->addError(new ChatError(ChatError::CREATION_ERROR));
	}

	/**
	 * Does nothing.
	 * @inheritdoc
	 */
	public function deleteMessage(Message $message): Result
	{
		return (new Result)->addError(new ChatError(ChatError::CREATION_ERROR));
	}

	protected function addIndex(): Chat
	{
		return $this;
	}

	protected function updateIndex(): Chat
	{
		return $this;
	}

	protected function getPushService(Message $message, SendingConfig $config): PushService
	{
		return new Message\Send\Push\GroupPushService($message, $config);
	}

	public function canDo(Action $action, mixed $target = null): bool
	{
		return false;
	}
}