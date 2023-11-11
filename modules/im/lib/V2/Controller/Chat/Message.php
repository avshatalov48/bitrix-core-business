<?php

namespace Bitrix\Im\V2\Controller\Chat;

use Bitrix\Im\Recent;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Controller\Filter\CheckMessageDisappearingDuration;
use Bitrix\Im\V2\Controller\Filter\UpdateStatus;
use Bitrix\Im\V2\Entity\View\ViewCollection;
use Bitrix\Im\V2\Message\Delete\DisappearService;
use Bitrix\Im\V2\Message\Forward\ForwardService;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Message\PushFormat;
use Bitrix\Im\V2\Message\Reply\ReplyService;
use Bitrix\Im\V2\Message\Send\PushService;
use Bitrix\Im\V2\Message\Update\UpdateService;
use Bitrix\Im\V2\Message\Delete\DeleteService;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Message\MessageService;
use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\CurrentUser;

class Message extends BaseController
{
	protected const MAX_MESSAGES_COUNT = 100;
	protected const MAX_MESSAGES_COUNT_FOR_FORWARD = 20;
	protected const MESSAGE_ON_PAGE_COUNT = 50;

	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			\Bitrix\Im\V2\Message::class,
			'message',
			function ($className, int $id) {
				return $this->getMessageById($id);
			}
		);
	}

	public function getAutoWiredParameters()
	{
		return array_merge([
			new ExactParameter(
				MessageCollection::class,
				'messages',
				function($className, array $ids) {
					if (count($ids) > self::MAX_MESSAGES_COUNT)
					{
						$this->addError(new MessageError(MessageError::TOO_MANY_MESSAGES));

						return null;
					}
					$ids = array_map('intval', $ids);

					return new MessageCollection($ids);
				}
			),
		], parent::getAutoWiredParameters());
	}

	public function configureActions()
	{
		return [
			'disappear' => [
				'+prefilters' => [
					new CheckMessageDisappearingDuration(),
				]
			],
			'read' => [
				'+postfilters' => [
					new UpdateStatus(),
				],
			],
			'list' => [
				'+postfilters' => [
					new UpdateStatus(),
				],
			],
			'getContext' => [
				'+postfilters' => [
					new UpdateStatus(),
				],
			],
			'tail' => [
				'+postfilters' => [
					new UpdateStatus(),
				],
			],
		];
	}

	/**
	 * @restMethod im.v2.Chat.Message.read
	 */
	public function readAction(MessageCollection $messages): ?array
	{
		$readResult = Chat::getInstance($messages->getCommonChatId())->readMessages($messages);

		if (!$readResult->isSuccess())
		{
			$this->addErrors($readResult->getErrors());

			return null;
		}

		return $this->convertKeysToCamelCase($readResult->getResult());
	}

	/**
	 * @restMethod im.v2.Chat.Message.tailViewers
	 */
	public function tailViewersAction(\Bitrix\Im\V2\Message $message, array $filter = [], array $order = [], int $limit = 50): ?array
	{
		$viewFilter = [
			'LAST_ID' => isset($filter['lastId']) ? (int)$filter['lastId'] : null,
			'MESSAGE_ID' => $message->getId(),
		];
		$viewOrder = ['ID' => $order['id'] ?? 'ASC'];
		$viewLimit = $this->getLimit($limit);

		$views = ViewCollection::find($viewFilter, $viewOrder, $viewLimit);

		return $this->toRestFormat($views);
	}

	/**
	 * @restMethod im.v2.Chat.Message.mark
	 */
	public function markAction(\Bitrix\Im\V2\Message $message): ?array
	{
		$markResult = $message->mark();

		if (!$markResult->isSuccess())
		{
			$this->addErrors($markResult->getErrors());

			return null;
		}

		return [];
	}

	/**
	 * @restMethod im.v2.Chat.Message.list
	 */
	public function listAction(Chat $chat, int $limit = self::MESSAGE_ON_PAGE_COUNT): ?array
	{
		$messageService = new MessageService($chat->getLoadContextMessage());
		$messages = $messageService->getMessageContext($limit, \Bitrix\Im\V2\Message::REST_FIELDS)->getResult();

		return $messageService->fillContextPaginationData($this->toRestFormat($messages), $messages, $limit);
	}

	/**
	 * @restMethod im.v2.Chat.Message.getContext
	 */
	public function getContextAction(\Bitrix\Im\V2\Message $message, int $range = self::MESSAGE_ON_PAGE_COUNT): ?array
	{
		$messageService = new MessageService($message);
		$messages = $messageService->getMessageContext($range, \Bitrix\Im\V2\Message::REST_FIELDS)->getResult();

		return $messageService->fillContextPaginationData($this->toRestFormat($messages), $messages, $range);
	}

	/**
	 * @restMethod im.v2.Chat.Message.tail
	 */
	public function tailAction(Chat $chat, array $filter = [], array $order = [], int $limit = 50): ?array
	{
		[$messageFilter, $messageOrder] = $this->prepareParamsForTail($chat, $filter, $order);

		return $this->getMessages($messageFilter, $messageOrder, $limit);
	}

	/**
	 * @restMethod im.v2.Chat.Message.search
	 */
	public function searchAction(Chat $chat, array $filter = [], array $order = [], int $limit = 50): ?array
	{
		[$messageFilter, $messageOrder] = $this->prepareParamsForSearch($chat, $filter, $order);

		return $this->getMessages($messageFilter, $messageOrder, $limit);
	}

	/**
	 * @restMethod im.v2.Chat.Message.pin
	 */
	public function pinAction(\Bitrix\Im\V2\Message $message): ?array
	{
		$pinResult = $message->pin();

		if (!$pinResult->isSuccess())
		{
			$this->addErrors($pinResult->getErrors());

			return null;
		}

		return [];
	}

	/**
	 * @restMethod im.v2.Chat.Message.unpin
	 */
	public function unpinAction(\Bitrix\Im\V2\Message $message): ?array
	{
		$unpinResult = $message->unpin();

		if (!$unpinResult->isSuccess())
		{
			$this->addErrors($unpinResult->getErrors());

			return null;
		}

		return [];
	}

	/**
	 * @restMethod im.v2.Chat.Message.forward
	 */
	public function forwardAction(Chat $chat, MessageCollection $messages, ?string $comment = null): ?array
	{
		if ($messages->count() > self::MAX_MESSAGES_COUNT_FOR_FORWARD)
		{
			$this->addError(new MessageError(MessageError::TOO_MANY_MESSAGES));

			return null;
		}

		$service = new ForwardService($chat);
		$result = $service->createMessages($messages, $comment);

		if (!$result->hasResult())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return $this->toRestFormat($result->getResult());
	}

	/**
	 * @restMethod im.v2.Chat.Message.delete
	 */
	public function deleteAction(\Bitrix\Im\V2\Message $message): ?bool
	{
		$service = new DeleteService($message);
		$service->setMode(DeleteService::MODE_AUTO);
		$result = $service->delete();

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return true;
	}

	/**
	 * @restMethod im.v2.Chat.Message.disappear
	 */
	public function disappearAction(\Bitrix\Im\V2\Message $message, int $hours): ?bool
	{
		$deleteService = new DeleteService($message);
		if ($deleteService->canDelete() < DeleteService::DELETE_HARD)
		{
			$this->addError(new MessageError(MessageError::MESSAGE_ACCESS_ERROR));

			return null;
		}

		$result = DisappearService::disappearMessage($message, $hours);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return true;
	}

	/**
	 * @restMethod im.v2.Chat.Message.update
	 */
	public function updateAction(
		\Bitrix\Im\V2\Message $message,
		string $text,
		string $urlPreview = 'Y'
	): ?bool
	{
		$result = (new UpdateService($message))
			->setUrlPreview($this->convertCharToBool($urlPreview))
			->update($text);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return true;
	}

	/**
	 * @restMethod im.v2.Chat.Message.inform
	 */
	public function informAction(
		\Bitrix\Im\V2\Message $message
	): ?array
	{
		$chat = $message->getChat();
		if (!($chat instanceof Chat\PrivateChat))
		{
			$this->addError(new Chat\ChatError(Chat\ChatError::WRONG_TYPE));

			return null;
		}

		$message->markAsImportant(true);

		$result = (new PushFormat())->validateDataForInform($message, $chat);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$pushService = new \Bitrix\Im\V2\Message\Inform\PushService();
		$pushService->sendInformPushPrivateChat($chat, $message);

		return ['result' => true];
	}

	/**
	 * @restMethod im.v2.Chat.Message.deleteRichUrl
	 */
	public function deleteRichUrlAction(\Bitrix\Im\V2\Message $message, CurrentUser $user): ?array
	{
		if ((int)$user->getId() !== $message->getAuthorId())
		{
			$this->addError(new MessageError(MessageError::WRONG_SENDER));

			return null;
		}

		(new \Bitrix\Im\V2\Message\Attach\AttachService())->deleteRichUrl($message);

		return ['result' => true];
	}

	protected function prepareParamsForTail(Chat $chat, array $filter, array $order): array
	{
		$messageFilter = [];
		$messageOrder = [];

		if (isset($order['id']))
		{
			$messageOrder['ID'] = strtoupper($order['id']);
		}

		if (isset($filter['lastId']))
		{
			$messageFilter['LAST_ID'] = (int)$filter['lastId'];
		}

		$messageFilter['START_ID'] = $chat->getStartId();
		$messageFilter['CHAT_ID'] = $chat->getChatId();

		return [$messageFilter, $messageOrder];
	}

	protected function prepareParamsForSearch(Chat $chat, array $filter, array $order): array
	{
		[$messageFilter, $messageOrder] = $this->prepareParamsForTail($chat, $filter, $order);

		if (isset($filter['searchMessage']) && is_string($filter['searchMessage']))
		{
			$messageFilter['SEARCH_MESSAGE'] = trim($filter['searchMessage']);
		}

		return [$messageFilter, $messageOrder];
	}

	protected function getMessages(array $filter, array $order, int $limit): array
	{
		$messages = MessageCollection::find(
			$filter,
			$order,
			$this->getLimit($limit),
			null,
			\Bitrix\Im\V2\Message::REST_FIELDS
		);

		$rest = $this->toRestFormat($messages);
		//todo: refactor. Change to popup data.
		$rest['hasNextPage'] = $messages->count() >= $limit;

		return $rest;
	}
}