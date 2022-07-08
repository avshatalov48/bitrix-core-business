<?php

use Bitrix\Mail\Internals\UserSignatureTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class MailUserSignatureEditComponent extends CBitrixComponent
{
	public function onPrepareComponentParams($params)
	{
		$params = parent::onPrepareComponentParams($params);

		return $params;
	}

	/**
	 * @return mixed|void
	 */
	public function executeComponent()
	{
		Loader::includeModule('fileman');
		if(!Loader::includeModule('mail'))
		{
			$this->showError(Loc::getMessage('MAIL_USERSIGNATURE_MODULE_ERROR'));
			return;
		}

		$this->arResult = [];
		$this->arResult['IFRAME'] = $this->arParams['IFRAME'] == 'Y' || $this->request->get('IFRAME') == 'Y' ? 'Y' : 'N';

		$senders = $this->getSenders();
		$this->arResult['senderType'] = null;
		$this->arResult['addresses'] = $this->arResult['senders'] = [];
		foreach($senders as $sender)
		{
			if(!isset($this->arResult['addresses'][$sender['email']]))
			{
				$this->arResult['addresses'][$sender['email']] = $sender['email'];
			}
			if(!isset($this->arResult['senders'][$sender['formated']]))
			{
				$this->arResult['senders'][$sender['formated']] = $sender['formated'];
			}
		}

		$signatureId = $this->arParams['VARIABLES']['id'];
		if($signatureId > 0)
		{
			$signature = UserSignatureTable::getById($signatureId)->fetchObject();
		}

		if (!empty($signature))
		{
			$this->arResult['signature'] = $signature->getSignature();
			$this->arResult['TITLE'] = Loc::getMessage('MAIL_USERSIGNATURE_EDIT_TITLE');
			$this->arResult['signatureId'] = $signatureId;
			$sender = $signature->getSender();
			$this->arResult['sender'] = $sender;
			if($sender)
			{
				if(isset($this->arResult['addresses'][$sender]))
				{
					$this->arResult['senderType'] = UserSignatureTable::TYPE_ADDRESS;
					$this->arResult['selectedAddress'] = $this->arResult['addresses'][$sender];
				}
				elseif(isset($this->arResult['senders'][$sender]))
				{
					$this->arResult['senderType'] = UserSignatureTable::TYPE_SENDER;
					$this->arResult['selectedSender'] = $this->arResult['senders'][$sender];
				}
			}
		}
		else
		{
			$this->arResult['TITLE'] = Loc::getMessage('MAIL_USERSIGNATURE_ADD_TITLE');
		}

		if(!$this->arResult['senderType'])
		{
			$this->arResult['senderType'] = UserSignatureTable::TYPE_SENDER;
			$this->arResult['selectedAddress'] = reset($this->arResult['addresses']);
			$this->arResult['selectedSender'] = reset($this->arResult['senders']);
		}
		elseif($this->arResult['senderType'] === UserSignatureTable::TYPE_ADDRESS)
		{
			$this->arResult['selectedSender'] = reset($this->arResult['senders']);
		}
		else
		{
			$this->arResult['selectedAddress'] = reset($this->arResult['addresses']);
		}

		global $APPLICATION;
		$APPLICATION->SetTitle($this->arResult['TITLE']);

		$this->includeComponentTemplate();
	}

	protected function showError($error)
	{
		ShowError($error);
		$this->includeComponentTemplate();
	}

	/**
	 * @return array
	 */
	protected function getSenders()
	{
		\CBitrixComponent::includeComponentClass('bitrix:main.mail.confirm');
		return \MainMailConfirmComponent::prepareMailboxes();
	}
}