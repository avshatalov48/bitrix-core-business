<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage bitrix24
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Main\Mail\Internal;

use Bitrix\Main\Entity;

class SenderSendCounterTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_main_mail_sender_send_counter';
	}

	public static function getMap()
	{
		return array(
			'DATE_STAT' => array(
				'data_type' => 'date',
				'primary' => true,
			),
			'EMAIL' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'CNT' => array(
				'data_type' => 'integer'
			),
		);
	}

	public static function mergeData(array $insert, array $update)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();
		$helper = $connection->getSqlHelper();

		$sql = $helper->prepareMerge($entity->getDBTableName(), $entity->getPrimaryArray(), $insert, $update);

		$sql = current($sql);
		if($sql <> '')
		{
			$connection->queryExecute($sql);
			$entity->cleanCache();
		}
	}
}
