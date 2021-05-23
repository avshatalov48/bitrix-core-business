<?php
namespace Bitrix\Sale\BsmSiteMaster\Tools;

/**
 * Class BsmPackage
 * @package Bitrix\Sale\BsmSiteMaster\Tools
 */
class BsmPackage
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