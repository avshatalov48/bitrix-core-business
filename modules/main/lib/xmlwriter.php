<?php
namespace Bitrix\Main;

class XmlWriter
{
	private $file = '';
	private $charset = '';
	private $tab = 0;
	private $f = null;
	private $lowercaseTag = false;
	private $errors = array();

	/**
	 * Constructor.
	 * @param array $params Array of settings.
	 */
	public function __construct(array $params)
	{
		if (isset($params['file']))
		{
			$server = \Bitrix\Main\Application::getInstance()->getContext()->getServer();
			$this->file = $server->getDocumentRoot() . trim($params['file']);
			// create new file
			if (
				isset($params['create_file']) &&
				$params['create_file'] === true &&
				is_writable($this->file)
			)
			{
				unlink($this->file);
			}
		}
		if (isset($params['charset']))
		{
			$this->charset = trim($params['charset']);
		}
		else
		{
			$this->charset = SITE_CHARSET;
		}
		if (isset($params['lowercase']) && $params['lowercase'] === true)
		{
			$this->lowercaseTag = true;
		}
		if (isset($params['tab']))
		{
			$this->tab = (int)$params['tab'];
		}
	}

	/**
	 * Prepare tag for write.
	 * @param string $tag Code of tag.
	 * @return string
	 */
	private function prepareTag($tag)
	{
		if ($this->lowercaseTag)
		{
			$tag = strtolower($tag);
		}
		return $tag;
	}

	/**
	 * Prepare value for write.
	 * @param string $value Value.
	 * @return string
	 */
	private function prepareValue($value)
	{
		$value = strtr(
				$value,
				array(
					'<' => '&lt;',
					'>' => '&gt;',
					'"' => '&quot;',
					'\'' => '&apos;',
					'&' => '&amp;',
				)
			);
		$value = preg_replace('/[\x01-\x08\x0B-\x0C\x0E-\x1F]/', '', $value);
		return $value;
	}

	/**
	 * Write begin tag to file.
	 * @param string $code Code of tag.
	 * @return void
	 */
	public function writeBeginTag($code)
	{
		if ($this->f)
		{
			fwrite($this->f, str_repeat("\t", $this->tab) . '<' . $this->prepareTag($code) . '>' . PHP_EOL);
			$this->tab++;
		}
	}

	/**
	 * Write end tag to file.
	 * @param string $code Code of tag.
	 * @return void
	 */
	public function writeEndTag($code)
	{
		if ($this->f)
		{
			$this->tab--;
			fwrite($this->f, str_repeat("\t", $this->tab) . '</' . $this->prepareTag($code) . '>' . PHP_EOL);
		}
	}

	/**
	 * Write full tag to file.
	 * @param string $code Code of tag.
	 * @param string $value Code for tag.
	 * @return void
	 */
	public function writeFullTag($code, $value)
	{
		if ($this->f)
		{
			$code = $this->prepareTag($code);
			fwrite($this->f,
							str_repeat("\t", $this->tab) .
							(
								trim($value) == ''
								? '<' . $code . ' />' . PHP_EOL
								:   '<' . $code . '>' .
										$this->prepareValue($value) .
									'</' . $code . '>' . PHP_EOL
							)
					);
		}
	}

	/**
	 * Add one more error.
	 * @param mixed $message Text of error.
	 * @param mixed $code Code of error.
	 * @return void
	 */
	private function addError($message, $code)
	{
		$this->errors[] = new Error($message, $code);
	}

	/**
	 * Return errors if exists.
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Open file for write, start write xml.
	 * @return void
	 */
	public function openFile()
	{
		if ($this->file == '')
		{
			$this->addError('File not accessible.', 'XML_FILE_NOT_ACCESSIBLE');
		}
		else
		{
			\CheckDirPath($this->file);
			$newFile = !file_exists($this->file);
			if (file_exists($this->file) && !is_writable($this->file))
			{
				chmod($this->file, BX_FILE_PERMISSIONS);
			}
			if (($this->f = fopen($this->file, 'ab')))
			{
				chmod($this->file, BX_FILE_PERMISSIONS);
				if ($newFile)
				{
					fwrite($this->f, '<?xml version="1.0" encoding="'. $this->charset .'"?>' . PHP_EOL);
				}
			}
			else
			{
				$this->addError('File not accessible.', 'XML_FILE_NOT_ACCESSIBLE');
			}
		}
	}

	/**
	 * Close the open file.
	 * @return void
	 */
	public function closeFile()
	{
		if ($this->f)
		{
			fclose($this->f);
		}
	}

	/**
	 * Write one chunk in xml file.
	 * @param array $item Data for write.
	 * @param string $wrapperTag If != '' wrapper the array in this tag.
	 * @return void
	 */
	public function writeItem(array $item, $wrapperTag = '')
	{
		if ($wrapperTag != '')
		{
			$this->writeBeginTag($wrapperTag);
		}
		foreach ($item as $tag => $value)
		{
			if (is_array($value))
			{
				$this->writeItem($value, $tag);
			}
			else
			{
				$this->writeFullTag($tag, $value);
			}
		}
		if ($wrapperTag != '')
		{
			$this->writeEndTag($wrapperTag);
		}
	}
}