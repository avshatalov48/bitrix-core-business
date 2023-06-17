<?php

namespace Bitrix\Im\V2\Link\Pin;

use Bitrix\Im\Dialog;
use Bitrix\Im\Model\LinkPinTable;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Error;
use Bitrix\Im\V2\Link\Push;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;

class PinService
{
	use ContextCustomer;

	public const ADD_PIN_EVENT = 'pinAdd';
	public const DELETE_PIN_EVENT = 'pinDelete';

	public function pinMessage(Message $message): Result
	{
		$result = new  Result();

		$pin = PinItem::createFromMessage($message, $this->getContext());
		$saveResult = $this->savePin($pin);

		if (!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		$saveParamResult = $this->saveInParam($message);

		if (!$saveParamResult->isSuccess())
		{
			return $result->addErrors($saveParamResult->getErrors());
		}

		$this->sendMessageAboutPin($pin);

		Push::getInstance()
			->setContext($this->context)
			->sendFull($pin, static::ADD_PIN_EVENT, ['CHAT_ID' => $pin->getChatId()])
		;

		return $result;
	}

	public function unpinMessage(Message $message): Result
	{
		$result = new Result();

		$pin = PinItem::getByMessage($message);

		if ($pin === null)
		{
			return $result;
		}

		$deleteResult = $pin->delete();

		if (!$deleteResult->isSuccess())
		{
			return $result->addErrors($deleteResult->getErrors());
		}

		$deleteParamResult = $this->deleteFromParam($message);

		if (!$deleteParamResult->isSuccess())
		{
			return $result->addErrors($deleteParamResult->getErrors());
		}

		Push::getInstance()
			->setContext($this->context)
			->sendIdOnly($pin, static::DELETE_PIN_EVENT, ['CHAT_ID' => $pin->getChatId()])
		;

		return $result;
	}

	public function getCount(int $chatId, ?int $startId = null): int
	{
		$filter = Query::filter()->where('CHAT_ID', $chatId);

		if (isset($startId) && $startId > 0)
		{
			$filter->where('MESSAGE_ID', '>=', $startId);
		}

		return LinkPinTable::getCount($filter);
	}

	protected function savePin(PinItem $pin): Result
	{
		try
		{
			return $pin->save();
		}
		catch (\Bitrix\Main\SystemException $exception)
		{
			return (new Result())->addError(new Message\MessageError(Message\MessageError::MESSAGE_IS_ALREADY_PIN));
		}
	}

	protected function saveInParam(Message $message): Result
	{
		//todo replace this with new api
		\CIMMessageParam::Set($message->getMessageId(), ['IS_PINNED' => 'Y']);
		\CIMMessageParam::SendPull($message->getMessageId(), ['IS_PINNED']);

		return new Result();
	}

	protected function deleteFromParam(Message $message): Result
	{
		//todo replace this with new api
		\CIMMessageParam::Set($message->getMessageId(), ['IS_PINNED' => 'N']);
		\CIMMessageParam::SendPull($message->getMessageId(), ['IS_PINNED']);

		return new Result();
	}

	protected function sendMessageAboutPin(PinItem $pin): Result
	{
		//todo: Replace with new API
		$dialogId = Dialog::getDialogId($pin->getChatId());
		$authorId = $this->getContext()->getUserId();

		$messageId = \CIMChat::AddMessage([
			'DIALOG_ID' => $dialogId,
			'SYSTEM' => 'Y',
			'MESSAGE' => $this->getMessageText($pin),
			'FROM_USER_ID' => $authorId,
			'PARAMS' => [
				'CLASS' => 'bx-messenger-content-item-system',
				'BETA' => 'Y'
			],
			'URL_PREVIEW' => 'N',
			'SKIP_CONNECTOR' => 'Y',
			'SKIP_COMMAND' => 'Y',
			'SILENT_CONNECTOR' => 'Y',
		]);

		$result = new Result();

		if ($messageId === false)
		{
			return $result->addError(new Error(''));
		}

		return $result;
	}

	protected function getMessageText(PinItem $pin): string
	{
		$genderModifier = ($this->getContext()->getUser()->getGender() === 'F') ? '_F' : '';
		$text = (new Message($pin->getMessageId()))->getQuotedMessage() . "\n";
		$text .= Loc::getMessage(
			'IM_CHAT_PIN_ADD_NOTIFICATION' . $genderModifier,
			[
				'#MESSAGE_ID#' => $pin->getMessageId(),
				'#USER_ID#' => $this->getContext()->getUserId(),
				'#DIALOG_ID#' => Chat::getInstance($pin->getChatId())->getDialogContextId(),
			]
		);

		return $text;
	}
}