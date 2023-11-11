<?php

namespace Bitrix\Im\Integration\Bizproc\Message;

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Result;

abstract class Template
{
	protected ErrorCollection $errors;
	protected bool $asRobotMessage = false;
	protected bool $enablePush = false;

	public function __construct()
	{
		$this->errors = new ErrorCollection();
	}

	public function formatMessage(array $messageFields): Result
	{
		$result = new Result();

		$this->validate();
		if ($this->errors->isEmpty())
		{
			$message = $this->buildMessage($messageFields);

			if ($this->asRobotMessage)
			{
				$message['SYSTEM'] = 'N';
				if (!is_array($message['PARAMS'] ?? null))
				{
					$message['PARAMS'] = [];
				}
				$message['PARAMS']['IS_ROBOT_MESSAGE'] =  'Y';
			}

			if ($this->enablePush)
			{
				$message['PUSH'] = 'Y';
				$message['PUSH_MESSAGE'] = $this->buildDescriptionText();
			}

			$result->setData($message);
		}
		else
		{
			$result->addErrors($this->errors->getValues());
		}

		return $result;
	}

	public function markAsRobotMessage()
	{
		$this->asRobotMessage = true;
	}

	public function enablePushMessage()
	{
		$this->enablePush = true;
	}

	abstract function buildMessage(array $messageFields): array;
	abstract protected function validate(): void;
	abstract public function setFields(array $fields): self;
	abstract public static function getFieldsMap(): array;
}