<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\Model\ChatTable;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Entity\User\User;
use Bitrix\Im\V2\Result;
use Bitrix\Im\Model\EO_Chat;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Service\Locator;
use Bitrix\Main\Localization\Loc;

class FavoriteChat extends PrivateChat
{
	protected function getDefaultEntityType(): string
	{
		return self::ENTITY_TYPE_FAVORITE;
	}

	public function getCompanion(?int $userId = null): User
	{
		return User::getInstance($this->getAuthorId());
	}

	public function getDialogId(): ?string
	{
		if ($this->dialogId || !$this->getChatId())
		{
			return $this->dialogId;
		}

		return $this->getAuthorId();
	}

	public function getDialogContextId(): ?string
	{
		return $this->getAuthorId() . ':' .$this->getAuthorId();
	}

	/**
	 * @param int|array|EO_Chat $source
	 */
	public function load($source = null): Result
	{
		$chatId = -1;
		$authorId = -1;

		if (is_numeric($source))
		{
			$chatId = (int)$source;
		}
		elseif ($source instanceof EO_Chat)
		{
			$chatId = $source->getId();
			$authorId = $source->getAuthorId();
		}
		elseif (is_array($source))
		{
			$chatId = (int)$source['ID'];
			$authorId = (int)$source['AUTHOR_ID'];
		}

		if ($chatId <= 0)
		{
			$chat = $this->getFavoriteChat($authorId);
			if ($chat)
			{
				$source = $chat->getChatId();
			}
		}

		return parent::load($source);
	}

	/**
	 * @return Result
	 */
	public function save(): Result
	{
		$saveResult = parent::save();
		if (
			$saveResult->isSuccess()
			&& $this->getChatId()
		)
		{
			$authorId = $this->getAuthorId();
			if (!$authorId)
			{
				$authorId = $this->getContext()->getUserId();
			}
		}

		return $saveResult;
	}

	/**
	 * Looks for self-personal chat by its owner.
	 * @param array $params
	 * <pre>
	 * [
	 * 	(int) TO_USER_ID
	 * ]
	 * </pre>
	 * @return Result
	 */
	public static function find(array $params = [], ?Context $context = null): Result
	{
		$result = new Result;

		if (empty($params['TO_USER_ID']))
		{
			$context = $context ?? Locator::getContext();
			$params['TO_USER_ID'] = $context->getUserId();
		}

		if ($params['TO_USER_ID'] <= 0)
		{
			return $result->addError(new ChatError(ChatError::WRONG_RECIPIENT));
		}

		$row = ChatTable::query()
			->setSelect(['ID'])
			->where('TYPE', self::IM_TYPE_PRIVATE)
			->where('ENTITY_TYPE', self::ENTITY_TYPE_FAVORITE)
			->where('AUTHOR_ID', (int)$params['TO_USER_ID'])
			->fetch()
			;

		if ($row)
		{
			$result->setResult([
				'ID' => (int)$row['ID']
			]);
		}

		return $result;
	}

	//region Access & Permissions

	protected function checkAccessWithoutCaching(int $userId): bool
	{
		return $this->getAuthorId() === $userId;
	}

	//endregion

	/**
	 * @param array $params
	 * @param Context|null $context
	 * @return Result
	 */
	public function add(array $params, ?Context $context = null): Result
	{
		$result = new Result;

		$paramsResult = $this->prepareParams($params);
		if (!$paramsResult->isSuccess())
		{
			return $result->addErrors($paramsResult->getErrors());
		}

		$params = $paramsResult->getResult();

		$chat = $this->getFavoriteChat($params['AUTHOR_ID'] ?? null);

		if (!$chat)
		{
			$chat = new FavoriteChat($params);
			$chat
				->setTitle(Loc::getMessage('IM_CHAT_FAVORITE_TITLE_V2'))
				->setDescription(Loc::getMessage('IM_CHAT_FAVORITE_DESCRIPTION'))
				->save()
			;

			if (!$chat->getChatId())
			{
				return $result->addError(new ChatError(ChatError::CREATION_ERROR));
			}

			if ($chat->getAuthorId() > 0)
			{
				RelationTable::add([
					'CHAT_ID' => $chat->getChatId(),
					'MESSAGE_TYPE' => \IM_MESSAGE_PRIVATE,
					'USER_ID' => $chat->getAuthorId(),
					'STATUS' => \IM_STATUS_READ,
				]);
			}
		}

		$chat->updateIndex();

		$result->setResult([
			'CHAT_ID' => $chat->getChatId(),
			'CHAT' => $chat,
		]);

		return $result;
	}

	protected function prepareParams(array $params = []): Result
	{
		$result = new Result();

		if (!isset($params['AUTHOR_ID']))
		{
			if (isset($params['FROM_USER_ID']))
			{
				$params['AUTHOR_ID'] = (int)$params['FROM_USER_ID'];
			}
			else
			{
				$params['AUTHOR_ID'] = 0;
			}
		}

		if ($params['AUTHOR_ID'] <= 0)
		{
			return $result->addError(new ChatError(ChatError::WRONG_SENDER));
		}

		$result->setResult($params);

		return $result;
	}

	private function getFavoriteChat(?int $userId = null): ?FavoriteChat
	{
		if (!$userId)
		{
			$context = $context ?? Locator::getContext();
			$userId = $context->getUserId();
		}

		$chatResult = self::find(['TO_USER_ID' => $userId]);
		if (!$chatResult->isSuccess() || !$chatResult->hasResult())
		{
			return null;
		}

		$result = $chatResult->getResult();
		return FavoriteChat::getInstance($result['ID']);
	}
}
