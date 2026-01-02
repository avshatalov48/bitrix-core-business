<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)	die();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Vote\Config\Feature;

class VotingImEditComponent extends \CBitrixComponent
{
	protected array $errors = [];

	public function executeComponent(): void
	{
		if (!$this->checkModules())
		{
			$this->showErrors();

			return;
		}

		$userId = Bitrix\Main\Engine\CurrentUser::get()->getId();
		$chatId = (string)$this->request->get('chatId');

		if (!$userId || !$chatId)
		{
			LocalRedirect('/');
		}

		$this->showVoteEditor($chatId);
	}

	protected function showVoteEditor(string $chatId): void
	{
		$this->arResult['CHAT_ID'] = $chatId;
		$this->arResult['CHANNEL_ID'] = \Bitrix\Vote\Integration\Im\ImVote::getOrCreateImMessageChannel();

		$this->includeComponentTemplate();
	}

	protected function checkModules(): bool
	{
		if (!Loader::includeModule('im'))
		{
			$this->errors[] = Loc::getMessage('VOTE_IM_EDIT_COMPONENT_MODULE_NOT_INSTALLED');
		}

		if (!Loader::includeModule('vote'))
		{
			$this->errors[] = Loc::getMessage('VOTE_IM_EDIT_COMPONENT_MODULE_VOTE_NOT_INSTALLED');
		}

		if (!Feature::instance()->isImIntegrationEnabled())
		{
			$this->errors[] = Loc::getMessage('VOTE_IM_EDIT_COMPONENT_FEATURE_NOT_AVAILABLE');
		}

		return empty($this->errors);
	}

	protected function showErrors(): void
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}
}