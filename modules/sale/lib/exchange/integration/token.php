<?php


namespace Bitrix\Sale\Exchange\Integration;


use Bitrix\Sale\Exchange\Integration\Entity\B24IntegrationRelationTable;
use Bitrix\Sale\Exchange\Integration\Entity\B24integrationTokenTable;

class Token
{
	static public function getToken(array $fields, $guid = null)
	{
		if($guid)
		{
			$fields["guid"] = $guid;
		}

		return static::createToken($fields);
	}

	static private function createToken(array $fields)
	{
		$token = null;
		if (isset($fields["guid"]))
		{
			$token = Entity\B24integrationTokenTable::getList(["select" => ["*"], "filter" => ["=GUID" => $fields["guid"]]])->fetchObject();
		}

		$token = $token ?: new Entity\Token();
		$result = $token->update($fields);

		return $result->isSuccess() ? $token : null;
	}

	/**
	 * @param $guid
	 * @internal
	 */
	static public function getExistsByGuid($guid)
	{
		$token = Entity\B24integrationTokenTable::getList(["select" => ["*"], "filter" => ["=GUID" => $guid]])->fetchObject();
		return ($token);
	}

	static public function delete($guid)
	{
		$row = B24integrationTokenTable::getRow(["filter" => ["=GUID" => $guid]]);
		$primary = $row ? $row['ID']:0;
		if($primary>0)
		{
			B24integrationTokenTable::delete($primary);
		}
	}
}