<?php

class CCloudTempFile
{
	private static $buckets = [];

	/**
	 * @return string
	 */
	protected static function GetAbsoluteRoot()
	{
		return '/tmp';
	}

	/**
	 * @param string $file_name
	 * @return bool
	 */
	public static function IsTempFile($file_name)
	{
		return preg_match('#^' . self::GetAbsoluteRoot() . '/BXTEMP-#', $file_name) > 0;
	}

	protected static $shutdownRegistered = false;

	protected static function RegisterShutdown()
	{
		if (!self::$shutdownRegistered)
		{
			register_shutdown_function(['CCloudTempFile', 'Cleanup']);
			self::$shutdownRegistered = true;
		}
	}

	/**
	 * @param CCloudStorageBucket $obBucket
	 * @param string $file_name
	 * @return string
	 */
	public static function GetFileName($obBucket, $file_name = '')
	{
		$dir_name = self::GetAbsoluteRoot();
		$file_name = Rel2Abs('/', '/' . $file_name);
		$temp_path = '';
		$i = 0;
		while (true)
		{
			$i++;

			if ($file_name == '/')
			{
				$dir_add = md5(mt_rand());
			}
			elseif ($i < 25)
			{
				$dir_add = mb_substr(md5(mt_rand()), 0, 3);
			}
			else
			{
				$dir_add = md5(mt_rand());
			}

			$temp_path = $dir_name . '/' . $dir_add . $file_name;

			if (!$obBucket->FileExists($temp_path))
			{
				self::$buckets[$obBucket->ID . '|' . $temp_path] = [
					'bucket' => $obBucket,
					'filePath' => $temp_path,
				];
				self::RegisterShutdown();
				break;
			}
		}

		return $temp_path;
	}

	/**
	 * @param CCloudStorageBucket $obBucket
	 * @param int $hours_to_keep_files
	 * @param string $subdir
	 * @return string
	 */
	public static function GetDirectoryName($obBucket, $hours_to_keep_files = 0, $subdir = '')
	{
		if ($hours_to_keep_files <= 0)
		{
			return self::GetFileName($obBucket,'');
		}

		$temp_path = ''; //This makes code analyzers happy. $temp_path will never be empty.
		if ($subdir === '')
		{
			$dir_name = self::GetAbsoluteRoot() . '/BXTEMP-' . date('Y-m-d/H/', time() + 3600 * $hours_to_keep_files);
			$i = 0;
			while (true)
			{
				$i++;
				$dir_add = md5(mt_rand());
				$temp_path = $dir_name . $dir_add . '/';

				if (!$obBucket->FileExists($temp_path))
				{
					break;
				}
			}
		}
		else //Fixed name during the session
		{
			$subdir = implode('/', (is_array($subdir) ? $subdir : [$subdir, bitrix_sessid()])) . '/';
			while (mb_strpos($subdir, '//') !== false)
			{
				$subdir = str_replace('//', '/', $subdir);
			}

			$bFound = false;
			for ($i = $hours_to_keep_files; $i > 0; $i--)
			{
				$dir_name = self::GetAbsoluteRoot() . '/BXTEMP-' . date('Y-m-d/H/', time() + 3600 * $i);
				$temp_path = $dir_name . $subdir;

				$list = $obBucket->ListFiles($temp_path, true);
				if ($list['file'] || $list['dir'])
				{
					$bFound = true;
					break;
				}
			}

			if (!$bFound)
			{
				$dir_name = self::GetAbsoluteRoot() . '/BXTEMP-' . date('Y-m-d/H/', time() + 3600 * $hours_to_keep_files);
				$temp_path = $dir_name . $subdir;
			}
		}

		if (!isset(self::$buckets[$obBucket->ID]))
		{
			self::$buckets[$obBucket->ID] = [
				'bucket' => $obBucket,
				'filePath' => null
			];
		}
		self::RegisterShutdown();

		return $temp_path;
	}

	protected static $my_pid = '';

	/**
	 * @param bool $lock
	 * @return bool
	 */
	protected static function cleanupFilesLock($lock = true)
	{
		if (!self::$my_pid)
		{
			self::$my_pid = md5(mt_rand());
		}

		$obCacheLock = null;
		$cache_ttl = 300;
		$uniq_str = 'cleanupFiles';
		$init_dir = 'clouds';

		$obCacheLock = new CPHPCache();
		if ($lock)
		{
			if ($obCacheLock->InitCache($cache_ttl, $uniq_str, $init_dir))
			{
				$vars = $obCacheLock->GetVars();
				if (self::$my_pid !== $vars['pid'])
				{
					return false; //There is other cleaning process
				}
			}
			elseif ($obCacheLock->StartDataCache())
			{
				$obCacheLock->EndDataCache(['pid' => self::$my_pid]);
			}
		}
		else
		{
			$obCacheLock->Clean($uniq_str, $init_dir);
		}

		return true;
	}

	/**
	 * @param CCloudStorageBucket $obBucket
	 * @param string $dir_name
	 * @param array $files
	 * @return string
	 */
	protected static function cleanupFiles($obBucket, $dir_name, $files)
	{

		$date = new \Bitrix\Main\Type\DateTime();
		$date->setTimeZone(new DateTimeZone('UTC'));
		$date->add('-1D');
		$tmp_expiration_time = $date->format('Y-m-d') . 'T' . $date->format('H:i:s');
		$now = date('Y-m-d/H/', time());

		foreach ($files['file'] as $i => $filePath)
		{
			//Files cleanup
			if (preg_match('#^BXTEMP-(.{4}-..-../../)#', $filePath, $match) && $match[1] < $now)
			{
				if (!static::cleanupFilesLock())
				{
					return false;
				}
				$obBucket->DeleteFile($dir_name . $filePath);
			}
			elseif ($files['file_mtime'][$i] < $tmp_expiration_time)
			{
				if (!static::cleanupFilesLock())
				{
					return false;
				}
				$obBucket->DeleteFile($dir_name . $filePath);
			}
		}

		if (static::cleanupFilesLock())
		{
			static::cleanupFilesLock(false);
		}

		return true;
	}

	//PHP shutdown cleanup
	public static function Cleanup()
	{
		foreach (self::$buckets as $bucket)
		{
			/* @var CCloudStorageBucket $obBucket */
			$obBucket = $bucket['bucket'];
			if (!is_null($bucket['filePath']) && $obBucket->FileExists($bucket['filePath']))
			{
				$obBucket->DeleteFile($bucket['filePath']);
			}
			elseif (static::cleanupFilesLock())
			{
				$dir_name = self::GetAbsoluteRoot() . '/';
				$list = $obBucket->ListFiles($dir_name, true);
				if ($list)
				{
					static::cleanupFiles($obBucket, $dir_name, $list);
				}
				static::cleanupFilesLock(false);
			}
		}
	}
}
