<?php

namespace Bitrix\Sender\UI\Controller;

use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\JsonController;
use Bitrix\Main\Engine\JsonPayload;
use Bitrix\Main;
use Bitrix\Sender\Preset\Templates\Consent;
use Bitrix\Sender\Security;

class ConsentPreview extends JsonController
{
	protected function getDefaultPreFilters()
	{
		return [
			new ActionFilter\Authentication(),
			new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
			new ActionFilter\Csrf(),
			new ActionFilter\CloseSession()
		];
	}

	public function loadDataAction(JsonPayload $payload)
	{
		$request = $payload->getData();
		$request = is_array($request) ? $request : [];

		$agreement = $this->getAgreement((int)$request['id']);

		if (!$agreement)
		{
			return false;
		}

		$contentBody = Security\Sanitizer::fixReplacedStyles($agreement->getHtml());
		$contentBody = Security\Sanitizer::sanitizeHtml($contentBody, $agreement->getText());
		return [
			'consentBody' => $contentBody,
			'approveBtnText' => Consent::getApproveBtnText($agreement),
			'rejectBtnText' => Consent::getRejectnBtnText(),
		];
	}

	private function getAgreement(int $agreementId): ?Main\UserConsent\Agreement
	{
		$agreement = new Main\UserConsent\Agreement($agreementId, ['fields' => []]);
		if (!$agreement->isActive() || !$agreement->isExist())
		{
			return null;
		}

		return $agreement;
	}
}
