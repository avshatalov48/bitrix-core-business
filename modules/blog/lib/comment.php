<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage blog
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Blog;

use Bitrix\Main\Entity;
use Bitrix\Main\NotImplementedException;

class CommentTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_blog_comment';
	}

	public static function getUfId()
	{
		return 'BLOG_COMMENT';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'BLOG_ID' => array(
				'data_type' => 'integer'
			),
			'POST_ID' => array(
				'data_type' => 'integer'
			),
			'POST' => array(
				'data_type' => '\Bitrix\Blog\Post',
				'reference' => array('=this.POST_ID' => 'ref.ID')
			),
			'PARENT_ID' => array(
				'data_type' => 'integer'
			),
			'AUTHOR_ID' => array(
				'data_type' => 'integer'
			),
			'ICON_ID' => array(
				'data_type' => 'integer'
			),
			'AUTHOR_NAME' => array(
				'data_type' => 'string'
			),
			'AUTHOR_EMAIL' => array(
				'data_type' => 'string'
			),
			'AUTHOR_IP' => array(
				'data_type' => 'string'
			),
			'AUTHOR_IP1' => array(
				'data_type' => 'string'
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime'
			),
			'TITLE' => array(
				'data_type' => 'string'
			),
			'POST_TEXT' => array(
				'data_type' => 'text',
			),
			'PUBLISH_STATUS' => array(
				'data_type' => 'string',
			),
			'HAS_PROPS' => array(
				'data_type' => 'string',
				'values' => array('N','Y')
			),
			'SHARE_DEST' => array(
				'data_type' => 'string',
			),
			'PATH' => array(
				'data_type' => 'string',
			),
		);

		return $fieldsMap;
	}

	public static function add(array $data)
	{
		throw new NotImplementedException("Use CBlogComment class.");
	}

	public static function update($primary, array $data)
	{
		throw new NotImplementedException("Use CBlogComment class.");
	}

	public static function delete($primary)
	{
		throw new NotImplementedException("Use CBlogComment class.");
	}
}
