<?php

class CCloudTempFile
{
	private static $buckets = array();

	protected static function GetAbsoluteRoot()
	{
		return "/tmp";
	}

	protected static $shutdownRegistered = false;
	protected static function RegisterShutdown()
	{
		if (!self::$shutdownRegistered)
		{
			register_shutdown_function(array('CCloudTempFile', 'Cleanup'));
			self::$shutdownRegistered = true;
		}
	}

	public static function GetFileName($obBucket, $file_name = '')
	{
		$dir_name = self::GetAbsoluteRoot();
		$file_name = rel2abs("/", "/".$file_name);
		$i = 0;

		while(true)
		{
			$i++;

			if($file_name == '/')
				$dir_add = md5(mt_rand());
			elseif($i < 25)
				$dir_add = substr(md5(mt_rand()), 0, 3);
			else
				$dir_add = md5(mt_rand());

			$temp_path = $dir_name."/".$dir_add.$file_name;

			if (!$obBucket->FileExists($temp_path))
			{
				self::$buckets[] = array(
					"bucket" => $obBucket,
					"filePath" => $temp_path,
				);
				self::RegisterShutdown();

				return $temp_path;
			}
		}
	}

	public static function GetDirectoryName($obBucket, $hours_to_keep_files = 0, $subdir = "")
	{
		if($hours_to_keep_files <= 0)
			return self::GetFileName('');

		if($subdir === "")
		{
			$dir_name = self::GetAbsoluteRoot().'/BXTEMP-'.date('Y-m-d/H/', time()+3600*$hours_to_keep_files);
			$i = 0;
			while(true)
			{
				$i++;
				$dir_add = md5(mt_rand());
				$temp_path = $dir_name.$dir_add."/";

				if (!$obBucket->FileExists($temp_path))
					break;
			}
		}
		else //Fixed name during the session
		{
			$subdir = implode("/", (is_array($subdir) ? $subdir : array($subdir, bitrix_sessid())))."/";
			while (strpos($subdir, "//") !== false)
				$subdir = str_replace("//", "/", $subdir);

			$bFound = false;
			for($i = $hours_to_keep_files; $i > 0; $i--)
			{
				$dir_name = self::GetAbsoluteRoot().'/BXTEMP-'.date('Y-m-d/H/', time()+3600*$i);
				$temp_path = $dir_name.$subdir;
				
				$list = $obBucket->ListFiles($temp_path, true);
				if($list['file'] || $list['dir'])
				{
					$bFound = true;
					break;
				}
			}

			if(!$bFound)
			{
				$dir_name = self::GetAbsoluteRoot().'/BXTEMP-'.date('Y-m-d/H/', time()+3600*$hours_to_keep_files);
				$temp_path = $dir_name.$subdir;
			}
		}
		self::$buckets[] = array(
			"bucket" => $obBucket,
			"filePath" => null
		);

		self::RegisterShutdown();

		return $temp_path;
	}

	//PHP shutdown cleanup
	public static function Cleanup()
	{
		foreach(self::$buckets as $bucket)
		{
			/* @var \CCloudStorageBucket $obBucket */
			$obBucket = $bucket['bucket'];
			if (!is_null($bucket['filePath']) && $obBucket->FileExists($bucket['filePath']))
			{
				$obBucket->DeleteFile($bucket['filePath']);
			}
			else
			{
				$now = date('Y-m-d/H/', time());
				$dir_name = self::GetAbsoluteRoot()."/";
				$list = $obBucket->ListFiles($dir_name, true);
				foreach ($list['file'] as $filePath)
				{
					if (preg_match("#^BXTEMP-(....-..-../../)#", $filePath, $match) && $match[1] < $now)
					{
						$obBucket->DeleteFile($dir_name.$filePath);
					}
				}
			}
		}
	}
}
