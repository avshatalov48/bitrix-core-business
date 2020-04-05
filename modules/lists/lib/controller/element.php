<?
namespace Bitrix\Lists\Controller;

use Bitrix\Iblock\Copy\Implement\Element as ElementImplementer;
use Bitrix\Lists\Entity\Utils;
use Bitrix\Lists\Security\ElementRight;
use Bitrix\Lists\Security\Right;
use Bitrix\Lists\Security\RightParam;
use Bitrix\Lists\Service\Param;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\ContainerCollection;
use Bitrix\Main\Copy\EntityCopier;

class Element extends Entity
{
	public function copyAction()
	{
		$param = $this->getParamFromRequest();
		$params = $param->getParams();

		$this->checkPermission($param, ElementRight::EDIT);
		if ($this->getErrors())
		{
			return null;
		}

		$containerCollection = new ContainerCollection();
		$containerCollection[] = new Container($params["ELEMENT_ID"]);

		$elementImplementer = new ElementImplementer();
		$elementCopier = new EntityCopier($elementImplementer);
		$result = $elementCopier->copy($containerCollection);

		if ($result->getErrors())
		{
			$this->addErrors($result->getErrors());
			return null;

		}
		else
		{
			$resultData = $result->getData();
			return $resultData[$params["ELEMENT_ID"]];
		}
	}

	protected function checkPermission(Param $param, $permission)
	{
		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);
		$rightParam->setEntityId(Utils::getElementId($param->getParams()));

		$right = new Right($rightParam, new ElementRight($rightParam));
		$right->checkPermission($permission);
		if ($right->hasErrors())
		{
			$this->addErrors($right->getErrors());
		}
	}
}