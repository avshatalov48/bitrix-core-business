<?php
namespace Bitrix\Forum\BadWords;

use Bitrix\Forum\Forum;
use Bitrix\Main\ArgumentException;
use Bitrix\Main;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\EnumField;
use \Bitrix\Main\ORM\Data\UpdateResult;

Loc::loadMessages(__FILE__);

class FilterTable extends \Bitrix\Main\Entity\DataManager
{
	private static $dataById = [];
	private const PATTERN_CREATE_METHOD_SIMPLE = "WORDS";
	private const PATTERN_CREATE_METHOD_TRANSLATE = "TRNSL";
	private const PATTERN_CREATE_METHOD_NONE = "PTTRN";

	public static function getTableName()
	{
		return 'b_forum_filter';
	}

	public static function getMap()
	{
		return [
			(new IntegerField("ID", ["primary" => true, "autocomplete" => true])),
			(new IntegerField("DICTIONARY_ID", ["required" => true])),
			(new StringField("WORDS", ["size" => 255])),
			(new TextField("PATTERN", ["required" => true])),
			(new EnumField("PATTERN_CREATE", [
				"values" => [
						self::PATTERN_CREATE_METHOD_SIMPLE,
						self::PATTERN_CREATE_METHOD_TRANSLATE,
						self::PATTERN_CREATE_METHOD_NONE
					],
				"default_value" => self::PATTERN_CREATE_METHOD_TRANSLATE,
				"required" => true
			])),
			(new StringField("REPLACEMENT", ["size" => 255, "default_value" => ""])),
			(new TextField("DESCRIPTION")),
			(new BooleanField("USE_IT", ["values" => ["N", "Y"], "default_value" => "Y"])),
		];
	}

	public static function checkFields(Result $result, $primary, array $data)
	{
		parent::checkFields($result, $primary, $data);
		if ($result->isSuccess())
		{
			if (array_key_exists("PATTERN", $data))
			{
				$res = self::checkPattern($data["PATTERN"]);
				if (!$res->isSuccess())
				{
					$result->addErrors($res->getErrors());
				}

			}
		}
		if ($result->isSuccess() && ($result instanceof UpdateResult))
		{
			unset(self::$dataById[$primary["ID"]]);
		}
		return $result;
	}

	protected static function checkPattern(string $pattern)
	{
		$result = new Main\Result();

		if ($pattern == '')
		{
			$result->addError(new Main\Error(Loc::getMessage("FLT_ERR_BAD_PATTERN")." [empty]", "emptyPattern"));
		}
		else if (strpos($pattern, "/") !== 0)
		{
			$result->addError(new Main\Error(Loc::getMessage("FLT_ERR_BAD_DELIMITER"), "badDelimiter"));
		}
		else if (($modificators = substr($pattern, strrpos($pattern, "/"))) && strpos($modificators, "e") !== false)
		{
			$result->addError(new Main\Error(Loc::getMessage("FLT_ERR_BAD_MODIFICATOR"), "badModificator"));
		}
		else
		{
			try
			{
				preg_match($pattern, "test string", $arTest);
			}
			catch (\Throwable $exception)
			{
				$result->addError(
					new Main\Error(
						Loc::getMessage("FLT_ERR_BAD_PATTERN")." ".$exception->getMessage(),
						"badPattern"
					)
				);
			}
		}
		return $result;
	}

	public static function getDataById($id, $ttl = 84600)
	{
		if (!array_key_exists($id, self::$dataById))
		{
			self::$dataById[$id] = self::getList([
				"select" => ["*"],
				"filter" => ["ID" => $id],
				"cache" => [
					"ttl" => $ttl
				]
			])->fetch();
		}
		return self::$dataById[$id];
	}
}

class Filter implements \ArrayAccess
{
	use \Bitrix\Forum\Internals\EntityFabric;
	use \Bitrix\Forum\Internals\EntityBaseMethods;

	/** @var int */
	protected $id;
	/** @var array */
	protected $data;

	public function __construct($id)
	{
		$this->id = $id;
		if ($id <= 0)
		{
			throw new \Bitrix\Main\ArgumentException(__CLASS__ . " empty id.");
		}
		else if (!($data = FilterTable::getDataById($this->id)))
		{
			throw new \Bitrix\Main\ArgumentException(__CLASS__ . " data with id is empty.");
		}
		else
		{
			$this->data = $data;
		}
	}

	public function update(array $fields)
	{
		$result = new Main\Result();

		if (isset($fields["WORDS"]) && $fields["WORDS"] == '')
		{
			$result->addError(new Main\Orm\EntityError(
				Loc::getMessage("FLT_ERR_DICT_PATT_MISSED"),
				"emptyData"
			));
		}
		else if (isset($fields["DICTIONARY_ID"]) && intval($fields["DICTIONARY_ID"]) <= 0)
		{
			$result->addError(new Main\Orm\EntityError(
				Loc::getMessage("FLT_ERR_DICTIONARY_MISSED"),
				"emptyDictionaryId"
			));
		}
		else if (isset($fields["WORDS"]) && $fields["WORDS"] !== $this["WORDS"] ||
			isset($fields["DICTIONARY_ID"]) && $fields["DICTIONARY_ID"] !== $this["DICTIONARY_ID"] ||
			isset($fields["PATTERN_CREATE"]) && $fields["PATTERN_CREATE"] !== $this["PATTERN_CREATE"]
		)
		{
			$fields["WORDS"] = isset($fields["WORDS"]) ? $fields["WORDS"] : $this["WORDS"];
			$fields["DICTIONARY_ID"] = isset($fields["DICTIONARY_ID"]) ? $fields["DICTIONARY_ID"] : $this["DICTIONARY_ID"];
			$fields["PATTERN_CREATE"] = isset($fields["PATTERN_CREATE"]) ? $fields["PATTERN_CREATE"] : $this["PATTERN_CREATE"];
			if (($wordEqual = FilterTable::getList([
					"select" => ["ID"],
					"filter" => [
						"DICTIONARY_ID" => $fields["DICTIONARY_ID"],
						"WORDS" => $fields["WORDS"],
						"!ID" => $this->id
					]
				])->fetch()) && !empty($wordEqual))
			{
				$result->addError(new Main\Orm\EntityError(
					Loc::getMessage("FLT_ALREADY_EXIST"),
					"alreadyExists"
				));
			}
			else if ($fields["PATTERN_CREATE"] === "PTTRN")
			{
				$fields["PATTERN"] = $fields["WORDS"];
			}
			else if ($fields["PATTERN_CREATE"] === "TRNSL")
			{
				$fields["PATTERN"] = Dictionary::getById($fields["DICTIONARY_ID"])->translitAndCreatePattern($fields["WORDS"]);
			}
			else
			{
				$fields["PATTERN_CREATE"] = "WORDS";
				$fields["PATTERN"] = Dictionary::getById($fields["DICTIONARY_ID"])->createPattern($fields["WORDS"]);
			}
		}

		if ($result->isSuccess())
		{
			$result = FilterTable::update($this->id, $fields);
		}
		return $result;
	}

	public function delete()
	{
		return FilterTable::delete($this->id);
	}

	public function generatePattern()
	{
		$fields = [];
		if ($this["PATTERN_CREATE"] === "PTTRN")
		{
			$fields["PATTERN"] = $this["WORDS"];
		}
		else if ($this["PATTERN_CREATE"] === "TRNSL")
		{
			$fields["PATTERN"] = Dictionary::getById($this["DICTIONARY_ID"])->translitAndCreatePattern($this["WORDS"]);
		}
		else
		{
			$fields["PATTERN_CREATE"] = "WORDS";
			$fields["PATTERN"] = Dictionary::getById($this["DICTIONARY_ID"])->createPattern($this["WORDS"]);
		}
		return FilterTable::update($this->id, $fields);
	}

	public static function add(array $fields)
	{
		$result = new Main\Result();
		if (!isset($fields["WORDS"]) || $fields["WORDS"] == '')
		{
			$result->addError(new Main\Orm\EntityError(
				Loc::getMessage("FLT_ERR_DICT_PATT_MISSED"),
				"emptyData"
			));
		}
		else if (intval($fields["DICTIONARY_ID"]) <= 0)
		{
			$result->addError(new Main\Orm\EntityError(
				Loc::getMessage("FLT_ERR_DICTIONARY_MISSED"),
				"emptyDictionaryId"
			));
		}
		else if (($wordEqual = FilterTable::getList([
				"select" => ["ID"],
				"filter" => [
					"DICTIONARY_ID" => $fields["DICTIONARY_ID"],
					"=WORDS" => $fields["WORDS"]
				]
			])->fetch()) && !empty($wordEqual))
		{
			$result->addError(new Main\Orm\EntityError(
				Loc::getMessage("FLT_ALREADY_EXIST"),
				"alreadyExists"
			));
		}
		else if ($fields["PATTERN_CREATE"] === "PTTRN")
		{
			$fields["PATTERN"] = $fields["WORDS"];
		}
		else if ($fields["PATTERN_CREATE"] === "TRNSL")
		{
			$fields["PATTERN"] = Dictionary::getById($fields["DICTIONARY_ID"])->translitAndCreatePattern($fields["WORDS"]);
		}
		else
		{
			$fields["PATTERN_CREATE"] = "WORDS";
			$fields["PATTERN"] = Dictionary::getById($fields["DICTIONARY_ID"])->createPattern($fields["WORDS"]);
		}

		if ($result->isSuccess())
		{
			$result = FilterTable::add($fields);
		}
		return $result;
	}
}