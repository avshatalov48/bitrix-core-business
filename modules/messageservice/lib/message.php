<?php
namespace Bitrix\MessageService;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\ORM\Data\AddResult;
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

	protected ?Error $error = null;

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
	public static function createFromFields(array $fields, Sender\Base $sender = null): Message
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
	public function setType(string $type): Message
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
	public function getType(): string
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
	 * @return AddResult Created Message result.
	 */
	public function send(): AddResult
	{
		global $USER;

		$checkResult = $this->checkFields();

		if (!$checkResult->isSuccess())
		{
			$result = new AddResult();
			$result->addErrors($checkResult->getErrors());
			return $result;
		}

		$sender = $this->getSender();
		$headers = $this->getHeaders();

		$result = Internal\Entity\MessageTable::add([
			'TYPE' => $this->getType(),
			'SENDER_ID' => $sender->getId(),
			'AUTHOR_ID' => $this->getAuthorId(),
			'MESSAGE_FROM' => $this->getFrom(),
			'MESSAGE_TO' => $this->getTo(),
			'MESSAGE_HEADERS' => count($headers) > 0 ? $headers : null,
			'MESSAGE_BODY' => $this->getBody(),
			'CLUSTER_GROUP' => defined('BX_CLUSTER_GROUP') ? BX_CLUSTER_GROUP : null
		]);
		if ($result->isSuccess())
		{
			$this->id = $result->getId();
			if (Main\Config\Option::get('messageservice', 'event_log_message_send', 'N') === 'Y')
			{
				$userId = is_object($USER) ? $USER->getId() : 0;
				\CEventLog::Log('INFO', 'MESSAGE_SEND', 'messageservice', $userId, $this->getTo());
			}
		}

		return $result;
	}

	public function sendDirectly(): Result\SendMessage
	{
		global $USER;

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
			'CLUSTER_GROUP' => defined('BX_CLUSTER_GROUP') ? BX_CLUSTER_GROUP : null
		];

		$sender->setSocketTimeout(5);
		$sender->setStreamTimeout(15);

		$result = $sender->sendMessage($messageFields);

		$messageFields['DATE_EXEC'] = new DateTime();
		$messageFields['SUCCESS_EXEC'] = $result->isSuccess() ? 'Y' : 'N';

		if ($result->getExternalId() !== null)
		{
			$messageFields['EXTERNAL_ID'] = $result->getExternalId();
		}
		if ($result->getStatus() !== null)
		{
			$messageFields['STATUS_ID'] = $result->getStatus();
		}

		$addResult = Internal\Entity\MessageTable::add($messageFields);
		if (!$addResult->isSuccess())
		{
			$result->addErrors($addResult->getErrors());
			$result->setStatus(MessageStatus::ERROR);

			return $result;
		}

		$this->id = $addResult->getId();
		$result->setId($this->id);

		if (Main\Config\Option::get('messageservice', 'event_log_message_send', 'N') === 'Y')
		{
			$userId = is_object($USER) ? $USER->getId() : 0;
			\CEventLog::Log('INFO', 'MESSAGE_SEND', 'messageservice', $userId, $this->getTo());
		}

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
	public function setFrom($from): Message
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
	public function setBody($body): Message
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
	public function setTo($to): Message
	{
		$this->to = (string)$to;
		return $this;
	}

	/**
	 * @return Sender\Base|null
	 */
	public function getSender(): ?Sender\Base
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
	public function setHeaders(array $headers): Message
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
	public function setAuthorId(int $authorId): Message
	{
		$this->authorId = $authorId;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getAuthorId()
	{
		return $this->authorId;
	}

	/**
	 * @return Error|null
	 */
	public function getError(): ?Error
	{
		return $this->error;
	}

	/**
	 * @param Error $error
	 * @return Message
	 */
	public function setError(Error $error): self
	{
		$this->error = $error;

		return $this;
	}

	public function getStatusId()
	{
		return $this->statusId;
	}

	public function update(array $fields): bool
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

	public function updateWithSendResult(Result\SendMessage $result, DateTime $nextExec): void
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

	public function updateStatusByExternalStatus(string $externalStatus): bool
	{

		$newInternalStatus = $this->sender::resolveStatus($externalStatus);

		$isUpdateSuccess = MessageTable::updateMessageStatuses(
			$this->id,
			$newInternalStatus,
			$externalStatus
		);

		if (!$isUpdateSuccess)
		{
			return false;
		}

		$this->statusId = $newInternalStatus;

		// events
		$eventFields = ['ID' => $this->id, 'STATUS_ID' => $this->statusId];
		Main\EventManager::getInstance()->send(new Main\Event(
				'messageservice',
				static::EVENT_MESSAGE_UPDATED,
				$eventFields)
		);
		Pull::onMessagesUpdate([$eventFields]);

		return true;
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

	private function setFields(array $fields): void
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
		{
			$this->id = (int)$fields['ID'];
		}
		if (isset($fields['TYPE']))
		{
			$this->setType($fields['TYPE']);
		}
		if (isset($fields['AUTHOR_ID']))
		{
			$this->setAuthorId((int)$fields['AUTHOR_ID']);
		}
		if (isset($fields['MESSAGE_FROM']))
		{
			$this->setFrom($fields['MESSAGE_FROM']);
		}
		if (isset($fields['MESSAGE_TO']))
		{
			$this->setTo($fields['MESSAGE_TO']);
		}
		if (
			isset($fields['MESSAGE_TEMPLATE'])
			&& $this->sender->isConfigurable()
			&& $this->sender->isTemplatesBased()
		)
		{
			$fields['MESSAGE_HEADERS'] = is_array($fields['MESSAGE_HEADERS']) ? $fields['MESSAGE_HEADERS'] : [];
			$fields['MESSAGE_HEADERS']['template'] = $this->sender->prepareTemplate($fields['MESSAGE_TEMPLATE']);
		}
		if (isset($fields['MESSAGE_HEADERS']) && is_array($fields['MESSAGE_HEADERS']))
		{
			$this->setHeaders($fields['MESSAGE_HEADERS']);
		}
		if (isset($fields['MESSAGE_BODY']))
		{
			$messageBody = $this->sender
				? $this->sender->prepareMessageBodyForSave($fields['MESSAGE_BODY'])
				: $fields['MESSAGE_BODY']
			;
			$this->setBody($messageBody);
		}
		if (isset($fields['STATUS_ID']))
		{
			$this->statusId = $fields['STATUS_ID'];
		}
		if (isset($fields['EXTERNAL_STATUS']))
		{
			$this->externalStatus = $fields['EXTERNAL_STATUS'];
		}
	}
}