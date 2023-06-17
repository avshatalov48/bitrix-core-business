<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\Dialog;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Controller\Filter\ActionUuidHandler;
use Bitrix\Im\V2\Controller\Filter\CheckChatAccess;
use Bitrix\Im\V2\Controller\Filter\SameChatMessageFilter;
use Bitrix\Im\V2\Controller\Filter\StartIdFilter;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Rest\RestAdapter;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\Converter;

class BaseController extends Controller
{
	protected const MAX_LIMIT = 200;
	protected const DEFAULT_LIMIT = 50;

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
					return $this->getMessageById($messageId);
				}
			),
		];
	}

	protected function getDefaultPreFilters()
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new SameChatMessageFilter(),
				new StartIdFilter(),
				new CheckChatAccess(),
				new ActionUuidHandler(),
			]
		);
	}

	protected function getLimit(int $limit): int
	{
		return $limit > 0 && $limit <= static::MAX_LIMIT ? $limit : static::DEFAULT_LIMIT;
	}

	protected function toRestFormat(RestConvertible $entity): array
	{
		return (new RestAdapter($entity))->toRestFormat();
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

	protected function getMessageById(int $id): ?Message
	{
		$message = new \Bitrix\Im\V2\Message($id);

		if ($message->getMessageId() === null)
		{
			$this->addError(new MessageError(MessageError::MESSAGE_NOT_FOUND));

			return null;
		}

		return $message;
	}
}
