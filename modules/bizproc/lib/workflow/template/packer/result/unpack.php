<?php
namespace Bitrix\Bizproc\Workflow\Template\Packer\Result;

use Bitrix\Main;
use Bitrix\Bizproc\Workflow\Template\Tpl;

class Unpack extends Main\Result
{
	/** @var Tpl */
	protected $tpl;

	/** @var array */
	protected $documentFields;

	/** @var array */
	protected $requiredApplications;

	/**
	 * @param Tpl $tpl
	 * @return $this
	 */
	public function setTpl(Tpl $tpl)
	{
		$this->tpl = $tpl;
		return $this;
	}

	/**
	 * @return Tpl
	 */
	public function getTpl()
	{
		return $this->tpl;
	}

	/**
	 * @return array
	 */
	public function getDocumentFields()
	{
		return $this->documentFields;
	}

	/**
	 * @param array $documentFields
	 * @return $this
	 */
	public function setDocumentFields(array $documentFields)
	{
		$this->documentFields = $documentFields;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getRequiredApplications()
	{
		return $this->requiredApplications;
	}

	/**
	 * @param array $requiredApplications
	 * @return $this
	 */
	public function setRequiredApplications($requiredApplications)
	{
		$this->requiredApplications = $requiredApplications;
		return $this;
	}
}