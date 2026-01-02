<?php

namespace Bitrix\Main\Data;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Config;
use Bitrix\Main\Data\Internal\CacheCleanPathTable;

class CacheEngineFiles implements CacheEngineInterface, CacheEngineStatInterface
{
	protected const LOCK_FILE = '/bitrix/cache/cacheCleanJob_lock.php';

	protected int $written = 0;
	protected int $read = 0;
	protected string $path = '';
	protected bool $useLock = false;
	protected string $rootDirectory;
	protected static array $lockHandles = [];
	protected static int $clusterGroup = 0;

	/**
	 * Engine constructor.
	 * @param array $options Cache options.
	 */
	public function __construct($options = [])
	{
		$config = Config\Configuration::getValue('cache');

		if (isset($config['use_lock']))
		{
			$this->useLock = (bool)$config['use_lock'];
		}
		if (isset($options['actual_data']) && !$this->useLock)
		{
			$this->useLock = !$options['actual_data'];
		}

		static::$clusterGroup = (defined('BX_CLUSTER_GROUP') ? (int)constant('BX_CLUSTER_GROUP') : 0);

		$this->rootDirectory = $config['root_directory'] ?? Main\Loader::getDocumentRoot();

		$key = $this->rootDirectory . self::LOCK_FILE;
		if (!file_exists($key))
		{
			if ($handle = fopen($key, "wb+"))
			{
				fwrite($handle, 'lock');
				fclose($handle);
			}
		}

		if ($this->lock($key))
		{
			Application::getInstance()->addBackgroundJob([$this, 'delayedDelete'], [], Application::JOB_PRIORITY_LOW);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getReadBytes()
	{
		return $this->read;
	}

	/**
	 * @inheritdoc
	 */
	public function getWrittenBytes()
	{
		return $this->written;
	}

	/**
	 * @inheritdoc
	 */
	public function getCachePath()
	{
		return $this->path;
	}

	/**
	 * @inheritdoc
	 */
	public function isAvailable()
	{
		return true;
	}

	/**
	 * Deletes physical file. Returns true on success.
	 * @param string $fileName Absolute physical path.
	 * @return void
	 */
	protected function unlink(string $fileName): void
	{
		$this->unlock($fileName);

		if (file_exists($fileName))
		{
			// Handle E_WARNING
			set_error_handler(function () {
				// noop
			});

			chmod($fileName, BX_FILE_PERMISSIONS);
			unlink($fileName);

			restore_error_handler();
		}
	}

	/**
	 * Generates very temporary file name by adding some random suffix to the file path.
	 * Returns empty string on failure.
	 * @param string $fileName File path within document root.
	 * @return string
	 */
	protected function randomizeFile(string $fileName): string
	{
		for ($i = 0; $i < 99; $i++)
		{
			$suffix = rand(0, 999999);
			if (!file_exists($this->rootDirectory . $fileName . $suffix))
			{
				return $fileName . $suffix;
			}
		}

		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function clean($baseDir, $initDir = '', $filename = '')
	{
		if (($filename !== false) && ($filename !== ''))
		{
			$this->unlink($this->rootDirectory . $baseDir . $initDir . $filename);
		}
		else
		{
			$initDir = trim($initDir, '/');
			if ($initDir == '')
			{
				$sourceDir = $this->rootDirectory . '/' . trim($baseDir, '/');
				if (file_exists($sourceDir) && is_dir($sourceDir))
				{
					$dh = opendir($sourceDir);
					if (is_resource($dh))
					{
						while ($entry = readdir($dh))
						{
							if (preg_match("/^(\\.|\\.\\.|.*\\.~\\d+)\$/", $entry))
							{
								continue;
							}

							if (is_dir($sourceDir . '/' . $entry))
							{
								$this->clean($baseDir, $entry);
							}
							elseif (is_file($sourceDir . '/' . $entry))
							{
								$this->unlink($sourceDir . '/' . $entry);
							}
						}
					}
				}
			}
			else
			{
				$source = '/' . trim($baseDir, '/') . '/' . $initDir;
				$source = rtrim($source, '/');
				$delayedDelete = false;

				if (!preg_match("/^(\\.|\\.\\.|.*\\.~\\d+)\$/", $source) && file_exists($this->rootDirectory . $source))
				{
					if (is_file($this->rootDirectory . $source))
					{
						$this->unlink($this->rootDirectory . $source);
					}
					else
					{
						$target = $this->randomizeFile($source . '.~');
						if ($target != '')
						{
							CacheCleanPathTable::add([
								'PREFIX' => $target,
								'CLUSTER_GROUP' => static::$clusterGroup
							]);

							if (@rename($this->rootDirectory . $source, $this->rootDirectory . $target))
							{
								$delayedDelete = true;
							}
						}
					}
				}

				if ($delayedDelete)
				{
					Application::getInstance()->getManagedCache()->read(3600, 'needClean');
					Application::getInstance()->getManagedCache()->setImmediate('needClean', 'Y');
				}
				else
				{
					DeleteDirFilesEx($baseDir . $initDir, $this->rootDirectory);
				}
			}
		}
	}

	/**
	 * Tries to put non-blocking exclusive lock on the file.
	 * Returns true if file not exists, or lock was successfully got.
	 * @param string $fileName Absolute cache file path.
	 * @return boolean
	 */
	protected function lock(string $fileName): bool
	{
		$wouldBlock = 0;
		self::$lockHandles[$fileName] = @fopen($fileName, "r+");
		if (self::$lockHandles[$fileName])
		{
			flock(self::$lockHandles[$fileName], LOCK_EX | LOCK_NB, $wouldBlock);
		}
		return $wouldBlock !== 1;
	}

	/**
	 * Releases the lock obtained by lock method.
	 * @param string $fileName Absolute cache file path.
	 * @return void
	 */
	protected function unlock(string $fileName): void
	{
		if (!empty(self::$lockHandles[$fileName]))
		{
			fclose(self::$lockHandles[$fileName]);
			unset(self::$lockHandles[$fileName]);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function read(&$vars, $baseDir, $initDir, $filename, $ttl)
	{
		$fn = $this->rootDirectory . '/' . ltrim($baseDir . $initDir, '/') . $filename;

		if (!file_exists($fn))
		{
			return false;
		}

		$ser_content = '';
		$dateexpire = 0;
		$datecreate = 0;
		$zeroDanger = false;

		$handle = null;
		if (is_array($vars))
		{
			$INCLUDE_FROM_CACHE = 'Y';

			if (!@include($fn))
			{
				return false;
			}
		}
		else
		{
			$handle = fopen($fn, 'rb');
			if (!$handle)
			{
				return false;
			}

			$datecreate = fread($handle, 2);
			if ($datecreate == 'BX')
			{
				$datecreate = fread($handle, 12);
				fread($handle, 12); // unused dateexpire
			}
			else
			{
				$datecreate .= fread($handle, 10);
			}
		}

		$this->read = @filesize($fn);
		$this->path = $fn;

		$res = true;
		if (intval($datecreate) < (time() - $ttl))
		{
			if ($this->useLock)
			{
				if ($this->lock($fn))
				{
					$res = false;
				}
			}
			else
			{
				$res = false;
			}
		}

		if ($res)
		{
			if (is_array($vars))
			{
				$vars = unserialize($ser_content);
			}
			else
			{
				$vars = fread($handle, $this->read);
			}
		}

		if ($handle)
		{
			fclose($handle);
		}

		return $res;
	}

	/**
	 * @inheritdoc
	 */
	public function write($vars, $baseDir, $initDir, $filename, $ttl)
	{
		static $search = ["\\", "'", "\0"];
		static $replace = ["\\\\", "\\'", "'.chr(0).'"];

		$ttl = (int) $ttl;
		$folder = $this->rootDirectory . '/' . ltrim($baseDir . $initDir, '/');
		$fn = $folder . $filename;
		$fnTmp = $folder . md5(mt_rand()) . '.tmp';

		if (!CheckDirPath($fn))
		{
			return;
		}

		if ($handle = fopen($fnTmp, "wb+"))
		{
			if (is_array($vars))
			{
				$contents = "<?";
				$contents .= "\nif(\$INCLUDE_FROM_CACHE!='Y')return false;";
				$contents .= "\n\$datecreate = '" . str_pad(time(), 12, "0", STR_PAD_LEFT) . "';";
				$contents .= "\n\$dateexpire = '" . str_pad(time() + intval($ttl), 12, "0", STR_PAD_LEFT) . "';";
				$contents .= "\n\$ser_content = '" . str_replace($search, $replace, serialize($vars)) . "';";
				$contents .= "\nreturn true;";
				$contents .= "\n?>";
			}
			else
			{
				$contents = "BX" . str_pad(time(), 12, "0", STR_PAD_LEFT) . str_pad(time() + $ttl, 12, "0", STR_PAD_LEFT);
				$contents .= $vars;
			}

			$this->written = fwrite($handle, $contents);
			$this->path = $fn;
			$len = strlen($contents);

			fclose($handle);
			$this->unlink($fn);

			if ($this->written === $len)
			{
				rename($fnTmp, $fn);
			}

			$this->unlink($fnTmp);

			if ($this->useLock)
			{
				$this->unlock($fn);
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	public function isCacheExpired($path)
	{
		if (!file_exists($path))
		{
			return true;
		}

		$fileHandler = fopen($path, 'rb');
		if ($fileHandler)
		{
			$header = fread($fileHandler, 150);
			fclose($fileHandler);
		}
		else
		{
			return true;
		}

		if (
			preg_match("/dateexpire\\s*=\\s*'(\\d+)'/im", $header, $match)
			|| preg_match("/^BX\\d{12}(\\d{12})/", $header, $match)
			|| preg_match("/^(\\d{12})/", $header, $match)
		)
		{
			if ($match[1] == '' || doubleval($match[1]) < time())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Deletes one cache directory. Works no longer than etime.
	 * @param integer $etime Timestamp when to stop working.
	 * @param array $path Record from b_cache_tag.
	 * @return void
	 */
	protected function deleteOneDir(int $etime = 0, array $path = []): void
	{
		if (empty($path))
		{
			return;
		}

		$deleteFromQueue = false;
		$root = $this->rootDirectory;
		$dirName = $root . $path['PREFIX'];

		if ($path['PREFIX'] != '' && file_exists($dirName))
		{
			if (is_file($dirName))
			{
				DeleteDirFilesEx($path['PREFIX'], $root);
				$deleteFromQueue = true;
			}
			elseif (($dir = scandir($dirName)) !== false)
			{
				$counter = count($dir);
				foreach ($dir as $file)
				{
					$counter--;
					if ($file != '.' && $file != '..')
					{
						DeleteDirFilesEx($path['PREFIX'] . '/' . $file, $root);
					}

					if (time() > $etime)
					{
						break;
					}
				}

				if ($counter == 0)
				{
					rmdir($dirName);
					$deleteFromQueue = true;
				}
			}
		}
		else
		{
			$deleteFromQueue = true;
		}

		if ($deleteFromQueue)
		{
			CacheCleanPathTable::delete($path['ID']);
		}
	}

	/**
	 * Agent function which deletes marked cache directories.
	 * @return void
	 */
	public function delayedDelete(): void
	{
		$delta = 10;
		$deleted = 0;
		$etime = time() + 5;

		$managedCache = Application::getInstance()->getManagedCache();
		$needClean = $managedCache->read(3600, 'needClean');

		if ($needClean != 'Y')
		{
			$this->unlock($this->rootDirectory . self::LOCK_FILE);
			return;
		}

		$count = (int)$managedCache->getImmediate(604800, 'delCount');
		if ($count < 1)
		{
			$count = 1;
		}

		$paths = CacheCleanPathTable::query()
			->setSelect(['ID', 'PREFIX'])
			->where('CLEAN_FROM', '<=', new \Bitrix\Main\Type\DateTime())
			->where('CLUSTER_GROUP', static::$clusterGroup)
			->setLimit($count + $delta)
			->exec();

		while($path = $paths->fetch())
		{
			$this->deleteOneDir($etime, $path);
			$deleted++;

			if (time() > $etime)
			{
				break;
			}
		}

		if ($deleted > $count)
		{
			$count = $deleted;
		}
		elseif ($deleted < $count && $count > 1)
		{
			$count--;
		}

		$managedCache->read(604800, 'delCount');
		if ($deleted > $count)
		{
			$managedCache->setImmediate('delCount', $deleted);
		}
		elseif ($deleted < $count && $count > 1)
		{
			$managedCache->setImmediate('delCount', $deleted);
		}

		if ($deleted == 0)
		{
			$managedCache->read(3600, 'needClean');
			$managedCache->setImmediate('needClean', $deleted);
		}

		$this->unlock($this->rootDirectory . self::LOCK_FILE);
	}
}
