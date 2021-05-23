<?php

namespace Bitrix\Translate;

/**
 * Filter.
 *
 * @property string|string[] $langId Language code.
 * @property int $pathId Path index Id.
 * @property int $nextPathId Path index Id.
 * @property int $nextLangPathId Lang path Id.
 * @property int $fileId File Id.
 * @property int $nextFileId File Id.
 * @property string $path File stricture path.
 * @property int $tabId Storage Id.
 *
 */
class Filter implements \Iterator, \Countable, \Serializable, \ArrayAccess
{
	const STORAGE_NAME = 'TRANSLATE_FILTER';
	const STORAGE_TAB_CNT = 'TRANSLATE_FILTER_TAB';

	/** @var array */
	private $params = array();

	/** @var array */
	private $iterateCodes = array();

	/** @var int */
	private $iteratePosition;


	/**
	 * Constructs filter object.
	 *
	 * @param array|int $param Init params.
	 */
	public function __construct($param = null)
	{
		if (is_array($param))
		{
			$this->params = $param;
		}
		elseif (is_int($param) && (int)$param > 0)
		{
			$this->restore((int)$param);
		}
	}

	//region getter/setter

	/**
	 * Checks existence of the parameter by its code.
	 *
	 * @param string $code Parameter code.
	 *
	 * @return boolean
	 */
	public function __isset($code)
	{
		return isset($this->params[$code]);
	}

	/**
	 * Returns parameter by its code.
	 *
	 * @param string $code Parameter code.
	 *
	 * @return string|mixed|null
	 */
	public function __get($code)
	{
		return $this->params[$code] ?: null;
	}

	/**
	 * Add parameter to collection.
	 *
	 * @param string $code Parameter code.
	 * @param string|mixed $value Parameter value.
	 *
	 * @return void
	 */
	public function __set($code, $value)
	{
		$this->params[$code] = $value;
		$this->iterateCodes[] = $code;
	}

	/**
	 * Unset parameter by its code.
	 *
	 * @param string $code Parameter code.
	 *
	 * @return void
	 */
	public function __unset($code)
	{
		if (isset($this->params[$code]))
		{
			unset($this->params[$code]);
			$this->iterateCodes = array_keys($this->params);
		}
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
		$code = $this->iterateCodes[$this->iteratePosition];

		return $this->params[$code] ?: null;
	}

	/**
	 * Move forward to next phrase element.
	 *
	 * @return void
	 */
	public function next()
	{
		++ $this->iteratePosition;
	}

	/**
	 * Return the key of the current phrase element.
	 *
	 * @return int|null
	 */
	public function key()
	{
		return $this->iterateCodes[$this->iteratePosition] ?: null;
	}

	/**
	 * Checks if current position is valid.
	 *
	 * @return boolean
	 */
	public function valid()
	{
		return isset($this->iterateCodes[$this->iteratePosition], $this->params[$this->iterateCodes[$this->iteratePosition]]);
	}

	/**
	 * Rewind the Iterator to the first element.
	 *
	 * @return void
	 */
	public function rewind()
	{
		$this->iteratePosition = 0;
		$this->iterateCodes = array_keys($this->params);
	}

	//endregion

	// region Serializable

	/**
	 * String representation of object.
	 * @return string
	 */
	public function serialize()
	{
		return serialize($this->params);
	}

	/**
	 * Constructs the object from a string representation.
	 * @param string $data Data to deserialize.
	 */
	public function unserialize($data)
	{
		if (!empty($data))
		{
			$deserialized = unserialize($data, ['allowed_classes' => false]);
			if (is_array($deserialized))
			{
				$this->params = $deserialized;
			}
		}
	}

	//endregion

	// region Storage

	/**
	 * Returns storage tab Id.
	 * @param bool $increment Generate new id.
	 * @return int
	 */
	public static function getTabId($increment = true)
	{
		$tabId = 0;
		if (isset($_SESSION[self::STORAGE_TAB_CNT]))
		{
			$tabId = $_SESSION[self::STORAGE_TAB_CNT];
		}
		if ($increment)
		{
			$tabId ++;
			$_SESSION[self::STORAGE_TAB_CNT] = $tabId;
		}

		return $tabId;
	}


	/**
	 * Stories the object into storage.
	 * @return void
	 */
	public function store()
	{
		if (!isset($_SESSION[self::STORAGE_NAME]))
		{
			$_SESSION[self::STORAGE_NAME] = array();
		}
		if (!isset($this->tabId))
		{
			$this->tabId = self::getTabId();
		}

		$_SESSION[self::STORAGE_NAME][$this->tabId] = $this->serialize();
	}

	/**
	 * Reconstructs the object from storage.
	 * @param int $id In of the saved date in storage.
	 */
	public function restore($id)
	{
		if (isset($_SESSION[self::STORAGE_NAME], $_SESSION[self::STORAGE_NAME][(int)$id]))
		{
			$this->unserialize($_SESSION[self::STORAGE_NAME][(int)$id]);
		}
		$this->tabId = (int)$id;
	}

	// endregion

	//region ArrayAccess

	/**
	 * Checks existence of the param by its code.
	 *
	 * @param string $code Phrase code.
	 *
	 * @return boolean
	 */
	public function offsetExists($code)
	{
		return isset($this->params[$code]);
	}

	/**
	 * Returns param by its code.
	 *
	 * @param string $code Param code.
	 *
	 * @return mixed|null
	 */
	public function offsetGet($code)
	{
		if (isset($this->params[$code]))
		{
			return $this->params[$code];
		}

		return null;
	}

	/**
	 * Offset to set.
	 *
	 * @param string $code Param code.
	 * @param mixed $param Param value.
	 *
	 * @return void
	 */
	public function offsetSet($code, $param)
	{
		$this->params[$code] = $param;
	}

	/**
	 * Unset param value by code.
	 *
	 * @param string $code Param code.
	 *
	 * @return void
	 */
	public function offsetUnset($code)
	{
		if (isset($this->params[$code]))
		{
			unset($this->params[$code]);
		}
	}

	// endregion

	//region Countable

	/**
	 * Returns amount params in the filter now.
	 *
	 * @return int
	 */
	public function count()
	{
		return is_array($this->params) ? count($this->params) : 0;
	}

	//endregion
}
