<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Error,
	Bitrix\Main\Loader,
	Bitrix\Sale\Domain;

Loc::loadMessages(__FILE__);

/**
 * Class SaleDomainVerificationFormAjaxController
 */
class SaleDomainVerificationFormAjaxController extends Bitrix\Main\Engine\Controller
{
	/** @var Domain\Verification\BaseManager $domainVerificationManager */
	private $domainVerificationManager;

	/**
	 * @param \Bitrix\Main\Engine\Action $action
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	protected function processBeforeAction(\Bitrix\Main\Engine\Action $action)
	{
		Loader::includeModule('sale');

		$parameters = $this->getUnsignedParameters();
		if ($parameters["MANAGER"])
		{
			if (is_subclass_of($parameters["MANAGER"], Domain\Verification\BaseManager::class))
			{
				$this->domainVerificationManager = $parameters["MANAGER"];
			}
			else
			{
				$this->errorCollection->add([new Error(Loc::getMessage("SALE_DVF_AJAX_MANAGER_ERROR"))]);
			}
		}
		else
		{
			$this->errorCollection->add([new Error(Loc::getMessage("SALE_DVF_AJAX_MANAGER_NOT_FOUND"))]);
		}

		return parent::processBeforeAction($action);
	}

	/**
	 * @param $id
	 * @return array
	 * @throws Exception
	 */
	public function deleteDomainAction($id)
	{
		if ($this->errorCollection->isEmpty())
		{
			$result = $this->domainVerificationManager::delete($id);
			if (!$result->isSuccess())
			{
				$this->errorCollection->add([new Error(Loc::getMessage("SALE_DVF_AJAX_DELETE_DOMAIN_ACTION_ERROR"))]);
			}
		}

		return [];
	}
}
