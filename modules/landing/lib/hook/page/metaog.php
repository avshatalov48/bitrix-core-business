<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Field;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class MetaOg extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'TITLE' => new Field\Text('TITLE', array(
				'title' => Loc::getMessage('LANDING_HOOK_METAOG_TITLE'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_METAOG_TITLE_PLACEHOLDER'),
				'maxlength' => 135
			)),
			'DESCRIPTION' => new Field\Textarea('DESCRIPTION', array(
				'title' => Loc::getMessage('LANDING_HOOK_METAOG_DESCRIPTION'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_METAOG_DESCRIPTION_PLACEHOLDER'),
				'maxlength' => 300
			)),
			'IMAGE' => new Field\Hidden('IMAGE', array(
				'title' => Loc::getMessage('LANDING_HOOK_METAOG_PICTURE')
			))
		);
	}

	/**
	 * Specific method gor get all landing's images.
	 * @return array
	 */
	public static function getAllImages()
	{
		$images = array();
		$res = \Bitrix\Landing\Internals\HookDataTable::getList(array(
			'select' => array(
				'VALUE', 'ENTITY_ID'
			),
			'filter' => array(
				'=HOOK' => 'METAOG',
				'=CODE' => 'IMAGE',
				'=ENTITY_TYPE' => \Bitrix\Landing\Hook::ENTITY_TYPE_LANDING
			)
		));
		while ($row = $res->fetch())
		{
			$images[$row['ENTITY_ID']] = $row['VALUE'];
		}

		return $images;
	}

	/**
	 * Title of Hook, if you want.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('LANDING_HOOK_METAOG_NAME');
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		return
				trim($this->fields['TITLE']) != '' ||
				trim($this->fields['DESCRIPTION']) != '' ||
				trim($this->fields['IMAGE']) != '';
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		$output = '';
		$og = array(
			'title' => \htmlspecialcharsbx(trim($this->fields['TITLE'])),
			'description' => \htmlspecialcharsbx(trim($this->fields['DESCRIPTION'])),
			'image' => trim($this->fields['IMAGE'])
		);
		foreach ($og as $key => $val)
		{
			if ($key == 'image' && intval($val) > 0)
			{
				$val = \Cfile::getFileArray($val);
			}
			if ($val)
			{
				if ($key == 'image')
				{
					if (is_array($val))
					{
						$output .=
							'<meta name="og:image" content="' . str_replace(' ', '%20', \htmlspecialcharsbx($val['SRC'])) . '" />' .
							'<meta name="og:image:width" content="' . $val['WIDTH'] . '" />' .
							'<meta name="og:image:height" content="' . $val['HEIGHT'] . '" />';
					}
					else
					{
						$output .= '<meta name="og:image" content="' . str_replace(' ', '%20', \htmlspecialcharsbx($val)) . '" />';
					}
				}
				else
				{
					$output .= '<meta name="og:' . $key . '" content="' . $val . '" />';
				}
			}
		}
		if ($output)
		{
			Manager::setPageView('MetaOG', $output);
		}
	}
}
