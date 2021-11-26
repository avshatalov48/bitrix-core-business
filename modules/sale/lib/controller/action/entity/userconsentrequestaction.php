<?php

namespace Bitrix\Sale\Controller\Action\Entity;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class UserConsentRequestAction
 * @package Bitrix\Sale\Controller\Action\Entity
 * @internal
 */
final class UserConsentRequestAction extends BaseAction
{
	private function checkParams(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		if (empty($fields['ID']) || (int)$fields['ID'] <= 0)
		{
			$result->addError(
				new Main\Error(
					'id not found',
					Sale\Controller\ErrorEnumeration::USER_CONSENT_REQUEST_ACTION_ID_NOT_FOUND
				)
			);
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

		$title = $fields['TITLE'];
		$replaceFields = is_array($fields['FIELDS']) ? $fields['FIELDS'] : [];

		$eventName = $fields['SUBMIT_EVENT_NAME'];
		$eventName = \CUtil::JSescape($eventName);
		$eventName = htmlspecialcharsbx($eventName);

		$params = [
			'ID' => (int)$fields['ID'],
			'IS_CHECKED' => $fields['IS_CHECKED'] === 'Y' ? 'Y' : 'N',
			'IS_LOADED' => $fields['IS_LOADED'] === 'Y' ? 'Y' : 'N',
			'AUTO_SAVE' => $fields['AUTO_SAVE'] === 'Y' ? 'Y' : 'N',
			'SUBMIT_EVENT_NAME' => $eventName,
			'REPLACE' => array(
				'button_caption' => $title,
				'fields' => $replaceFields,
			)
		];

		return new Main\Engine\Response\Component('bitrix:main.userconsent.request', '', $params);
	}
}
