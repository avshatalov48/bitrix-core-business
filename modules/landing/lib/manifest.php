<?php
namespace Bitrix\Landing;

class Manifest extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'ManifestTable';

	/**
	 * Add new record.
	 * @param array $fields Params for add.
	 * @return \Bitrix\Main\Result|null
	 */
	public static function add($fields)
	{
		if (!isset($fields['CONTENT']))
		{
			$fields['CONTENT'] = '';
		}
		if (!isset($fields['MANIFEST']))
		{
			$fields['MANIFEST'] = [];
		}
		$fields['CONTENT'] = trim($fields['CONTENT']);

		if (isset($fields['CODE']))
		{
			$res = self::getList(
				array(
					'select' => array(
						'ID', 'CONTENT', 'MANIFEST'
					),
					'filter' => array(
						'=CODE' => $fields['CODE']
					)
				)
			);
			if ($row = $res->fetch())
			{
				if (
					md5($row['CONTENT']) !=
					md5($fields['CONTENT'])
					||
					md5(serialize($row['MANIFEST'])) !=
					md5(serialize($fields['MANIFEST']))
				)
				{
					return parent::update($row['ID'], $fields);
				}
				else
				{
					return null;
				}

			}
		}

		return parent::add($fields);
	}

	/**
	 * Get manifest of block by code.
	 * @param string $code Block code.
	 * @param bool $full Full row, not only manifest.
	 * @return array
	 */
	public static function getByCode($code, $full = false)
	{
		static $manifests = array();

		if (!isset($manifests[$code]))
		{
			$res = self::getList(array(
				'select' => array(
					'MANIFEST', 'CONTENT'
				),
				'filter' => array(
					'=CODE' => trim($code)
				)
 			));
			if ($row = $res->fetch())
			{
				$manifests[$code] = $row;
			}
			else
			{
				$manifests[$code] = array();
			}
		}

		return $full ? $manifests[$code] : $manifests[$code]['MANIFEST'];
	}
}