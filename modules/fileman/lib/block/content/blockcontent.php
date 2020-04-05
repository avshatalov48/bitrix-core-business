<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Fileman\Block\Content;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class BlockContent
{
	CONST TYPE_STYLES = 'STYLES';
	CONST TYPE_BLOCKS = 'BLOCKS';

	protected $list = array();

	public function __construct()
	{

	}

	public function add($type, $place, $value)
	{
		$this->list[] = array(
			'type' => $type,
			'place' => $place,
			'value' => $value,
		);
	}

	public function getStyles()
	{
		return $this->filterListByType(self::TYPE_STYLES);
	}

	public function getBlocks()
	{
		return $this->filterListByType(self::TYPE_BLOCKS);
	}

	protected function filterListByType($type)
	{
		$result = array();
		foreach ($this->list as $item)
		{
			if ($item['type'] != $type)
			{
				continue;
			}

			//$result[$item['code']] = $item['value'];
			$result[] = $item;
		}

		return $result;
	}
}