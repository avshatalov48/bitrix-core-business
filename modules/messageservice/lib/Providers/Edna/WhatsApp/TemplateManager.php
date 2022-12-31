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
			$result[] = [
				'ID' => Json::encode($template['content']),
				'TITLE' => $template['name'],
				'PREVIEW' => $template['content']['text'],
			];
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