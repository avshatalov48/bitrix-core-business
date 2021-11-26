<?php
namespace Bitrix\Main\UI\Uploader;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Result;
use \Bitrix\Main\UI\FileInputUtility;
use \Bitrix\Main\Web\HttpClient;
use \Bitrix\Main\Web\Uri;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;

class FileRemoved
{
	/** @var Package */
	protected $package;
	/** @var array */
	protected $data = array();

	/**
	 * File constructor.
	 * @param Package $package Package for file.
	 * @param array $file File array.
	 */
	public function __construct($package, array $file)
	{
		$hash = File::initHash(array("id" => $file["id"], "name" => $file["name"]));
		$this->data = array(
			"hash" => $hash,
			"id" => $file["id"],
			"uploadStatus" => 'removed',
			"name" => $file["name"],
		);

		$this->package = $package;

		FileInputUtility::instance()->unRegisterFile($this->package->getCid(), $this->getHash());
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->data["id"];
	}

	/**
	 * @return string
	 */
	public function getHash()
	{
		return $this->data["hash"];
	}
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->data["name"];
	}

	/**
	 * Returns file whole data.
	 * @return array
	 */
	public function toArray()
	{
		return $this->data;
	}

	public function isUploaded()
	{
		return true;
	}

	public function hasError()
	{
		return false;
	}
}