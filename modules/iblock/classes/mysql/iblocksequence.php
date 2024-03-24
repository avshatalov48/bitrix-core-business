<?php

use Bitrix\Main\Application;

class CIBlockSequence extends CAllIBlockSequence
{
	public function GetNext()
	{
		$connection = Application::getConnection();

		$iblockId = (int)$this->iblock_id;
		$propertyId = (int)$this->property_id;

		$query = "
			INSERT INTO b_iblock_sequence (IBLOCK_ID, CODE, SEQ_VALUE)
			VALUES (" . $iblockId . ", 'PROPERTY_" . $propertyId . "', LAST_INSERT_ID(1))
			ON DUPLICATE KEY UPDATE SEQ_VALUE = LAST_INSERT_ID(SEQ_VALUE + 1)
		";

		$connection->queryExecute($query);

		return $connection->getInsertedId();
	}

	public function SetNext($value)
	{
		$value = (int)$value;

		$connection = Application::getConnection();

		$iblockId = (int)$this->iblock_id;
		$propertyId = (int)$this->property_id;

		$query = "
			INSERT INTO b_iblock_sequence (IBLOCK_ID, CODE, SEQ_VALUE)
			VALUES (" . $iblockId . ", 'PROPERTY_" . $propertyId . "', LAST_INSERT_ID(" . $value . "))
			ON DUPLICATE KEY UPDATE SEQ_VALUE = LAST_INSERT_ID(" . $value . ")
		";

		$connection->queryExecute($query);

		return $connection->getInsertedId();
	}
}
