<?php
namespace Bitrix\Main;

use Bitrix\Main\Entity;

class SiteTemplate
{
	protected $id;

	public function __construct($id)
	{
		$this->id = $id;
	}
}

class SiteTemplateTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_site_template';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'SITE_ID' => array(
				'data_type' => 'string',
			),
			'CONDITION' => array(
				'data_type' => 'string'
			),
			'SORT' => array(
				'data_type' => 'integer',
			),
			'TEMPLATE' => array(
				'data_type' => 'string'
			),
			'SITE' => array(
				'data_type' => 'Bitrix\Main\Site',
				'reference' => array('=this.SITE_ID' => 'ref.LID'),
			),
		);
	}
}
