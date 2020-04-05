<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use \Bitrix\Landing\Manager;
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
				'title' => Loc::getMessage('LANDING_HOOK_FI_PICTURE')
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
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		return $this->fields['PICTURE']->getValue() > 0;
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		$picture = intval($this->fields['PICTURE']->getValue());

		if ($picture > 0)
		{
			$icons = '';
			// first simple favicons
			$sizes = array('16x16', '32x32', '96x96');
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
				$ext = array_pop(explode('.', $file['src']));
				$icons .= '<link rel="icon" type="image/' . $ext .
						  '" href="' . $file['src'] . '" sizes="' . $size . '">';
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
						  ' href="' . $file['src'] . '" sizes="' . $size . '">';
			}
			if ($icons)
			{
				Manager::setPageView(
					'BeforeHeadClose',
					$icons
				);
			}
		}
	}
}