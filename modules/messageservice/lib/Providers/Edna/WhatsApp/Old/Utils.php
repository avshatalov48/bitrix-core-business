<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp\Old;

use Bitrix\Main\Result;
use Bitrix\MessageService\Providers\Constants\InternalOption;
use Bitrix\MessageService\Providers\Edna\WhatsApp;
use Bitrix\MessageService\Providers\Edna\WhatsApp\ExternalSender;
use Bitrix\MessageService\Providers\OptionManager;

class Utils extends WhatsApp\Utils
{
	public function testConnection(): Result
	{
		$requestParams = ['imType' => 'WHATSAPP'];

		$externalSender =
			new ExternalSender(
				$this->optionManager->getOption(InternalOption::API_KEY),
				Constants::API_ENDPOINT
			)
		;

		return $externalSender->callExternalMethod('im-subject/by-apikey', $requestParams);
	}

	public function getMessageTemplates(string $subject = ''): Result
	{
		$result = new Result();
		if (defined('WA_EDNA_RU_TEMPLATES_STUB') && WA_EDNA_RU_TEMPLATES_STUB === true)
		{
			return $result->setData($this->getMessageTemplatesStub());
		}

		$params = ['imType' => 'whatsapp'];
		if ($subject !== '')
		{
			$params['subject'] = $subject;
		}

		$externalSender =
			new ExternalSender(
			$this->optionManager->getOption(InternalOption::API_KEY),
			Constants::API_ENDPOINT
		);

		$templatesRequestResult = $externalSender->callExternalMethod('getOutMessageMatchers', $params);
		if ($templatesRequestResult->isSuccess())
		{
			$templates = $templatesRequestResult->getData();

			$checkErrors = $this->checkForErrors($templates);
			if ($checkErrors->isSuccess())
			{
				$templates = $this->removeUnsupportedTemplates($templates);
				$result->setData($templates);
			}
			else
			{
				$result->addErrors($checkErrors->getErrors());
			}
		}
		else
		{
			$result->addErrors($templatesRequestResult->getErrors());
		}

		return $result;
	}

	/**
	 * Returns stub with HSM template from docs:
	 * https://edna.docs.apiary.io/#reference/api/getoutmessagematchers
	 *
	 * @return array
	 */
	protected function getMessageTemplatesStub(): array
	{
		return [
			'result' => [
				[
					'id' => 206,
					'name' => 'test template',
					'imType' => 'whatsapp',
					'language' => 'AU',
					'content' => [
						'header' => [],
						'text' => 'whatsapp text',
						'footer' => [
							'text' => 'footer text'
						],
						'keyboard' => [
							'row' => [
								'buttons' => [
									[
										'text' => 'button1',
										'payload' => 'button1',
										'buttonType' => 'QUICK_REPLY'
									]
								]
							]
						]
					],
					'category' => 'ISSUE_UPDATE',
					'status' => 'PENDING',
					'createdAt' => '2020-11-12T11:31:39.000+0000',
					'updatedAt' => '2020-11-12T11:31:39.000+0000'
				],
				[
					'id' => 207,
					'name' => 'one more template',
					'imType' => 'whatsapp',
					'language' => 'AU',
					'content' => [
						'header' => [],
						'text' => 'one more template',
						'footer' => [
							'text' => 'footer text'
						],
						'keyboard' => [
							'row' => [
								'buttons' => [
									[
										'text' => 'button1',
										'payload' => 'button1',
										'buttonType' => 'QUICK_REPLY'
									]
								]
							]
						]
					],
					'category' => 'ISSUE_UPDATE',
					'status' => 'PENDING',
					'createdAt' => '2020-11-12T11:31:39.000+0000',
					'updatedAt' => '2020-11-12T11:31:39.000+0000'
				]
			],
			'code' => 'ok'
		];
	}
}