<?php

namespace Bitrix\Main\UI;

use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\UI\Uploader\Uploader;

class FileInputReceiver
{
	protected $status = array();
	protected $id = "unknown";
	protected $uploader;

	const STATUS_SUCCESS      = 'success';
	const STATUS_DENIED       = 'denied';
	const STATUS_ERROR        = 'error';
	const STATUS_NEED_AUTH    = 'need_auth';
	const STATUS_INVALID_SIGN = 'invalid_sign';

	function __construct($signature)
	{
		global $USER;

		if (!$USER->IsAuthorized())
			throw new AccessDeniedException(Loc::getMessage("BXU_AccessDenied_Authorize"));

		$sign = new Signer;
		$params = unserialize(base64_decode($sign->unsign($signature, "fileinput")), ["allowed_classes" => false]);
		$this->id = $params["id"];

		$this->uploader = new Uploader($params);
		$this->uploader->setHandler("onFileIsUploaded", array($this, "handleFile"));
	}
	protected function getAgent()
	{
		return $this->uploader;
	}

	public static function sign($params = array())
	{
		$sign = new Signer();
		return $sign->sign(base64_encode(serialize($params)), "fileinput");
	}


	protected static function handleFileByPath(&$file)
	{
		$key = "default";

		$docRoot = \CBXVirtualIo::GetInstance()->CombinePath(\CTempFile::GetAbsoluteRoot());
		$file["files"][$key]["path"] = \CBXVirtualIo::GetInstance()->GetFile($file["files"][$key]["tmp_name"])->GetPathWithName();
		if (mb_strpos($file["files"][$key]["path"], $docRoot) === 0)
			$file["files"][$key]["path"] = str_replace("//", "/", "/".mb_substr($file["files"][$key]["path"], mb_strlen($docRoot)));

		$file["files"][$key]["tmp_url"] = $file["files"][$key]["url"];
		$file["type"] = $file["files"][$key]["type"];

		return true;
	}

	protected static function handleFileByHash($hash, &$file)
	{
		$file["uploadId"] = $hash;
		return true;
	}

	public function handleFile($hash, &$file)
	{
		if ($this->id == "path")
		{
			return self::handleFileByPath($file);
		}
		return self::handleFileByHash($hash, $file);
	}

	public function exec()
	{
		$this->getAgent()->checkPost();
	}
}
