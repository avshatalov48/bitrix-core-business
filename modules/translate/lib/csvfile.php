<?php
namespace Bitrix\Translate;

use Bitrix\Main\Text\BinaryString;
use Bitrix\Main;

class CsvFile extends File
{
	// fields type with delimiter,
	const FIELDS_TYPE_FIXED_WIDTH = 'F';
	// fields type fixed width
	const FIELDS_TYPE_WITH_DELIMITER = 'R';

	/**
	 * fields type
	 * @var string
	 */
	protected $fieldsType = self::FIELDS_TYPE_WITH_DELIMITER;

	// field delimiter
	const DELIMITER_TAB = "\t";
	const DELIMITER_ZPT = ',';
	const DELIMITER_SPS = ' ';
	const DELIMITER_TZP = ';';

	/**
	 * field delimiter
	 * @var string
	 */
	protected $fieldDelimiter = self::DELIMITER_TZP;

	// UTF-8 Byte-Order Mark
	const BOM_TYPE_UTF8 = "\xEF\xBB\xBF";

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

	const LINE_DELIMITER_WIN = "\r\n";
	const LINE_DELIMITER_UNIX = "\r";

	/**
	 * line delimiter
	 * @var string
	 */
	protected $rowDelimiter = self::LINE_DELIMITER_WIN;

	/**
	 * array of delimiters positions in fixed width case
	 * @var array
	 */
	protected $widthMap = array();

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
	 *
	 * @return bool
	 */
	public function openLoad()
	{
		if (parent::openLoad())
		{
			$this->fileSize = $this->getSize();
			$this->checkUtf8Bom();
		}

		return $this->isExists() && $this->isReadable();
	}

	/**
	 * Opens file for writing.
	 *
	 * @return bool
	 */
	public function openWrite()
	{
		if (parent::openWrite())
		{
			$this->fileSize = 0;
			if ($this->hasBom)
			{
				$this->fileSize = $this->write($this->bomMark);
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
	public function setUtf8Bom($mark = self::BOM_TYPE_UTF8)
	{
		$this->bomMark = $mark;

		return $this;
	}

	/**
	 * Tells true if UTF Byte-Order Mark exists in the file.
	 *
	 * @return bool
	 */
	public function hasUtf8Bom()
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
	public function prefaceWithUtf8Bom($exists = true)
	{
		$this->hasBom = $exists;

		return $this;
	}

	/**
	 * Check UTF-8 Byte-Order Mark
	 * @return bool
	 */
	public function checkUtf8Bom()
	{
		$this->seek(0);
		$bom = $this->read(BinaryString::getLength($this->bomMark));
		if($bom === $this->bomMark)
		{
			$this->hasBom = true;
		}

		if ($this->hasBom)
		{
			$this->seek(BinaryString::getLength($this->bomMark));
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
	public function setFieldsType($fieldsType = self::FIELDS_TYPE_WITH_DELIMITER)
	{
		$this->fieldsType =
			($fieldsType == self::FIELDS_TYPE_FIXED_WIDTH ? self::FIELDS_TYPE_FIXED_WIDTH : self::FIELDS_TYPE_WITH_DELIMITER);

		return $this;
	}

	/**
	 * Sets up delimiter character.
	 *
	 * @param string $fieldDelimiter Char.
	 *
	 * @return self
	 */
	public function setFieldDelimiter($fieldDelimiter = self::DELIMITER_TZP)
	{
		$this->fieldDelimiter = (strlen($fieldDelimiter) > 1 ? substr($fieldDelimiter, 0, 1) : $fieldDelimiter);

		return $this;
	}

	/**
	 * Sets up row delimiter character.
	 *
	 * @param string $rowDelimiter Char.
	 *
	 * @return self
	 */
	public function setRowDelimiter($rowDelimiter = self::LINE_DELIMITER_WIN)
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
	public function setFirstHeader($firstHeader = false)
	{
		$this->firstHeader = $firstHeader;

		return $this;
	}

	/**
	 * Tells true if first row is a header.
	 *
	 * @return bool
	 */
	public function getFirstHeader()
	{
		return $this->firstHeader;
	}

	/**
	 * Sets up fields widths.
	 *
	 * @param int[] $mapFields Fields widths.
	 * @return self
	 */
	public function setWidthMap($mapFields)
	{
		$this->widthMap = array();
		for ($i = 0, $n = count($mapFields); $i < $n; $i++)
		{
			$this->widthMap[$i] = (int)$mapFields[$i];
		}

		return $this;
	}

	/**
	 * Fetches data row as delimited columns.
	 *
	 * @return array|bool
	 */
	protected function fetchDelimiter()
	{
		$isInside = false;
		$str = '';
		$result = array();
		while ($this->currentPosition < $this->fileSize)
		{
			$ch = $this->buffer[$this->bufferPosition];
			if ($ch == "\r" || $ch == "\n")
			{
				if (!$isInside)
				{
					while ($this->currentPosition < $this->fileSize)
					{
						$this->incrementCurrentPosition();
						$ch = $this->buffer[$this->bufferPosition];
						if ($ch != "\r" && $ch != "\n")
						{
							break;
						}
					}
					if ($this->firstHeader)
					{
						$this->firstHeader = false;
						$result = array();
						$str = '';
						continue;
					}

					$result[] = $str;
					return $result;
				}
			}
			elseif ($ch == "\"")
			{
				if (!$isInside)
				{
					$isInside = true;
					$this->incrementCurrentPosition();
					continue;
				}

				$this->incrementCurrentPosition();
				if ($this->buffer[$this->bufferPosition] != "\"")
				{
					$isInside = false;
					continue;
				}
			}
			elseif ($ch == $this->fieldDelimiter)
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
				$this->bufferSize = BinaryString::getLength($this->buffer);
				$this->bufferPosition = 0;
			}

			$str .= $ch;
		}

		if ($str <> '')
		{
			$result[] = $str;
		}

		if(empty($result))
		{
			return false;
		}

		return $result;
	}

	/**
	 * Fetches data row as fixed width columns.
	 *
	 * @return array|bool
	 */
	protected function fetchWidth()
	{
		$str = '';
		$ind = 1;
		$jnd = 0;
		$result = array();

		while ($this->currentPosition < $this->fileSize)
		{
			$ch = $this->buffer[$this->bufferPosition];
			if ($ch == "\r" || $ch == "\n")
			{
				while ($this->currentPosition < $this->fileSize)
				{
					$this->incrementCurrentPosition();
					$ch = $this->buffer[$this->bufferPosition];
					if ($ch != "\r" && $ch != "\n")
					{
						break;
					}
				}
				if ($this->firstHeader)
				{
					$this->firstHeader = false;
					$result = array();
					$ind = 1;
					$str = '';
					continue;
				}

				$result[] = $str;
				return $result;
			}
			if ($ind == $this->widthMap[$jnd])
			{
				$result[] = $str.$ch;
				$str = '';
				$this->incrementCurrentPosition();
				$ind++;
				$jnd++;
				continue;
			}

			//inline "call"
			$this->currentPosition ++;
			$this->bufferPosition ++;
			if($this->bufferPosition >= $this->bufferSize)
			{
				$this->buffer = $this->read( 1024 * 1024);
				$this->bufferSize = BinaryString::getLength($this->buffer);
				$this->bufferPosition = 0;
			}

			$ind ++;
			$str .= $ch;
		}

		if ($str <> '')
		{
			$result[] = $str;
		}

		if(empty($result))
		{
			return false;
		}

		return $result;
	}

	/**
	 * Fetch data row.
	 *
	 * @return array|bool
	 */
	public function fetch()
	{
		if ($this->fieldsType == self::FIELDS_TYPE_WITH_DELIMITER)
		{
			if ($this->fieldDelimiter == '')
			{
				return false;
			}

			return $this->fetchDelimiter();
		}

		if (empty($this->widthMap))
		{
			return false;
		}

		return $this->fetchWidth();
	}

	/**
	 * Moves reading position and reads file into buffer.
	 *
	 * @return void
	 */
	protected function incrementCurrentPosition()
	{
		$this->currentPosition ++;
		$this->bufferPosition ++;
		if ($this->bufferPosition >= $this->bufferSize)
		{
			$this->buffer = $this->read( 1024 * 1024);
			$this->bufferSize = BinaryString::getLength($this->buffer);
			$this->bufferPosition = 0;
		}
	}

	/**
	 * Moves reading position to the first byte.
	 *
	 * @return void
	 */
	protected function moveFirst()
	{
		$this->setPos(0);
	}

	/**
	 * Returns reading position.
	 *
	 * @return int
	 */
	protected function getPos()
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
	protected function setPos($position = 0)
	{
		$position = intval($position);
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

		$this->bufferSize = BinaryString::getLength($this->buffer);
		$this->bufferPosition = 0;
	}

	/**
	 * Writes data fields into file as row.
	 *
	 * @param array $fields Data field.
	 * 
	 * @return bool
	 */
	public function put(array $fields)
	{
		$length = false;
		if ($this->fieldsType == self::FIELDS_TYPE_WITH_DELIMITER)
		{
			$content = '';
			for ($i = 0, $n = count($fields); $i < $n; $i++)
			{
				if ($i>0)
				{
					$content .= $this->fieldDelimiter;
				}
				$pos1 = strpos($fields[$i], $this->fieldDelimiter);
				$pos2 = $pos1 || strpos($fields[$i], "\"");
				$pos3 = $pos2 || strpos($fields[$i], "\n");
				$pos4 = $pos3 || strpos($fields[$i], "\r");
				if ($pos1 !== false || $pos2 !== false || $pos3 !== false || $pos4 !== false)
				{
					$fields[$i] = str_replace("\"", "\"\"", $fields[$i]);
					$fields[$i] = str_replace("\\", "\\\\", $fields[$i]);
				}
				$content .= "\"";
				$content .= $fields[$i];
				$content .= "\"";
			}
			if ($content <> '')
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

		return ($length !== false);
	}
}
