<?php
namespace Bitrix\Clouds;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
li
 * <li> FILE_PATH string(600) mandatory
 * <li> FILE_SIZE int optional
 * <li> FILE_MTIME datetime optional
 * <li> FILE_HASH string(50) optional
 * </ul>
 *
 * @package Bitrix\Clouds
 **/

class FileHashTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_clouds_file_hash';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'BUCKET_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('FILE_HASH_ENTITY_BUCKET_ID_FIELD'),
			),
			'FILE_PATH' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateFilePath'),
				'title' => Loc::getMessage('FILE_HASH_ENTITY_FILE_PATH_FIELD'),
			),
			'FILE_SIZE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FILE_HASH_ENTITY_FILE_SIZE_FIELD'),
			),
			'FILE_MTIME' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('FILE_HASH_ENTITY_FILE_MTIME_FIELD'),
			),
			'FILE_HASH' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateFileHash'),
				'title' => Loc::getMessage('FILE_HASH_ENTITY_FILE_HASH_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for FILE_PATH field.
	 *
	 * @return array
	 */
	public static function validateFilePath()
	{
		return array(
			new Main\Entity\Validator\Length(null, 600),
		);
	}
	/**
	 * Returns validators for FILE_HASH field.
	 *
	 * @return array
	 */
	public static function validateFileHash()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Stores file hashes to the database.
	 *
	 * @param integer $bucketId Clouds storage bucket identifier.
	 * @param array $files File list as it returned by CCloudStorageBucket::ListFiles.
	 *
	 * @return Main\DB\Result
	 * @see CCloudStorageBucket::ListFiles
	 */
	public static function addList($bucketId, array $files)
	{
		$bucketId = intval($bucketId);
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$values = array();
		foreach ($files["file"] as $i => $file)
		{
			$fileSize = $files["file_size"][$i];
			$fileMtime = \CCloudUtil::gmtTimeToDateTime($files["file_mtime"][$i]);
			$fileHash = $files["file_hash"][$i];
			$values []= "("
				.$bucketId
				.",'".$helper->forSql($file)."'"
				.",".intval($fileSize)
				.",'".$fileMtime->format("Y-m-d h:i:s")."'"
				.",'".$helper->forSql($fileHash)."'"
				.")"
			;
		}
		$sql = "
			INSERT INTO ".static::getTableName()."
			(BUCKET_ID, FILE_PATH, FILE_SIZE, FILE_MTIME, FILE_HASH)
			VALUES
			".implode(",\n", $values)."
			ON DUPLICATE KEY UPDATE FILE_SIZE=values(FILE_SIZE), FILE_MTIME=values(FILE_MTIME), FILE_HASH=values(FILE_HASH)
		";
		return $connection->query($sql);
	}
	/**
	 * Returns last stored key for given bucket.
	 *
	 * @param integer $bucketId Clouds storage bucket identifier.
	 *
	 * @return string
	 */
	public static function getLastKey($bucketId)
	{
		$bucketId = intval($bucketId);
		$connection = Main\Application::getConnection();
		$last = $connection->query("SELECT max(FILE_PATH) LAST_KEY from ".static::getTableName()." WHERE BUCKET_ID=".$bucketId)->fetch();
		return $last && $last["LAST_KEY"]? $last["LAST_KEY"]: "";
	}
	/**
	 * Clears all stored file hashes for the bucket.
	 *
	 * @param integer $bucketId Clouds storage bucket identifier.
	 *
	 * @return Main\DB\Result
	 */
	public static function deleteAll($bucketId)
	{
		$bucketId = intval($bucketId);
		$connection = Main\Application::getConnection();
		return $connection->query("DELETE from ".static::getTableName()." WHERE BUCKET_ID=".$bucketId);
	}
}
