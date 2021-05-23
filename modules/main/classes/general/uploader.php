<?php

/**
 * Class CFileUploader
 * @deprecated Use \Bitrix\Main\UI\Uploader\Uploader
 */
class CFileUploader extends \Bitrix\Main\UI\Uploader\Uploader
{
	public function __construct($params, $doCheckPost = true)
	{
		parent::__construct($params);
		if ($doCheckPost !== false)
		{
			$this->checkPost(($doCheckPost === true || $doCheckPost == "post"));
		}
	}
}
