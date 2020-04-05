<?php
namespace Bitrix\Main\Replica;

use Bitrix\Main\Loader;

if (Loader::includeModule('replica'))
{
	class UrlMetadataHandler extends \Bitrix\Replica\Client\BaseHandler
	{
		protected $tableName = "b_urlpreview_metadata";
		protected $moduleId = "main";
		protected $className = "\\Bitrix\\Main\\UrlPreview\\UrlMetadataTable";
		protected $primary = array(
			"ID" => "auto_increment",
		);
		protected $predicates = array();
		protected $translation = array(
			"IMAGE_ID" => "b_file.ID",
		);
		protected $fields = array(
			"DATE_INSERT" => "datetime",
			"DATE_EXPIRE" => "datetime",
			"TITLE" => "text",
			"DESCRIPTION" => "text",
			"SITE_NAME" => "text",
		);

		/**
		 * Called before log write. You may return false and not log write will take place.
		 *
		 * @param array $record Database record.
		 * @return boolean
		 */
		public function beforeLogInsert(array $record)
		{
			if ($record["TYPE"] === \Bitrix\Main\UrlPreview\UrlMetadataTable::TYPE_DYNAMIC)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	}
}
