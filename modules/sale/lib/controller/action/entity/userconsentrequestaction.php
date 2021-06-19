<?php

namespace Bitrix\Sale\Controller\Action\Entity;

use Bitrix\Main;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\Json;
use Bitrix\Sale;

class UserConsentRequestAction extends BaseAction
{
	private function checkParams(array $fields): Sale\Result
	{
		$result = new Sale\Result();

		if (empty($fields['ID']) || (int)$fields['ID'] <= 0)
		{
			$this->addError(new Main\Error('id not found', 202440400001));
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
		$replaceFields = \is_array($fields['FIELDS']) ? $fields['FIELDS'] : Json::encode([]);

		if (!Main\Application::getInstance()->isUtfMode())
		{
			$replaceFields = Encoding::convertEncoding($replaceFields, SITE_CHARSET, "UTF-8");
		}

		$replaceFields = Json::decode($replaceFields);

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
				'fields' => implode("\n\r", $replaceFields)
			)
		];

		return new Main\Engine\Response\Component('bitrix:main.userconsent.request', '', $params);
	}
}
