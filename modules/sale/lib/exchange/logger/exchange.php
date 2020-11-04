<?php


namespace Bitrix\Sale\Exchange\Logger;


use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Exchange\Internals\ExchangeLogTable;

class Exchange
{
	protected $providerType = null;

	/**
	 * Exchange constructor.
	 * @param $providerType
	 * @throws ArgumentException
	 */
	public function __construct($providerType)
	{
		if($providerType == '')
		{
			throw new ArgumentException('Options providerType must be specified', 'providerType');
		}

		$this->providerType = $providerType;
	}

	/**
	 * @param array $params
	 * @return \Bitrix\Main\ORM\Data\AddResult
	 * @throws \Exception
	 */
	public function add(array $params)
	{
		$params['PROVIDER'] = $this->providerType;
		return ExchangeLogTable::add($params);
	}

	/**
	 * @param $params
	 * @return \Bitrix\Main\ORM\Query\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getList(array $params)
	{
		$params['filter'] = isset($params['filter']) ? $params['filter']:[];

		$params['filter']['PROVIDER'] = $this->providerType;
		return ExchangeLogTable::getList($params);
	}

	/**
	 * @param $timeUpdate
	 * @param $entityTypeId
	 * @param $direction
	 * @return array
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getEffectedRows($timeUpdate, $entityTypeId, $direction)
	{
		$result = array();

		if($timeUpdate <> '')
		{
			$r = ExchangeLogTable::getList(array(
				'select' => array(
					'ENTITY_ID',
					'ENTITY_DATE_UPDATE'
				),
				'filter' => array(
					'ENTITY_TYPE_ID'=>$entityTypeId,
					'=ENTITY_DATE_UPDATE'=>$timeUpdate,
					'=DIRECTION'=>$direction,
					'=PROVIDER'=>$this->providerType,
				),
				'order'=>array('ID'=>'ASC'),

			));
			while ($order = $r->fetch())
				$result[$order['ENTITY_DATE_UPDATE']->toString()][]=$order['ENTITY_ID'];
		}
		return $result;
	}

	public function isEffected($list, array $logs)
	{
		$dateUpdate = $list["DATE_UPDATE"]->toString();

		$result = (isset($logs[$dateUpdate]) &&
			in_array($list['ID'], $logs[$dateUpdate]));

		return $result;
	}

	/**
	 * Clears old logging data
	 */
	public function deleteOldRecords($direction, $interval)
	{
		ExchangeLogTable::deleteOldRecords($direction, $this->providerType, $interval);
	}
}