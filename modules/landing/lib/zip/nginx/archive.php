<?php
namespace Bitrix\Landing\Zip\Nginx;

use \Bitrix\Main\Context;
use \Bitrix\Main\Loader;

class Archive
{
	/**
	 * Archive name.
	 * @var string
	 */
	protected $name;

	/**
	 * Archive Entries.
	 * @var ArchiveEntry[]
	 */
	protected $entries = [];

	/**
	 * Archive constructor.
	 * @param string $name Archive name.
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * Add one entry. in current archive.
	 * @param ArchiveEntry $archiveEntry Entry for archive.
	 */
	public function addEntry($archiveEntry)
	{
		if ($archiveEntry instanceof ArchiveEntry)
		{
			$this->entries[] = $archiveEntry;
		}
	}

	/**
	 * Returns true if the archive does not have entries.
	 * @return bool
	 */
	public function isEmpty()
	{
		return empty($this->entries);
	}

	/**
	 * Return entries as string.
	 * @return string
	 */
	protected function getFileList()
	{
		$list = [];
		foreach ($this->entries as $entry)
		{
			$list[] = (string)$entry;
		}
		unset($entry);

		return implode("\n", $list);
	}

	/**
	 * Add necessary headers.
	 * @return void
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	protected function addHeaders()
	{
		$httpResponse = Context::getCurrent()->getResponse();
		$httpResponse->addHeader('X-Archive-Files', 'zip');

		$utfName = \CHTTP::urnEncode($this->name, 'UTF-8');
		$translitName = \CUtil::translit($this->name, LANGUAGE_ID, [
			'max_len' => 1024,
			'safe_chars' => '.',
			'replace_space' => '-',
		]);
		$httpResponse->addHeader(
			'Content-Disposition',
			"attachment; filename=\"" . $translitName . "\"; filename*=utf-8''" . $utfName
		);

		unset($utfName, $translitName, $httpResponse);
	}

	/**
	 * Sends content to output stream and sets necessary headers.
	 * @return void
	 */
	public function send()
	{
		if (!$this->isEmpty())
		{
			$this->disableCompression();
			$this->addHeaders();
			Context::getCurrent()->getResponse()->flush(
				$this->getFileList()
			);
		}
	}

	/**
	 * Disable compression of module compression.
	 */
	protected function disableCompression()
	{
		if (Loader::includeModule('compression'))
		{
			\CCompress::disableCompression();
		}
	}
}