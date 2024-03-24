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
			VALUES (" . $iblockId . ", 'PROPERTY_" . $propertyId ."', 1)
			ON CONFLICT (IBLOCK_ID, CODE) DO UPDATE SET SEQ_VALUE = b_iblock_sequence.SEQ_VALUE + 1
			RETURNING SEQ_VALUE
		";

		$result = $connection->query($query);
		$row = $result->fetch();

		return $row['SEQ_VALUE'];
	}

	public function SetNext($value)
	{
		$value = (int)$value;

		$connection = Application::getConnection();

		$iblockId = (int)$this->iblock_id;
		$propertyId = (int)$this->property_id;

		$query = "
			INSERT INTO b_iblock_sequence (IBLOCK_ID, CODE, SEQ_VALUE)
			VALUES (" . $iblockId . ", 'PROPERTY_" . $propertyId . "', " . $value . ")
			ON CONFLICT (IBLOCK_ID, CODE) DO UPDATE SET SEQ_VALUE = " . $value . "
			RETURNING SEQ_VALUE
		";

		$result = $connection->query($query);
		$row = $result->fetch();

		return $row['SEQ_VALUE'];
	}
}
