<?php
class CAllCloudStorageBucket
{
	protected/*.int.*/$_ID = 0;
	/**
	 * @param double $file_size
	 * @param int $file_count
	 * @return CDBResult
	*/
	public function SetFileCounter($file_size, $file_count)
	{
		global $DB, $CACHE_MANAGER;
		$res = $DB->Query("
			UPDATE b_clouds_file_bucket
			SET FILE_COUNT = ".intval($file_count)."
			,FILE_SIZE = ".roundDB($file_size)."
			WHERE ID = ".$this->GetActualBucketId()."
		");

		if(CACHED_b_clouds_file_bucket !== false)
			$CACHE_MANAGER->CleanDir("b_clouds_file_bucket");
		return $res;
	}
	/**
	 * @param double $file_size
	 * @return CDBResult
	*/
	function IncFileCounter($file_size = 0.0)
	{
		global $DB, $CACHE_MANAGER;
		$res = $DB->Query("
			UPDATE b_clouds_file_bucket
			SET FILE_COUNT = FILE_COUNT + 1
			,FILE_SIZE = FILE_SIZE + ".roundDB($file_size)."
			WHERE ID = ".$this->GetActualBucketId()."
		");

		if (defined("BX_CLOUDS_COUNTERS_DEBUG"))
			\CCloudsDebug::getInstance()->endAction();

		if ($file_size)
			COption::SetOptionString("main_size", "~cloud", intval(COption::GetOptionString("main_size", "~cloud")) + $file_size);

		if(CACHED_b_clouds_file_bucket !== false)
			$CACHE_MANAGER->CleanDir("b_clouds_file_bucket");
		return $res;
	}
	/**
	 * @param double $file_size
	 * @return CDBResult
	*/
	function DecFileCounter($file_size = 0.0)
	{
		global $DB, $CACHE_MANAGER;
		$res = $DB->Query("
			UPDATE b_clouds_file_bucket
			SET FILE_COUNT = case when FILE_COUNT - 1 >= 0 then FILE_COUNT - 1 else 0 end
			,FILE_SIZE = case when FILE_SIZE - ".roundDB($file_size)." >= 0 then FILE_SIZE - ".roundDB($file_size)." else 0 end
			WHERE ID = ".$this->GetActualBucketId()."
		");

		if (defined("BX_CLOUDS_COUNTERS_DEBUG"))
			\CCloudsDebug::getInstance()->endAction();

		if ($file_size)
			COption::SetOptionString("main_size", "~cloud", intval(COption::GetOptionString("main_size", "~cloud")) - $file_size);

		if(CACHED_b_clouds_file_bucket !== false)
			$CACHE_MANAGER->CleanDir("b_clouds_file_bucket");
		return $res;
	}

	protected function GetActualBucketId()
	{
		if (
			$this->isFailoverEnabled() && CCloudFailover::IsEnabled()
			&& $this->FAILOVER_ACTIVE === 'Y'
			&& $this->FAILOVER_BUCKET_ID > 0
		)
			return $this->FAILOVER_BUCKET_ID;
		else
			return $this->ID;
	}
}
