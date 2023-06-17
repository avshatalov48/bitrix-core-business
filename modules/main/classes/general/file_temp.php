<?php

use Bitrix\Main;
use Bitrix\Main\Security;

class CTempFile
{
	private static $is_exit_function_registered = false;
	private static $arFiles = array();

	public static function GetAbsoluteRoot()
	{
		$io = CBXVirtualIo::GetInstance();

		if(defined('BX_TEMPORARY_FILES_DIRECTORY'))
		{
			return rtrim(BX_TEMPORARY_FILES_DIRECTORY, '/');
		}
		else
		{
			return $io->CombinePath(
				$_SERVER["DOCUMENT_ROOT"],
				COption::GetOptionString("main", "upload_dir", "upload"),
				"tmp"
			);
		}
	}

	public static function GetFileName($file_name = '')
	{
		$dir_name = self::GetAbsoluteRoot();
		$file_name = rel2abs("/", "/".$file_name);
		$i = 0;

		while(true)
		{
			$i++;

			if($file_name == '/')
				$dir_add = Security\Random::getString(32);
			elseif($i < 25)
				$dir_add = substr(Security\Random::getString(32), 0, 3);
			else
				$dir_add = Security\Random::getString(32);

			$temp_path = $dir_name."/".$dir_add.$file_name;

			if(!file_exists($temp_path))
			{
				//Delayed unlink
				if(!self::$is_exit_function_registered)
				{
					self::$is_exit_function_registered = true;
					register_shutdown_function(array('CTempFile', 'Cleanup'));
				}

				self::$arFiles[$temp_path] = $dir_name."/".$dir_add;

				//Function ends only here
				return $temp_path;
			}
		}
	}

	public static function GetDirectoryName($hours_to_keep_files = 0, $subdir = "")
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
				$dir_add = Security\Random::getString(32);
				$temp_path = $dir_name.$dir_add."/";

				if(!file_exists($temp_path))
					break;
			}
		}
		else //Fixed name during the session
		{
			$localStorage = Main\Application::getInstance()->getLocalSession('userSessionData');
			if (!isset($localStorage['tempFileToken']))
			{
				$localStorage->set('tempFileToken', Security\Random::getString(32));
			}
			$token = $localStorage->get('tempFileToken');

			$subdir = implode("/", (is_array($subdir) ? $subdir : array($subdir, $token)))."/";
			while (strpos($subdir, "//") !== false)
				$subdir = str_replace("//", "/", $subdir);
			$bFound = false;
			for($i = $hours_to_keep_files-1; $i > 0; $i--)
			{
				$dir_name = self::GetAbsoluteRoot().'/BXTEMP-'.date('Y-m-d/H/', time()+3600*$i);
				$temp_path = $dir_name.$subdir;
				if(file_exists($temp_path) && is_dir($temp_path))
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

		//Delayed unlink
		if(!self::$is_exit_function_registered)
		{
			self::$is_exit_function_registered = true;
			register_shutdown_function(array('CTempFile', 'Cleanup'));
		}

		//Function ends only here
		return $temp_path;
	}

	//PHP shutdown cleanup
	public static function Cleanup()
	{
		foreach(self::$arFiles as $temp_path => $temp_dir)
		{
			if(file_exists($temp_path))
			{
				//Clean a file from CTempFile::GetFileName('some.jpg');
				if(is_file($temp_path))
				{
					unlink($temp_path);
					@rmdir($temp_dir);
				}
				//Clean whole temporary directory from CTempFile::GetFileName('');
				elseif(
					mb_substr($temp_path, -1) == '/'
					&& strpos($temp_path, "BXTEMP") === false
					&& is_dir($temp_path)
				)
				{
					CTempFile::_absolute_path_recursive_delete($temp_path);
				}
			}
			elseif(file_exists($temp_dir))
			{
				@rmdir($temp_dir);
			}
		}

		//Clean directories with $hours_to_keep_files > 0
		$dir_name = self::GetAbsoluteRoot()."/";
		if (file_exists($dir_name))
		{
			if ($handle = opendir($dir_name))
			{
				while (($day_files_dir = readdir($handle)) !== false)
				{
					if ($day_files_dir == '.' || $day_files_dir == '..')
						continue;
					if (preg_match("/^BXTEMP-(.*?)\$/", $day_files_dir) && is_dir($dir_name.$day_files_dir))
					{
						CTempFile::_process_directory($dir_name, $day_files_dir);
					}
				}
				closedir($handle);
			}
		}
	}

	private static function _process_directory($dir_name, $day_files_dir)
	{
		$this_day_name = 'BXTEMP-'.date('Y-m-d');
		if ($day_files_dir < $this_day_name)
		{
			CTempFile::_absolute_path_recursive_delete($dir_name.$day_files_dir);
		}
		elseif ($day_files_dir == $this_day_name)
		{
			if ($hour_handle = opendir($dir_name.$day_files_dir))
			{
				$this_hour_name = date('H');
				while (($hour_files_dir = readdir($hour_handle)) !== false)
				{
					if ($hour_files_dir == '.' || $hour_files_dir == '..')
						continue;
					if ($hour_files_dir < $this_hour_name)
						CTempFile::_absolute_path_recursive_delete($dir_name.$day_files_dir.'/'.$hour_files_dir);
				}
			}
		}
	}

	private static function _absolute_path_recursive_delete($path)
	{
		if($path == '' || $path == '/')
			return false;

		$f = true;
		if(is_file($path) || is_link($path))
		{
			if(@unlink($path))
				return true;
			return false;
		}
		elseif(is_dir($path))
		{
			if($handle = opendir($path))
			{
				while(($file = readdir($handle)) !== false)
				{
					if($file == "." || $file == "..")
						continue;

					if(!CTempFile::_absolute_path_recursive_delete($path."/".$file))
						$f = false;
				}
				closedir($handle);
			}
			$r = @rmdir($path);
			if(!$r)
				return false;
			return $f;
		}
		return false;
	}

}
