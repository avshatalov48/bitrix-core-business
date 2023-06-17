<?php
namespace Bitrix\Forum\BadWords;

use Bitrix\Main;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

Loc::loadMessages(__FILE__);

/**
 * Class DictionaryTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Dictionary_Query query()
 * @method static EO_Dictionary_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Dictionary_Result getById($id)
 * @method static EO_Dictionary_Result getList(array $parameters = array())
 * @method static EO_Dictionary_Entity getEntity()
 * @method static \Bitrix\Forum\BadWords\EO_Dictionary createObject($setDefaultValues = true)
 * @method static \Bitrix\Forum\BadWords\EO_Dictionary_Collection createCollection()
 * @method static \Bitrix\Forum\BadWords\EO_Dictionary wakeUpObject($row)
 * @method static \Bitrix\Forum\BadWords\EO_Dictionary_Collection wakeUpCollection($rows)
 */
class DictionaryTable extends Main\Entity\DataManager
{
	private static $dataById = [];
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_forum_dictionary';
	}

	public static function getMap()
	{
		return [
			(new IntegerField("ID", ["primary" => true, "autocomplete" => true])),
			(new StringField("TITLE", ["required" => true, "size" => 50])),
			(new EnumField("TYPE", ["values" => ["T", "W"], "required" => true])),
		];
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

class Dictionary {

	use \Bitrix\Forum\Internals\EntityFabric;
	use \Bitrix\Forum\Internals\EntityBaseMethods;

	/** @var int */
	protected $id; // means nothing now
	/** @var array */
	protected $data;
	/** @var int */
	protected $translitId;
	/** @var string */
	private const NOT_WORD = "\s.,;:!?\#\-\*\|\[\]\(\)";

	public function __construct($id, $languageId = null)
	{
		$this->id = $id;
		$this->data = DictionaryTable::getById($this->id)->fetch();
		//Todo make a link with translit dictionary
		if ($languageId === null)
		{
			$languageId = LANGUAGE_ID;
		}
		$this->translitId = Main\Config\Option::get("forum", "FILTER_DICT_T", "", $languageId);
	}

	public function delete()
	{
		$result = DictionaryTable::delete($this->id);
		if ($result->isSuccess())
		{
			global $DB;
			if ($this->data["TYPE"] == "T")
				$DB->Query("DELETE FROM b_forum_letter WHERE DICTIONARY_ID=".$this->id);
			else
				$DB->Query("DELETE FROM b_forum_filter WHERE DICTIONARY_ID=".$this->id);
		}
	}

	private function getTranslitDictionary($multiLetter = true) : array
	{
		static $letters = null;
		if (is_null($letters))
		{
			$letters = [
				"singleLetter" => [],
				"multiLetter" => []
			];
			$dbRes = LetterTable::getList([
				"select" => ["*"],
				"filter" => ["DICTIONARY_ID" => $this->translitId],
				"cache" => [
					"ttl" => 84600
				]
			]);

			while ($lett = $dbRes->fetch())
			{
				$space = false;

				$arrRes = array();
				$arrRepl = explode(",", $lett["REPLACEMENT"]);
				// create letters.
				for ($ii = 0; $ii < count($arrRepl); $ii++)
				{
					$arrRepl[$ii] = trim($arrRepl[$ii]);
					if (mb_strlen($lett["LETTER"]) == 1)
					{
						if (mb_strlen($arrRepl[$ii]) == 1)
						{
							$arrRes[$ii] = $arrRepl[$ii]."+";
						}
						else if (mb_strpos($arrRepl[$ii], "(") === 0 && mb_substr($arrRepl[$ii], -1, 1) == ")")
						{
							$arrRes[$ii] = $arrRepl[$ii]."+";
						}
						else if (mb_strpos($arrRepl[$ii], "(") === 0 && mb_substr($arrRepl[$ii], -2, 1) == ")")
						{
							$arrRes[$ii] = $arrRepl[$ii];
						}
						else if (mb_strlen($arrRepl[$ii]) > 1)
						{
							$arrRes[$ii] = "[".$arrRepl[$ii]."]+";
						}
						else
						{
							$space = true;
						}
					}
					else if ($arrRepl[$ii] <> '')
					{
						$arrRes[$ii] = $arrRepl[$ii];
					}
				}

				if (mb_strlen($lett["LETTER"]) == 1)
				{
					if ($space)
					{
						$arrRes[] = "";
					}
					$letters["singleLetter"][$lett["LETTER"]] = "(".implode("|", $arrRes).")";
				}
				else
				{
					$letters["multiLetter"]["/".preg_quote($lett["LETTER"])."/is".BX_UTF_PCRE_MODIFIER] = "(".implode("|", $arrRes).")";
				}
			}
			$letters["singleLetter"]["*"] = "[^".self::NOT_WORD."]*";
			$letters["singleLetter"]["+"] = "[^".self::NOT_WORD."]+";
			$letters["singleLetter"]["?"] = ".?";
		}
		return ($multiLetter === true ? $letters["multiLetter"] : $letters["singleLetter"]);
	}

	public function translitAndCreatePattern(string $word) : string
	{
		//replace big construction
		$letters = $this->getTranslitDictionary(true);
		$word = preg_replace(array_keys($letters), array_values($letters), mb_strtolower(trim($word)));

		//replace single letter construction
		$letters = $this->getTranslitDictionary(false);
		$replace = array_flip(array_keys($letters));

		$length = strlen(count($replace));
		$replace1 = array_map(function ($number) use ($length) {
			$number = str_pad($number, $length, "0", STR_PAD_LEFT);
			return "\017x$number";}, $replace);

		$word = str_replace(array_keys($replace), array_values($replace1), $word);
		$word = preg_quote($word);
		$word = str_replace(array_values($replace1), array_values($letters), $word);

		$word = "/(?<=[".self::NOT_WORD."])(".$word.")(?=[".self::NOT_WORD."])/is".BX_UTF_PCRE_MODIFIER;

		return $word;
	}

	public function createPattern(string $word) : string
	{
		$res = "/(?<=[".self::NOT_WORD."])(".preg_quote($word).")(?=[".self::NOT_WORD."])/is".BX_UTF_PCRE_MODIFIER;
		return $res;
	}
}