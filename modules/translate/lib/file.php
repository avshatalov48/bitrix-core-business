<?php

namespace Bitrix\Translate;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Translate\Index;


class File
	extends Translate\IO\File
	implements \Iterator, \Countable, \ArrayAccess
{
	/** @var string */
	protected $languageId;

	/** @var string */
	protected $sourceEncoding;

	/** @var string */
	protected $operatingEncoding;

	/** @var string[] */
	protected $messages = null;

	/** @var int */
	protected $messagesCount = null;

	/** @var array */
	protected $messageCodes = [];

	/** @var int */
	protected $dataPosition = 0;

	/** @var Index\FileIndex */
	protected $fileIndex;


	//region Fabric

	/**
	 * Constructs instance by path.
	 *
	 * @param string $path Path to language file.
	 *
	 * @return Translate\File
	 * @throws Main\ArgumentException
	 */
	public static function instantiateByPath($path)
	{
		if (empty($path) || !is_string($path) || (mb_substr($path, -4) !== '.php') || !preg_match("#.+/lang/[a-z]{2}/.+\.php$#", $path))
		{
			throw new Main\ArgumentException("Parameter 'path' has a wrong value");
		}

		$file = (new static($path))
			->setLangId(Translate\IO\Path::extractLangId($path));

		return $file;
	}


	/**
	 * Constructs instance by file index.
	 *
	 * @param Index\FileIndex $fileIndex Language file index.
	 *
	 * @return Translate\File
	 * @throws Main\ArgumentException
	 */
	public static function instantiateByIndex(Index\FileIndex $fileIndex)
	{
		if (!$fileIndex instanceof Index\FileIndex)
		{
			throw new Main\ArgumentException();
		}

		$file = (new static($fileIndex->getFullPath()))
			->setLangId($fileIndex->getLangId());

		return $file;
	}


	/**
	 * Constructs instance by io file.
	 *
	 * @param Main\IO\File $fileIn Language file.
	 *
	 * @return Translate\File
	 * @throws Main\ArgumentException
	 */
	public static function instantiateByIoFile(Main\IO\File $fileIn)
	{
		if (!$fileIn instanceof Main\IO\File || $fileIn->getExtension() !== 'php')
		{
			throw new Main\ArgumentException();
		}

		$file = (new static($fileIn->getPath()))
			->setLangId(Translate\IO\Path::extractLangId($fileIn->getPath()));

		return $file;
	}

	//endregion

	//region Language & Encoding

	/**
	 * Returns language code of the file. If it is empty tries to detect it.
	 * @return string
	 */
	public function getLangId()
	{
		if (empty($this->languageId))
		{
			$this->languageId = Translate\IO\Path::extractLangId($this->getPath());
		}
		return $this->languageId;
	}

	/**
	 * Sets language code of the file.
	 *
	 * @param string $languageId Lang code.
	 *
	 * @return self
	 */
	public function setLangId($languageId)
	{
		$this->languageId = $languageId;

		return $this;
	}

	/**
	 * Returns source encoding of the file.
	 * @return bool
	 */
	public function getSourceEncoding()
	{
		static $encodingCache = array();

		if (empty($this->sourceEncoding))
		{
			$language = $this->getLangId();
			if (isset($encodingCache[$language]))
			{
				$this->sourceEncoding = $encodingCache[$language];
			}
			else
			{
				$this->sourceEncoding = Main\Localization\Translation::getSourceEncoding($language);
				$encodingCache[$language] = $this->sourceEncoding;
			}
		}

		return $this->sourceEncoding;
	}

	/**
	 * Sets source encoding of the file.
	 *
	 * @param string $encoding Encoding code.
	 *
	 * @return self
	 */
	public function setSourceEncoding($encoding)
	{
		$this->sourceEncoding = $encoding;

		return $this;
	}

	/**
	 * Returns operating encoding.
	 * @return bool
	 */
	public function getOperatingEncoding()
	{
		if (empty($this->operatingEncoding))
		{
			$this->operatingEncoding = Main\Localization\Translation::getCurrentEncoding();
		}

		return $this->operatingEncoding;
	}

	/**
	 * Sets operating encoding.
	 *
	 * @param string $encoding Encoding code.
	 *
	 * @return self
	 */
	public function setOperatingEncoding($encoding)
	{
		$this->operatingEncoding = $encoding;

		return $this;
	}

	// endregion

	//region Validators

	/**
	 * Lints php code.
	 *
	 * @param string $content Content to validate either content of the current file will be taken.
	 * @param int[] $validTokens Allowed php tokens.
	 * @param string[] $validChars Allowed statement characters.
	 *
	 * @return bool
	 */
	public function lint(
		$content = '',
		$validTokens = array(T_OPEN_TAG, T_CLOSE_TAG, T_WHITESPACE, T_CONSTANT_ENCAPSED_STRING, T_VARIABLE, T_COMMENT, T_DOC_COMMENT),
		$validChars = array('[', ']', ';', '=')
	)
	{
		$isValid = false;

		if (empty($content))
		{
			if ($this->isExists())
			{
				$content = $this->getContents();
			}
		}
		if (empty($content) || !is_string($content))
		{
			$this->addError(new Main\Error("Parse Error: Empty content"));
			return $isValid;
		}

		if (function_exists('token_get_all'))
		{
			$tokens = token_get_all($content);

			$line = $tokens[0][2] || 1;
			if (!is_array($tokens[0]) || $tokens[0][0] !== T_OPEN_TAG)
			{
				$this->addError(new Main\Error("Parse Error: Wrong open tag ".token_name($tokens[0][0])." '{$tokens[0][1]}' at line {$line}"));
			}
			else
			{
				$isValid = true;
				foreach ($tokens as $token)
				{
					if (is_array($token))
					{
						$line = $token[2];
						if (
							!in_array($token[0], $validTokens) ||
							($token[0] === T_VARIABLE && $token[1] != '$MESS')
						)
						{
							$this->addError(new Main\Error("Parse Error: Wrong token ". token_name($token[0]). " '{$token[1]}' at line {$line}"));
							$isValid = false;
							break;
						}
					}
					elseif (is_string($token))
					{
						if (!in_array($token, $validChars))
						{
							$line ++;
							$this->addError(new Main\Error("Parse Error: Expected character '{$token}' at line {$line}"));
							$isValid = false;
							break;
						}
					}
				}
			}
		}

		return $isValid;
	}

	// endregion

	//region Load & Save

	/**
	 * Loads language file for operate.
	 *
	 * @return bool
	 */
	public function load()
	{
		$this->messages = [];
		$this->messageCodes = [];
		$this->messagesCount = 0;

		if (!$this->isExists() || !$this->isFile() || ($this->getExtension() !== 'php'))
		{
			return false;
		}

		// language id
		$langId = $this->getLangId();
		if (empty($langId))
		{
			$this->addError(new Main\Error('Language Id must be filled'));
			return false;
		}

		// encoding
		$targetEncoding = $this->getOperatingEncoding();
		$sourceEncoding = $this->getSourceEncoding();
		$convertEncoding = (mb_strtolower($targetEncoding) != mb_strtolower($sourceEncoding));
		if ($convertEncoding)
		{
			$path = Main\Localization\Translation::convertLangPath($this->getPhysicalPath(), $this->getLangId());

			if (Main\Localization\Translation::getDeveloperRepositoryPath() !== null)
			{
				$convertEncoding = (stripos($path, Main\Localization\Translation::getDeveloperRepositoryPath()) === 0);
			}
			if (!$convertEncoding && Main\Localization\Translation::useTranslationRepository())
			{
				$convertEncoding = (stripos($path, Main\Localization\Translation::getTranslationRepositoryPath()) === 0);
			}
		}

		$MESS = array();
		include $this->getPhysicalPath();

		if (is_array($MESS) && count($MESS) > 0)
		{
			foreach ($MESS as $phraseId => $phrase)
			{
				if ($convertEncoding)
				{
					$phrase = Main\Text\Encoding::convertEncoding($phrase, $sourceEncoding, $targetEncoding);
				}

				$this->messages[$phraseId] = $phrase;
				$this->messageCodes[] = $phraseId;
				$this->messagesCount ++;
			}
		}

		// todo: Handle here developer's comment from file

		return true;
	}

	//endregion

	//region Load & Save

	/**
	 * Save changes or create new file.
	 *
	 * @return boolean
	 */
	public function save()
	{
		// language id
		$langId = $this->getLangId();
		if (empty($langId))
		{
			throw new Main\SystemException("Language Id must be filled");
		}

		// encoding
		$operatingEncoding = $this->getOperatingEncoding();
		$sourceEncoding = $this->getSourceEncoding();
		$convertEncoding = (mb_strtolower($operatingEncoding) != mb_strtolower($sourceEncoding));
		if ($convertEncoding)
		{
			$path = Main\Localization\Translation::convertLangPath($this->getPhysicalPath(), $this->getLangId());

			if (Main\Localization\Translation::getDeveloperRepositoryPath() !== null)
			{
				$convertEncoding = (stripos($path, Main\Localization\Translation::getDeveloperRepositoryPath()) === 0);
			}
			if (!$convertEncoding && Main\Localization\Translation::useTranslationRepository())
			{
				$convertEncoding = (stripos($path, Main\Localization\Translation::getTranslationRepositoryPath()) === 0);
			}
		}

		$content = '';
		foreach ($this->messages as $phraseId => $phrase)
		{
			if (empty($phrase) && $phrase !== '0')
			{
				// remove empty
				continue;
			}
			$phrase = str_replace(["\r\n", "\r"], ["\n", ''], $phrase);
			if ($convertEncoding)
			{
				$phrase = Main\Text\Encoding::convertEncoding($phrase, $operatingEncoding, $sourceEncoding);
			}
			$row = "\$MESS[\"". \EscapePHPString($phraseId). "\"] = \"". \EscapePHPString($phrase). "\"";
			$content .= "\n". $row. ';';
		}
		unset($phraseId, $phrase, $row);

		if ($content <> '')
		{
			if (parent::putContents('<?php'. $content. "\n") === false)
			{
				$filePath = $this->getPath();
				throw new Main\IO\IoException("Couldn't write language file '{$filePath}'");
			}
		}
		else
		{
			// todo: Add module setting that will allow / disallow drop empty lang files.
			if ($this->isExists())
			{
				$this->markWritable();
				$this->delete();
			}
		}

		return true;
	}

	/**
	 * Removes empty parent chain up to "lang".
	 *
	 * @return boolean
	 */
	public function removeEmptyParents()
	{
		// todo: Add module setting that will allow / disallow drop empty lang folders.
		$ret = true;
		$parentFolder = $this->getDirectory();
		while (true)
		{
			if ($parentFolder->isExists() && count($parentFolder->getChildren()) > 0)
			{
				$ret = false;
				break;
			}
			if ($parentFolder->isExists())
			{
				if ($parentFolder->delete() !== true)
				{
					$ret = false;
					break;
				}
			}
			if ($parentFolder->getName() === 'lang')
			{
				break;
			}
			$parentFolder = $parentFolder->getDirectory();
		}

		return $ret;
	}

	/**
	 * Performs backup action.
	 *
	 * @return bool
	 */
	public function backup()
	{
		if (!$this->isExists())
		{
			return true;
		}

		$langId = $this->getLangId();

		$fullPath = $langFile = $this->getPhysicalPath();

		if (Main\Localization\Translation::useTranslationRepository() && in_array($langId, Translate\Config::getTranslationRepositoryLanguages()))
		{
			if (mb_strpos($langFile, Main\Localization\Translation::getTranslationRepositoryPath()) === 0)
			{
				$langFile = str_replace(
					Main\Localization\Translation::getTranslationRepositoryPath(). '/',
					'',
					$langFile
				);
			}
		}
		if (Main\Localization\Translation::getDeveloperRepositoryPath() !== null)
		{
			if (mb_strpos($langFile, Main\Localization\Translation::getDeveloperRepositoryPath()) === 0)
			{
				$langFile = str_replace(
					Main\Localization\Translation::getDeveloperRepositoryPath(). '/',
					'',
					$langFile
				);
			}
		}
		if (mb_strpos($langFile, Main\Application::getDocumentRoot()) === 0)
		{
			$langFile = str_replace(
				Main\Application::getDocumentRoot(). '/',
				'',
				$langFile
			);
		}

		$backupFolder = Translate\Config::getBackupFolder(). '/'. dirname($langFile). '/';
		if (!Translate\IO\Path::checkCreatePath($backupFolder))
		{
			$this->addError(new Main\Error("Couldn't create backup path '{$backupFolder}'"));
			return false;
		}

		$sourceFilename = basename($langFile);
		$prefix = date('YmdHi');
		$endpointBackupFilename = $prefix. '_'. $sourceFilename;
		if (file_exists($backupFolder. $endpointBackupFilename))
		{
			$i = 1;
			while (file_exists($backupFolder. '/'. $endpointBackupFilename))
			{
				$i ++;
				$endpointBackupFilename = $prefix. '_'. $i. '_'. $sourceFilename;
			}
		}

		$isSuccessfull = (bool) @copy($fullPath, $backupFolder. '/'. $endpointBackupFilename);
		@chmod($backupFolder. '/'. $endpointBackupFilename, BX_FILE_PERMISSIONS);

		if (!$isSuccessfull)
		{
			$this->addError(new Main\Error("Couldn't backup file '{$fullPath}'"));
		}

		return $isSuccessfull;
	}

	//endregion


	//region Index


	/**
	 * Returns or creates file index instance.
	 *
	 * @return Index\FileIndex
	 */
	public function getFileIndex()
	{
		if (!$this->fileIndex instanceof Index\FileIndex)
		{
			$indexFileRes = Index\Internals\FileIndexTable::getList([
				'filter' => [
					'=LANG_ID' => $this->getLangId(),
					'=FULL_PATH' => $this->getPath(),
				],
				'limit' => 1
			]);
			$this->fileIndex = $indexFileRes->fetchObject();
		}

		if (!$this->fileIndex instanceof Index\FileIndex)
		{
			$this->fileIndex = (new Index\FileIndex())
				->setFullPath($this->getPath())
				->setLangId($this->getLangId());
		}

		return $this->fileIndex;
	}


	/**
	 * Updates phrase index.
	 *
	 * @return Index\FileIndex
	 */
	public function updatePhraseIndex()
	{
		$this->getFileIndex();
		if ($this->fileIndex->getId() > 0)
		{
			$phraseData = array();
			foreach ($this as $code => $phrase)
			{
				$phraseData[] = array(
					'FILE_ID' => $this->fileIndex->getId(),
					'PATH_ID' => $this->fileIndex->getPathId(),
					'LANG_ID' => $this->getLangId(),
					'CODE' => $code,
					'PHRASE' => $phrase,
				);
			}

			Index\Internals\PhraseIndexTable::purge(new Translate\Filter(['fileId' => $this->fileIndex->getId()]));

			if (count($phraseData) > 0)
			{
				Index\Internals\PhraseIndexTable::bulkAdd($phraseData);
			}

			$this->fileIndex
				->setPhraseCount($this->count())
				->setIndexed(true)
				->setIndexedTime(new Main\Type\DateTime())
				->save();
		}

		return $this->fileIndex;
	}


	/**
	 * Drops phrase index.
	 *
	 * @return bool
	 */
	public function deletePhraseIndex()
	{
		$this->getFileIndex();
		if ($this->fileIndex->getId() > 0)
		{
			Index\Internals\FileIndexTable::purge(new Translate\Filter(['id' => $this->fileIndex->getId()]));
			unset($this->fileIndex);
		}

		return true;
	}

	/**
	 * Returns ORM\Collection object.
	 *
	 * @return Index\PhraseIndexCollection
	 */
	public function getPhraseIndexCollection()
	{
		$phraseIndexCollection = new Index\PhraseIndexCollection();
		foreach ($this->messages as $code => $message)
		{
			$phraseIndexCollection[] = (new Index\PhraseIndex)
				->setLangId($this->getLangId())
				->setCode($code)
				->setPhrase($message)
			;
		}

		return $phraseIndexCollection;
	}

	//endregion

	//region ArrayAccess

	/**
	 * Checks existence of the phrase by its code.
	 *
	 * @param string $code Phrase code.
	 *
	 * @return boolean
	 */
	public function offsetExists($code)
	{
		return isset($this->messages[$code]);
	}

	/**
	 * Returns phrase by its code.
	 *
	 * @param string $code Phrase code.
	 *
	 * @return string|null
	 */
	public function offsetGet($code)
	{
		if (isset($this->messages[$code]))
		{
			return $this->messages[$code];
		}

		return null;
	}

	/**
	 * Offset to set
	 *
	 * @param string $code Phrase code.
	 * @param string $phrase Phrase.
	 *
	 * @return void
	 */
	public function offsetSet($code, $phrase)
	{
		if (!isset($this->messages[$code]))
		{
			if ($this->messagesCount === null)
			{
				$this->messagesCount = 1;
			}
			else
			{
				$this->messagesCount ++;
			}
			$this->messageCodes[] = $code;
		}
		$this->messages[$code] = $phrase;
	}

	/**
	 * Unset phrase by code.
	 *
	 * @param string $code Phrase code.
	 *
	 * @return void
	 */
	public function offsetUnset($code)
	{
		if (isset($this->messages[$code]))
		{
			unset($this->messages[$code]);
			$this->messagesCount --;
			if (($i = array_search($code, $this->messageCodes)) !== false)
			{
				unset($this->messageCodes[$i]);
			}
		}
	}

	/**
	 * Sorts phrases by key, except russian.
	 *
	 * @return self
	 */
	public function sortPhrases()
	{
		\ksort($this->messages, \SORT_NATURAL);
		$this->rewind();

		return $this;
	}

	/**
	 * Returns all phrases from the language file with theirs codes.
	 * @return array
	 */
	public function getPhrases()
	{
		return $this->messages;
	}

	/**
	 * Returns all phrase codes from the language file.
	 * @return string[]
	 */
	public function getCodes()
	{
		return is_array($this->messages) ? array_keys($this->messages) : [];
	}

	//endregion

	//region Iterator

	/**
	 * Return the current phrase element.
	 *
	 * @return string|null
	 */
	public function current()
	{
		$code = $this->messageCodes[$this->dataPosition];

		if (!isset($this->messages[$code]) || !is_string($this->messages[$code]) || (empty($this->messages[$code]) && $this->messages[$code] !== '0'))
		{
			return null;
		}

		return $this->messages[$code];
	}

	/**
	 * Move forward to next phrase element.
	 *
	 * @return void
	 */
	public function next()
	{
		++ $this->dataPosition;
	}

	/**
	 * Return the key of the current phrase element.
	 *
	 * @return int|null
	 */
	public function key()
	{
		return $this->messageCodes[$this->dataPosition] ?: null;
	}

	/**
	 * Checks if current position is valid.
	 *
	 * @return boolean
	 */
	public function valid()
	{
		return isset($this->messageCodes[$this->dataPosition], $this->messages[$this->messageCodes[$this->dataPosition]]);
	}

	/**
	 * Rewind the Iterator to the first element.
	 *
	 * @return void
	 */
	public function rewind()
	{
		$this->dataPosition = 0;
		$this->messageCodes = array_keys($this->messages);
	}

	//endregion

	//region Countable

	/**
	 * Returns amount phrases in the language file.
	 *
	 * @param bool $allowDirectFileAccess Allow include file to count phrases.
	 *
	 * @return int
	 */
	public function count($allowDirectFileAccess = false)
	{
		if ($this->messagesCount === null)
		{
			if ($this->messages !== null && count($this->messages) > 0)
			{
				$this->messagesCount = count($this->messages);
			}
			elseif ($allowDirectFileAccess)
			{
				$MESS = array();
				include $this->getPhysicalPath();

				if (is_array($MESS) && count($MESS) > 0)
				{
					$this->messagesCount = count($MESS);
				}
			}
		}

		return $this->messagesCount ?: 0;
	}

	//endregion


	/**
	 * Returns string fiile content.
	 *
	 * @return string|bool
	 */
	public function getContents()
	{
		$data = parent::getContents();

		if (is_string($data))
		{
			// encoding
			$targetEncoding = $this->getOperatingEncoding();
			$sourceEncoding = $this->getSourceEncoding();
			if ($targetEncoding != $sourceEncoding)
			{
				$data = Main\Text\Encoding::convertEncoding($data, $sourceEncoding, $targetEncoding);
			}
		}

		return $data;
	}

	/**
	 * Puts data sting into file.
	 *
	 * @param string $data Data to save.
	 * @param int $flags Flag to operate previous content @see Main\IO\File::REWRITE | Main\IO\File::APPEND.
	 *
	 * @return bool|int
	 * @throws Main\IO\FileNotFoundException
	 */
	public function putContents($data, $flags = self::REWRITE)
	{
		// encoding
		$operatingEncoding = $this->getOperatingEncoding();
		$sourceEncoding = $this->getSourceEncoding();
		if ($operatingEncoding != $sourceEncoding)
		{
			$data = Main\Text\Encoding::convertEncoding($data, $operatingEncoding, $sourceEncoding);
		}

		return parent::putContents($data, $flags);
	}


	/**
	 * Compares two files and returns excess amount of phrases.
	 *
	 * @param self $ethalon File to compare.
	 *
	 * @return bool|int
	 */
	public function countExcess(self $ethalon)
	{
		return (int)count(array_diff($this->getCodes(), $ethalon->getCodes()));
	}

	/**
	 * Compares two files and returns deficiency amount of phrases.
	 *
	 * @param self $ethalon File to compare.
	 *
	 * @return bool|int
	 */
	public function countDeficiency(self $ethalon)
	{
		return (int)count(array_diff($ethalon->getCodes(), $this->getCodes()));
	}
}
