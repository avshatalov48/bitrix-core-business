<?php

use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Access\Map\MailingAction;
use Bitrix\Sender\Internals\CommonSenderComponent;
use Bitrix\Sender\Message;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderMessageEditorComponent extends CommonSenderComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		if (!$this->arParams['MESSAGE_CODE'])
		{
			$this->errors->setError(new Error('Message code is not set.'));
		}

		return $this->errors->count() == 0;
	}

	protected function initParams()
	{
		$this->arParams['VALUE'] = isset($this->arParams['VALUE']) ? $this->arParams['VALUE'] : '';
		$this->arParams['MESSAGE_CODE'] = isset($this->arParams['MESSAGE_CODE']) ? $this->arParams['MESSAGE_CODE'] : null;
		$this->arParams['MESSAGE_ID'] = isset($this->arParams['MESSAGE_ID']) ? $this->arParams['MESSAGE_ID'] : null;
		$this->arParams['MESSAGE'] = isset($this->arParams['MESSAGE']) ? $this->arParams['MESSAGE'] : null;

		$this->arParams['TEMPLATE_TYPE'] = isset($this->arParams['TEMPLATE_TYPE']) ? $this->arParams['TEMPLATE_TYPE'] : null;
		$this->arParams['TEMPLATE_ID'] = isset($this->arParams['TEMPLATE_ID']) ? $this->arParams['TEMPLATE_ID'] : null;
		$this->arParams['IS_TRIGGER'] = isset($this->arParams['IS_TRIGGER']) ? (bool) $this->arParams['IS_TRIGGER'] :
			false;
		$this->arParams['CAN_EDIT'] = $this->arParams['CAN_EDIT']??
									$this->getAccessController()->check(
										MailingAction::getMap()[$this->arParams['MESSAGE_CODE']]
										);

		$isBus = !Bitrix\Main\Loader::includeModule('intranet');

		$baseUri = $isBus ? '/bitrix/admin/agreement_edit.php' : '/settings/configs/userconsent/';

		$this->arParams['CONSENT_PARAMS'] = [
			'PATH_TO_ADD' => $baseUri . ($isBus ? '?ID=0' : 'edit/0/'),
			'PATH_TO_EDIT' =>  $baseUri . ($isBus ? '?ID=#id#' : 'edit/#id#/') ,
			'PATH_TO_CONSENT_LIST' => $isBus ? '/bitrix/admin/agreement_consents.php?AGREEMENT_ID=#id#&apply_filter=Y' :
				$baseUri . 'consents/#id#/?AGREEMENT_ID=#id#&apply_filter=Y'
		];
	}

	protected function prepareResult()
	{
		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';
		$this->arResult['SUBMIT_FORM_URL'] = Context::getCurrent()->getRequest()->getRequestUri();

		// init message
		$message = $this->arParams['MESSAGE'];
		if (!$message)
		{
			try
			{
				$message = Message\Adapter::getInstance($this->arParams['MESSAGE_CODE']);
				$message->loadConfiguration($this->arParams['MESSAGE_ID']);
			}
			catch (\Bitrix\Main\SystemException $exception)
			{
				$this->errors->setError(new Error(Loc::getMessage(
					'SENDER_MESSAGE_EDITOR_ERROR_UNKNOWN_CODE',
					array('%code%' => $this->arParams['MESSAGE_CODE'])
				)));

				return false;
			}
		}


		// get options list
		$configuration = $message->getConfiguration();
		$this->arResult['MESSAGE_VIEW'] = $configuration->getView();
		$this->arResult['LIST'] = array(
			array(
				'options' => Message\Configuration::convertToArray(
					$configuration->getOptionsByGroup(Message\ConfigurationOption::GROUP_DEFAULT)
				),
				'isAdditional' => false,
			)
		);

		$options = Message\Configuration::convertToArray(
			$configuration->getOptionsByGroup(Message\ConfigurationOption::GROUP_ADDITIONAL)
		);
		if (count($options) > 0)
		{
			$this->arResult['LIST'][] = array(
				'options' => $options,
				'isAdditional' => true,
			);
		}


		$this->arResult['IS_SUPPORT_TESTING'] = $message->getTester()->isSupport();
		$this->arResult['MESSAGE_CODE'] = $message->getCode();
		$this->arResult['MESSAGE_ID'] = $message->getId();

		return true;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		parent::executeComponent();
		parent::prepareResultAndTemplate();
	}

	public function getEditAction()
	{
		return $this->getViewAction();
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_MAILING_VIEW;
	}
}