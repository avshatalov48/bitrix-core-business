<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

class CBitrixCatalogImport1C extends CBitrixComponent
{
	const XML_TREE_TABLE_NAME = 'b_xml_tree_import_1c';

	public function checkDatabaseServerTime($secondsDrift = 600)
	{
		global $DB;

		CTimeZone::Disable();
		$sql = "select ".$DB->DateFormatToDB("YYYY-MM-DD HH:MI:SS", $DB->GetNowFunction())." DB_TIME from b_user";
		$query = $DB->Query($DB->TopSql($sql, 1));
		$record = $query->Fetch();
		CTimeZone::Enable();

		$dbTime = $record? MakeTimeStamp($record["DB_TIME"], "YYYY-MM-DD HH:MI:SS"): 0;
		$webTime = time();

		if ($dbTime)
		{
			if ($dbTime > ($webTime + $secondsDrift))
				return false;
			elseif ($dbTime < ($webTime - $secondsDrift))
				return false;
			else
				return true;
		}

		return true;
	}

	public function cleanUpDirectory($directoryName)
	{
		//Cleanup previous import files
		$directory = new \Bitrix\Main\IO\Directory($directoryName);
		if ($directory->isExists())
		{
			if (defined("BX_CATALOG_IMPORT_1C_PRESERVE"))
			{
				$i = 0;
				while (\Bitrix\Main\IO\Directory::isDirectoryExists($directory->getPath().$i))
				{
					$i++;
				}
				$directory->rename($directory->getPath().$i);
			}
			else
			{
				foreach ($directory->getChildren() as $directoryEntry)
				{
					$match = array();
					if ($directoryEntry->isDirectory() && $directoryEntry->getName() === "Reports")
					{
						$emptyDirectory = true;
						$reportsDirectory = new \Bitrix\Main\IO\Directory($directoryEntry->getPath());
						foreach ($reportsDirectory->getChildren() as $reportsEntry)
						{
							$match = array();
							if (preg_match("/(\\d\\d\\d\\d-\\d\\d-\\d\\d)\\./", $reportsEntry->getName(), $match))
							{
								if (
									$match[1] >= date("Y-m-d", time()-5*24*3600) //no more than 5 days old
									&& $match[1] < date("Y-m-d") //not today or future
								)
								{
									//Preserve the file
									$emptyDirectory = false;
								}
								else
								{
									$reportsEntry->delete();
								}
							}
							else
							{
								$reportsEntry->delete();
							}
						}

						if ($emptyDirectory)
						{
							$directoryEntry->delete();
						}
					}
					else
					{
						$directoryEntry->delete();
					}
				}
			}
		}
	}
}