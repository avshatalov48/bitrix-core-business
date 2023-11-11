<?php

namespace Bitrix\Im\Integration\Bizproc\Message;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class NewsTemplate extends PlainTemplate
{
	protected const DEFAULT_BORDER_COLOR = '#468EE5';

	protected string $title = '';

	public function buildMessage(array $messageFields): array
	{
		$attach = new \CIMMessageParamAttach(0, static::DEFAULT_BORDER_COLOR);

		$attach->SetDescription($this->buildDescriptionText());

		$attach->AddGrid([
			[
				'DISPLAY' => 'LINE',
				'NAME' => $this->title,
				'VALUE' => $this->buildMessageText(),
			]
		]);

		$messageFields['ATTACH'] = $attach;

		return $messageFields;
	}

	protected function buildDescriptionText(): string
	{
		$text = mb_substr(\CBPHelper::convertBBtoText($this->title . ' ' . $this->messageText), 0, 200);
		if (mb_strlen($text) === 200)
		{
			$text .= '...';
		}

		return $text;
	}

	protected function validate(): void
	{
		parent::validate();

		if ($this->title === '')
		{
			$this->errors->setError(new Error(
				Loc::getMessage('IM_BIZPROC_MESSAGE_NEWS_TEMPLATE_FIELD_NAME_MESSAGE_TITLE_ERROR')
			));
		}
	}

	public function setFields(array $fields): self
	{
		parent::setFields($fields);

		if (!empty($fields['MessageTitle']))
		{
			$this->title = trim(\CBPHelper::stringify($fields['MessageTitle']));
		}

		return $this;
	}

	public static function getFieldsMap(): array
	{
		return array_merge(
			[
				'MessageTitle' => [
					'Name' => Loc::getMessage('IM_BIZPROC_MESSAGE_NEWS_TEMPLATE_FIELD_NAME_MESSAGE_TITLE'),
					'FieldName' => 'title',
					'Type' => FieldType::STRING,
					'Required' => true,
					'Multiple' => false,
				],
			],
			parent::getFieldsMap(),
		);
	}
}
