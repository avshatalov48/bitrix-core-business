<?php
namespace Bitrix\MessageService;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\MessageService\Sender\Result\SendMessage;

Loc::loadMessages(__FILE__);

class Message
{
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

	/**
	 * @param array $fields
	 * @param Sender\Base|null $sender
	 * @return Message
	 */
	public static function createFromFields(array $fields, Sender\Base $sender = null)
	{
		$message = new static($sender);

		if (!$sender && isset($fields['SENDER_ID']))
		{
			$sender = Sender\SmsManager::getSenderById($fields['SENDER_ID']);
			$message->setSender($sender);
		}
		if (isset($fields['TYPE']))
			$message->setType($fields['TYPE']);
		if (isset($fields['AUTHOR_ID']))
			$message->setAuthorId($fields['AUTHOR_ID']);
		if (isset($fields['MESSAGE_FROM']))
			$message->setFrom($fields['MESSAGE_FROM']);
		if (isset($fields['MESSAGE_TO']))
			$message->setTo($fields['MESSAGE_TO']);
		if (isset($fields['MESSAGE_HEADERS']))
			$message->setHeaders($fields['MESSAGE_HEADERS']);
		if (isset($fields['MESSAGE_BODY']))
			$message->setBody($fields['MESSAGE_BODY']);

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
		$result = new Result();

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

		return $result;
	}

	public function sendDirectly()
	{
		$checkResult = $this->checkFields();

		if (!$checkResult->isSuccess())
		{
			$result = new AddResult();
			$result->addErrors($checkResult->getErrors());
			return $result;
		}

		$sender = $this->getSender();

		if (!Sender\Limitation::checkDailyLimit($sender->getId(), $this->getFrom()))
		{
			$result = new SendMessage();
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_MESSAGE_ERROR_LIMITATION')));
			$result->setStatus(MessageStatus::DEFERRED);

			return $result;
		}

		$headers = $this->getHeaders();
		$messageFields = array(
			'TYPE' => $this->getType(),
			'SENDER_ID' => $sender->getId(),
			'AUTHOR_ID' => $this->getAuthorId(),
			'MESSAGE_FROM' => $this->getFrom(),
			'MESSAGE_TO' => $this->getTo(),
			'MESSAGE_HEADERS' => count($headers) > 0 ? $headers : null,
			'MESSAGE_BODY' => $this->getBody(),
		);

		$result = $sender->sendMessage($messageFields);

		if ($result->isSuccess())
		{
			$messageFields['DATE_EXEC'] = new DateTime();
			$messageFields['SUCCESS_EXEC'] = 'Y';
			if ($result->getExternalId() !== null)
			{
				$messageFields['EXTERNAL_ID'] = $result->getExternalId();
			}
			if ($result->getStatus() !== null)
			{
				$messageFields['STATUS_ID'] = $result->getStatus();
			}
			$addResult = Internal\Entity\MessageTable::add($messageFields);
			if ($addResult->isSuccess())
			{
				$result->setId($addResult->getId());
			}
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
}