<?php
namespace Bitrix\Landing\Hook\Page;

use \Bitrix\Landing\Field;
use Bitrix\Main\Localization\Loc;

class Fonts extends \Bitrix\Landing\Hook\Page
{
	/**
	 * Default font for backward compatibility.
	 */
	public const DEFAULT_FONTS = [
		'g-font-open-sans' => [
			'name' => 'Open Sans',
			'family' => '"Open Sans", Helvetica, Arial, sans-serif',
			'url' => 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@100;200;300;400;500;600;700;800;900&subset=cyrillic',
		],
		'g-font-roboto' => [
			'name' => 'Roboto',
			'family' => '"Roboto", Arial, sans-serif',
			'url' => 'https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;600;700;800;900&subset=cyrillic,cyrillic-ext,latin-ext',
		],
		'g-font-roboto-slab' => [
			'name' => 'Roboto Slab',
			'family' => '"Roboto Slab", Helvetica, Arial, sans-serif',
			'url' => 'https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100;200;300;400;500;600;700;800;900&subset=cyrillic,cyrillic-ext,latin-ext',
		],
		'g-font-montserrat' => [
			'name' => 'Montserrat',
			'family' => '"Montserrat", Helvetica, Arial, sans-serif',
			'url' => 'https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&subset=cyrillic',
		],
		'g-font-alegreya-sans' => [
			'name' => 'Alegreya Sans',
			'family' => '"Alegreya Sans", sans-serif',
			'url' => 'https://fonts.googleapis.com/css2?family=Alegreya+Sans:wght@100;200;300;400;500;600;700;800;900&subset=cyrillic-ext,latin-ext',
		],
		'g-font-cormorant-infant' => [
			'name' => 'Cormorant Infant',
			'family' => '"Cormorant Infant", serif',
			'url' => 'https://fonts.googleapis.com/css2?family=Cormorant+Infant:wght@100;200;300;400;500;600;700;800;900&subset=cyrillic-ext,latin-ext',
		],
		'g-font-pt-sans-caption' => [
			'name' => 'PT Sans Caption',
			'family' => '"PT Sans Caption", sans-serif',
			'url' => 'https://fonts.googleapis.com/css2?family=PT+Sans+Caption:wght@100;200;300;400;500;600;700;800;900&subset=cyrillic-ext,latin-ext',
		],
		'g-font-pt-sans-narrow' => [
			'name' => 'PT Sans Narrow',
			'family' => '"PT Sans Narrow", sans-serif',
			'url' => 'https://fonts.googleapis.com/css2?family=PT+Sans+Narrow:wght@100;200;300;400;500;600;700;800;900&PT+Sans:wght@100;200;300;400;500;600;700;800;900&subset=cyrillic-ext,latin-ext',
		],
		'g-font-pt-sans' => [
			'name' => 'PT Sans',
			'family' => '"PT Sans", sans-serif',
			'url' => 'https://fonts.googleapis.com/css2?family=PT+Sans:wght@100;200;300;400;500;600;700;800;900&subset=cyrillic-ext,latin-ext',
		],
		'g-font-lobster' => [
			'name' => 'Lobster',
			'family' => '"Lobster", cursive',
			'url' => 'https://fonts.googleapis.com/css2?family=Lobster:wght@100;200;300;400;500;600;700;800;900&subset=cyrillic-ext,latin-ext',
		],
	];

	/**
	 * Set fonts on the page.
	 * @var array
	 */
	protected static $setFonts = [];

	/**
	 * Map of the field.
	 * @return array
	 */
	protected function getMap()
	{
		return [
			'CODE' => new Field\Textarea(
				'CODE', [
				'title' => Loc::getMessage('LNDNGHOOK_FONTS_FONT_BASE'),
			]),
		];
	}

	/**
	 * Enable or not the hook.
	 * @return boolean
	 */
	public function enabled()
	{
		return true;
	}

	/**
	 * Sets font code as using on the page.
	 * @param string $fontCode Font code.
	 * @return void
	 */
	public static function setFontCode(string $fontCode): void
	{
		if (!array_key_exists($fontCode, self::$setFonts))
		{
			self::$setFonts[$fontCode] = [];
		}
	}

	/**
	 * Outputs default font.
	 * @param string $code Font code.
	 * @return string
	 */
	public static function outputDefaultFont(string $code): string
	{
		if (isset(self::DEFAULT_FONTS[$code]))
		{
			$fontUrl = self::DEFAULT_FONTS[$code]['url'];

			return '<link 
						rel="preload" 
						as="style" 
						onload="this.removeAttribute(\'onload\');this.rel=\'stylesheet\'" 
						data-font="' . $code . '" 
						data-protected="true" 
						href="' . $fontUrl . '">
					<noscript>
						<link
							rel="stylesheet" 
							data-font="' . $code . '" 
							data-protected="true" 
							href="' . $fontUrl . '">
					</noscript>';
		}

		return '';
	}

	/**
	 * Sets fonts data to the page.
	 * @param array $fonts Fonts data ([fontCode => fontStyle]).
	 * @return void
	 */
	protected function outputFonts(array $fonts): void
	{
		$setFonts = [];

		foreach (self::$setFonts as $fontCode => $foo)
		{
			if (isset($fonts[$fontCode]))
			{
				unset(self::$setFonts[$fontCode]);
				$setFonts[] = $fonts[$fontCode];
			}
		}

		// set default fonts
		foreach (self::$setFonts as $fontCode => $foo)
		{
			$setFonts[] = self::outputDefaultFont($fontCode);
		}

		if ($setFonts)
		{
			\Bitrix\Landing\Manager::setPageView(
				'BeforeHeadClose',
				implode('', $setFonts)
			);
		}
	}

	/**
	 * Exec hook.
	 * @return void
	 */
	public function exec()
	{
		if (!self::$setFonts)
		{
			return;
		}
		// @fix for #101643

		$this->fields['CODE'] = str_replace(
			['st yle', 'onl oad', 'li nk'],
			['style', 'onload', 'link'],
			$this->fields['CODE']
		);
		$styleFound = preg_match_all(
			'#(<noscript>.*?<style.*?data-id="([^"]+)"[^>]*>[^<]+</style>)#is',
			$this->fields['CODE'],
			$matches
		);

		$fonts = [];
		if ($styleFound)
		{
			$fonts = array_combine($matches[2], $matches[1]);
		}
		$this->outputFonts($fonts);
	}
}