<?php
namespace Bitrix\Sale\CrmSiteMaster\Tools;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Class CrmPackage
 * @package Bitrix\Sale\CrmSiteMaster\Tools
 */
class CrmPackage
{
	private $id;
	private $siteId;

	/**
	 * @param $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getPath()
	{
		$pathToWizard = \CWizardUtil::MakeWizardPath($this->getId());
		$path = \CWizardUtil::GetRepositoryPath().$pathToWizard;

		return $path;
	}

	/**
	 * @param $siteId
	 */
	public function setSiteId($siteId)
	{
		$this->siteId = $siteId;
	}

	/**
	 * @return string
	 */
	public function getSiteId()
	{
		return $this->siteId;
	}
}