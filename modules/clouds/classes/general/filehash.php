<?php

class CCloudFileHash
{
	public static function getFileHashAgent($bucket_id, $step_size = 1000)
	{
		$bucket_id = intval($bucket_id);
		if ($bucket_id <= 0)
		{
			return '';
		}
		$bucket = new CCloudStorageBucket($bucket_id);
		if (!$bucket->Init())
		{
			return '';
		}

		$last_key = \Bitrix\Clouds\FileHashTable::getLastKey($bucket_id);
		$step_size = intval($step_size);
		if ($step_size <= 0)
		{
			$step_size = 1000;
		}

		$files = $bucket->ListFiles('/', true, $step_size, $last_key);
		if ($files && $files["file"])
		{
			\Bitrix\Clouds\FileHashTable::addList($bucket_id, $files);
		}

		if (!$files || count($files["file"]) < $step_size)
		{
			//We have done with the listing proceed to save hashes to b_file_hash table.
			return "CCloudFileHash::setFileHashAgent($bucket_id, 0, $step_size);";
		}
		//Continue to read cloud hashes to the database.
		return "CCloudFileHash::getFileHashAgent($bucket_id, $step_size);";
	}

	public static function setFileHashAgent($bucket_id, $last_file_id, $step_size = 1000)
	{
		global $DB;
		$bucket_id = intval($bucket_id);
		$last_file_id = intval($last_file_id);
		$step_size = intval($step_size);
		if ($step_size <= 0)
		{
			$step_size = 1000;
		}

		$rs = $DB->Query("
			select f.ID, concat(f.SUBDIR, '/', f.FILE_NAME) FILE_PATH, f.FILE_SIZE
			from b_file f
			LEFT JOIN b_file_hash h on h.FILE_ID = f.ID
			where f.HANDLER_ID = $bucket_id
			and h.FILE_ID is null
			and f.ID > $last_file_id
			ORDER BY f.ID
			limit $step_size
			");
		$files = array();
		while ($ar = $rs->Fetch())
		{
			$files[$ar["FILE_PATH"]] = $ar["ID"];
			$last_file_id = $ar["ID"];
		}
		if (!$files)
		{
			return '';
		}

		$rs = \Bitrix\Clouds\FileHashTable::getList(array(
			"filter" => array(
				"=BUCKET_ID" => $bucket_id,
				"=FILE_PATH" => array_keys($files),
			),
		));
		$values = array();
		while ($ar = $rs->fetch())
		{
			if (isset($files[$ar["FILE_PATH"]]))
			{
				$file_id = intval($files[$ar["FILE_PATH"]]);
				$file_size = intval($ar["FILE_SIZE"]);
				$file_hash = $DB->ForSql($ar["FILE_HASH"]);
				$values[] = "($file_id,$file_size,'$file_hash')";
			}
		}
		if ($values)
		{
			$DB->Query("
				INSERT INTO b_file_hash
				(FILE_ID, FILE_SIZE, FILE_HASH)
				values
				".implode(",\n", $values)."
				ON DUPLICATE KEY UPDATE
				FILE_SIZE=values(FILE_SIZE), FILE_HASH=values(FILE_HASH)
			");
		}

		return "CCloudFileHash::setFileHashAgent($bucket_id, $last_file_id, $step_size);";
	}
}
