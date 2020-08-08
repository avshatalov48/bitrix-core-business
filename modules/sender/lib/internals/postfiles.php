<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Internals;

use Bitrix\Main\Application;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Context;

/**
 * Class PostFiles
 * @package Bitrix\Sender\Internals
 */
class PostFiles
{
	/** @var  HttpRequest $request Request. */
	protected $request;

	/** @var  string $inputName Input name. */
	protected $inputName;

	/**
	 * Get posted files from context.
	 *
	 * @param string $inputName Input name.
	 * @param array $savedFiles Saved files.
	 * @return array
	 */
	public static function getFromContext($inputName, array $savedFiles = array())
	{
		$instance = new static($inputName);
		return $instance->getFiles($savedFiles);
	}

	/**
	 * PostFiles constructor.
	 *
	 * @param string $inputName Input name.
	 * @param HttpRequest|null $request Request.
	 */
	public function __construct($inputName, HttpRequest $request = null)
	{
		$this->inputName = $inputName;

		if (!$request)
		{
			$request = Context::getCurrent()->getRequest();
		}
		$this->request = $request;
	}

	/**
	 * Get files.
	 *
	 * @param array $savedFiles Saved files.
	 * @param array $files Temporary files or files from media lib.
	 * @return array
	 */
	public function getFiles(array $savedFiles = [], array $files = [])
	{
		$result = array();

		$newFiles = $this->getMediaLib($files);
		$newFiles = array_merge($newFiles, $this->getPosted());
		foreach($newFiles as $file)
		{
			if (!is_array($file))
			{
				continue;
			}

			$fileId = self::saveFile($file);
			if ($fileId)
			{
				$result[] = $fileId;
			}
		}

		$result = array_merge($result, $this->getExisted($files));

		$filesToDelete = array_diff($savedFiles, $result);
		$filesToDelete = array_merge($this->getDeleted(), $filesToDelete);
		$filesToDelete = array_unique($filesToDelete);
		foreach ($filesToDelete as $fileId)
		{
			\CFile::Delete($fileId);
		}


		return $result;
	}

	protected function getDeleted()
	{
		$result = array();
		$del = $this->request->get($this->inputName . '_del');
		if(!is_array($del))
		{
			return $result;
		}

		foreach($del as $file => $fileMarkDel)
		{
			$file = intval($file);
			if($file>0)
			{
				$result[] = $file;
			}
		}

		return $result;
	}

	protected function getPosted()
	{
		$result = array();
		$fileList = $this->request->getFile($this->inputName);
		if(!is_array($fileList))
		{
			return $result;
		}

		foreach($fileList as $attribute => $files)
		{
			if(!is_array($files))
			{
				continue;
			}

			foreach($files as $index => $value)
			{
				$result[$index][$attribute] = $value;
			}
		}

		foreach($result as $index => $file)
		{
			if(!is_uploaded_file($file["tmp_name"]))
			{
				unset($result[$index]);
			}
		}

		return $result;
	}

	/**
	 * Get media lib files.
	 *
	 * @param array|null $files Files.
	 * @return array
	 */
	public function getMediaLib(array $files = null)
	{
		//New from media library and file structure
		$result = array();

		if (empty($files))
		{
			$files = $this->request->get($this->inputName);
		}
		if(!is_array($files))
		{
			return $result;
		}

		foreach($files as $index => $value)
		{
			if (is_string($value) && preg_match("/^https?:\\/\\//", $value))
			{
				$result[$index] = \CFile::MakeFileArray($value);
			}
			else
			{
				if(is_array($value))
				{
					$filePath = $value['tmp_name'];
				}
				else
				{
					$filePath = $value;
				}

				$isCheckedSuccess = false;
				$io = \CBXVirtualIo::GetInstance();
				$docRoot = Application::getDocumentRoot();
				if(mb_strpos($filePath, \CTempFile::GetAbsoluteRoot()) === 0)
				{
					$absPath = $filePath;
				}
				elseif(mb_strpos($io->CombinePath($docRoot, $filePath), \CTempFile::GetAbsoluteRoot()) === 0)
				{
					$absPath = $io->CombinePath($docRoot, $filePath);
				}
				else
				{
					$absPath = $io->CombinePath(\CTempFile::GetAbsoluteRoot(), $filePath);
					$isCheckedSuccess = true;
				}

				$absPath = realpath(str_replace("\\", "/", $absPath));
				if (mb_strpos($absPath, realpath(\CTempFile::GetAbsoluteRoot())) !== 0)
				{
					continue;
				}

				if (!$isCheckedSuccess && $io->ValidatePathString($absPath) && $io->FileExists($absPath))
				{
					$docRoot = $io->CombinePath($docRoot, '/');
					$relPath = str_replace($docRoot, '', $absPath);
					$perm = $GLOBALS['APPLICATION']->GetFileAccessPermission($relPath);
					if ($perm >= "W")
					{
						$isCheckedSuccess = true;
					}
				}

				if($isCheckedSuccess)
				{
					$result[$index] = \CFile::MakeFileArray($io->GetPhysicalName($absPath));
					if(is_array($value))
					{
						$result[$index]['name'] = $value['name'];
					}
				}

			}
		}

		return $result;
	}

	/**
	 * Get existed files.
	 *
	 * @param array|null $files Files.
	 * @return array
	 */
	public function getExisted(array $files = null)
	{
		$result = array();

		if (empty($files))
		{
			$files = $this->request->get($this->inputName);
		}
		if(!is_array($files))
		{
			return $result;
		}

		foreach($files as $index => $value)
		{
			if (!is_numeric($index) || !is_numeric($value))
			{
				continue;
			}

			$file = \CFile::getByID($value)->fetch();
			if (!$file || $file['MODULE_ID'] !== 'sender')
			{
				continue;
			}

			$result[] = (int) $value;
		}

		return $result;
	}

	/**
	 * Save file.
	 *
	 * @param array $file File data.
	 * @return int|null
	 */
	public static function saveFile(array $file)
	{
		if($file["name"] == '' || intval($file["size"]) <= 0)
		{
			return null;
		}

		$pathHash = md5($file["tmp_name"]);
		$sessionKey = 'sender_post_files';
		if (!empty($_SESSION[$sessionKey][$pathHash]))
		{
			$fileId = (int) $_SESSION[$sessionKey][$pathHash];
			return $fileId ?: null;
		}

		$file["MODULE_ID"] = "sender";
		$fileId = (int) \CFile::saveFile($file, "sender", true);
		if ($fileId)
		{
			$_SESSION[$sessionKey][$pathHash] = $fileId;
			return $fileId;
		}

		return null;
	}
}