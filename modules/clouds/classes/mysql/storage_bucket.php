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
			WHERE ID = ".$this->_ID."
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
			".($file_size > 0.0? ",FILE_SIZE = FILE_SIZE + ".roundDB($file_size): "")."
			WHERE ID = ".$this->_ID."
		");
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
			SET FILE_COUNT = FILE_COUNT - 1
			".($file_size > 0.0? ",FILE_SIZE = if(FILE_SIZE - ".roundDB($file_size)." > 0, FILE_SIZE - ".roundDB($file_size).", 0)": "")."
			WHERE ID = ".$this->_ID." AND FILE_COUNT > 0
		");
		if(CACHED_b_clouds_file_bucket !== false)
			$CACHE_MANAGER->CleanDir("b_clouds_file_bucket");
		return $res;
	}
}
