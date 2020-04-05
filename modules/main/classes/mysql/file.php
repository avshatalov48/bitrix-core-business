<?
class CFile extends CAllFile
{
	public static function Delete($ID)
	{
		global $DB;
		$io = CBXVirtualIo::GetInstance();
		$ID = intval($ID);

		if($ID <= 0)
			return;

		$res = CFile::GetByID($ID);
		if($res = $res->Fetch())
		{
			$delete_size = 0;
			$upload_dir = COption::GetOptionString("main", "upload_dir", "upload");

			$dname = $_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$res["SUBDIR"];
			$fname = $dname."/".$res["FILE_NAME"];
			$file = $io->GetFile($fname);

			if($file->isExists() && $file->unlink())
				$delete_size += $res["FILE_SIZE"];

			$delete_size += CFile::ResizeImageDelete($res);

			foreach(GetModuleEvents("main", "OnFileDelete", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($res));

			$DB->Query("DELETE FROM b_file WHERE ID = ".$ID);

			$directory = $io->GetDirectory($dname);
			if($directory->isExists() && $directory->isEmpty())
				$directory->rmdir();

			CFile::CleanCache($ID);

			/****************************** QUOTA ******************************/
			if($delete_size > 0 && COption::GetOptionInt("main", "disk_space") > 0)
				CDiskQuota::updateDiskQuota("file", $delete_size, "delete");
			/****************************** QUOTA ******************************/
		}
	}

	public static function DoDelete($ID)
	{
		CFile::Delete($ID);
	}
}
?>