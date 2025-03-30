<?php

namespace Bitrix\Im\V2\Controller\Chat;

use Bitrix\Im\V2\Analytics\MessageAnalytics;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Controller\Filter\CheckActionAccess;
use Bitrix\Im\V2\Entity\View\ViewCollection;
use Bitrix\Im\V2\Message\Delete\DisappearService;
use Bitrix\Im\V2\Message\Forward\ForwardService;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Message\PushFormat;
use Bitrix\Im\V2\Message\Send\SendingService;
use Bitrix\Im\V2\Message\Update\UpdateService;
use Bitrix\Im\V2\Message\Delete\DeleteService;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Message\MessageService;
use Bitrix\Im\V2\Permission\Action;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\CurrentUser;

class Message extends BaseController
{
	protected const MAX_MESSAGES_COUNT = 100;
	protected const MESSAGE_ON_PAGE_COUNT = 50;
	private const ALLOWED_FIELDS_UPDATE = [
		'MESSAGE',
		'ATTACH',
		'KEYBOARD',
		'MENU',
	];
	private const ALLOWED_FIELDS_SEND = [
		'MESSAGE',
		'ATTACH',
		'SYSTEM',
		'KEYBOARD',
		'MENU',
		'URL_PREVIEW',
		'SKIP_CONNECTOR',
		'TEMPLATE_ID',
		'REPLY_ID',
		'BOT_ID',
		'COPILOT',
		'SILENT_CONNECTOR',
	];

	public function getPrimaryAutoWiredParameter()
	{
		return new ExactParameter(
			\Bitrix\Im\V2\Message::class,
			'message',
			function ($className, int $id) {
				return new \Bitrix\Im\V2\Message($id);
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
					return $this->getMessagesByIds($ids);
				}
			),

			new ExactParameter(
				MessageCollection::class,
				'forwardMessages',
				function($className, array $fields) {
					$forwardIds = $fields['forwardIds'] ?? [];

					if (empty($forwardIds))
					{
						return null;
					}

					if (count($forwardIds) > self::MAX_MESSAGES_COUNT)
					{
						$this->addError(new MessageError(MessageError::TOO_MANY_MESSAGES));

						return null;
					}

					$forwardIds = array_map('intval', $forwardIds);
					$messageCollection = new MessageCollection($forwardIds);
					foreach ($messageCollection as $message)
					{
						$messageId = $message->getId();

						$uuid = array_search($messageId, $forwardIds, true);
						if ($uuid)
						{
							$message->setForwardUuid($uuid);

							if ($message->getForwardUuid() === null)
							{
								$this->addError(new MessageError(MessageError::WRONG_UUID));

								return null;
							}
						}
					}

					return $messageCollection;
				}
			),
		], parent::getAutoWiredParameters());
	}

	public function configureActions()
	{
		return [
			'send' => [
				'+prefilters' => [
					new CheckActionAccess(Action::Send),
				],
			],
			'pin' => [
				'+prefilters' => [
					new CheckActionAccess(Action::PinMessage),
				],
			],
			'unpin' => [
				'+prefilters' => [
					new CheckActionAccess(Action::PinMessage),
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
	public function listAction(Chat $chat, int $limit = self::MESSAGE_ON_PAGE_COUNT, string $ignoreMark = 'N'): ?array
	{
		$messageService = new MessageService($chat->getLoadContextMessage($this->convertCharToBool($ignoreMark)));
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
			$this->addError(new MessageError(MessageError::ACCESS_DENIED));

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
	 * @restMethod im.v2.Chat.Message.send
	 */
	public function sendAction(
		Chat $chat,
		?\CRestServer $restServer = null,
		array $fields = [],
		?MessageCollection $forwardMessages = null
	): ?array
	{
		if (!empty($this->getErrors()))
		{
			return null;
		}

		$fields['message'] = $this->getRawValue('fields')['message'] ?? $fields['message'] ?? null;
		$fields = $this->prepareFields($fields, self::ALLOWED_FIELDS_SEND);
		$result = (new SendingService())->prepareFields($chat, $fields, $forwardMessages, $restServer);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$fields = $result->getResult();
		$fields['SKIP_USER_CHECK'] = 'Y';
		$fields['WAIT_FULL_EXECUTION'] = 'N';

		$messageId = \CIMMessenger::Add($fields);

		if (isset($forwardMessages) && $forwardMessages->count() > 0)
		{
			$forwardResult = $this->sendForwardMessages($chat, $forwardMessages);
			if (!$forwardResult->isSuccess())
			{
				$this->addErrors($forwardResult->getErrors());

				return null;
			}

			foreach ($forwardMessages as $message)
			{
				(new MessageAnalytics($message))->addShareMessage();
			}
		}

		if ($messageId === false && !isset($forwardResult))
		{
			$this->addError(new MessageError(MessageError::SENDING_FAILED));

			return null;
		}

		return [
			'id' => $messageId ?: null,
			'uuidMap' => isset($forwardResult) ? $forwardResult->getResult() : []
		];
	}

	/**
	 * @restMethod im.v2.Chat.Message.update
	 */
	public function updateAction(
		\Bitrix\Im\V2\Message $message,
		array $fields = [],
		string $urlPreview = 'Y',
		int $botId = 0
	): ?bool
	{
		$fields['message'] = $this->getRawValue('fields')['message'] ?? $fields['message'] ?? null;
		$fields = $this->prepareFields($fields, self::ALLOWED_FIELDS_UPDATE);
		$message->setBotId($botId);
		$result = (new UpdateService($message))
			->setUrlPreview($this->convertCharToBool($urlPreview))
			->update($fields)
		;

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
			$this->addError(new Chat\ChatError(Chat\ChatError::WRONG_CHAT_TYPE));

			return null;
		}

		$message->markAsImportant(true);

		$result = (new PushFormat($message))->validateDataForInform();
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		$pushService = new \Bitrix\Im\V2\Message\Inform\PushService();
		$pushService->sendInformPushPrivateChat($message);

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
		$wasFilteredByTariffRestrictions = $rest['tariffRestrictions']['isHistoryLimitExceeded'] ?? false;
		//todo: refactor. Change to popup data.
		$rest['hasNextPage'] = !$wasFilteredByTariffRestrictions && $messages->count() >= $limit;

		return $rest;
	}

	private function sendForwardMessages(Chat $chat, MessageCollection $messages): Result
	{
		$result = new Result();

		if ($messages->count() > MessageService::getMultipleActionMessageLimit())
		{
			return $result->addError(new MessageError(MessageError::TOO_MANY_MESSAGES));
		}

		$service = new ForwardService($chat);
		$result = $service->createMessages($messages);

		if (!$result->hasResult())
		{
			return $result->addErrors($result->getErrors());
		}

		return $result;
	}
}
