<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\MessageService\Providers\Constants\InternalOption;

class EmojiConverter
{
	public function convertEmojiInTemplate(array $messageTemplate, string $type): array
	{
		$template = $messageTemplate;
		if (isset($template['text']))
		{
			$template['text'] = $this->convertTextSection($template['text'], $type);
		}
		if (isset($template['header']))
		{
			$template['header'] = $this->convertHeaderSection($template['header'], $type);
		}
		if (isset($template['footer']))
		{
			$template['footer'] = $this->convertFooterSection($template['footer'], $type);
		}
		if (isset($template['keyboard']))
		{
			$template['keyboard'] = $this->convertKeyboardSection($template['keyboard'], $type);
		}

		return $template;
	}

	public function convertEmoji(string $text, string $type): string
	{
		if (!in_array($type, [InternalOption::EMOJI_DECODE, InternalOption::EMOJI_ENCODE], true))
		{
			return $text;
		}

		return \Bitrix\Main\Text\Emoji::$type($text);
	}

	protected function convertTextSection(?string $textSection, string $type): string
	{
		if (is_string($textSection))
		{
			$textSection = $this->convertEmoji($textSection, $type);
		}

		return $textSection;
	}

	protected function convertHeaderSection(?array $headerSection, string $type): array
	{
		if (isset($headerSection['text']))
		{
			$headerSection['text'] = $this->convertEmoji($headerSection['text'], $type);
		}

		return $headerSection;
	}

	protected function convertFooterSection(?array $footerSection, string $type): array
	{
		if (isset($footerSection['text']))
		{
			$footerSection['text'] = $this->convertEmoji($footerSection['text'], $type);
		}

		return $footerSection;
	}

	/**
	 * Example:
	 *
	 * 'keyboard' => [
	 *        'rows' => [
	 *            [
	 *                'buttons' => [
	 *                    [
	 *                        'text' => 'Red',
	 *                        'payload' => '1',
	 *                    ],
	 *                    [
	 *                        'text' => 'blue',
	 *                        'payload' => '2',
	 *                    ]
	 *                ]
	 *            ]
	 *        ]
	 * ]
	 *
	 * @see https://docs.edna.ru/kb/message-matchers-get-by-request/
	 * @param array|null $keyboardSection
	 * @param string $type
	 * @return array
	 */
	protected function convertKeyboardSection(?array $keyboardSection, string $type): array
	{
		if (isset($keyboardSection['rows']) && is_array($keyboardSection['rows']))
		{
			foreach ($keyboardSection['rows'] as $rowIndex => $row)
			{
				if (isset($row['buttons']) && is_array($row['buttons']))
				{
					foreach ($row['buttons'] as $buttonIndex => $button)
					{
						if (isset($button['text']))
						{
							$keyboardSection['rows'][$rowIndex]['buttons'][$buttonIndex]['text'] =
								$this->convertEmoji($button['text'], $type);
						}
					}
				}
			}
		}

		return $keyboardSection;
	}
}