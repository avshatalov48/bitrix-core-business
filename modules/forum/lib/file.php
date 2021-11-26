<?php
namespace Bitrix\Forum;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\EnumField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\Validators\DateValidator;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Vote\ChannelTable;
use Bitrix\Vote\QuestionTable;
use Bitrix\Vote\Vote\Anonymity;
use Bitrix\Vote\Vote\EventLimits;
use Bitrix\Vote\Vote\Option;

/**
 * Class MessageTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FORUM_ID int
 * <li> TOPIC_ID int
 * <li> MESSAGE_ID int
 * <li> FILE_ID int mandatory
 * <li> USER_ID int
 * <li> TIMESTAMP_X datetime mandatory
 * <li> HITS int
 * </ul>
 *
 * @package Bitrix\Forum
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_File_Query query()
 * @method static EO_File_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_File_Result getById($id)
 * @method static EO_File_Result getList(array $parameters = array())
 * @method static EO_File_Entity getEntity()
 * @method static \Bitrix\Forum\EO_File createObject($setDefaultValues = true)
 * @method static \Bitrix\Forum\EO_File_Collection createCollection()
 * @method static \Bitrix\Forum\EO_File wakeUpObject($row)
 * @method static \Bitrix\Forum\EO_File_Collection wakeUpCollection($rows)
 */
class FileTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_forum_file';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
//			(new IntegerField("ID", ["primary" => true, "autocomplete" => true])),
			(new IntegerField("FORUM_ID", ["required" => true])),
			(new IntegerField("TOPIC_ID")),
			(new IntegerField("MESSAGE_ID")),
			(new IntegerField("FILE_ID", ["primary" => true])),
			(new IntegerField("USER_ID")),
			(new Reference("USER", \Bitrix\Main\UserTable::class, Join::on("this.USER_ID", "ref.ID"))),
			(new DatetimeField("TIMESTAMP_X", ["default_value" => function(){return new DateTime();}])),
			(new IntegerField("HITS")),
			(new Reference("FORUM", ForumTable::class, Join::on("this.FORUM_ID", "ref.ID"))),
			(new Reference("FILE", Main\FileTable::class, Join::on("this.FILE_ID", "ref.ID")))
		];
	}

	public static function deleteBatch(array $filter)
	{
		$tableName = static::getTableName();
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();

		$where = [];
		foreach ($filter as $key => $value)
		{
			$where[] = $helper->prepareAssignment($tableName, $key, $value);
		}
		$where = implode(' AND ', $where);

		if($where)
		{
			$quotedTableName = $helper->quote($tableName);
			$connection->queryExecute("DELETE FROM {$quotedTableName} WHERE {$where}");
		}
	}
}
class File
{
	public static function checkFiles(Forum $forum, &$files, $params = ["TOPIC_ID" => 0, "MESSAGE_ID" => 0, "USER_ID" => 0])
	{
		$result = new \Bitrix\Main\Result();

		if (empty($files))
		{
			return $result;
		}

		if (array_key_exists("name", $files))
		{
			$files = array($files);
		}

		$filesize = intval(\Bitrix\Main\Config\Option::get("forum", "file_max_size", 5242880));
		$existingFiles = [];
		foreach ($files as $key => $file)
		{
			if ($file["FILE_ID"] > 0)
			{
				$files[$key]["old_file"] = $file["FILE_ID"];
				$existingFiles[] = $file["FILE_ID"];
				continue;
			}
			if ($file["name"] == '')
			{
				unset($files[$key]);
				continue;
			}

			// Y - Image files		F - Files of specified type		A - All files
			if ($forum["ALLOW_UPLOAD"] == "Y")
			{
				$res = \CFile::CheckImageFile($file, $filesize, 0, 0);
			}
			elseif ($forum["ALLOW_UPLOAD"] == "F")
			{
				$res = \CFile::CheckFile($file, $filesize, false, $forum["ALLOW_UPLOAD_EXT"]);
			}
			elseif ($forum["ALLOW_UPLOAD"] == "A")
			{
				$res = \CFile::CheckFile($file, $filesize, false, false);
			}
			else
			{
				$res = "Uploading is forbidden";
			}
			if ($res <> '')
			{
				$result->addError(new Main\Error($res));
			}
		}

		if (!empty($existingFiles))
		{
			$dbRes = FileTable::getList([
				"select" => ["FILE_ID"],
				"filter" => [
					"FORUM_ID" => $params["FORUM_ID"] ?: $forum->getId(),
					"TOPIC_ID" => $params["TOPIC_ID"],
					"MESSAGE_ID" => $params["MESSAGE_ID"],
					"FILE_ID" => $existingFiles
				] + ($params["MESSAGE_ID"] > 0 ? [] : ["USER_ID" => $params["USER_ID"]]),
				"order" => [
					"FILE_ID" => "ASC"
				]
			]);
			while ($res = $dbRes->fetch())
			{
				if (in_array($res["FILE_ID"], $existingFiles))
				{
					$existingFiles = array_diff($existingFiles, [$res["FILE_ID"]]);
				}
			}
			if (!empty($existingFiles))
			{
				$result->addError(new Main\Error("The file is occupied."));
			}
		}
		return $result;
	}

	public static function saveFiles(&$files, $params, $uploadDir = "forum/upload")
	{
		$filesToUpdate = [];
		$filesToAdd = [];
		$filesToDel = [];
		$result = new \Bitrix\Main\Result();
		foreach ($files as $key => $file)
		{
			$file["MODULE_ID"] = "forum";

			if (array_key_exists("del", $file))
			{
				$id = $file["old_file"] ?: $file["FILE_ID"];
				\CFile::Delete($id);
				$filesToDel[$id] = $file + ["key" => $key];
			}
			if ($file["FILE_ID"] > 0)
			{
				$filesToUpdate[$file["FILE_ID"]] = $file + ["key" => $key];
			}
			else
			{
				$id = \CFile::SaveFile($file, $uploadDir);
				if ($id > 0)
				{
					$files[$key]["FILE_ID"] = $id;
					$filesToAdd[$id] = $file + ["key" => $key];
				}
				else
				{
					$result->addError(new Main\Error("The file is not saved."));
				}
			}
		}
		foreach ($filesToDel as $id => $file)
		{
			FileTable::delete($id);
			unset($files[$file["key"]]);
		}
		if (!empty($filesToUpdate))
		{
			$row = [
				"FORUM_ID" => $params["FORUM_ID"],
				"TOPIC_ID" => $params["TOPIC_ID"],
				"MESSAGE_ID" => $params["MESSAGE_ID"],
				"USER_ID" => $params["USER_ID"]
			];
			FileTable::updateMulti(array_keys($filesToUpdate), $row);
			foreach ($filesToUpdate as $id => $file)
			{
				$files[$file["key"]] = array_merge($files[$file["key"]], $row);
			}
		}
		if (!empty($filesToAdd))
		{
			$rows = [];
			foreach ($filesToAdd as $id => $file)
			{
				$row = [
					"FILE_ID" => $id,
					"FORUM_ID" => $params["FORUM_ID"],
					"TOPIC_ID" => $params["TOPIC_ID"],
					"MESSAGE_ID" => $params["MESSAGE_ID"],
					"USER_ID" => $params["USER_ID"]
				];
				$files[$file["key"]] = array_merge($files[$file["key"]], $row);
				$rows[] = $row;
			}
			FileTable::addMulti($rows);
		}
		$result->setData(array_keys($filesToUpdate) + array_keys($filesToAdd));
		return $result;
	}
}