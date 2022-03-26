<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Rest\Entity\BusinessValuePersonDomainType;
use Bitrix\Sale\Rest\Entity\RelationType;
use Bitrix\Sale\Result;

class Enum extends ControllerBase
{
	public function getRelationTypesAction()
	{
		$r=[];
		foreach(RelationType::getAllDescriptions() as $id=>$name)
		{
			$r[] = ['ID'=>RelationType::resolveName($id), 'NAME'=>$name];
		}
		return ['ENUM'=>$r];
	}

	public function getBusinessValuePersonDomainTypesAction()
	{
		$r=[];
		foreach(BusinessValuePersonDomainType::getAllDescriptions() as $id=>$name)
		{
			$r[] = ['ID'=>BusinessValuePersonDomainType::resolveName($id), 'NAME'=>$name];
		}
		return ['ENUM'=>$r];
	}

	public function getPaymentIsCashTypesAction()
	{
		$messages = Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/admin/pay_system_edit.php');

		$r[] = ['ID'=>'N', 'NAME'=>$messages['SPS_IS_CASH_TYPE_NO_CASH']];
		$r[] = ['ID'=>'Y', 'NAME'=>$messages['SPS_IS_CASH_TYPE_CASH']];
		$r[] = ['ID'=>'A', 'NAME'=>$messages['SPS_IS_CASH_TYPE_ACQUIRING']];

		return ['ENUM'=>$r];
	}

	public function getPropertyTypesAction()
	{
		$r = [];
		$property = new Property();

		foreach ($property->getTypes()['ENUM'] as $name=>$description)
		{
			if($name == 'LOCATION')
				continue;
			if($name == 'FILE')
				continue;

			$r[$name] = $description;

		}
		return ['ENUM'=>$r];
	}

	public function getStatusTypesAction()
	{
		$messages = Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/admin/status_edit.php');

		$r[] = ['ID'=>'O', 'NAME'=>$messages['SSEN_TYPE_O']];
		$r[] = ['ID'=>'D', 'NAME'=>$messages['SSEN_TYPE_D']];

		return ['ENUM'=>$r];
	}

	protected function checkPermissionEntity($name, $arguments=[])
	{
		return new Result();
	}
}