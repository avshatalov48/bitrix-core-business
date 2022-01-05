<?php
namespace Bitrix\Landing\Hook\Page;

use Bitrix\Landing\Field;
use Bitrix\Landing\File;
use Bitrix\Landing\Manager;
use Bitrix\Landing\PublicAction;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

Loc::loadMessages(__FILE__);

class Background extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return array(
			'USE' => new Field\Checkbox('USE', array(
				'title' => Loc::getMessage('LANDING_HOOK_BG_USE')
			)),
			'PICTURE' => new Field\Hidden('PICTURE', array(
				'title' => Loc::getMessage('LANDING_HOOK_BG_PICTURE'),
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
			)),
			'POSITION' => new Field\Select('POSITION', array(
				'title' => Loc::getMessage('LANDING_HOOK_BG_POSITION'),
				'help' => Loc::getMessage('LANDING_HOOK_BG_POSITION_HELP_2'),
				'options' => array(
					'center' => Loc::getMessage('LANDING_HOOK_BG_POSITION_CENTER_2'),
					'repeat' => Loc::getMessage('LANDING_HOOK_BG_POSITION_REPEAT_2'),
					'center_repeat_y' => Loc::getMessage('LANDING_HOOK_BG_POSITION_CENTER_REPEAT_Y'),
				)
			)),
			'COLOR' => new Field\Text('COLOR', array(
				'title' => Loc::getMessage('LANDING_HOOK_BG_COLOR')
			))
		);
	}

	/**
	 * Title of Hook, if you want.
	 * @return string
	 */
	public function getTitle()
	{
		return Loc::getMessage('LANDING_HOOK_BG_NAME');
	}

	/**
	 * Description of Hook, if you want.
	 * @return string
	 */
	public function getDescription()
	{
		return Loc::getMessage('LANDING_HOOK_BG_DESCRIPTION');
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

		return $this->fields['USE']->getValue() == 'Y';
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

		$picture = \htmlspecialcharsbx(trim($this->fields['PICTURE']->getValue()));
		$color = \htmlspecialcharsbx(trim($this->fields['COLOR']->getValue()));
		$position = trim($this->fields['POSITION']->getValue());

		$this->setBackground($picture, $color, $position);
	}

	/**
	 * Sets background.
	 * @param string|null $picture Picture path or id.
	 * @param string|null $color Color code.
	 * @param string|null $position Position code.
	 * @return void
	 */
	public static function setBackground(?string $picture, ?string $color = null, ?string $position = null): void
	{
		/**
		 * !!!
		 * Also see landing.pub/templates/.default/result_modifier.php
		 * for web form backward compatibility.
		 */

		if ($picture && is_numeric($picture) && (int)$picture > 0)
		{
			$picture = \htmlspecialcharsbx(
				File::getFilePath((int)$picture)
			);
		}

		if ($picture)
		{
			if ($position === 'center')
			{
				Asset::getInstance()->addString(
					'<style type="text/css">
						body {
							background-image: url("' . $picture . '");
							background-attachment: fixed;
							background-size: cover;
							background-position: center;
							background-repeat: no-repeat;
						}
						.bx-ios.bx-touch body {
								background-attachment: scroll;
								background-position: top;
								background-repeat: repeat-y;
								background-size: 100%;
						}
					</style>'
				);
			}
			elseif ($position === 'repeat')
			{
				Asset::getInstance()->addString(
					'<style type="text/css">
						body {
							background-image: url("' . $picture . '");
							background-attachment: fixed;
							background-position: center;
							background-repeat: repeat;
						}
					</style>'
				);
			}
			else
			{
				Asset::getInstance()->addString(
					'<style type="text/css">
						body {
							background-image: url("' . $picture . '");
							background-attachment: scroll;
							background-position: top;
							background-repeat: repeat-y;
							background-size: 100%;
						}
					</style>'
				);
			}
		}

		if ($color)
		{
			Asset::getInstance()->addString(
				'<style type="text/css">
					body {
						background-color: ' . $color . '!important;
					}
				</style>'
			);
		}
	}
}