<?php

namespace Bitrix\Translate\IO;

use Bitrix\Main;
use Bitrix\Translate;

class CsvFile
	extends Translate\IO\File
{
	// fields type with delimiter,
	public const FIELDS_TYPE_FIXED_WIDTH = 'F';
	// fields type fixed width
	public const FIELDS_TYPE_WITH_DELIMITER = 'R';

	public const ERROR_32K_FIELD_LENGTH = '32k_field_length';

	/**
	 * fields type
	 * @var string
	 */
	protected $fieldsType = self::FIELDS_TYPE_WITH_DELIMITER;

	// field delimiter
	public const DELIMITER_TAB = "\t";
	public const DELIMITER_ZPT = ',';
	public const DELIMITER_SPS = ' ';
	public const DELIMITER_TZP = ';';

	/**
	 * field delimiter
	 * @var string
	 */
	protected $fieldDelimiter = self::DELIMITER_TZP;

	// UTF-8 Byte-Order Mark
	public const BOM_TYPE_UTF8 = "\xEF\xBB\xBF";

	/**
	 * UTF Byte-Order Mark.
	 * @var string
	 */
	protected $bomMark = self::BOM_TYPE_UTF8;

	/**
	 * First bites is file are the Byte-Order Mark.
	 * @var bool
	 */
	protected $hasBom = false;

	public const LINE_DELIMITER_WIN = "\r\n";
	public const LINE_DELIMITER_UNIX = "\r";

	/**
	 * line delimiter
	 * @var string
	 */
	protected $rowDelimiter = self::LINE_DELIMITER_WIN;

	/**
	 * array of delimiters positions in fixed width case
	 * @var array
	 */
	protected $widthMap = [];

	/**
	 * The first row is columns titles.
	 * @var bool
	 */
	protected $firstHeader = false;

	/**
	 * File length.
	 * @var string
	 */
	private $fileSize;
	/**
	 * Current file position for fetch.
	 * @var int
	 */
	private $currentPosition = 0;
	/** @var string  */
	private $buffer = '';
	/** @var int  */
	private $bufferPosition = 0;
	/** @var int  */
	private $bufferSize = 0;


	/**
	 * Opens file for reading.
	 *
	 * @return bool
	 */
	public function openLoad(): bool
	{
		if ($this->isExists())
		{
			$this->open(Main\IO\FileStreamOpenMode::READ);

			$this->fileSize = $this->getSize();
			$this->checkUtf8Bom();
		}

		return $this->isExists() && $this->isReadable();
	}

	/**
	 * Opens file for writing.
	 *
	 * @param string $mode File writing mode.
	 * @see \Bitrix\Main\IO\FileStreamOpenMode
	 *
	 * @return bool
	 */
	public function openWrite(string $mode = Main\IO\FileStreamOpenMode::WRITE): bool 
	{
		$this->open($mode);

		if (\is_resource($this->filePointer))
		{
			if ($mode === Main\IO\FileStreamOpenMode::WRITE)
			{
				$this->fileSize = 0;
				if ($this->hasBom)
				{
					$this->fileSize = $this->write($this->bomMark);
				}
			}
			else
			{
				$this->fileSize = $this->getSize();
			}

			return true;
		}

		return false;
	}

	/**
	 * Sets UTF Byte-Order Mark.
	 *
	 * @param string $mark BOM mark.
	 * @return self
	 */
	public function setUtf8Bom(string $mark = self::BOM_TYPE_UTF8): self
	{
		$this->bomMark = $mark;

		return $this;
	}

	/**
	 * Tells true if UTF Byte-Order Mark exists in the file.
	 *
	 * @return bool
	 */
	public function hasUtf8Bom(): bool
	{
		return $this->hasBom;
	}

	/**
	 * Sets if UTF-8 Byte-Order Mark exists.
	 *
	 * @param bool $exists Flag value to setup.
	 *
	 * @return self
	 */
	public function prefaceWithUtf8Bom(bool $exists = true): self
	{
		$this->hasBom = $exists;

		return $this;
	}

	/**
	 * Measures byte length of the string.
	 * @param string $data
	 * @return int
	 */
	protected function getStringByteLength(string $data): int
	{
		return \mb_strlen($data, '8bit');
	}

	/**
	 * Check UTF-8 Byte-Order Mark
	 * @return bool
	 */
	public function checkUtf8Bom(): bool
	{
		$this->seek(0);
		$bom = $this->read($this->getStringByteLength($this->bomMark));
		if($bom === $this->bomMark)
		{
			$this->hasBom = true;
		}

		if ($this->hasBom)
		{
			$this->seek($this->getStringByteLength($this->bomMark));
		}
		else
		{
			$this->seek(0);
		}

		return $this->hasBom;
	}

	/**
	 * Set fields type.
	 *
	 * @param string $fieldsType Type.
	 * @return self
	 */
	public function setFieldsType(string $fieldsType = self::FIELDS_TYPE_WITH_DELIMITER): self
	{
		$this->fieldsType =
			($fieldsType === self::FIELDS_TYPE_FIXED_WIDTH ? self::FIELDS_TYPE_FIXED_WIDTH : self::FIELDS_TYPE_WITH_DELIMITER);

		return $this;
	}

	/**
	 * Sets up delimiter character.
	 *
	 * @param string $fieldDelimiter Char.
	 *
	 * @return self
	 */
	public function setFieldDelimiter(string $fieldDelimiter = self::DELIMITER_TZP): self
	{
		$this->fieldDelimiter = (\mb_strlen($fieldDelimiter) > 1 ? \mb_substr($fieldDelimiter, 0, 1) : $fieldDelimiter);

		return $this;
	}

	/**
	 * Sets up row delimiter character.
	 *
	 * @param string $rowDelimiter Char.
	 *
	 * @return self
	 */
	public function setRowDelimiter(string $rowDelimiter = self::LINE_DELIMITER_WIN): self
	{
		$this->rowDelimiter = $rowDelimiter;

		return $this;
	}

	/**
	 * Sets first row as a header.
	 *
	 * @param bool $firstHeader Flag.
	 * @return self
	 */
	public function setFirstHeader(bool $firstHeader = false): self
	{
		$this->firstHeader = $firstHeader;

		return $this;
	}

	/**
	 * Tells true if first row is a header.
	 *
	 * @return bool
	 */
	public function getFirstHeader(): bool 
	{
		return $this->firstHeader;
	}

	/**
	 * Sets up fields widths.
	 *
	 * @param int[] $mapFields Fields widths.
	 * @return self
	 */
	public function setWidthMap(array $mapFields): self
	{
		$this->widthMap = [];
		for ($i = 0, $n = \count($mapFields); $i < $n; $i++)
		{
			$this->widthMap[$i] = (int)$mapFields[$i];
		}

		return $this;
	}

	/**
	 * Fetches data row as delimited columns.
	 *
	 * @return array|null
	 */
	protected function fetchDelimiter(): ?array
	{
		$isInside = false;
		$str = '';
		$result = [];
		while ($this->currentPosition <= $this->fileSize)
		{
			$ch = $this->buffer[$this->bufferPosition];
			if ($ch === "\r" || $ch === "\n")
			{
				if (!$isInside)
				{
					while ($this->currentPosition <= $this->fileSize)
					{
						$this->incrementCurrentPosition();
						$ch = $this->buffer[$this->bufferPosition];
						if ($ch !== "\r" && $ch !== "\n")
						{
							break;
						}
					}
					if ($this->firstHeader)
					{
						$this->firstHeader = false;
						$result = [];
						$str = '';
						continue;
					}

					$result[] = $str;

					return $result;
				}
			}
			elseif ($ch === "\"")
			{
				if (!$isInside)
				{
					$isInside = true;
					$this->incrementCurrentPosition();
					continue;
				}

				$this->incrementCurrentPosition();
				if ($this->buffer[$this->bufferPosition] !== "\"")
				{
					$isInside = false;
					continue;
				}
			}
			elseif ($ch === $this->fieldDelimiter)
			{
				if (!$isInside)
				{
					$result[] = $str;
					$str = '';
					$this->incrementCurrentPosition();
					continue;
				}
			}

			//inline "call"
			$this->currentPosition ++;
			$this->bufferPosition ++;
			if ($this->bufferPosition >= $this->bufferSize)
			{
				$this->buffer = $this->read(1024 * 1024);
				$this->bufferSize = $this->getStringByteLength($this->buffer);
				$this->bufferPosition = 0;
			}

			$str .= $ch;
		}

		if ($str !== '')
		{
			$result[] = $str;
		}

		if ($result === [])
		{
			$result = null;
		}

		return $result;
	}

	/**
	 * Fetches data row as fixed width columns.
	 *
	 * @return array|null
	 */
	protected function fetchWidth(): ?array
	{
		$str = '';
		$ind = 1;
		$jnd = 0;
		$result = [];

		while ($this->currentPosition <= $this->fileSize)
		{
			$ch = $this->buffer[$this->bufferPosition];
			if ($ch === "\r" || $ch === "\n")
			{
				while ($this->currentPosition <= $this->fileSize)
				{
					$this->incrementCurrentPosition();
					$ch = $this->buffer[$this->bufferPosition];
					if ($ch !== "\r" && $ch !== "\n")
					{
						break;
					}
				}
				if ($this->firstHeader)
				{
					$this->firstHeader = false;
					$result = [];
					$ind = 1;
					$str = '';
					continue;
				}

				$result[] = $str;

				return $result;
			}
			if ($ind === $this->widthMap[$jnd])
			{
				$result[] = $str. $ch;
				$str = '';
				$this->incrementCurrentPosition();
				$ind ++;
				$jnd ++;
				continue;
			}

			//inline "call"
			$this->currentPosition ++;
			$this->bufferPosition ++;
			if($this->bufferPosition >= $this->bufferSize)
			{
				$this->buffer = $this->read( 1024 * 1024);
				$this->bufferSize = $this->getStringByteLength($this->buffer);
				$this->bufferPosition = 0;
			}

			$ind ++;
			$str .= $ch;
		}

		if ($str !== '')
		{
			$result[] = $str;
		}

		if ($result === [])
		{
			$result = null;
		}

		return $result;
	}

	/**
	 * Fetch data row.
	 *
	 * @return array|null
	 */
	public function fetch(): ?array
	{
		if ($this->fieldsType === self::FIELDS_TYPE_WITH_DELIMITER)
		{
			if ($this->fieldDelimiter === '')
			{
				return null;
			}

			return $this->fetchDelimiter();
		}

		if (empty($this->widthMap))
		{
			return null;
		}

		return $this->fetchWidth();
	}

	/**
	 * Moves reading position and reads file into buffer.
	 *
	 * @return void
	 */
	protected function incrementCurrentPosition(): void
	{
		$this->currentPosition ++;
		$this->bufferPosition ++;
		if ($this->bufferPosition >= $this->bufferSize)
		{
			$this->buffer = $this->read( 1024 * 1024);
			$this->bufferSize = $this->getStringByteLength($this->buffer);
			$this->bufferPosition = 0;
		}
	}

	/**
	 * Moves reading position to the first byte.
	 *
	 * @return void
	 */
	public function moveFirst(): void
	{
		$this->setPos(0);
	}

	/**
	 * Returns reading position.
	 *
	 * @return int
	 */
	public function getPos(): int
	{
		return $this->currentPosition;
	}

	/**
	 * Sets new reading position.
	 *
	 * @param int $position Reading position.
	 *
	 * @return void
	 */
	public function setPos(int $position = 0): void
	{
		if ($position <= $this->fileSize)
		{
			$this->currentPosition = $position;
		}
		else
		{
			$this->currentPosition = $this->fileSize;
		}

		$pos = $this->currentPosition;
		if($this->hasBom)
		{
			$pos += 3;
		}
		$this->seek($pos);

		$this->buffer = $this->read(1024 * 1024);

		$this->bufferSize = $this->getStringByteLength($this->buffer);
		$this->bufferPosition = 0;
	}

	/**
	 * Writes data fields into file as row.
	 *
	 * @param array $fields Data field.
	 * 
	 * @return bool
	 */
	public function put(array $fields): bool
	{
		$length = false;
		$throw32KWarning = false;
		if ($this->fieldsType == self::FIELDS_TYPE_WITH_DELIMITER)
		{
			$content = '';
			for ($i = 0, $n = \count($fields); $i < $n; $i++)
			{
				if ($i>0)
				{
					$content .= $this->fieldDelimiter;
				}
				//$pos1 = strpos($fields[$i], $this->fieldDelimiter);
				//$pos2 = $pos1 || strpos($fields[$i], "\"");
				//$pos3 = $pos2 || strpos($fields[$i], "\n");
				//$pos4 = $pos3 || strpos($fields[$i], "\r");
				//if ($pos1 !== false || $pos2 !== false || $pos3 !== false || $pos4 !== false)
				if ($fields[$i] === null)
				{
					$fields[$i] = '';
				}
				elseif (\preg_match("#[\"\n\r]+#".\BX_UTF_PCRE_MODIFIER, $fields[$i]))
				{
					$fields[$i] = \str_replace("\"", "\"\"", $fields[$i]);
					//$fields[$i] = str_replace("\\", "\\\\", $fields[$i]);
				}
				$content .= "\"";
				$content .= $fields[$i];
				$content .= "\"";

				// ms excel las limitation with total number of characters that a cell can contain 32767 characters
				if ($throw32KWarning !== true && $this->getStringByteLength($fields[$i]) > 32767)
				{
					$throw32KWarning = true;
				}
			}
			if ($content !== '')
			{
				$content .= $this->rowDelimiter;

				$length = $this->write($content);
				if ($length !== false)
				{
					$this->fileSize += $length;
				}
			}
		}
		// todo: $this->fieldsType == self::FIELDS_TYPE_FIXED_WIDTH

		if ($throw32KWarning)
		{
			if (!$this->hasError(self::ERROR_32K_FIELD_LENGTH))
			{
				$this->addError(new Main\Error(
					'Excel has limit when the total number of characters that a cell can contain is 32767 characters.',
					self::ERROR_32K_FIELD_LENGTH
				));
			}
		}

		return ($length !== false);
	}
}
