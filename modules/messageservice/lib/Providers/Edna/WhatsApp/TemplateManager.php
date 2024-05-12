<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\Json;
use Bitrix\MessageService\Providers\Constants\InternalOption;
use Bitrix\MessageService\Providers\Edna\EdnaRu;

class TemplateManager extends \Bitrix\MessageService\Providers\Base\TemplateManager
{

	protected EdnaRu $utils;
	protected EmojiConverter $emoji;

	public function __construct(string $providerId, EdnaRu $utils, EmojiConverter $emoji)
	{
		parent::__construct($providerId);

		$this->utils = $utils;
		$this->emoji = $emoji;
	}

	/**
	 * @param array|null $context
	 * @return array<int, array{ID: string, TITLE: string, PREVIEW: string, HEADER: string, FOOTER: string, PLACEHOLDERS: array, KEYBOARD: array}>
	 */
	public function getTemplatesList(array $context = null): array
	{
		$templatesResult = $this->utils->getMessageTemplates();
		if (!$templatesResult->isSuccess())
		{
			return [];
		}

		$templates = $templatesResult->getData();
		if (!is_array($templates))
		{
			return [];
		}

		$result = [];
		foreach ($templates as $template)
		{
			$tmp = [
				'ID' => Json::encode($template['content']),
				'ORIGINAL_ID' => (int)$template['id'],
				'TITLE' => $template['name'],
				'PREVIEW' => $template['content']['text'] ?? '',
			];

			if (!empty($template['content']['header']['text']))
			{
				$tmp['HEADER'] = $template['content']['header']['text'];
			}
			if (!empty($template['content']['footer']['text']))
			{
				$tmp['FOOTER'] = $template['content']['footer']['text'];
			}
			if (!empty($template['content']['keyboard']['rows']))
			{
				$tmp['KEYBOARD'] = $template['content']['keyboard'];
			}
			if (!empty($template['placeholders']))
			{
				$tmp['PLACEHOLDERS'] = [];
			}
			if (!empty($template['placeholders']['text']))
			{
				$tmp['PLACEHOLDERS']['PREVIEW'] = $template['placeholders']['text'];
			}
			if (!empty($template['placeholders']['header']))
			{
				$tmp['PLACEHOLDERS']['HEADER'] = $template['placeholders']['header'];
			}
			if (!empty($template['placeholders']['footer']))
			{
				$tmp['PLACEHOLDERS']['FOOTER'] = $template['placeholders']['footer'];
			}

			$result[] = $tmp;
		}

		return $result;
	}

	public function prepareTemplate($templateData): array
	{
		try
		{
			$messageTemplateDecoded = Json::decode($templateData);
			$messageTemplateDecoded =
				$this->emoji->convertEmojiInTemplate($messageTemplateDecoded, InternalOption::EMOJI_ENCODE);
		}
		catch (\Bitrix\Main\ArgumentException $e)
		{
			throw new ArgumentException('Incorrect message template');
		}

		return $messageTemplateDecoded;
	}

	public function isTemplatesBased(): bool
	{
		return true;
	}

}