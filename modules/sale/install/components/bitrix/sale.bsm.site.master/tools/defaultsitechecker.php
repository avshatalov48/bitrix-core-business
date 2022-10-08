<?php

namespace Bitrix\Sale\BsmSiteMaster\Tools;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class DefaultSiteChecker
 * @package Bitrix\Sale\CrmSiteMaster\Tools
 */
class DefaultSiteChecker
{
	private $result;

	public function __construct()
	{
		$this->result = new Main\Result();
	}

	/**
	 * @return Main\Result
	 */
	public function checkSite(): Main\Result
	{
		if (!$this->isDefaultSiteExists())
		{
			$this->setError();
		}

		return $this->getResult();
	}

	private function isDefaultSiteExists(): bool
	{
		return (bool)Main\SiteTable::getList([
			'select' => ['LID'],
			'filter' => [
				'ACTIVE' => 'Y',
				'DEF' => 'Y',
			]
		])->fetch();
	}

	private function setError()
	{
		$this->result->addError(
			new Main\Error(
				Loc::getMessage('SALE_BSM_WIZARD_DEFAULTSITECHECKER_DEFAULT_SITE_NOT_EXISTS')
			)
		);
	}

	private function getResult(): Main\Result
	{
		return $this->result;
	}
}