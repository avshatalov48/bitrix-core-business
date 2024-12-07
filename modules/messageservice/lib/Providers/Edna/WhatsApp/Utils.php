<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\MessageService\Internal\Entity\MessageTable;
use Bitrix\MessageService\Internal\Entity\TemplateTable;
use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Providers\Edna\EdnaUtils;

class Utils extends EdnaUtils
{
	public const CACHE_KEY_TEMPLATES = 'whatsapp_templates_cache';
	public const CACHE_DIR_TEMPLATES = '/messageservice/templates/';
	protected const CACHE_TIME_TEMPLATES = 14400;

	protected function initializeDefaultExternalSender(): Providers\ExternalSender
	{
		return new ExternalSender(
			$this->optionManager->getOption(Providers\Constants\InternalOption::API_KEY),
			RegionHelper::getApiEndPoint(),
			$this->optionManager->getSocketTimeout(),
			$this->optionManager->getStreamTimeout()
		);
	}

	public function getMessageTemplates(string $subject = ''): Result
	{
		$result = new Result();

		if ($this->optionManager->getOption('enable_templates_stub', 'N') === 'Y')
		{
			$templates = $this->removeUnsupportedTemplates($this->getMessageTemplatesStub());
			return $result->setData($templates);
		}
		
		$subjectList = [$subject];
		if ($subject === '')
		{
			$subjectList = $this->optionManager->getOption('sender_id', []);
		}

		$verifiedSubjectIdResult = $this->getVerifiedSubjectIdList($subjectList);
		if (!$verifiedSubjectIdResult->isSuccess())
		{
			return $verifiedSubjectIdResult;
		}

		$verifiedSubjectIdList = $verifiedSubjectIdResult->getData();

		$templates = [];

		$cache = Cache::createInstance();
		if ($cache->initCache(self::CACHE_TIME_TEMPLATES, self::CACHE_KEY_TEMPLATES, self::CACHE_DIR_TEMPLATES))
		{
			$templates = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			foreach ($verifiedSubjectIdList as $subjectId)
			{
				$requestParams = [
					'subjectId' => $subjectId
				];

				$templatesRequestResult =
					$this->externalSender->callExternalMethod(Providers\Edna\Constants\Method::GET_TEMPLATES, $requestParams);

				if ($templatesRequestResult->isSuccess())
				{
					$templates = array_merge($templates, $templatesRequestResult->getData());
				}
			}

			$templates = $this->excludeOutdatedTemplates($templates);
			$templates = $this->replaceNameToTitle($templates);

			$cache->endDataCache($templates);
		}

		$checkErrors = $this->checkForErrors($templates);
		if ($checkErrors->isSuccess())
		{
			$templates = $this->removeUnsupportedTemplates($templates);
			$templates = $this->checkForPlaceholders($templates);
			$result->setData($templates);
		}
		else
		{
			$result->addErrors($checkErrors->getErrors());
		}

		return $result;
	}

	public function prepareTemplateMessageText(array $message): string
	{
		$latestMessage = '';
		if (isset($message['MESSAGE_HEADERS']['template']['header']['text']))
		{
			$latestMessage .= $message['MESSAGE_HEADERS']['template']['header']['text'] . '#BR#';
		}

		if (isset($message['MESSAGE_HEADERS']['template']['text']))
		{
			$latestMessage .= $message['MESSAGE_HEADERS']['template']['text'] . '#BR#';
		}

		if (isset($message['MESSAGE_HEADERS']['template']['footer']['text']))
		{
			$latestMessage .= $message['MESSAGE_HEADERS']['template']['footer']['text'];
		}

		return $latestMessage;
	}

	public function getSentTemplateMessage(string $from, string $to): string
	{
		$message = MessageTable::getList([
			'select' => ['ID', 'MESSAGE_HEADERS'],
			'filter' => [
				'=SENDER_ID' => $this->providerId,
				'=MESSAGE_FROM' => $from,
				'=MESSAGE_TO' => '+' . $to,
			],
			'limit' => 1,
			'order' => ['ID' => 'DESC'],
		])->fetch();

		if (!$message)
		{
			return '';
		}

		return $this->prepareTemplateMessageText($message);
	}

	protected function getMessageTemplatesStub(): array
	{
		return [
			[
				'id' => 242,
				'name' => 'only text',
				'channelType' => 'whatsapp',
				'language' => 'RU',
				'content' => [
					'attachment' => null,
					'action' => null,
					'caption' => null,
					'header' => null,
					'text' => "Hello! Welcome to our platform.",
					'footer' => null,
					'keyboard' => null,
				],
				'category' => 'ACCOUNT_UPDATE',
				'status' => Providers\Edna\Constants\TemplateStatus::PENDING,
				'locked' => false,
				'type' => 'OPERATOR',
				'createdAt' => '2021-07-15T14:16:54.417024Z',
				'updatedAt' => '2021-07-16T13:08:26.275414Z',
			],
			[
				'id' => 267,
				'name' => 'text + header + footer',
				'channelType' => 'whatsapp',
				'language' => 'RU',
				'content' => [
					'attachment' => null,
					'action' => null,
					'caption' => null,
					'header' => [
						'text' => 'Greetings',
					],
					'text' => 'Hello! Welcome to our platform.',
					'footer' => [
						'text' => 'Have a nice day',
					],
					'keyboard' => null,
				],
				'category' => 'ACCOUNT_UPDATE',
				'status' => Providers\Edna\Constants\TemplateStatus::APPROVED,
				'locked' => false,
				'type' => 'USER',
				'createdAt' => '2021-07-20T09:21:42.444454Z',
				'updatedAt' => '2021-07-20T09:21:42.444454Z',
			],
			[
				'id' => 268,
				'name' => 'text + buttons',
				'channelType' => 'whatsapp',
				'language' => 'RU',
				'content' => [
					'attachment' => null,
					'action' => null,
					'caption' => null,
					'header' => null,
					'text' => "Hello! Welcome to our platform. Have you already tried it?",
					'footer' => null,
					'keyboard' => [
						'rows' => [
							[
								'buttons' => [
									[
										'text' => 'Yes',
										'buttonType' => "QUICK_REPLY",
										'payload' => '1'
									],
									[
										'text' => 'No',
										'buttonType' => "QUICK_REPLY",
										'payload' => '2'
									],
								],
							],
						],
					],

				],
				'category' => 'ACCOUNT_UPDATE',
				'status' => Providers\Edna\Constants\TemplateStatus::APPROVED,
				'locked' => false,
				'type' => 'USER',
				'createdAt' => '2021-07-20T09:21:42.444454Z',
				'updatedAt' => '2021-07-20T09:21:42.444454Z',
			],
			[
				'id' => 269,
				'name' => 'text + button-link',
				'channelType' => 'whatsapp',
				'language' => 'RU',
				'content' => [
					'attachment' => null,
					'action' => null,
					'caption' => null,
					'header' => null,
					'text' => 'Hello! Welcome to our platform. Follow the link bellow to read manuals:',
					'footer' => null,
					'keyboard' => [
						'rows' => [
							[
								'buttons' => [
									[
										'text' => 'Manual',
										'buttonType' => "URL",
										'url' => "https://docs.edna.io/"
									],
								],
							],
						],
					],
				],
				'category' => 'ACCOUNT_UPDATE',
				'status' => Providers\Edna\Constants\TemplateStatus::DISABLED,
				'locked' => false,
				'type' => 'USER',
				'createdAt' => '2021-07-20T09:21:42.444454Z',
				'updatedAt' => '2021-07-20T09:21:42.444454Z',
			],
		];
	}

	private function getVerifiedSubjectIdList(array $subjectList): Result
	{
		$channelListResult = $this->getChannelList(Providers\Edna\Constants\ChannelType::WHATSAPP);
		if (!$channelListResult->isSuccess())
		{
			return $channelListResult;
		}

		$filteredSubjectList = [];
		foreach ($channelListResult->getData() as $channel)
		{
			if (isset($channel['subjectId']) && in_array($channel['subjectId'], $subjectList, true))
			{
				$filteredSubjectList[] = $channel['subjectId'];
			}
		}

		$result = new Result();
		if (empty($filteredSubjectList))
		{
			$result->addError(new Error('Verified subjects are missing'));
			
			return $result;
		}
		$result->setData($filteredSubjectList);
		
		return $result;
	}

	/**
	 * @param array{status: string} $template
	 * @return bool
	 */
	protected function checkApprovedStatus(array $template): bool
	{
		return isset($template['status']) && $template['status'] === Providers\Edna\Constants\TemplateStatus::APPROVED;
	}

	protected function checkForPlaceholders(array $templates): array
	{
		foreach ($templates as &$template)
		{
			$template['placeholders'] = [];

			if (
				!empty($template['content']['header']['text'])
				&& $this->hasPlaceholder($template['content']['header']['text'])
			)
			{
				$template['placeholders']['header'] = $this->extractPlaceholders($template['content']['header']['text']);
			}

			if (
				!empty($template['content']['text'])
				&& $this->hasPlaceholder($template['content']['text'])
			)
			{
				$template['placeholders']['text'] = $this->extractPlaceholders($template['content']['text']);
			}

			if (
				!empty($template['content']['footer']['text'])
				&& $this->hasPlaceholder($template['content']['footer']['text'])
			)
			{
				$template['placeholders']['footer'] = $this->extractPlaceholders($template['content']['footer']['text']);
			}
		}

		return $templates;
	}

	protected function hasPlaceholder(string $text): bool
	{
		return !empty($text) && preg_match("/{{[\d]+}}/", $text);
	}

	protected function extractPlaceholders(string $text): array
	{
		preg_match_all("/({{[\d]+}})/", $text, $matches);

		return !empty($matches[0]) ? $matches[0] : [];
	}

	protected function checkForErrors(array $templates): Result
	{
		$checkResult = new Result();
		foreach ($templates as $template)
		{
			if (!is_array($template))
			{
				$exception = new \Bitrix\Main\SystemException(
					'Incorrect response from the Edna service: ' . var_export($templates, true)
				);

				\Bitrix\Main\Application::getInstance()->getExceptionHandler()->writeToLog($exception);

				return $checkResult->addError(
					new Error('Incorrect response from the Edna service.', 400, $templates)
				);
			}
		}

		return $checkResult;
	}

	protected function removeUnsupportedTemplates(array $templates): array
	{
		$filteredTemplates = [];
		foreach ($templates as $template)
		{
			if (!is_array($template))
			{
				continue;
			}

			if (!$this->checkApprovedStatus($template))
			{
				continue;
			}

			$filteredTemplates[] = $template;
		}

		return $filteredTemplates;
	}

	protected function replaceNameToTitle(array $templates = []): array
	{
		$autoTemplates = TemplateTable::getList([
			'filter' => ['=ACTIVE' => 'Y']
		])->fetchAll();

		$titles = [];
		foreach ($autoTemplates as $autoTemplate)
		{
			$titles[$autoTemplate['NAME']] = $autoTemplate['TITLE'];
		}

		foreach ($templates as $key => $template)
		{
			$templates[$key]['name'] = $titles[$template['name']] ?? $template['name'];
		}

		return $templates;
	}

	protected function excludeOutdatedTemplates(array $templates = []): array
	{
		$outdatedTemplatesResult = TemplateTable::getList([
			'filter' => ['=ACTIVE' => 'N']
		])->fetchAll();

		$outdatedTemplates = [];
		foreach ($outdatedTemplatesResult as $outdatedTemplate)
		{
			$outdatedTemplates[$outdatedTemplate['NAME']] = true;
		}

		$activeTemplates = array_filter($templates, function ($template) use ($outdatedTemplates) {
			return is_array($template) && isset($template['name']) && !isset($outdatedTemplates[$template['name']]);
		});

		return array_values($activeTemplates);
	}

	public function sendTemplate(string $name, string $text, array $examples = [], ?string $langCode = null): Result
	{
		if (is_null($langCode))
		{
			$langCode = Application::getInstance()->getContext()->getLanguage();
		}

		if (!$this->validateLanguage($langCode))
		{
			return (new Result)->addError(new Error('Unknown language code'));
		}

		$validateTemplateName = $this->validateTemplateName($name);
		if (!$validateTemplateName->isSuccess())
		{
			return $validateTemplateName;
		}

		$validateTemplateText = $this->validateTemplateText($text);
		if (!$validateTemplateText->isSuccess())
		{
			return $validateTemplateText;
		}
		$validateExamples = $this->validateExamples($text, $examples);
		if (!$validateExamples->isSuccess())
		{
			return $validateExamples;
		}

		$subjectList = $this->optionManager->getOption('sender_id', []);
		$verifiedSubjectIdResult = $this->getVerifiedSubjectIdList($subjectList);
		if (!$verifiedSubjectIdResult->isSuccess())
		{
			return $verifiedSubjectIdResult;
		}

		$verifiedSubjectIdList = $verifiedSubjectIdResult->getData();

		$requestParams = [
			'messageMatcher' => [
				'name' => $name,
				'channelType' => $this->getChannelType(),
				'language' => $langCode,
				'category' => 'UTILITY',
				'type' => 'OPERATOR',
				'contentType' => 'TEXT',
				'content' => [
					'text' => $text,
					'textExampleParams' => $examples
				]
			],
			'subjectIds' => $verifiedSubjectIdList,
		];

		return $this->externalSender->callExternalMethod(Providers\Edna\Constants\Method::SEND_TEMPLATE, $requestParams);
	}

	protected function getChannelType(): string
	{
		return Providers\Edna\Constants\ChannelType::WHATSAPP;
	}

	protected function validateTemplateText(string $text): Result
	{
		$result = new Result();

		if (mb_strlen($text) > 1024)
		{
			return $result->addError(new Error('The maximum number of characters is 1024'));
		}

		if (!preg_match('/^(?!.* {4,}).*$/ui', $text))
		{
			return $result->addError(new Error('The text cannot contain newlines and 4 spaces in a row'));
		}

		return $result;
	}

	protected function validateExamples(string $text, array $examples): Result
	{
		$result = new Result();

		$variables = [];
		preg_match_all('/{{[0-9]+}}/ui', $text, $variables);
		if (count($variables[0]) !== count($examples))
		{
			return $result->addError(new Error('The number of examples differs from the number of variables'));
		}

		return $result;
	}

	public static function cleanTemplatesCache(): void
	{
		$cache = Cache::createInstance();
		$cache->clean(
			\Bitrix\MessageService\Providers\Edna\WhatsApp\Utils::CACHE_KEY_TEMPLATES,
			\Bitrix\MessageService\Providers\Edna\WhatsApp\Utils::CACHE_DIR_TEMPLATES
		);
	}
}
