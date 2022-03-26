<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Hook;
use \Bitrix\Landing\Internals\HookDataTable;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Field;
use \Bitrix\Landing\PublicAction;
use \Bitrix\Landing\Landing\Seo;
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
				'maxlength' => 140,
				'searchable' => true
			)),
			'DESCRIPTION' => new Field\Textarea('DESCRIPTION', array(
				'title' => Loc::getMessage('LANDING_HOOK_METAOG_DESCRIPTION'),
				'placeholder' => Loc::getMessage('LANDING_HOOK_METAOG_DESCRIPTION_PLACEHOLDER'),
				'maxlength' => 300,
				'searchable' => true
			)),
			'IMAGE' => new Field\Hidden('IMAGE', array(
				'title' => Loc::getMessage('LANDING_HOOK_METAOG_PICTURE'),
				'fetch_data_modification' => function($value)
				{
					if (PublicAction::restApplication())
					{
						if ($value > 0)
						{
							$path = File::getFilePath($value);
							if ($path)
							{
								$path = Manager::getUrlFromFile($path);
								return $path;
							}
						}
					}
					return $value;
				}
			))
		);
	}

	/**
	 * Specific method gor get all landing's images.
	 * @param string $entityType Entity type.
	 * @return array
	 */
	public static function getAllImages($entityType = Hook::ENTITY_TYPE_LANDING)
	{
		$images = array();
		$res = HookDataTable::getList(array(
			'select' => array(
				'VALUE', 'ENTITY_ID'
			),
			'filter' => array(
				'=HOOK' => 'METAOG',
				'=CODE' => 'IMAGE',
				'=ENTITY_TYPE' => $entityType,
				'=PUBLIC' => 'N'
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
	 * Exec or not hook in edit mode.
	 * @return boolean
	 */
	public function enabledInEditMode()
	{
		return false;
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		if ($this->issetCustomExec())
		{
			return true;
		}

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
		if ($this->execCustom())
		{
			return;
		}

		$output = '';
		$files = [];
		$tags = [
			'title' => \htmlspecialcharsbx(Seo::processValue('title', $this->fields['TITLE'])),
			'description' => \htmlspecialcharsbx(Seo::processValue('description', $this->fields['DESCRIPTION'])),
			'image' => trim($this->fields['IMAGE']),
			'type' => 'website'
		];
		foreach (['og', 'twitter'] as $rootTag)
		{
			foreach ($tags as $key => $val)
			{
				if ($key == 'image' && intval($val) > 0)
				{
					$val = intval($val);
					if (!array_key_exists($val, $files))
					{
						$files[$val] = File::getFileArray($val);
					}
					$val = $files[$val];
				}
				if ($val)
				{
					if ($key == 'image')
					{
						if (is_array($val))
						{
							$val['SRC'] = Manager::getUrlFromFile($val['SRC']);
							$output .= '<meta property="' . $rootTag . ':image" content="' . str_replace(' ', '%20', \htmlspecialcharsbx($val['SRC'])) . '" />';
							if ($rootTag != 'twitter')
							{
								$output .=
									'<meta property="' . $rootTag . ':image:width" content="' . $val['WIDTH'] . '" />' .
									'<meta property="' . $rootTag . ':image:height" content="' . $val['HEIGHT'] . '" />';
							}
						}
						else
						{
							$output .= '<meta property="' . $rootTag . ':image" content="' . str_replace(' ', '%20', \htmlspecialcharsbx($val)) . '" />';
						}
						if ($rootTag == 'twitter')
						{
							$output .= '<meta name="twitter:card" content="summary_large_image" />';
						}
					}
					else
					{
						$output .= '<meta property="' . $rootTag . ':' . $key . '" content="' . $val . '" />';
					}
				}
			}
		}
		if ($output)
		{
			Manager::setPageView('MetaOG', $output);
		}
	}
}
