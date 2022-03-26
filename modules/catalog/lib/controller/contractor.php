<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;

class Contractor extends \Bitrix\Main\Engine\Controller
{
	public function createContractorAction(array $fields)
	{
		if (!CurrentUser::get()->canDoOperation('catalog_store'))
		{
			$this->addError(new Error('Access denied!'));

			return null;
		}

		$companyName = $fields['companyName'];

		if (empty($companyName))
		{
			$this->addError(new Error('Empty name'));

			return null;
		}

		$userId = CurrentUser::get()->getId();
		$fields = [
			'PERSON_TYPE' => CONTRACTOR_JURIDICAL,
			'COMPANY' => $companyName,
			'CREATED_BY' => $userId,
			'MODIFIED_BY' => $userId,
		];

		$contractorId = \CCatalogContractor::add($fields);
		if (!$contractorId)
		{
			$this->addError(new Error('Error adding contractor'));

			return null;
		}

		return [
			'id' => $contractorId
		];
	}
}
