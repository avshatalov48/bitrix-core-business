<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\Dialog;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Controller\Filter\ActionUuidHandler;
use Bitrix\Im\V2\Controller\Filter\AuthorizationPrefilter;
use Bitrix\Im\V2\Controller\Filter\CheckChatAccess;
use Bitrix\Im\V2\Controller\Filter\SameChatMessageFilter;
use Bitrix\Im\V2\Controller\Filter\StartIdFilter;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\MessageError;
use Bitrix\Im\V2\Rest\RestAdapter;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\ParameterDictionary;

abstract class BaseController extends Controller
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
			[
				new AuthorizationPrefilter(),
			],
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

	protected function toRestFormat(RestConvertible ...$entities): array
	{
		return (new RestAdapter(...$entities))->toRestFormat();
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
			if(Application::getInstance()->isUtfMode())
			{
				return $rawData[$key];
			}

			return Encoding::convertEncoding($rawData[$key], 'UTF-8', SITE_CHARSET);
		}

		$data  = $list->toArray();

		return $data[$key] ?? null;
	}
}