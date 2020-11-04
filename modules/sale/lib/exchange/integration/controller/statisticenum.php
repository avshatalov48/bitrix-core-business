<?php


namespace Bitrix\Sale\Exchange\Integration\Controller;


use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Controller;
use Bitrix\Sale\Exchange\Integration\Entity\StatusType;
use Bitrix\Sale\Exchange\Integration\EntityType;

class StatisticEnum extends Controller
{
	public function getStatusTypesAction()
	{
		$r[] = ['code'=>StatusType::SUCCESS_NAME, 'name'=>StatusType::getDescription(StatusType::SUCCESS)];
		$r[] = ['code'=>StatusType::PROCESS_NAME, 'name'=>StatusType::getDescription(StatusType::PROCESS)];
		$r[] = ['code'=>StatusType::FAULTY_NAME, 'name'=>StatusType::getDescription(StatusType::FAULTY)];

		return ['enum'=>$r];
	}

	public function getEntityTypesAction()
	{
		$r[] = ['id'=>EntityType::ORDER, 'name'=>EntityType::getDescription(EntityType::ORDER)];

		return ['enum'=>$r];
	}
}