<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Landing\File;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\PublicAction;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Favicon extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'PICTURE' => new Field\Hidden('PICTURE', array(
				'title' => Loc::getMessage('LANDING_HOOK_FI_PICTURE'),
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
	 * Title of Hook, if you want.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('LANDING_HOOK_FI_PICTURE');
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

		return $this->fields['PICTURE']->getValue() > 0;
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

		$picture = intval($this->fields['PICTURE']->getValue());

		if ($picture > 0)
		{
			$icons = "\n";
			// first simple favicons
			$sizes = ['16x16', '32x32', '96x96'];
			foreach ($sizes as $size)
			{
				list($w, $h) = explode('x', $size);
				$file = \CFile::resizeImageGet(
					\Bitrix\Landing\File::getFileArray($picture),
					array(
						'width' => $w,
						'height' => $h
					),
					BX_RESIZE_IMAGE_EXACT
				);
				$srcExplode = explode('.', $file['src']);
				$ext = array_pop($srcExplode);
				$icons .= '<link rel="icon" type="image/' . $ext .
						  '" href="' . $file['src'] . '" sizes="' . $size . '">' . "\n";
			}
			// apple icons
			$sizes = array('120x120', '180x180', '152x152', '167x167');
			foreach ($sizes as $size)
			{
				list($w, $h) = explode('x', $size);
				$file = \CFile::resizeImageGet(
					\Bitrix\Landing\File::getFileArray($picture),
					array(
						'width' => $w,
						'height' => $h
					),
					BX_RESIZE_IMAGE_EXACT
				);
				$icons .= '<link rel="apple-touch-icon"' .
						  ' href="' . $file['src'] . '" sizes="' . $size . '">' . "\n";
			}
			if ($icons)
			{
				Manager::setPageView(
					'BeforeHeadClose',
					$icons,
					true
				);
			}
		}
	}
}