<?php
namespace Bitrix\Main\Engine\Response\Zip;

use Bitrix\Main\HttpResponse;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\Uri;

class Archive extends HttpResponse
{
	public const MOD_ZIP_HEADER_NAME = 'X-Archive-Files';
	/**
	 * Archive name.
	 * @var string
	 */
	protected string $name;

	/**
	 * Archive Entries.
	 * @var ArchiveEntry[]|EntryInterface[]
	 */
	protected array $entries = [];

	/**
	 * Archive constructor.
	 * @param string $name Archive name.
	 */
	public function __construct(string $name)
	{
		parent::__construct();

		$this->name = $name;
		$this->addHeader(self::MOD_ZIP_HEADER_NAME, 'zip');
	}

	/**
	 * Add one entry. in current archive.
	 * @param ArchiveEntry|EntryInterface $archiveEntry Entry for archive.
	 */
	public function addEntry($archiveEntry): void
	{
		if ($archiveEntry instanceof ArchiveEntry)
		{
			$this->entries[] = $archiveEntry;
		}
		elseif ($archiveEntry instanceof EntryInterface)
		{
			$this->entries[] = $archiveEntry;
		}
	}

	private function convertEntryInterfaceToString(EntryInterface $entry): string
	{
		$crc32 = ($entry->getCrc32() !== '') ? $entry->getCrc32() : '-';
		$name = Encoding::convertEncoding(
			$entry->getPath(),
			LANG_CHARSET,
			'UTF-8'
		);

		return "{$crc32} {$entry->getSize()} {$entry->getServerRelativeUrl()} {$name}";
	}

	/**
	 * Returns true if the archive does not have entries.
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return empty($this->entries);
	}

	/**
	 * Return entries as string.
	 * @return string
	 */
	protected function getFileList(): string
	{
		$list = [];
		foreach ($this->entries as $entry)
		{
			if ($entry instanceof ArchiveEntry)
			{
				$list[] = (string)$entry;
			}
			elseif ($entry instanceof EntryInterface)
			{
				$list[] = $this->convertEntryInterfaceToString($entry);
			}
		}

		return implode("\n", $list);
	}

	protected function setContentDispositionHeader(): void
	{
		$utfName = Uri::urnEncode($this->name, 'UTF-8');
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

	public function send(): void
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