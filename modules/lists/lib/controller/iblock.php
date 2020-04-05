<?
namespace Bitrix\Lists\Controller;

use Bitrix\Iblock\Copy\Manager;
use Bitrix\Lists\Copy\Implement\Children\Field;
use Bitrix\Lists\Copy\Implement\Iblock as IblockImplementer;
use Bitrix\Lists\Security\IblockRight;
use Bitrix\Lists\Security\Right;
use Bitrix\Lists\Security\RightParam;
use Bitrix\Lists\Service\Param;
use Bitrix\Main\Type\Dictionary;

class Iblock extends Entity
{
	public function copyAction()
	{
		$param = $this->getParamFromRequest();
		$params = $param->getParams();

		$this->checkPermission($param, IblockRight::EDIT);
		if ($this->getErrors())
		{
			return null;
		}

		$manager = new Manager($params["IBLOCK_TYPE_ID"], [$params["IBLOCK_ID"]], $params["SOCNET_GROUP_ID"]);

		$manager->setIblockImplementer(new IblockImplementer());
		$manager->setFieldImplementer(new Field());

		$dictionary = new Dictionary([
			"LIST_ELEMENT_URL" => ($params["LIST_ELEMENT_URL"] ? $params["LIST_ELEMENT_URL"] : "")
		]);
		$manager->setDictionary($dictionary);

		$result = $manager->startCopy();

		if ($result->getErrors())
		{
			$this->addErrors($result->getErrors());
			return null;
		}

		$mapIdsCopiedIblock = $manager->getMapIdsCopiedEntity();

		if (array_key_exists($params["IBLOCK_ID"], $mapIdsCopiedIblock))
		{
			return $mapIdsCopiedIblock[$params["IBLOCK_ID"]];
		}
		else
		{
			return null;
		}
	}

	private function checkPermission(Param $param, $permission)
	{
		global $USER;
		$rightParam = new RightParam($param);
		$rightParam->setUser($USER);

		$right = new Right($rightParam, new IblockRight($rightParam));
		$right->checkPermission($permission);
		if ($right->hasErrors())
		{
			$this->addErrors($right->getErrors());
		}
	}
}