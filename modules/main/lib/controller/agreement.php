<?php
namespace Bitrix\Main\Controller;

use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Error;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\UserConsent;

class Agreement extends Engine\Controller
{
	public function configureActions()
	{
		return [
			'get' => [
				'-prefilters' => [
					Authentication::class,
					Engine\ActionFilter\Csrf::class,
				],
			],
		];
	}

	public function getAction(int $id, string $sec, array $replace): array
	{
		$agreement = $this->getAgreement($id, $sec);
		if (!$agreement)
		{
			return [];
		}

		$agreement->setReplace($replace);

		$result = [
			'id' => $agreement->getId(),
			'title' => $agreement->getTitle(),
			'label' => $agreement->getLabel(),
			'url' => $agreement->getUrl(),
			'content' => [
				'text' => $agreement->getText(),
				'html' => $agreement->getHtml(),
			],
		];

		return $result;
	}

	private function getAgreement(int $id, string $sec): ?UserConsent\Agreement
	{
		$agreement = new UserConsent\Agreement($id);
		if (!$agreement->isExist() || !$agreement->isActive())
		{
			$this->addError(new Error('Agreement not found'));
			return null;
		}

		$secStored = $agreement->getData()['SECURITY_CODE'] ?? '';
		if ($secStored && $sec !== $secStored)
		{
			$this->addError(new Error('Wrong security code'));
			return null;
		}

		return $agreement;
	}
}

