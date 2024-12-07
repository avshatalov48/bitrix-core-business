<?php
IncludeModuleLangFile(__FILE__);

abstract class CCloudStorageService
{
	protected $verb = '';
	protected $host = '';
	protected $url = '';

	protected $errno = 0;
	protected $errstr = '';

	protected $status = 0;
	protected $headers = /*.(array[string]string).*/[];
	protected $result = '';

	public $tokenHasExpired = false;
	protected $streamTimeout = 0;

	/**
	 * @return CCloudStorageService
	 * @deprecated
	*/
	abstract public function GetObject();

	/**
	 * @return string
	*/
	abstract public function GetID();

	/**
	 * @return string
	*/
	abstract public function GetName();

	/**
	 * @return array[string]string
	*/
	abstract public function GetLocationList();

	/**
	 * @param array[string]string $arBucket
	 * @param bool $bServiceSet
	 * @param string $cur_SERVICE_ID
	 * @param bool $bVarsFromForm
	 * @return string
	*/
	abstract public function GetSettingsHTML($arBucket, $bServiceSet, $cur_SERVICE_ID, $bVarsFromForm);

	/**
	 * @param array[string]string $arBucket
	 * @param array[string]string & $arSettings
	 * @return bool
	*/
	abstract public function CheckSettings($arBucket, &$arSettings);

	/**
	 * @param array[string]string $arBucket
	 * @return bool
	*/
	abstract public function CreateBucket($arBucket);

	/**
	 * @param array[string]string $arBucket
	 * @return bool
	*/
	abstract public function DeleteBucket($arBucket);

	/**
	 * @param array[string]string $arBucket
	 * @return bool
	*/
	abstract public function IsEmptyBucket($arBucket);

	/**
	 * @param array[string]string $arBucket
	 * @param mixed $arFile
	 * @param bool $encoded
	 * @return string
	*/
	public function GetFileSRC($arBucket, $arFile, $encoded = true)
	{
		return '';
	}

	/**
	 * @param array[string]string $arBucket
	 * @param string $filePath
	 * @return bool
	*/
	abstract public function FileExists($arBucket, $filePath);

	/**
	 * @param array[string]string $arBucket
	 * @param mixed $arFile
	 * @param string $filePath
	 * @return bool
	*/
	abstract public function FileCopy($arBucket, $arFile, $filePath);

	/**
	 * @param array[string]string $arBucket
	 * @param mixed $arFile
	 * @param string $filePath
	 * @return bool
	*/
	public function DownloadToFile($arBucket, $arFile, $filePath)
	{
		$url = $this->GetFileSRC($arBucket, $arFile);
		$request = new Bitrix\Main\Web\HttpClient([
			'streamTimeout' => $this->streamTimeout,
		]);
		$result = $request->download($url, $filePath);
		if ($request->getStatus() == 404 || $request->getStatus() == 403)
		{
			return false;
		}
		return $result;
	}

	/**
	 * @param array[string]string $arBucket
	 * @param string $filePath
	 * @return bool
	*/
	abstract public function DeleteFile($arBucket, $filePath);

	/**
	 * @param array[string]string $arBucket
	 * @param string $filePath
	 * @param mixed $arFile
	 * @return bool
	*/
	abstract public function SaveFile($arBucket, $filePath, $arFile);

	/**
	 * @param array[string]string $arBucket
	 * @param string $filePath
	 * @param bool $bRecursive
	 * @return array[string][int]string
	*/
	abstract public function ListFiles($arBucket, $filePath, $bRecursive = false);

	/**
	 * @param array[string]string $arBucket
	 * @param string $filePath
	 * @return null|false|array
	*/
	public function GetFileInfo($arBucket, $filePath)
	{
		return null; // not implemented
	}

	/**
	 * @param array[string]string $arBucket
	 * @param string $sourcePath
	 * @param string $targetPath
	 * @param bool $overwrite
	 * @return bool
	*/
	public function FileRename($arBucket, $sourcePath, $targetPath, $overwrite = true)
	{
		if ($this->FileExists($arBucket, $sourcePath))
		{
			$contentType = $this->headers['Content-Type'];
		}
		else
		{
			return false;
		}

		if ($this->FileExists($arBucket, $targetPath))
		{
			if (!$overwrite)
			{
				return false;
			}

			if (!$this->DeleteFile($arBucket, $targetPath))
			{
				return false;
			}
		}

		$arFile = [
			'SUBDIR' => '',
			'FILE_NAME' => ltrim($sourcePath, '/'),
			'CONTENT_TYPE' => $contentType,
		];

		if (!$this->FileCopy($arBucket, $arFile, $targetPath))
		{
			return false;
		}

		if (!$this->DeleteFile($arBucket, $sourcePath))
		{
			return false;
		}

		return true;
	}

	/**
	 * @param array[string]string $arBucket
	 * @param mixed & $NS
	 * @param string $filePath
	 * @param float $fileSize
	 * @param string $ContentType
	 * @return bool
	*/
	abstract public function InitiateMultipartUpload($arBucket, &$NS, $filePath, $fileSize, $ContentType);

	/**
	 * @return float
	*/
	abstract public function GetMinUploadPartSize();

	/**
	 * @param array[string]string $arBucket
	 * @param mixed & $NS
	 * @param string $data
	 * @return bool
	*/
	abstract public function UploadPart($arBucket, &$NS, $data);

	/**
	 * @param array[string]string $arBucket
	 * @param mixed & $NS
	 * @param string $data
	 * @param int $part_no
	 * @return bool
	*/
	public function UploadPartNo($arBucket, &$NS, $data, $part_no)
	{
		return false;
	}

	/**
	 * @param array[string]string $arBucket
	 * @param mixed & $NS
	 * @return bool
	*/
	abstract public function CompleteMultipartUpload($arBucket, &$NS);

	/**
	 * @param array[string]string $arBucket
	 * @param mixed & $NS
	 * @return bool
	*/
	public function CancelMultipartUpload($arBucket, &$NS)
	{
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @return void
	*/
	public function SetHeader($name, $value)
	{
	}

	/**
	 * @param string $name
	 * @return void
	 */
	public function UnsetHeader($name)
	{
	}

	/**
	 * @param bool $state
	 * @return void
	 */
	public function SetPublic($state = true)
	{
	}

	/**
	 * @return array[string]string
	*/
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * @return int
	*/
	public function GetLastRequestStatus()
	{
		return $this->status;
	}

	/**
	 * @param string $headerName
	 * @return string
	*/
	public function GetLastRequestHeader($headerName)
	{
		$loweredName = mb_strtolower($headerName);
		foreach ($this->headers as $name => $value)
		{
			if (mb_strtolower($name) === $loweredName)
			{
				return $value;
			}
		}
		return '';
	}

	/**
	 * @return CCloudStorageService
	*/
	public static function GetObjectInstance()
	{
		return new static();
	}

	public function formatError()
	{
		if ($this->errno > 0)
		{
			return GetMessage('CLO_STORAGE_HTTP_ERROR', [
				'#verb#' => $this->verb,
				'#url#' => $this->url,
				'#errno#' => $this->errno,
				'#errstr#' => $this->errstr,
			]);
		}
		return '';
	}
}
