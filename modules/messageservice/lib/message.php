<?php
namespace Bitrix\MessageService;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Error;
use Bitrix\Main;
use Bitrix\Main\Type\DateTime;
use Bitrix\MessageService\Integration\Pull;
use Bitrix\MessageService\Internal\Entity\MessageTable;
use Bitrix\MessageService\Sender\Result;

Loc::loadMessages(__FILE__);

class Message
{
	public const EVENT_MESSAGE_UPDATED = 'messageUpdated';

	protected $id;

	/** @var Sender\Base $sender */
	protected $sender;

	/** @var  string $type */
	protected $type;
	/** @var  int $authorId */
	protected $authorId = 0;
	/** @var  string $from */
	protected $from;
	/** @var  string $to */
	protected $to;
	/** @var  array $headers */
	protected $headers = array();
	/** @var  string $body */
	protected $body;

	protected $statusId;
	protected $externalStatus;

	/**
	 * Message constructor.
	 * @param Sender\Base|null $sender
	 */
	public function __construct(Sender\Base $sender = null)
	{
		if ($sender)
		{
			$this->setSender($sender);
		}
	}

	public static function loadById(int $id): ?Message
	{
		$fields = MessageTable::getRowById($id);
		if (!$fields)
		{
			return null;
		}
		$instance = new static();
		$instance->setFields($fields);

		return $instance;
	}

	public static function loadByExternalId(string $senderId, string $externalId, ?string $from = null): ?Message
	{
		$fields = MessageTable::getByExternalId($senderId, $externalId, $from)->fetch();
		if (!$fields)
		{
			return null;
		}
		$instance = new static();
		$instance->setFields($fields);

		return $instance;
	}

	/**
	 * @param array $fields
	 * @param Sender\Base|null $sender
	 * @return Message
	 */
	public static function createFromFields(array $fields, Sender\Base $sender = null)
	{
		$message = new static($sender);
		$message->setFields($fields);

		return $message;
	}

	public static function getFieldsById($messageId)
	{
		$iterator = Internal\Entity\MessageTable::getById($messageId);
		return $iterator->fetch();
	}

	/**
	 * @param string $type
	 * @return Message
	 * @throws ArgumentTypeException
	 */
	public function setType($type)
	{
		if (!MessageType::isSupported($type))
		{
			throw new ArgumentTypeException('Unsupported message type');
		}

		$this->type = $type;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	public function checkFields()
	{
		$result = new Main\Result();

		$sender = $this->getSender();
		$from = $this->getFrom();

		if (!$sender)
		{
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_MESSAGE_ERROR_SENDER')));
		}
		elseif (!$sender->canUse())
		{
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_MESSAGE_ERROR_SENDER_CAN_USE')));
		}
		elseif ($sender->getType() !== $this->getType())
		{
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_MESSAGE_ERROR_TYPE')));
		}
		elseif (!$from || !$sender->isCorrectFrom($from))
		{
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_MESSAGE_ERROR_FROM')));
		}

		return $result;
	}

	/**
	 * @return \Bitrix\Main\Entity\AddResult Created Message result.
	 */
	public function send()
	{
		$checkResult = $this->checkFields();

		if (!$checkResult->isSuccess())
		{
			$result = new AddResult();
			$result->addErrors($checkResult->getErrors());
			return $result;
		}

		$sender = $this->getSender();
		$headers = $this->getHeaders();

		$result = Internal\Entity\MessageTable::add(array(
			'TYPE' => $this->getType(),
			'SENDER_ID' => $sender->getId(),
			'AUTHOR_ID' => $this->getAuthorId(),
			'MESSAGE_FROM' => $this->getFrom(),
			'MESSAGE_TO' => $this->getTo(),
			'MESSAGE_HEADERS' => count($headers) > 0 ? $headers : null,
			'MESSAGE_BODY' => $this->getBody(),
		));
		if ($result->isSuccess())
		{
			$this->id = $result->getId();
		}

		return $result;
	}

	public function sendDirectly(): Result\SendMessage
	{
		$checkResult = $this->checkFields();

		if (!$checkResult->isSuccess())
		{
			$result = new Result\SendMessage();
			return $result->addErrors($checkResult->getErrors());
		}

		$sender = $this->getSender();

		if (!Sender\Limitation::checkDailyLimit($sender->getId(), $this->getFrom()))
		{
			$result = new Result\SendMessage();
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_MESSAGE_ERROR_LIMITATION')));
			$result->setStatus(MessageStatus::DEFERRED);

			return $result;
		}

		$headers = $this->getHeaders();
		$messageFields = [
			'TYPE' => $this->getType(),
			'SENDER_ID' => $sender->getId(),
			'AUTHOR_ID' => $this->getAuthorId(),
			'MESSAGE_FROM' => $this->getFrom(),
			'MESSAGE_TO' => $this->getTo(),
			'MESSAGE_HEADERS' => count($headers) > 0 ? $headers : null,
			'MESSAGE_BODY' => $this->getBody(),
		];

		$addResult = Internal\Entity\MessageTable::add($messageFields);
		if (!$addResult->isSuccess())
		{
			$result = new Result\SendMessage();
			$result->addErrors($addResult->getErrors());
			$result->setStatus(MessageStatus::ERROR);

			return $result;
		}
		$this->id = $addResult->getId();
		$messageFields['ID'] = $this->id;

		$result = $sender->sendMessage($messageFields);
		$result->setId($this->id);

		$updateFields = [
			'DATE_EXEC' => new DateTime(),
			'SUCCESS_EXEC' => $result->isSuccess() ? 'Y' : 'N',
		];
		if ($result->getExternalId() !== null)
		{
			$updateFields['EXTERNAL_ID'] = $result->getExternalId();
		}
		if ($result->getStatus() !== null)
		{
			$updateFields['STATUS_ID'] = $result->getStatus();
		}
		Internal\Entity\MessageTable::update($this->id, $updateFields);

		return $result;
	}

	/**
	 * @return mixed
	 */
	public function getFrom()
	{
		return $this->from;
	}

	/**
	 * @param mixed $from
	 * @return $this
	 */
	public function setFrom($from)
	{
		$this->from = (string)$from;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * @param mixed $body
	 * @return $this
	 */
	public function setBody($body)
	{
		$this->body = (string)$body;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTo()
	{
		return $this->to;
	}

	/**
	 * @param mixed $to
	 * @return $this
	 */
	public function setTo($to)
	{
		$this->to = (string)$to;
		return $this;
	}

	/**
	 * @return Sender\Base|null
	 */
	public function getSender()
	{
		return $this->sender;
	}

	/**
	 * @param Sender\Base $sender
	 * @return $this
	 */
	public function setSender(Sender\Base $sender)
	{
		$this->sender = $sender;
		return $this;
	}

	/**
	 * @param array $headers
	 * @return Message
	 */
	public function setHeaders(array $headers)
	{
		$this->headers = $headers;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * @param int $authorId
	 * @return Message
	 */
	public function setAuthorId($authorId)
	{
		$this->authorId = (int)$authorId;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getAuthorId()
	{
		return $this->authorId;
	}

	public function update(array $fields)
	{
		$updateResult = MessageTable::update($this->id, $fields);
		if (!$updateResult->isSuccess())
		{
			return false;
		}

		$this->setFields($fields);

		// events
		$eventFields = array_merge(['ID' => $this->id], $fields);
		Main\EventManager::getInstance()->send(new Main\Event(
			'messageservice',
			static::EVENT_MESSAGE_UPDATED,
			$eventFields)
		);
		Pull::onMessagesUpdate([$eventFields]);

		return true;
	}

	public function updateWithSendResult(Result\SendMessage $result, DateTime $nextExec)
	{
		$toUpdate = ['SUCCESS_EXEC' => 'E', 'DATE_EXEC' => new DateTime()];
		if ($result->isSuccess())
		{
			$toUpdate['SUCCESS_EXEC'] = 'Y';
			if ($result->getExternalId() !== null)
			{
				$toUpdate['EXTERNAL_ID'] = $result->getExternalId();
			}
			if ($result->getStatus() !== null)
			{
				$toUpdate['STATUS_ID'] = $result->getStatus();
			}
		}
		elseif ($result->getStatus() === MessageStatus::DEFERRED)
		{
			$toUpdate = array(
				'SUCCESS_EXEC' => 'N',
				'NEXT_EXEC' => $nextExec,
				'STATUS_ID' => MessageStatus::DEFERRED
			);
		}
		else
		{
			$toUpdate['STATUS_ID'] = MessageStatus::ERROR;
		}

		$errors = $result->getErrorMessages();
		if ($errors)
		{
			$toUpdate['EXEC_ERROR'] = implode(PHP_EOL, $errors);
		}

		$this->update($toUpdate);
	}

	public function updateStatusByExternalStatus(string $externalStatus)
	{
		return $this->update([
			'EXTERNAL_STATUS' => $externalStatus,
			'STATUS_ID' => $this->sender->resolveStatus($externalStatus),
		]);
	}

	public function updateStatus(int $newStatusId): bool
	{
		$updateResult = MessageTable::updateStatusId($this->id, $newStatusId);
		if (!$updateResult)
		{
			return false;
		}

		$this->statusId = $newStatusId;

		// events
		$eventFields = ['ID' => $this->id,	'STATUS_ID' => $this->statusId];
		Main\EventManager::getInstance()->send(new Main\Event(
				'messageservice',
				static::EVENT_MESSAGE_UPDATED,
				$eventFields)
		);
		Pull::onMessagesUpdate([$eventFields]);

		return true;
	}

	private function setFields(array $fields)
	{
		if (!$this->sender && isset($fields['SENDER_ID']))
		{
			$sender = Sender\SmsManager::getSenderById($fields['SENDER_ID']);
			if ($sender)
			{
				$this->setSender($sender);
			}
		}
		if (isset($fields['ID']))
			$this->id = (int)$fields['ID'];
		if (isset($fields['TYPE']))
			$this->setType($fields['TYPE']);
		if (isset($fields['AUTHOR_ID']))
			$this->setAuthorId($fields['AUTHOR_ID']);
		if (isset($fields['MESSAGE_FROM']))
			$this->setFrom($fields['MESSAGE_FROM']);
		if (isset($fields['MESSAGE_TO']))
			$this->setTo($fields['MESSAGE_TO']);
		if (isset($fields['MESSAGE_TEMPLATE']) && $sender->isConfigurable() && $sender->isTemplatesBased())
		{
			$fields['MESSAGE_HEADERS'] = is_array($fields['MESSAGE_HEADERS']) ? $fields['MESSAGE_HEADERS'] : [];
			$fields['MESSAGE_HEADERS']['template'] = $sender->prepareTemplate($fields['MESSAGE_TEMPLATE']);
		}
		if (isset($fields['MESSAGE_HEADERS']) && is_array($fields['MESSAGE_HEADERS']))
			$this->setHeaders($fields['MESSAGE_HEADERS']);
		if (isset($fields['MESSAGE_BODY']))
		{
			$messageBody = $sender
				? $sender->prepareMessageBodyForSave($fields['MESSAGE_BODY'])
				: $fields['MESSAGE_BODY'];
			$this->setBody($messageBody);
		}
		if (isset($fields['STATUS_ID']))
			$this->statusId = $fields['STATUS_ID'];
		if (isset($fields['EXTERNAL_STATUS']))
			$this->statusId = $fields['EXTERNAL_STATUS'];
	}
}