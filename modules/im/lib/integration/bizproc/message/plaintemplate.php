<?php

namespace Bitrix\Im\Integration\Bizproc\Message;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class PlainTemplate extends Template
{
	protected string $messageText = '';

	public function buildMessage(array $messageFields): array
	{
		$messageFields['MESSAGE'] = $this->buildMessageText();

		return $messageFields;
	}

	protected function buildMessageText(): string
	{
		$text = $this->messageText;

		if ($this->asRobotMessage)
		{
			$text .= PHP_EOL . $this->buildRobotText();
		}

		return $text;
	}

	protected function buildDescriptionText(): string
	{
		$text = mb_substr(\CBPHelper::convertBBtoText($this->messageText), 0, 200);
		if (mb_strlen($text) === 200)
		{
			$text .= '...';
		}

		return $text;
	}

	/**
	 * @intrnal
	 * @return string
	 */
	protected function buildRobotText()
	{
		return '[i]'. Loc::getMessage('IM_BIZPROC_MESSAGE_PLAIN_TEMPLATE_SENT_BY_ROBOT') .'[/i]';
	}

	protected function validate(): void
	{
		if ($this->messageText === '')
		{
			$fieldsMap = static::getFieldsMap();

			$this->errors->setError(
				new Error(
					Loc::getMessage(
						'IM_BIZPROC_MESSAGE_PLAIN_TEMPLATE_ERROR_EMPTY_FIELD',
						['#FIELD_NAME#' => $fieldsMap['MessageText']['Name']]
					),
				)
			);
		}
	}

	public function setFields(array $fields): self
	{
		if (!empty($fields['MessageText']))
		{
			$this->messageText = trim(\CBPHelper::stringify($fields['MessageText']));
		}

		return $this;
	}

	public static function getFieldsMap(): array
	{
		return [
			'MessageText' => [
				'Name' => Loc::getMessage('IM_BIZPROC_MESSAGE_PLAIN_TEMPLATE_FIELD_NAME_MESSAGE_TEXT'),
				'FieldName' => 'template_message_text',
				'Type' => FieldType::TEXT,
				'Required' => true,
				'Multiple' => false,
			],
		];
	}
}
