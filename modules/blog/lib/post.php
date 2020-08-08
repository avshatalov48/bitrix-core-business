<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage blog
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Blog;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;

Loc::loadMessages(__FILE__);

class PostTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_blog_post';
	}

	public static function getUfId()
	{
		return 'BLOG_POST';
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
			'AUTHOR_ID' => array(
				'data_type' => 'integer'
			),
			'CODE' => array(
				'data_type' => 'string'
			),
			'MICRO' => array(
				'data_type' => 'string',
				'values' => array('N','Y')
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime'
			),
			'DATE_PUBLISH' => array(
				'data_type' => 'datetime'
			),
			'PUBLISH_STATUS' => array(
				'data_type' => 'string',
				'values' => array(BLOG_PUBLISH_STATUS_DRAFT, BLOG_PUBLISH_STATUS_READY, BLOG_PUBLISH_STATUS_PUBLISH)
			),
			'ENABLE_COMMENTS' => array(
				'data_type' => 'string',
				'values' => array('N','Y')
			),
			'NUM_COMMENTS' => array(
				'data_type' => 'integer'
			),
			'NUM_COMMENTS_ALL' => array(
				'data_type' => 'integer'
			),
			'VIEWS' => array(
				'data_type' => 'integer'
			),
			'HAS_SOCNET_ALL' => array(
				'data_type' => 'string',
				'values' => array('N','Y')
			),
			'HAS_TAGS' => array(
				'data_type' => 'string',
				'values' => array('N','Y')
			),
			'HAS_IMAGES' => array(
				'data_type' => 'string',
				'values' => array('N','Y')
			),
			'HAS_PROPS' => array(
				'data_type' => 'string',
				'values' => array('N','Y')
			),
			'HAS_COMMENT_IMAGES' => array(
				'data_type' => 'string',
				'values' => array('N','Y')
			),
			'TITLE' => array(
				'data_type' => 'string',
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),
			'DETAIL_TEXT' => array(
				'data_type' => 'text',
			),
			'CATEGORY_ID' => array(
				'data_type' => 'string',
			),
		);

		return $fieldsMap;
	}

	public static function add(array $data)
	{
		throw new NotImplementedException("Use CBlogPost class.");
	}

	public static function update($primary, array $data)
	{
		throw new NotImplementedException("Use CBlogPost class.");
	}

	public static function delete($primary)
	{
		throw new NotImplementedException("Use CBlogPost class.");
	}
}
