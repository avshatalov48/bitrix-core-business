<?php
namespace Bitrix\Bizproc\Workflow\Template\Packer\Result;

use Bitrix\Main;

class Pack extends Main\Result
{
	protected $packageData;

	/**
	 * @param $packageData
	 * @return $this
	 */
	public function setPackage($packageData)
	{
		$this->packageData = $packageData;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPackage()
	{
		return $this->packageData;
	}
}