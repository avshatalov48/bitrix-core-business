<?php
namespace Bitrix\Forum\BadWords;

use Bitrix\Main;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

Loc::loadMessages(__FILE__);

class LetterTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_forum_letter';
	}

	public static function getMap()
	{
		return [
			(new IntegerField("ID", ["primary" => true, "autocomplete" => true])),
			(new IntegerField("DICTIONARY_ID", ["required" => true])),
			(new StringField("LETTER", ["required" => true, "size" => 50])),
			(new StringField("REPLACEMENT", ["size" => 255]))];
	}

	public static function checkFields(Result $result, $primary, array $data)
	{
		parent::checkFields($result, $primary, $data);

		if ($result->isSuccess())
		{
			if (isset($data["LETTER"]) || isset($data["DICTIONARY_ID"]))
			{
				$filter = [
					"DICTIONARY_ID" => isset($data["DICTIONARY_ID"]) ? $data["DICTIONARY_ID"] : null,
					"LETTER" => isset($data["LETTER"]) ? $data["LETTER"] : null
				];

				if ($result instanceof UpdateResult)
				{
					if (
						($filter["DICTIONARY_ID"] === null || $filter["LETTER"] === null) &&
						($letter = self::getById($primary["ID"])->fetch())
					)
					{
						if ($filter["LETTER"] === null)
							$filter["LETTER"] = $letter["LETTER"];
						if ($filter["DICTIONARY_ID"] === null)
							$filter["DICTIONARY_ID"] = $letter["DICTIONARY_ID"];
					}
					$filter["!=ID"] = $primary["ID"];
				}
				if ($res = self::getList(["select" => ["ID"], "filter" => $filter])->fetch())
				{
					$result->addError(new Main\Error(Loc::getMessage("FLT_ALREADY_EXIST"), "doubleLetter"));
				}
			}
		}
	}
}