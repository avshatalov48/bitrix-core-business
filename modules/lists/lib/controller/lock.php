<?php
namespace Bitrix\Lists\Controller;

use Bitrix\Lists\Security\ElementRight;

class Lock extends Element
{
	public function lockAction()
	{
		$param = $this->getParamFromRequest();
		$params = $param->getParams();

		$this->checkPermission($param, ElementRight::EDIT);
		if ($this->getErrors())
		{
			$this->addErrors($this->getErrors());
			return null;
		}

		\CIBlockElement::WF_Lock($params["ELEMENT_ID"], false);
	}

	public function unLockAction()
	{
		$param = $this->getParamFromRequest();
		$params = $param->getParams();

		$this->checkPermission($param, ElementRight::EDIT);
		if ($this->getErrors())
		{
			$this->addErrors($this->getErrors());
			return null;
		}

		\CIBlockElement::WF_UnLock($params["ELEMENT_ID"], false);
	}
}