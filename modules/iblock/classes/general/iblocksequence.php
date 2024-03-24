<?php

use Bitrix\Main\Application;
use Bitrix\Iblock;

abstract class CAllIBlockSequence
{
	public $iblock_id = 0;
	public $property_id = 0;

	public function __construct($iblock_id, $property_id = 0)
	{
		$this->iblock_id = $iblock_id;
		$this->property_id = $property_id;
	}

	public function Drop($bAll = false)
	{
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$iblockId = (int)$this->iblock_id;
		$propertyId = (int)$this->property_id;

		//OR part of the where is just for some cleanup
		$strSql = "
			DELETE
			FROM b_iblock_sequence
			WHERE IBLOCK_ID = " . $iblockId . "
			" . (!$bAll ? "AND CODE = 'PROPERTY_" . $propertyId . "'" : "") . "
			OR NOT EXISTS (
				SELECT * FROM
				b_iblock_property
				WHERE " . $helper->getConcatFunction("'PROPERTY_'", 'b_iblock_property.ID') . " = b_iblock_sequence.CODE
				AND b_iblock_property.IBLOCK_ID = b_iblock_sequence.IBLOCK_ID
			)
		";
		unset($helper);

		$connection->queryExecute($strSql);
		unset($connection);
	}

	public function GetCurrent()
	{
		$row = Iblock\SequenceTable::getRow([
			'select' => [
				'SEQ_VALUE'
			],
			'filter' => [
				'=IBLOCK_ID' => (int)$this->iblock_id,
				'=CODE' => 'PROPERTY_' . (int)$this->property_id,
			],
		]);

		return $row['SEQ_VALUE'] ?? 0;
	}

	abstract public function GetNext();

	abstract public function SetNext($value);
}
