<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\MessageService\Internal\Entity\MessageTable;
use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Providers\Edna\EdnaUtils;
use Bitrix\MessageService\Providers\OptionManager;

class Utils extends EdnaUtils
{
	protected string $providerId;
	protected OptionManager $optionManager;

	public function __construct(string $providerId, OptionManager $optionManager)
	{
		$this->providerId = $providerId;

		parent::__construct($optionManager);
	}

	protected function initializeDefaultExternalSender(): Providers\ExternalSender
	{
		return new ExternalSender(
			$this->optionManager->getOption(Providers\Constants\InternalOption::API_KEY),
			Constants::API_ENDPOINT,
			$this->optionManager->getSocketTimeout(),
			$this->optionManager->getStreamTimeout()
		);
	}

	public function getMessageTemplates(string $subject = ''): Result
	{
		if ($this->optionManager->getOption('enable_templates_stub', 'N') === 'Y')
		{
			return $this->removeUnsupportedTemplates($this->getMessageTemplatesStub());
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
		foreach ($verifiedSubjectIdList as $subjectId)
		{
			$requestParams = [
				'subjectId' => $subjectId
			];

			$templatesRequestResult =
				$this->externalSender->callExternalMethod(Providers\Edna\Constants\Method::GET_TEMPLATES, $requestParams)
			;

			if ($templatesRequestResult->isSuccess())
			{
				$templates = array_merge($templates, $templatesRequestResult->getData());
			}
		}

		$result = new Result();
		$result->setData($templates);

		return $this->removeUnsupportedTemplates($result);
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

	protected function getMessageTemplatesStub(): Result
	{
		$result = new Result();
		$result->setData([
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
		]);

		return $result;
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
	protected function checkForPlaceholders($template): bool
	{
		return
			$this->hasPlaceholder($template['content']['header']['text'] ?? '')
			|| $this->hasPlaceholder($template['content']['text'] ?? '')
			|| $this->hasPlaceholder($template['content']['footer']['text'] ?? '')
			;
	}

	protected function hasPlaceholder(string $text): bool
	{
		$placeholder = '{{1}}';

		return strpos($text, $placeholder) !== false;
	}

	protected function removeUnsupportedTemplates(Result $templates): Result
	{
		if (!$templates->isSuccess())
		{
			return $templates;
		}

		$templatesData = $templates->getData();
		if (!$templatesData)
		{
			return $templates;
		}

		$filteredTemplates = [];
		foreach ($templatesData as $template)
		{
			if (!$this->checkApprovedStatus($template))
			{
				continue;
			}
			if ($this->checkForPlaceholders($template))
			{
				continue;
			}

			$filteredTemplates[] = $template;
		}

		$templatesData = $filteredTemplates;
		$result = new Result();
		$result->setData($templatesData);

		return $result;
	}
}