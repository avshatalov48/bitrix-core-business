<?php

namespace Bitrix\Main\Access\Auth;

use Bitrix\Main\Access\AccessCode;

class AccessAuthProvider extends \CAuthProvider
{
	protected const PROVIDER_ID = 'access';

	public static function GetProviders()
	{
		return [
			[
				"ID" => self::PROVIDER_ID,
				"CLASS" => self::class,
			]
		];
	}

	public function __construct()
	{
		$this->id = self::PROVIDER_ID;
	}

	public function UpdateCodes($userId)
	{
		global $DB;

		$iblockId = \COption::GetOptionInt('intranet', 'iblock_structure');
		if ($iblockId > 0)
		{
			$tableName = "b_uts_iblock_". $iblockId ."_section";

			if (!$DB->TableExists($tableName))
			{
				return null;
			}

			$res = $DB->query("
				SELECT VALUE_ID
				FROM ". $tableName ."
				WHERE UF_HEAD = " . $userId
			);

			while ($row = $res->fetch())
			{
				$id = (int) $row['VALUE_ID'];
				$sql = '
				INSERT INTO b_user_access
				(`USER_ID`, `PROVIDER_ID`, `ACCESS_CODE`)
				VALUES
				('.$userId.',"'.$this->id.'","'.AccessCode::ACCESS_DIRECTOR.'0"),
				('.$userId.',"'.$this->id.'","'.AccessCode::ACCESS_DIRECTOR.$id.'")
			';
				$DB->query($sql);
			}
		}
	}
}