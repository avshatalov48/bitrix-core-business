<?php

namespace Bitrix\Sale\Controller\Action\Entity;

use Bitrix\Main;
use Bitrix\Main\Engine\Response\ContentArea\ContentAreaInterface;
use Bitrix\Main\Engine\Response\HtmlContent;
use Bitrix\Sale;

/**
 * Class UserConsentRequestAction
 * @package Bitrix\Sale\Controller\Action\Entity
 * @internal
 */
final class UserConsentRequestAction extends Sale\Controller\Action\BaseAction
{
	private function checkParams(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		if (empty($fields['ITEMS']))
		{
			$result->addError(
				new Main\Error(
					'consents not found',
					Sale\Controller\ErrorEnumeration::USER_CONSENT_REQUEST_ACTION_CONSENTS_NOT_FOUND
				)
			);
		}

		foreach ($fields['ITEMS'] as $item)
		{
			if (empty($item['ID']) || (int)$item['ID'] <= 0)
			{
				$result->addError(
					new Main\Error(
						'consents not found',
						Sale\Controller\ErrorEnumeration::USER_CONSENT_REQUEST_ACTION_ID_NOT_FOUND
					)
				);
			}
		}

		return $result;
	}

	public function run(array $fields)
	{
		$checkParamsResult = $this->checkParams($fields);
		if (!$checkParamsResult->isSuccess())
		{
			$this->addErrors($checkParamsResult->getErrors());

			return null;
		}

		$title = (string)($fields['TITLE'] ?? '');
		$replaceFields = !empty($fields['FIELDS']) && is_array($fields['FIELDS']) ? $fields['FIELDS'] : [];

		$eventName = (string)($fields['SUBMIT_EVENT_NAME'] ?? '');
		$eventName = \CUtil::JSescape($eventName);
		$eventName = htmlspecialcharsbx($eventName);

		if (empty($fields['ITEMS']))
		{
			return null;
		}

		global $APPLICATION;

		ob_start();
		foreach ($fields['ITEMS'] as $item)
		{
			$APPLICATION->IncludeComponent(
				'bitrix:main.userconsent.request',
				'',
				[
					'ID' => (int)$item['ID'],
					'IS_CHECKED' => $item['CHECKED'],
					'REQUIRED' => $item['REQUIRED'],
					'IS_LOADED' => ($fields['IS_LOADED'] ?? 'N') === 'Y' ? 'Y' : 'N',
					'AUTO_SAVE' => ($fields['AUTO_SAVE'] ?? 'N') === 'Y' ? 'Y' : 'N',
					'SUBMIT_EVENT_NAME' => $eventName . '-' . $item['ID'],
					'REPLACE' => [
						'button_caption' => $title,
						'fields' => $replaceFields,
					],
				],
			);
		}

		return $this->getHtmlContent(ob_get_clean());
	}

	private function getHtmlContent(string $html): HtmlContent
	{
		$content = new class($html) implements ContentAreaInterface
		{
			private string $html;

			public function __construct(string $html)
			{
				$this->html = $html;
			}

			public function getHtml()
			{
				return $this->html;
			}
		};

		return new HtmlContent($content);
	}
}
