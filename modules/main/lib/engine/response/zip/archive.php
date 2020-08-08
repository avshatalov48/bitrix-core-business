<?php
namespace Bitrix\Main\Engine\Response\Zip;

use \Bitrix\Main\Loader;

class Archive extends \Bitrix\Main\HttpResponse
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
		parent::__construct();
		$this->name = $name;

		$this->addHeader('X-Archive-Files', 'zip');
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

		return implode("\n", $list);
	}

	protected function setContentDispositionHeader()
	{
		$utfName = \CHTTP::urnEncode($this->name, 'UTF-8');
		$translitName = \CUtil::translit($this->name, LANGUAGE_ID, [
			'max_len' => 1024,
			'safe_chars' => '.',
			'replace_space' => '-',
		]);
		$this->addHeader(
			'Content-Disposition',
			"attachment; filename=\"{$translitName}\"; filename*=utf-8''{$utfName}"
		);
	}

	/**
	 * Sends content to output stream and sets necessary headers.
	 * @return void
	 */
	public function send()
	{
		if (!$this->isEmpty())
		{
			$this->setContentDispositionHeader();
			$this->setContent(
				$this->getFileList()
			);
		}

		parent::send();
	}
}