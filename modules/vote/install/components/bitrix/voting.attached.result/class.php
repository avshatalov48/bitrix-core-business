<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)	die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Security\Sign\BadSignatureException;
use Bitrix\Vote\Attach;
use Bitrix\Vote\Service\AttachedVoteFrontendFormatService;
use Bitrix\Vote\Service\VotedCollectorService;

class VotingAttachedResultComponent extends \CBitrixComponent
{
	private const ANSWER_VOTED_LIMIT = 10;

	public function executeComponent(): void
	{
		$userId = (int)\Bitrix\Main\Engine\CurrentUser::get()->getId();
		if (!$userId)
		{
			$this->arResult['ERROR_DESCRIPTION'] = Loc::getMessage('VOTE_ATTACHED_RESULT_COMPONENT_AUTH_ERROR');

			$this->includeComponentTemplate('error');

			return;
		}

		if (!Loader::includeModule('vote'))
		{
			$this->arResult['ERROR_DESCRIPTION'] = Loc::getMessage('VOTE_ATTACHED_RESULT_COMPONENT_MODULE_VOTE_NOT_INSTALLED');

			$this->includeComponentTemplate('error');

			return;
		}

		try
		{
			$attach = $this->getAttach();
			$this->arResult['VOTE'] = [
				'attach' => (new AttachedVoteFrontendFormatService())->format($attach, $userId),
				'voted' => (new VotedCollectorService())->getByAttach($attach, self::ANSWER_VOTED_LIMIT),
			];
			$this->arResult['VOTED_PAGE_SIZE'] = self::ANSWER_VOTED_LIMIT;
			$this->includeComponentTemplate();
		}
		catch (ObjectNotFoundException|BadSignatureException)
		{
			$this->arResult['ERROR_DESCRIPTION'] = Loc::getMessage('VOTE_ATTACHED_RESULT_COMPONENT_VOTE_NOT_FOUND');
			$this->includeComponentTemplate('error');
		}
	}

	/**
	 * @return Attach
	 * @throws BadSignatureException
	 * @throws ObjectNotFoundException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	private function getAttach(): Attach
	{
		$id = (string)($this->arParams['SIGNED_ATTACH_ID'] ?? '');
		$id = $this->removeQueryParams($id);

		$urlService = new \Bitrix\Vote\Service\AttachedVoteResultUrlService();

		return $urlService->getAttachByUrlId($id);
	}

	private function removeQueryParams(string $value): string
	{
		$value = explode('/', $value, 2)[0] ?? '';
		$value = explode('?', $value, 2)[0] ?? '';

		return $value;
	}
}