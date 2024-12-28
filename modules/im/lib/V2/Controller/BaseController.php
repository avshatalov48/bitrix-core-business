<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\Dialog;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\CommentChat;
use Bitrix\Im\V2\Controller\Filter\ActionUuidHandler;
use Bitrix\Im\V2\Controller\Filter\AuthorizationPrefilter;
use Bitrix\Im\V2\Controller\Filter\AutoJoinToChat;
use Bitrix\Im\V2\Controller\Filter\CheckChatAccess;
use Bitrix\Im\V2\Controller\Filter\SameChatMessageFilter;
use Bitrix\Im\V2\Controller\Filter\UpdateStatus;
use Bitrix\Im\V2\Link\Pin\PinCollection;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Message\MessageService;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Rest\RestAdapter;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\ActionFilter\CloseSession;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\ParameterDictionary;

abstract class BaseController extends Controller
{
	protected const MAX_LIMIT = 200;
	protected const DEFAULT_LIMIT = 50;
	protected const MAX_MESSAGES_COUNT = 100;

	public function getAutoWiredParameters()
	{
		return [
			new ExactParameter(
				Chat::class,
				'chat',
				function($className, string $dialogId) {
					$chatId = Dialog::getChatId($dialogId);

					return Chat::getInstance((int)$chatId);
				}
			),
			new ExactParameter(
				Chat::class,
				'chat',
				function($className, int $chatId) {
					return Chat::getInstance($chatId);
				}
			),
			new ExactParameter(
				Message::class,
				'message',
				function ($className, int $messageId) {
					return new Message($messageId);
				}
			),
			new ExactParameter(
				MessageCollection::class,
				'messages',
				function ($className, array $messageIds) {
					return $this->getMessagesByIds($messageIds);
				}
			),
			new ExactParameter(
				Message::class,
				'message',
				function ($className, int $commentChatId) {
					return new Message(\Bitrix\Im\V2\Chat::getInstance($commentChatId)->getParentMessageId());
				}
			),
			new ExactParameter(
				\Bitrix\Im\V2\Chat::class,
				'chat',
				function($className, int $postId, string $createIfNotExists = 'N') {
					return $this->getChatByPostId($postId, $createIfNotExists === 'Y');
				}
			),
		];
	}

	protected function getDefaultPreFilters()
	{
		return array_merge(
			[
				new AuthorizationPrefilter(),
				new UpdateStatus(),
			],
			parent::getDefaultPreFilters(),
			[
				new CloseSession(true),
				new SameChatMessageFilter(),
				new CheckChatAccess(),
				new ActionUuidHandler(),
				new AutoJoinToChat(),
			]
		);
	}

	protected function getLimit(int $limit): int
	{
		return $limit > 0 && $limit <= static::MAX_LIMIT ? $limit : static::DEFAULT_LIMIT;
	}

	protected function toRestFormat(RestConvertible ...$entities): array
	{
		return (new RestAdapter(...$entities))->toRestFormat();
	}

	protected function load(\Bitrix\Im\V2\Chat $chat, int $messageLimit, int $pinLimit, bool $ignoreMark = false, ?Message $targetMessage = null): array
	{
		$messageLimit = $this->getLimit($messageLimit);
		$pinLimit = $this->getLimit($pinLimit);
		$messageService = new MessageService($targetMessage ?? $chat->getLoadContextMessage($ignoreMark));
		$messages = $messageService->getMessageContext($messageLimit, Message::REST_FIELDS)->getResult();
		$pins = PinCollection::find(
			['CHAT_ID' => $chat->getChatId(), 'START_ID' => $chat->getStartId() ?: null],
			['ID' => 'DESC'],
			$pinLimit
		);
		$restAdapter = new RestAdapter($chat, $messages, $pins);

		$rest = $restAdapter->toRestFormat();

		return $messageService->fillContextPaginationData($rest, $messages, $messageLimit);
	}

	protected function toRestFormatWithPaginationData(array $entities, int $needCount, int $realCount): array
	{
		$rest = (new RestAdapter(...$entities))->toRestFormat();
		$rest['hasNextPage'] = $realCount >= $needCount;

		return $rest;
	}

	public static function recursiveWhiteList($fields, $whiteList, bool $sanitizeOnly = false)
	{
		$data = [];
		$converter = new Converter(Converter::TO_SNAKE | Converter::TO_UPPER);
		foreach ($fields as $field => $value)
		{
			if (is_array($value))
			{
				$data[$converter->process($field)] = self::recursiveWhiteList($value, $whiteList[$field], true);
			}
			elseif ((is_array($whiteList) && in_array($field, $whiteList)) || $sanitizeOnly)
			{
				$data[$converter->process($field)] = $value;
			}
		}

		return $data;
	}

	//todo: think about recursion in method.
	protected function checkWhiteList(array $fields, array $whiteList): array
	{
		$filteredFields = [];

		foreach ($whiteList as $allowedField)
		{
			if (isset($fields[$allowedField]))
			{
				$filteredFields[$allowedField] = $fields[$allowedField];
			}
		}

		return $filteredFields;
	}

	protected function getChatByPostId(int $postId, bool $createIfNotExists): ?Chat
	{
		$message = new Message($postId);

		if (!$message->checkAccess()->isSuccess())
		{
			$this->addError(new MessageError(MessageError::ACCESS_DENIED));

			return null;
		}

		$result = CommentChat::get($message, $createIfNotExists);

		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());

			return null;
		}

		return $result->getResult();
	}

	protected function getMessagesByIds(array $ids): ?MessageCollection
	{
		if (count($ids) > static::MAX_MESSAGES_COUNT)
		{
			$this->addError(new MessageError(MessageError::TOO_MANY_MESSAGES));

			return null;
		}
		$ids = array_map('intval', $ids);

		return new MessageCollection($ids);
	}

	protected function convertCharToBool(string $char, bool $default = false): bool
	{
		if ($char === 'Y')
		{
			return true;
		}
		if ($char === 'N')
		{
			return false;
		}

		return $default;
	}

	protected function getRawValue(string $key)
	{
		return $this->prepareRawValue($this->request->getPostList(), $key)
			?? $this->prepareRawValue($this->request->getQueryList(), $key)
			?? null
		;
	}

	private function prepareRawValue(ParameterDictionary $list, string $key)
	{
		$rawData = $list->toArrayRaw();
		if (isset($rawData[$key]))
		{
			return $rawData[$key];
		}

		$data  = $list->toArray();

		return $data[$key] ?? null;
	}

	protected function prepareFields(array $fields, array $whiteList): array
	{
		$converter = new Converter(Converter::TO_SNAKE | Converter::TO_UPPER | Converter::KEYS);
		$fields = $converter->process($fields);

		return  $this->checkWhiteList($fields, $whiteList);
	}
}