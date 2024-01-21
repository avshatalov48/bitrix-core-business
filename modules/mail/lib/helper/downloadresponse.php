<?php

namespace Bitrix\Mail\Helper;

/**
 * Response with headers that induce browser downloading as file
 * should be in module main
 */
class DownloadResponse extends \Bitrix\Main\HttpResponse
{
	/**
	 * Constructor
	 *
	 * @param string $content Content to download
	 * @param string $name Name of downloaded file (should be compliant with standards)
	 * @param string $contentType MIME content type for browser
	 */
	public function __construct(string $content, string $name = 'download_file', string $contentType = 'application/octet-stream')
	{
		parent::__construct();
		$this->setContent($content);

		$headers = new \Bitrix\Main\Web\HttpHeaders();
		$headers->add('Pragma', 'public');
		$headers->add('Expires', '0');
		$headers->add('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
		$headers->add('Content-Disposition', 'attachment; filename="' . $name . '";');
		$headers->add('Content-Transfer-Encoding', 'binary');
		$headers->add('Content-Length', strlen($content));
		$headers->add('Content-Type', $contentType);

		$this->setHeaders($headers);
	}

}
