<?php
IncludeModuleLangFile(__FILE__);

class CCloudFailover
{
	const ST_ERROR = -1;
	const ST_UNKNOWN = 0;
	const ST_FAILOVER = 1;
	const ST_END = 2;
	const ST_CONTINUE = 3;

	public static function IsEnabled()
	{
		return (COption::GetOptionString("clouds", "failover_enabled") === "Y");
	}
	
	public static function queueDelete($obBucket, $FILE_PATH)
	{
		if (
			$obBucket->FAILOVER_BUCKET_ID > 0
			&& $obBucket->FAILOVER_DELETE === "Y"
			&& $obBucket->getQueueFlag()
		)
		{
			if (
				($obBucket->isFailoverEnabled() && CCloudFailover::IsEnabled())
				&& ($obBucket->FAILOVER_ACTIVE === "Y")
			)
			{
				$BUCKET_ID = $obBucket->ID;
			}
			else
			{
				$BUCKET_ID = $obBucket->FAILOVER_BUCKET_ID;
			}
			\Bitrix\Clouds\DeleteQueueTable::add(array(
				"TIMESTAMP_X" => new \Bitrix\Main\Type\DateTime(),
				"BUCKET_ID" => $BUCKET_ID,
				"FILE_PATH" => $FILE_PATH,
			));
		}
	}

	public static function queueCopy($obBucket, $FILE_PATH)
	{
		if (
			$obBucket->FAILOVER_BUCKET_ID > 0
			&& $obBucket->FAILOVER_COPY === "Y"
			&& $obBucket->getQueueFlag()
		)
		{
			if (
				($obBucket->isFailoverEnabled() && CCloudFailover::IsEnabled())
				&& ($obBucket->FAILOVER_ACTIVE === "Y")
			)
			{
				$TARGET_BUCKET_ID = $obBucket->ID;
				$SOURCE_BUCKET_ID = $obBucket->FAILOVER_BUCKET_ID;
			}
			else
			{
				$TARGET_BUCKET_ID = $obBucket->FAILOVER_BUCKET_ID;
				$SOURCE_BUCKET_ID = $obBucket->ID;
			}

			\Bitrix\Clouds\CopyQueueTable::add(array(
				"TIMESTAMP_X" => new \Bitrix\Main\Type\DateTime(),
				"OP" => \Bitrix\Clouds\CopyQueueTable::OP_COPY,
				"SOURCE_BUCKET_ID" => $SOURCE_BUCKET_ID,
				"SOURCE_FILE_PATH" => $FILE_PATH,
				"TARGET_BUCKET_ID" => $TARGET_BUCKET_ID,
				"TARGET_FILE_PATH" => $FILE_PATH,
			));

			$deleteTasks = \Bitrix\Clouds\DeleteQueueTable::getList(array(
				'select' => array('ID'),
				'filter'=> array(
					'=BUCKET_ID' => $TARGET_BUCKET_ID,
					'=FILE_PATH' => $FILE_PATH,
				),
			));
			while ($task = $deleteTasks->fetch())
			{
				\Bitrix\Clouds\DeleteQueueTable::delete($task['ID']);
			}
		}
	}

	public static function queueRename($obBucket, $FILE_PATH_FROM, $FILE_PATH_TO)
	{
		if (
			$obBucket->FAILOVER_BUCKET_ID > 0
			&& $obBucket->FAILOVER_COPY === "Y"
			&& $obBucket->getQueueFlag()
		)
		{
			if (
				($obBucket->isFailoverEnabled() && CCloudFailover::IsEnabled())
				&& ($obBucket->FAILOVER_ACTIVE === "Y")
			)
			{
				$BUCKET_ID = $obBucket->ID;
			}
			else
			{
				$BUCKET_ID = $obBucket->FAILOVER_BUCKET_ID;
			}

			\Bitrix\Clouds\CopyQueueTable::add(array(
				"TIMESTAMP_X" => new \Bitrix\Main\Type\DateTime(),
				"OP" => \Bitrix\Clouds\CopyQueueTable::OP_RENAME,
				"SOURCE_BUCKET_ID" => $BUCKET_ID,
				"SOURCE_FILE_PATH" => $FILE_PATH_FROM,
				"TARGET_BUCKET_ID" => $BUCKET_ID,
				"TARGET_FILE_PATH" => $FILE_PATH_TO,
			));

			$deleteTasks = \Bitrix\Clouds\DeleteQueueTable::getList(array(
				'select' => array('ID'),
				'filter'=> array(
					'=BUCKET_ID' => $BUCKET_ID,
					'=FILE_PATH' => $FILE_PATH_TO,
				),
			));
			while ($task = $deleteTasks->fetch())
			{
				\Bitrix\Clouds\DeleteQueueTable::delete($task['ID']);
			}
		}
	}
	
	public static function executeDeleteQueue()
	{
		$deleteTask = \Bitrix\Clouds\DeleteQueueTable::getList(array(
			'limit' => 1,
			'order' => Array('ID' => 'ASC')
		))->fetch();
		if ($deleteTask)
		{
			$testBucket = new CCloudStorageBucket($deleteTask["BUCKET_ID"]);
			if (
				($testBucket->isFailoverEnabled() && CCloudFailover::IsEnabled())
				&& ($testBucket->FAILOVER_ACTIVE === "Y")
			)
			{
				return CCloudFailover::ST_FAILOVER;
			}

			$obBucket = new CCloudStorageBucket($deleteTask["BUCKET_ID"], false);
			if ((time() - $deleteTask["TIMESTAMP_X"]->getTimestamp()) > $obBucket->FAILOVER_DELETE_DELAY)
			{
				if ($obBucket->Init())
				{
					$obBucket->setQueueFlag(false);
					if (!CCloudTempFile::IsTempFile($deleteTask["FILE_PATH"]))
					{
						$fileExists = $obBucket->FileExists($deleteTask["FILE_PATH"]);
						if ($fileExists)
							$fileSize = $obBucket->GetFileSize($deleteTask["FILE_PATH"]);
						$result = $obBucket->DeleteFile($deleteTask["FILE_PATH"]);
						if ($result && $fileExists)
							$obBucket->DecFileCounter($fileSize);
					}
					else
					{
						$result = $obBucket->DeleteFile($deleteTask["FILE_PATH"]);
					}
					//AddMessage2Log(array($deleteTask, $result));
					\Bitrix\Clouds\DeleteQueueTable::delete($deleteTask["ID"]);
				}
			}
			return CCloudFailover::ST_CONTINUE;
		}
		else
		{
			return CCloudFailover::ST_END;
		}
	}

	public static function executeCopyQueue()
	{
		$task = \Bitrix\Clouds\CopyQueueTable::getList(array(
			'filter' => array("=STATUS" => "Y"),
			'limit' => 1,
			'order' => Array('ID' => 'ASC')
		))->fetch();
		if ($task)
		{
			if ($task["OP"] == \Bitrix\Clouds\CopyQueueTable::OP_RENAME)
			{
				return static::executeRenameTask($task);
			}
			elseif ($task["OP"] == \Bitrix\Clouds\CopyQueueTable::OP_COPY)
			{
				return static::executeCopyTask($task, true);
			}
			elseif ($task["OP"] == \Bitrix\Clouds\CopyQueueTable::OP_SYNC)
			{
				return static::executeCopyTask($task, false);
			}
			else
			{
				\Bitrix\Clouds\CopyQueueTable::delete($task["ID"]);
			}
			
			return CCloudFailover::ST_CONTINUE;
		}
		else
		{
			return CCloudFailover::ST_END;
		}
	}
	
	public static function executeCopyTask($copyTask, $overwrite)
	{
		//Check if failover condition is active
		$testBucket = new CCloudStorageBucket($copyTask["SOURCE_BUCKET_ID"]);
		if (
			($testBucket->isFailoverEnabled() && CCloudFailover::IsEnabled())
			&& ($testBucket->FAILOVER_ACTIVE === "Y")
		)
		{
			return CCloudFailover::ST_FAILOVER;
		}

		$testBucket = new CCloudStorageBucket($copyTask["TARGET_BUCKET_ID"]);
		if (
			($testBucket->isFailoverEnabled() && CCloudFailover::IsEnabled())
			&& ($testBucket->FAILOVER_ACTIVE === "Y")
		)
		{
			return CCloudFailover::ST_FAILOVER;
		}

		//Initialize storages
		$sourceBucket = new CCloudStorageBucket($copyTask["SOURCE_BUCKET_ID"], false);
		if (!$sourceBucket->Init())
		{
			\Bitrix\Clouds\CopyQueueTable::update($copyTask["ID"], array(
				"FAIL_COUNTER" => $copyTask["FAIL_COUNTER"] + 1,
				"STATUS" => $copyTask["FAIL_COUNTER"] >= COption::GetOptionInt("clouds", "max_copy_fail_count")? "F": $copyTask["STATUS"],
				"ERROR_MESSAGE" => "CCloudFailover::executeCopyQueue(".$copyTask["ID"]."): failed to init source bucket."
			));
			return CCloudFailover::ST_ERROR;
		}

		$targetBucket = new CCloudStorageBucket($copyTask["TARGET_BUCKET_ID"], false);
		if (!$targetBucket->Init())
		{
			\Bitrix\Clouds\CopyQueueTable::update($copyTask["ID"], array(
				"FAIL_COUNTER" => $copyTask["FAIL_COUNTER"] + 1,
				"STATUS" => $copyTask["FAIL_COUNTER"] >= COption::GetOptionInt("clouds", "max_copy_fail_count")? "F": $copyTask["STATUS"],
				"ERROR_MESSAGE" => "CCloudFailover::executeCopyQueue(".$copyTask["ID"]."): failed to init target bucket."
			));
			return CCloudFailover::ST_ERROR;
		}

		//Check if source file is exists
		if (!$sourceBucket->FileExists($copyTask["SOURCE_FILE_PATH"]))
		{
			\Bitrix\Clouds\CopyQueueTable::update($copyTask["ID"], array(
				"FAIL_COUNTER" => $copyTask["FAIL_COUNTER"] + 1,
				"STATUS" => $copyTask["FAIL_COUNTER"] >= COption::GetOptionInt("clouds", "max_copy_fail_count")? "F": $copyTask["STATUS"],
				"ERROR_MESSAGE" => "CCloudFailover::executeCopyQueue(".$copyTask["ID"]."): source file does not exists."
			));
			return CCloudFailover::ST_ERROR;
		}

		$CONTENT_TYPE = $sourceBucket->GetService()->GetLastRequestHeader('Content-Type');
		$CONTENT_LENGTH = $sourceBucket->GetService()->GetLastRequestHeader('Content-Length');

		if ($copyTask["FILE_SIZE"] == -1)
		{
			if ($CONTENT_LENGTH)
			{
				$copyTask["FILE_SIZE"] = intval($CONTENT_LENGTH);
			}
			else
			{
				$copyTask["FILE_SIZE"] = $sourceBucket->GetFileSize($copyTask["SOURCE_FILE_PATH"]);
			}
			\Bitrix\Clouds\CopyQueueTable::update($copyTask["ID"], array(
				"FILE_SIZE" => $copyTask["FILE_SIZE"],
			));
		}
		//AddMessage2Log($copyTask);
		$targetBucket->setQueueFlag(false);
		$tempPath = $copyTask["TARGET_FILE_PATH"].".fail-over-copy-part";

		$CLOchunkSize = $targetBucket->GetService()->GetMinUploadPartSize();
		if ($copyTask["FILE_SIZE"] <= $CLOchunkSize)
		{
			$http = new \Bitrix\Main\Web\HttpClient();
			$arFile = array(
				"type" => $CONTENT_TYPE,
				"content" => false,
			);
			$arFile["content"] = $http->get($sourceBucket->GetFileSRC($copyTask["SOURCE_FILE_PATH"]));
			if ($arFile["content"] === false)
			{
				\Bitrix\Clouds\CopyQueueTable::update($copyTask["ID"], array(
					"FAIL_COUNTER" => $copyTask["FAIL_COUNTER"] + 1,
					"STATUS" => $copyTask["FAIL_COUNTER"] >= COption::GetOptionInt("clouds", "max_copy_fail_count")? "F": $copyTask["STATUS"],
					"ERROR_MESSAGE" => "CCloudFailover::executeCopyQueue(".$copyTask["ID"]."): failed to download."
				));
				return CCloudFailover::ST_ERROR;
			}

			if (!$overwrite && $targetBucket->FileExists($copyTask["TARGET_FILE_PATH"]))
			{
				\Bitrix\Clouds\CopyQueueTable::delete($copyTask["ID"]);
				return CCloudFailover::ST_CONTINUE;
			}

			$res = $targetBucket->SaveFile($copyTask["TARGET_FILE_PATH"], $arFile);
			if ($res)
			{
				\Bitrix\Clouds\CopyQueueTable::delete($copyTask["ID"]);
				return CCloudFailover::ST_CONTINUE;
			}
			else
			{
				\Bitrix\Clouds\CopyQueueTable::update($copyTask["ID"], array(
					"FAIL_COUNTER" => $copyTask["FAIL_COUNTER"] + 1,
					"STATUS" => $copyTask["FAIL_COUNTER"] >= COption::GetOptionInt("clouds", "max_copy_fail_count")? "F": $copyTask["STATUS"],
					"ERROR_MESSAGE" => "CCloudFailover::executeCopyQueue(".$copyTask["ID"]."): failed to upload file."
				));
				return CCloudFailover::ST_ERROR;
			}
		}

		$upload = new CCloudStorageUpload($tempPath);
		if ($copyTask["FILE_POS"] == 0)
		{
			if (!$overwrite && $targetBucket->FileExists($copyTask["TARGET_FILE_PATH"]))
			{
				\Bitrix\Clouds\CopyQueueTable::delete($copyTask["ID"]);
				return CCloudFailover::ST_CONTINUE;
			}

			if (!$upload->isStarted())
			{
				if (!$upload->Start($targetBucket, $copyTask["FILE_SIZE"], $CONTENT_TYPE))
				{
					\Bitrix\Clouds\CopyQueueTable::update($copyTask["ID"], array(
						"FAIL_COUNTER" => $copyTask["FAIL_COUNTER"] + 1,
						"STATUS" => $copyTask["FAIL_COUNTER"] >= COption::GetOptionInt("clouds", "max_copy_fail_count")? "F": $copyTask["STATUS"],
						"ERROR_MESSAGE" => "CCloudFailover::executeCopyQueue(".$copyTask["ID"]."): failed to start upload."
					));
					return CCloudFailover::ST_ERROR;
				}
			}
		}

		//Download part

		$http = new \Bitrix\Main\Web\HttpClient();
		$rangeStart = $copyTask["FILE_POS"];
		$rangeEnd = min($copyTask["FILE_POS"] + $targetBucket->getService()->GetMinUploadPartSize(), $copyTask["FILE_SIZE"]) - 1;
		$http->setHeader("Range", "bytes=".$rangeStart."-".$rangeEnd);
		$data = $http->get($sourceBucket->GetFileSRC($copyTask["SOURCE_FILE_PATH"]));

		$uploadResult = false;
		while ($upload->hasRetries())
		{
			if ($upload->Next($data, $targetBucket))
			{
				$uploadResult = true;
				break;
			}
		}

		if (!$uploadResult)
		{
			\Bitrix\Clouds\CopyQueueTable::update($copyTask["ID"], array(
				"FAIL_COUNTER" => $copyTask["FAIL_COUNTER"] + 1,
				"STATUS" => $copyTask["FAIL_COUNTER"] >= COption::GetOptionInt("clouds", "max_copy_fail_count")? "F": $copyTask["STATUS"],
				"ERROR_MESSAGE" => "CCloudFailover::executeCopyQueue(".$copyTask["ID"]."): upload part failed."
			));
			return CCloudFailover::ST_ERROR;
		}

		$filePos = $upload->getPos();

		//Continue next time
		if ($filePos < $copyTask["FILE_SIZE"])
		{
			\Bitrix\Clouds\CopyQueueTable::update($copyTask["ID"], array(
				"FILE_POS" => $filePos,
			));
			return CCloudFailover::ST_CONTINUE;
		}

		if (!$upload->Finish($targetBucket))
		{
			\Bitrix\Clouds\CopyQueueTable::update($copyTask["ID"], array(
				"FAIL_COUNTER" => $copyTask["FAIL_COUNTER"] + 1,
				"STATUS" => $copyTask["FAIL_COUNTER"] >= COption::GetOptionInt("clouds", "max_copy_fail_count")? "F": $copyTask["STATUS"],
				"ERROR_MESSAGE" => "CCloudFailover::executeCopyQueue(".$copyTask["ID"]."): finish has failed."
			));
			return CCloudFailover::ST_ERROR;
		}

		if (!CCloudTempFile::IsTempFile($copyTask["TARGET_FILE_PATH"]))
		{
			$targetBucket->IncFileCounter($copyTask["FILE_SIZE"]);
		}

		if ($overwrite && $targetBucket->FileExists($copyTask["TARGET_FILE_PATH"]))
		{
			$fileSize = $targetBucket->GetFileSize($copyTask["TARGET_FILE_PATH"]);
			if ($targetBucket->DeleteFile($copyTask["TARGET_FILE_PATH"]))
			{
				if (!CCloudTempFile::IsTempFile($copyTask["TARGET_FILE_PATH"]))
				{
					$targetBucket->DecFileCounter($fileSize);
				}
			}
		}

		if (!$targetBucket->FileRename($tempPath, $copyTask["TARGET_FILE_PATH"]))
		{
			\Bitrix\Clouds\CopyQueueTable::update($copyTask["ID"], array(
				"FAIL_COUNTER" => $copyTask["FAIL_COUNTER"] + 1,
				"STATUS" => $copyTask["FAIL_COUNTER"] >= COption::GetOptionInt("clouds", "max_copy_fail_count")? "F": $copyTask["STATUS"],
				"ERROR_MESSAGE" => "CCloudFailover::executeCopyQueue(".$copyTask["ID"]."): rename failed."
			));
			return CCloudFailover::ST_ERROR;
		}

		\Bitrix\Clouds\CopyQueueTable::delete($copyTask["ID"]);
		return CCloudFailover::ST_CONTINUE;
	}

	public static function executeRenameTask($renameTask)
	{
		$testBucket = new CCloudStorageBucket($renameTask["SOURCE_BUCKET_ID"]);
		if (
			($testBucket->isFailoverEnabled() && CCloudFailover::IsEnabled())
			&& ($testBucket->FAILOVER_ACTIVE === "Y")
		)
		{
			return CCloudFailover::ST_FAILOVER;
		}

		$obBucket = new CCloudStorageBucket($renameTask["SOURCE_BUCKET_ID"], false);
		if ($obBucket->Init())
		{
			$obBucket->setQueueFlag(false);
			$result = $obBucket->FileRename($renameTask["SOURCE_FILE_PATH"], $renameTask["TARGET_FILE_PATH"]);
			\Bitrix\Clouds\CopyQueueTable::delete($renameTask["ID"]);
		}

		return CCloudFailover::ST_CONTINUE;
	}

	public static function queueAgent()
	{
		if (static::lock())
		{
			$etime = time() + COption::GetOptionInt("clouds", "queue_agent_time");
			$deleteStatus = CCloudFailover::ST_CONTINUE;
			$copyStatus = CCloudFailover::ST_CONTINUE;
			do
			{
				if ($deleteStatus === CCloudFailover::ST_CONTINUE)
				{
					$deleteStatus = static::executeDeleteQueue();
					if ($deleteStatus === CCloudFailover::ST_FAILOVER)
					{
						break;
					}
				}
				
				if ($copyStatus === CCloudFailover::ST_CONTINUE)
				{
					$copyStatus = static::executeCopyQueue();
					if ($copyStatus === CCloudFailover::ST_FAILOVER)
					{
						break;
					}
				}
				
				if (
					($deleteStatus !== CCloudFailover::ST_CONTINUE)
					&& ($copyStatus !== CCloudFailover::ST_CONTINUE)
				)
				{
					break;
				}
			}
			while (time() < $etime);
		}
		static::unlock();

		return 'CCloudFailover::queueAgent();';
	}
	
	public static function syncAgent($bucketFrom, $bucketTo, $limit = 100)
	{
		$bucketFrom = intval($bucketFrom);
		$bucketTo = intval($bucketTo);
		$limit = intval($limit);

		if (static::lock())
		{
			$bucket = new CCloudStorageBucket($bucketFrom, false);
			if ($bucket->Init())
			{
				$etime = time() + COption::GetOptionInt("clouds", "sync_agent_time");
				do
				{
					$lastJob = \Bitrix\Clouds\CopyQueueTable::getList(array(
						"select" => array("SOURCE_FILE_PATH"),
						"filter" => array(
							"=OP" => \Bitrix\Clouds\CopyQueueTable::OP_SYNC,
							"=SOURCE_BUCKET_ID" => $bucketFrom,
							"=TARGET_BUCKET_ID" => $bucketTo,
						),
						"order"  => array("ID" => "DESC"),
						"limit"  => 1
					))->fetch();
					$lastKey = $lastJob? ltrim($lastJob["SOURCE_FILE_PATH"], '/'): '';

					$files = $bucket->ListFiles("/", true, $limit, $lastKey);
					if ($files === false || empty($files["file"]))
					{
						return "";
					}

					foreach ($files['file'] as $fileName)
					{
						\Bitrix\Clouds\CopyQueueTable::add(array(
							"TIMESTAMP_X" => new \Bitrix\Main\Type\DateTime(),
							"OP" => \Bitrix\Clouds\CopyQueueTable::OP_SYNC,
							"SOURCE_BUCKET_ID" => $bucketFrom,
							"SOURCE_FILE_PATH" => "/".$fileName,
							"TARGET_BUCKET_ID" => $bucketTo,
							"TARGET_FILE_PATH" => "/".$fileName,
						));
					}
				}
				while (time() < $etime);
			}
		}
		static::unlock();

		return "CCloudFailover::syncAgent($bucketFrom, $bucketTo, $limit);";
	}

	protected static $lock = false;

	public static function lock()
	{
		$max_parallel_count = COption::GetOptionInt("clouds", "agents_max_parallel_count");
		if ($max_parallel_count == 0)
		{
			return true;
		}
		elseif ($max_parallel_count == 1)
		{
			if (self::_lock_by_id(0))
			{
				return true;
			}
		}
		else
		{
			for ($i = 0; $i < $max_parallel_count; $i++)
			{
				$lockId = mt_rand(0, $max_parallel_count - 1);
				if (self::_lock_by_id($lockId))
				{
					return true;
				}
			}
			for ($i = 0; $i < $max_parallel_count; $i++)
			{
				if (self::_lock_by_id($i))
				{
					return true;
				}
			}
		}
		return false;
	}
	
	public static function unlock()
	{
		if (static::$lock)
		{
			flock(static::$lock, LOCK_UN);
			fclose(static::$lock);
			static::$lock = false;
		}
	}
	
	protected static function _lock_by_id($lockId)
	{
		$lock_file_template = CTempFile::GetAbsoluteRoot()."/clouds-%d.lock";
		$lock_file_name = sprintf($lock_file_template, $lockId);
		static::$lock = fopen($lock_file_name, "w");
		if (!static::$lock)
		{
			return false;
		}
		$locked = flock(static::$lock, LOCK_EX | LOCK_NB);
		if (!$locked)
		{
			fclose(static::$lock);
			static::$lock = false;
		}
		return $locked;
	}
}
