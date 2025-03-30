<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UrlPreview\UrlPreview;
use Bitrix\Main\Web\Uri;
use Bitrix\Socialnetwork\Collab\Url\UrlManager;
use Bitrix\Socialnetwork\Integration\Im\Chat\Workgroup;
use Bitrix\Socialnetwork\Item\Workgroup\Type;
use Bitrix\Socialnetwork\Provider\GroupProvider;

class CTextParser
{
	public $type = 'html';
	public $serverName = '';
	public $preg;

	public $imageWidth = 800;
	public $imageHeight = 800;
	public $maxStringLen = 0;
	public $maxAnchorLength = 40;

	//https://www.w3.org/TR/CSS2/fonts.html#propdef-font-size
	public $arFontSize = [
		1 => 'xx-small',
		2 => 'small',
		3 => 'medium',
		4 => 'large',
		5 => 'x-large',
		6 => 'xx-large',
	];
	public $allow = [
		'HTML' => 'N',
		'ANCHOR' => 'Y',
		'BIU' => 'Y',
		'IMG' => 'Y',
		'QUOTE' => 'Y',
		'CODE' => 'Y',
		'FONT' => 'Y',
		'LIST' => 'Y',
		'EMOJI' => 'Y',
		'SMILES' => 'Y',
		'CLEAR_SMILES' => 'N',
		'NL2BR' => 'N',
		'VIDEO' => 'Y',
		'TABLE' => 'Y',
		'CUT_ANCHOR' => 'N',
		'SHORT_ANCHOR' => 'N',
		'TEXT_ANCHOR' => 'Y',
		'ALIGN' => 'Y',
		'USERFIELDS' => 'N',
		'USER' => 'Y',
		'PROJECT' => 'Y',
		'DEPARTMENT' => 'Y',
		'P' => 'Y',
		'TAG' => 'N',
		'SPOILER' => 'Y',
	];
	public $anchorType = 'html';
	public $smiles = null;
	public $smilesGallery = CSmileGallery::GALLERY_DEFAULT;
	public $bMobile = false;
	public $LAZYLOAD = 'N';
	public $parser_nofollow = 'N';
	public $link_target = '_blank';
	public $authorName = '';
	public $pathToUser = '';
	public $pathToUserEntityType = false;
	public $pathToUserEntityId = false;
	public $useTypography = false;
	protected $tagClasses = [
		'p' => 'ui-typography-paragraph',
		'b' => 'ui-typography-text-bold',
		'i' => 'ui-typography-text-italic',
		'u' => 'ui-typography-text-underline',
		's' => 'ui-typography-text-strikethrough',
		'code' => 'ui-typography-code',
		'quote' => 'ui-typography-quote',
		'url' => 'ui-typography-link',
		'image-container' => 'ui-typography-image-container',
		'image' => 'ui-typography-image',
		'ol' => 'ui-typography-ol',
		'ul' => 'ui-typography-ul',
		'li' => 'ui-typography-li',
		'table' => 'ui-typography-table',
		'td' => 'ui-typography-table-cell',
		'tr' => 'ui-typography-table-row',
		'th' => 'ui-typography-table-cell ui-typography-table-cell-header',
		'mention' => 'ui-typography-mention',
		'smiley' => 'ui-typography-smiley',
		'hashtag' => 'ui-typography-hashtag',
	];

	protected $wordSeparator = "\\s.,;:!?\\#\\-\\*\\|\\[\\]\\(\\)\\{\\}";
	protected $smilePatterns = null;
	protected $smileReplaces = null;
	protected static $repoSmiles = [];
	protected $defended_urls = [];
	protected $anchorSchemes = null;
	protected $userField;
	protected $tagPattern = "/([\s]+|^)#([^\s,\.\[\]<>]+)/is";
	protected $pathToSmile = '';
	protected $ajaxPage;

	protected $code_open = 0;
	protected $code_error = 0;
	protected $code_closed = 0;
	protected $quote_open = 0;
	protected $quote_error = 0;
	protected $quote_closed = 0;

	public function __construct()
	{
		global $APPLICATION;

		$this->ajaxPage = $APPLICATION->GetCurPageParam('', ['bxajaxid', 'logout']);
	}

	public function getAnchorSchemes()
	{
		if ($this->anchorSchemes === null)
		{
			static $schemes = null;
			if ($schemes === null)
			{
				$schemes = Option::get('main', '~parser_anchor_schemes', 'http|https|news|ftp|aim|mailto|file|tel|callto|skype|viber');
			}
			$this->anchorSchemes = $schemes;
		}
		return $this->anchorSchemes;
	}

	public function setAnchorSchemes($schemes)
	{
		$this->anchorSchemes = $schemes;
	}

	protected function initSmiles()
	{
		if (!array_key_exists($this->smilesGallery, self::$repoSmiles))
		{
			$smiles = CSmile::getByGalleryId(CSmile::TYPE_SMILE, $this->smilesGallery);
			$arSmiles = [];
			foreach ($smiles as $smile)
			{
				$arTypings = explode(' ', $smile['TYPING']);
				foreach ($arTypings as $typing)
				{
					$arSmiles[] = array_merge($smile, [
						'TYPING' => $typing,
						'IMAGE'  => CSmile::PATH_TO_SMILE . $smile['SET_ID'] . '/' . $smile['IMAGE'],
						'DESCRIPTION' => $smile['NAME'],
						'DESCRIPTION_DECODE' => 'Y',
					]);
				}
			}
			self::$repoSmiles[$this->smilesGallery] = $arSmiles;
		}
		$this->smiles = self::$repoSmiles[$this->smilesGallery];
	}

	protected function initSmilePatterns()
	{
		$this->smilePatterns = [];
		$this->smileReplaces = [];

		$pre = '';
		foreach ($this->smiles as $row)
		{
			if (preg_match("/\\w\$/", $row['TYPING']))
			{
				$pre .= '|' . preg_quote($row['TYPING'], '/');
			}
		}

		foreach ($this->smiles as $row)
		{
			if ($row['TYPING'] != '' && $row['IMAGE'] != '')
			{
				$code = str_replace(["'", "<", ">"], ["\\'", "&lt;", "&gt;"], $row["TYPING"]);
				$patt = preg_quote($code, "/");
				$code = preg_quote(str_replace(["\x5C"], ["&#092;"], $code));
				$image = preg_quote(str_replace("'", "\\'", $row["IMAGE"]));
				$description = preg_quote(htmlspecialcharsbx(str_replace(["\x5C"], ["&#092;"], $row["DESCRIPTION"]), ENT_QUOTES), "/");

				$patternName = 'pattern' . count($this->smilePatterns);

				$this->smilePatterns[] = "/(?<=^|\\>|[" . $this->wordSeparator . "\\&]" . $pre . ")(?P<" . $patternName . ">$patt)(?=$|\\<|[" . $this->wordSeparator . "\\&])/su";

				$this->smileReplaces[$patternName] = [
					'code' => $code,
					'image' => $image,
					'description' => $description,
					'width' => intval($row['IMAGE_WIDTH']),
					'height' => intval($row['IMAGE_HEIGHT']),
					'descriptionDecode' => $row['DESCRIPTION_DECODE'] == 'Y',
					'imageDefinition' => $row['IMAGE_DEFINITION'] ?: CSmile::IMAGE_SD,
				];
			}
		}
		usort($this->smilePatterns, function($a, $b) { return (mb_strlen($a) > mb_strlen($b) ? -1 : 1); });
	}

	// Added $attributes for links
	public function convertText($text, $attributes = [])
	{
		if (!is_string($text) || $text == '')
		{
			return '';
		}

		$attributes = is_array($attributes) ? $attributes : [];

		$text = preg_replace(["#([?&;])PHPSESSID=([0-9a-zA-Z]{32})#i", "/\\x{00A0}/u"], ["\\1PHPSESSID1=", " "], $text);

		$this->defended_urls = [];

		if ($this->serverName == '' && $this->type == 'rss')
		{
			$dbSite = CSite::GetByID(SITE_ID);
			$arSite = $dbSite->Fetch();
			$serverName = $arSite['SERVER_NAME'];
			if ($serverName == '')
			{
				if (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME != '')
				{
					$serverName = SITE_SERVER_NAME;
				}
				else
				{
					$serverName = COption::GetOptionString('main', 'server_name');
				}
			}
			if ($serverName != '')
			{
				$this->serverName = 'http://' . $serverName;
			}
		}

		$this->preg = ['counter' => 0, 'pattern' => [], 'replace' => [], 'cache' => []];

		foreach (GetModuleEvents('main', 'TextParserBefore', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [&$text, &$this]);
		}

		if (($this->allow['CODE'] ?? '') == 'Y')
		{
			$text = preg_replace_callback(
				[
					"#(\\[code(?:\\s+[^]]*]|]))(.+?)(\\[/code(?:\\s+[^]]*]|]))#isu",
					"#(<code(?:\\s+[^>]*>|>))(.+?)(</code(?:\\s+[^>]*>|>))#isu",
				],
				[$this, 'convertCode'],
				$text
			);
		}
		if (($this->allow['HTML'] ?? '') != 'Y' && ($this->allow['NL2BR'] ?? '') == 'Y')
		{
			$text = preg_replace("#<br(.*?)>#is", "\n", $text);
		}
		if (($this->allow['HTML'] ?? '') != 'Y')
		{
			// тут она превращается!
			if (($this->allow['ANCHOR'] ?? '') == 'Y')
			{
				$text = preg_replace(
					[
						"#<a[^>]+href\\s*=\\s*(['\"])(.+?)(?:\\1)[^>]*>(.*?)</a[^>]*>#isu",
						"#<a[^>]+href(\\s*=\\s*)([^'\">]+)>(.*?)</a[^>]*>#isu",
					],
					"[url=\\2]\\3[/url]", $text
				);
			}
			if (($this->allow['BIU'] ?? '') == 'Y')
			{
				$replaced = 0;
				do
				{
					$text = preg_replace(
						"/<([busi])[^>a-z]*>(.+?)<\\/(\\1)[^>a-z]*>/isu",
						"[\\1]\\2[/\\1]",
					$text, -1, $replaced);
				}
				while ($replaced > 0);
			}
			if (($this->allow['P'] ?? '') == 'Y')
			{
				$replaced = 0;
				do
				{
					$text = preg_replace(
						"/<p[^>a-z]*>(.+?)<\\/p[^>a-z]*>/isu",
						"[p]\\1[/p]",
						$text, -1, $replaced);
				}
				while ($replaced > 0);
			}
			if (($this->allow['IMG'] ?? '') == 'Y')
			{
				$text = preg_replace(
					"#<img[^>]+src\\s*=[\\s'\"]*(((http|https|ftp)://[.\\-_:a-z0-9@]+)*(/[-_/=:.a-z0-9@{}&?%]+)+)[\\s'\"]*[^>]*>#isu",
					"[img]\\1[/img]", $text
				);
			}
			if (($this->allow['FONT'] ?? '') == 'Y')
			{
				$text = preg_replace(
					[
						"/<font[^>]+size\\s*=[\\s'\"]*([0-9]+)[\\s'\"]*[^>]*>(.+?)<\\/font[^>]*>/isu",
						"/<font[^>]+color\\s*=[\\s'\"]*(#[a-f0-9]{6})[^>]*>(.+?)<\\/font[^>]*>/isu",
						"/<font[^>]+face\\s*=[\\s'\"]*([a-z\\s\\-]+)[\\s'\"]*[^>]*>(.+?)<\\/font[^>]*>/isu",
					],
					[
						"[size=\\1]\\2[/size]",
						"[color=\\1]\\2[/color]",
						"[font=\\1]\\2[/font]",
					],
					$text
				);
			}
			if (($this->allow['LIST'] ?? '') == 'Y')
			{
				$text = preg_replace(
					[
						"/<ul((\\s[^>]*)|(\\s*))>(.+?)<\\/ul([^>]*)>/isu",
						"/<ol((\\s[^>]*)|(\\s*))>(.+?)<\\/ol([^>]*)>/isu",
						"/<li((\\s[^>]*)|(\\s*))>(.+?)<\\/li([^>]*)>/isu",
						"/<li((\\s[^>]*)|(\\s*))>/isu",
					],
					[
						"[list]\\4[/list]",
						"[list=1]\\4[/list]",
						"[*]\\4",
						"[*]",
					],
					$text
				);
			}
			if (($this->allow['TABLE'] ?? '') == 'Y')
			{
				$text = preg_replace(
					[
						"/<table((\\s[^>]*)|(\\s*))>/isu",
						"/<\\/table([^>]*)>/isu",
						"/<tr((\\s[^>]*)|(\\s*))>/isu",
						"/<\\/tr([^>]*)>/isu",
						"/<td((\\s[^>]*)|(\\s*))>/isu",
						"/<\\/td([^>]*)>/isu",
						"/<th((\\s[^>]*)|(\\s*))>/isu",
						"/<\\/th([^>]*)>/isu",
					],
					[
						"[table]",
						"[/table]",
						"[tr]",
						"[/tr]",
						"[td]",
						"[/td]",
						"[th]",
						"[/th]",
					],
					$text
				);
			}
			if (($this->allow['QUOTE'] ?? '') == 'Y')
			{
				$text = preg_replace("#<(/?)quote(.*?)>#is", "[\\1quote]", $text);
			}

			if (preg_match("/<cut/isu", $text, $matches))
			{
				$text = preg_replace(
					"/<cut([^>]*)>(.+?)<\/cut>/isu",
					"[cut=\\1]\\2[/cut]",
					$text);
			}

			if ($text != '')
			{
				if ($this->preg['counter'] > 0)
				{
					$res = mb_strlen((string)$this->preg['counter']);
					$p = ['\d'];
					while (($res--) > 1)
					{
						$p[] = '\d{' . ($res + 1) . '}';
					}
					$text = preg_replace(
						["/<(?!\017#(" . implode(")|(", $p) . ")>)/", "/(?<!<\017#(" . implode(")|(", $p) . "))>/", "/\"/"],
						["&lt;", "&gt;", "&quot;"],
						$text
					);
				}
				else
				{
					$text = str_replace(
						["<", ">", "/\"/"],
						["&lt;", "&gt;", "&quot;"],
						$text
					);

				}
			}
		}
		$patt = [];
		if (($this->allow['VIDEO'] ?? '') == 'Y')
		{
			$patt[] = "/\\[video([^\\]]*)\\](.+?)\\[\\/video[\\s]*\\]/isu";
		}
		if (($this->allow['IMG'] ?? '') == 'Y')
		{
			$patt[] = "/\\[img([^\\]]*)\\](.+?)\\[\\/img\\]/isu";
		}

		foreach (GetModuleEvents('main', 'TextParserBeforeAnchorTags', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [&$text, &$this]);
		}

		if (($this->allow['ANCHOR'] ?? '') == 'Y')
		{
			$patt[] = "/\\[url\\](.*?)\\[\\/url\\]/iu";
			$patt[] = "/\\[url\\s*=\\s*(
			(?:
				[^\\[\\]]++
				|\\[ (?: (?>[^\\[\\]]+) | (?:\\1) )* \\]
			)+
			)\\s*\\](.*?)\\[\\/url\\]/ixsu";

			$text = preg_replace_callback(
				$patt,
				fn($matches) => $this->preconvertAnchor($matches, $attributes['ANCHOR'] ?? []),
				$text,
			);

			if (($this->allow['TEXT_ANCHOR'] ?? 'Y') == 'Y')
			{
				$schemes = $this->getAnchorSchemes();

				$boundaries = [
					'&lt;' => '&gt;',
					'(' => ')', // doubtful
					"“" => "”",
					"‘" => "’",
					"«" => "»",
				];

				$patt = [];
				foreach ($boundaries as $start => $end)
				{
					if (str_contains($text, $start))
					{
						$patt[] = "/(?<=" . preg_quote($start) . ")(?<!\\[nomodify\\]|<nomodify>)((((" . $schemes . "):\\/\\/)|www\\.)[._:a-z0-9@-].*?)(?=" . preg_quote($end) . ")/isu";
					}
				}

				$patt[] = "/(?<=^|[" . $this->wordSeparator . "])(?<!\\[nomodify\\]|<nomodify>)((((" . $schemes . "):\\/\\/)|www\\.)[._:a-z0-9@-].*?)(?=[\\s\"{}]|&quot;|\$)/isu";

				$text = preg_replace_callback(
					$patt,
					fn($matches) => $this->preconvertUrl($matches, $attributes['TEXT_ANCHOR'] ?? []),
					$text,
				);
			}
		}
		elseif (!empty($patt))
		{
			$text = preg_replace_callback($patt, [$this, 'preconvertAnchor'], $text);
		}

		$text = preg_replace("/<\\/?nomodify>/iu", '', $text);

		if (($this->allow['SPOILER'] ?? '') === 'Y')
		{
			if (preg_match("/\\[(cut|spoiler)/isu", $text, $matches))
			{
				$text = preg_replace(
					[
						"/\\[(cut|spoiler)(([^]])*)]/isu",
						"/\\[\\/(cut|spoiler)]/isu",
					],
					[
						"\001\\2\002",
						"\003",
					],
					$text
				);

				while (preg_match("/(\001([^\002]*)\002([^\001\002\003]+)\003)/isu", $text, $matches))
				{
					$text = preg_replace_callback("/\001([^\002]*)\002([^\001\002\003]+)\003/isu", [ $this, 'convert_spoiler_tag' ], $text);
				}

				$text = preg_replace(
					[
						"/\001([^\002]+)\002/",
						"/\001\002/",
						"/\003/",
					],
					[
						"[spoiler\\1]",
						"[spoiler]",
						"[/spoiler]",
					],
					$text
				);
			}
		}

		foreach (GetModuleEvents('main', 'TextParserBeforeTags', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [&$text, &$this]);
		}

		if (
			($this->allow['SMILES'] ?? '') == 'Y'
			|| ($this->allow['CLEAR_SMILES'] ?? '') == 'Y'
		)
		{
			if (str_contains($text, "<nosmile>"))
			{
				$text = preg_replace_callback(
					"/<nosmile>(.*?)<\\/nosmile>/isu",
					[$this, "defendTags"],
					$text
				);
			}
			if ($this->smiles === null)
			{
				$this->initSmiles();
			}
			if (!empty($this->smiles))
			{
				if ($this->smilePatterns === null)
				{
					$this->initSmilePatterns();
				}

				if (!empty($this->smilePatterns))
				{
					$text = preg_replace_callback($this->smilePatterns, [$this, 'convertEmoticon'], ' ' . $text . ' ');
				}
			}
		}

		$text = $this->post_convert_anchor_tag($text);

		if (!isset($this->allow['EMOJI']) || $this->allow['EMOJI'] != 'N')
		{
			$text = \Bitrix\Main\Text\Emoji::decode($text);
		}

		$res = array_merge(
			[
				'VIDEO' => 'N',
				'IMG' => 'N',
				'ANCHOR' => 'N',
				'BIU' => 'N',
				'LIST' => 'N',
				'FONT' => 'N',
				'TABLE' => 'N',
				'ALIGN' => 'N',
				'QUOTE' => 'N',

				// TODO: change to N
				// This option was added in 2016 as a default value for cases like this
				// $textParser = new CTextParser(); $textParser->allow = [...];
				'P' => 'Y',
			],
			$this->allow
		);
		foreach ($res as $tag => $val)
		{
			if ($val != 'Y')
			{
				continue;
			}

			if (str_contains($text, '<nomodify>'))
			{
				$text = preg_replace_callback(
					"/<nomodify>(.*?)<\\/nomodify>/isu",
					[$this, "defendTags"],
					$text
				);
			}

			switch ($tag)
			{
				case 'VIDEO':
					$text = preg_replace_callback(
						"/\\[video([^]]*)](.+?)\\[\\/video\\s*]/isu",
						[$this, 'convertVideo'],
						$text
					);
					break;
				case 'IMG':
					$text = preg_replace_callback(
						"/\\[img([^]]*)](.+?)\\[\\/img]/isu",
						[$this, 'convertImage'],
						$text
					);
					break;
				case 'ANCHOR':
					$arUrlPatterns = [
						"/\\[url\\](.*?)\\[\\/url\\]/iu",
						"/\\[url\\s*=\\s*(
							(?:
								[^\\[\\]]++
								|\\[ (?: (?>[^\\[\\]]+) | (?:\\1) )* \\]
							)+
							)\\s*\\](.*?)\\[\\/url\\]/ixsu",
					];

					if (($this->allow['CUT_ANCHOR'] ?? '') != 'Y')
					{
						$text = preg_replace_callback(
							$arUrlPatterns,
							fn($matches) => $this->convertAnchor($matches, $attributes['ANCHOR'] ?? []),
							$text,
						);
					}
					else
					{
						$text = preg_replace($arUrlPatterns, '', $text);
					}
					break;
				case 'BIU':
					$replaced  = 0;
					do
					{
						$text = preg_replace_callback(
							"/\\[([busi])](.*?)\\[\\/(\\1)]/isu",
							function($matches) {
								$tag = strtolower($matches[1]);
								$className = $this->useTypography ? ' class="' . $this->tagClasses[$tag] . '"' : '';

								return "<$tag$className>" . $matches[2]. "</$tag>";
							},
							$text,
							-1,
							$replaced
						);
					}
					while ($replaced > 0);
					break;
				case 'P':
					$replaced  = 0;
					do
					{
						$text = preg_replace_callback(
							"/\\[p](.*?)\\[\\/p](([ \r\t]*)\n?)/isu",
							function($matches) {
								$className = $this->useTypography ? ' class="' . $this->tagClasses['p'] . '"' : '';

								return "<p$className>". self::trimLineBreaks($matches[1]) . '</p>';
							},
							$text,
							-1,
							$replaced
						);
					}
					while ($replaced > 0);
					break;
				case 'LIST':
					$olClassName = $this->useTypography ? ' class="' . $this->tagClasses['ol'] . '"' : '';
					$ulClassName = $this->useTypography ? ' class="' . $this->tagClasses['ul'] . '"' : '';
					$liClassName = $this->useTypography ? ' class="' . $this->tagClasses['li'] . '"' : '';

					while (preg_match("/\\[list\\s*=\\s*([1a])\\s*](.+?)\\[\\/list]/isu", $text))
					{
						$text = preg_replace(
							[
								"/\\[list\\s*=\\s*1\\s*](\\s*)(.+?)\\[\\/list](([\040\\r\\t]*)\\n?)/isu",
								"/\\[list\\s*=\\s*a\\s*](\\s*)(.+?)\\[\\/list](([\040\\r\\t]*)\\n?)/isu",
								"/\\[\\*]/u",
							],
							[
								"<ol$olClassName>\\2</ol>",
								"<ol type=\"a\"$olClassName>\\2</ol>",
								"<li$liClassName>",
							],
							$text
						);
					}
					while (preg_match("/\\[list](.+?)\\[\\/list](([\\040\\r\\t]*)\\n?)/isu", $text))
					{
						$text = preg_replace(
							[
								"/\\[list](\\s*)(.+?)\\[\\/list](([\\040\\r\\t]*)\\n?)/isu",
								"/\\[\\*]/u",
							],
							[
								"<ul$ulClassName>\\2</ul>",
								"<li$liClassName>",
							],
							$text
						);
					}
					break;
				case 'FONT':
					while (preg_match("/\\[size\\s*=\\s*([^]]+)](.*?)\\[\\/size]/isu", $text))
					{
						$text = preg_replace_callback(
							"/\\[size\\s*=\\s*([^]]+)](.*?)\\[\\/size]/isu",
							[$this, 'convertFontSize'],
							$text
						);
					}
					while (preg_match("/\\[font\\s*=\\s*([^]]+)](.*?)\\[\\/font]/isu", $text))
					{
						$text = preg_replace_callback(
							"/\\[font\\s*=\\s*([^]]+)](.*?)\\[\\/font]/isu",
							[$this, 'convertFont'],
							$text
						);
					}
					while (preg_match("/\\[color\\s*=\\s*([^]]+)](.*?)\\[\\/color]/isu", $text))
					{
						$text = preg_replace_callback(
							"/\\[color\\s*=\\s*([^]]+)](.*?)\\[\\/color]/isu",
							[$this, 'convertFontColor'],
							$text
						);
					}
					break;
				case 'TABLE':
					while (preg_match("/\\[table]/isu", $text))
					{
						$tagToCheckList = ['td', 'th', 'tr', 'table'];
						foreach ($tagToCheckList as $tagToCheck)
						{
							preg_match_all("/\\[" . $tagToCheck . "]/isu", $text, $matches);
							$opentags = count($matches['0']);

							preg_match_all("/\\[\\/" . $tagToCheck . "]/is", $text, $matches);
							$closetags = count($matches['0']);

							$unclosed = $opentags - $closetags;
							if ($unclosed > 0)
							{
								$text .= str_repeat('[/' . $tagToCheck . ']', $unclosed);
							}
						}

						$tableClass = $this->useTypography ? $this->tagClasses['table'] : 'data-table';
						$openTableTag = (
							$this->bMobile
								? "<div style=\"overflow-x: auto;\"><table class=\"$tableClass\">"
								: "<table class=\"$tableClass\">"
							);
							$closeTableTag = (
							$this->bMobile
								? '</table></div>'
								: '</table>'
						);

						$trClass = $this->useTypography ? ' class="' . $this->tagClasses['tr'] . '"' : '';
						$tdClass = $this->useTypography ? ' class="' . $this->tagClasses['td'] . '"' : '';
						$thClass = $this->useTypography ? ' class="' . $this->tagClasses['th'] . '"' : '';

						$text = preg_replace(
							[
								"/\\[table]/isu",
								"/\\[\\/table](?:(?:[\\040\\r\\t]*)\\n?)/isu",
								"/(\\s*?)\\[tr]/isu",
								"/\\[\\/tr](\\s*?)/isu",
								"/(\\s*?)\\[td]/isu",
								"/\\[\\/td](\\s*?)/isu",
								"/(\\s*?)\\[th]/isu",
								"/\\[\\/th](\\s*?)/isu",
							],
							[
								$openTableTag,
								$closeTableTag,
								"<tr$trClass>",
								'</tr>',
								"<td$tdClass>",
								'</td>',
								"<th$thClass>",
								'</th>',
							],
							$text
						);
					}
					break;
				case 'ALIGN':
					$paragraph = "<p class=\"{$this->tagClasses['p']}\">\\1</p>";

					$replaced  = 0;
					do
					{
						$text = preg_replace(
							[
								"/\\[left](.*?)\\[\\/left](([\\040\\r\\t]*)\\n?)/isu",
								"/\\[right](.*?)\\[\\/right](([\\040\\r\\t]*)\\n?)/isu",
								"/\\[center](.*?)\\[\\/center](([\\040\\r\\t]*)\\n?)/isu",
								"/\\[justify](.*?)\\[\\/justify](([\\040\\r\\t]*)\\n?)/isu",
							],
							[
								$this->useTypography ? $paragraph : "<div align=\"left\">\\1</div>",
								$this->useTypography ? $paragraph : "<div align=\"right\">\\1</div>",
								$this->useTypography ? $paragraph : "<div align=\"center\">\\1</div>",
								$this->useTypography ? $paragraph : "<div align=\"justify\">\\1</div>",
							],
							$text,
							-1,
							$replaced
						);
					}
					while ($replaced > 0);
					break;
				case 'QUOTE':
					while (preg_match("/\\[quote[^]]*](.*?)\\[\\/quote[^]]*]/isu", $text))
					{
						$text = preg_replace_callback(
							"/\\[quote[^]]*](.*?)\\[\\/quote[^]]*](([\\040\\r\\t]*)\\n?)/isu",
							[$this, 'convertQuote'],
							$text
						);
					}
					break;
			}
		}

		if (preg_match("/\[cut/isu", $text, $matches))
		{
			$text = preg_replace(
				[
					"/\[cut(([^]])*)]/isu",
					"/\[\/cut]/isu",
				],
				[
					"\001\\1\002",
					"\003",
				],
				$text
			);

			$text = preg_replace_callback(
				"/(\001([^\002]*)\002([^\001\002\003]+)\003)/isu",
				function($matches) {
					return $this->convert_cut_tag($matches[3], $matches[2]);
				},
				$text
			);

			$text = preg_replace(
				[
					"/\001([^\002]+)\002/",
					"/\001\002/",
					"/\003/",
				],
				[
					"[cut\\1]",
					"[cut]",
					"[/cut]",
				],
				$text
			);
		}

		if (str_contains($text, '<nomodify>'))
		{
			$text = preg_replace_callback(
				"/<nomodify>(.*?)<\\/nomodify>/isu",
				[$this, 'defendTags'],
				$text
			);
		}

		if (isset($this->allow['USERFIELDS']) && is_array($this->allow['USERFIELDS']))
		{
			foreach ($this->allow['USERFIELDS'] as $userField)
			{
				if (is_array($userField['USER_TYPE']) && array_key_exists('TAG', $userField['USER_TYPE']) )
				{
					$userField['TAG'] = $userField['USER_TYPE']['TAG'];
				}
				if (empty($userField['TAG']))
				{
					switch($userField['USER_TYPE_ID'])
					{
						case 'webdav_element' :
							$userField['TAG'] = 'DOCUMENT ID';
							break;
						case 'vote' :
							$userField['TAG'] = 'VOTE ID';
							break;
					}
				}

				if (!empty($userField['TAG']) && array_key_exists('VALUE', $userField) && !empty($userField['VALUE']) &&
					method_exists($userField['USER_TYPE']['CLASS_NAME'], 'GetPublicViewHTML') )
				{
					$userField['VALUE'] = (is_array($userField['VALUE']) ? $userField['VALUE'] : [$userField['VALUE']]);
					$this->userField = $userField;
					$text = preg_replace_callback(
						"/\\[(" . (is_array($userField["TAG"]) ? implode("|", $userField["TAG"]) : $userField["TAG"]) . ")\\s*=\\s*([a-z0-9]+)([^]]*)]/isu",
						[$this, 'convert_userfields'],
						$text
					);
				}
			}
		}

		if (!isset($this->allow['USER']) || $this->allow['USER'] != 'N')
		{
			do
			{
				$textOriginal = $text;
				$text = preg_replace_callback(
					"/\[user\s*=\s*([^]]*)]((?:(?!\[user\s*=\s*[^]]*]).)+?)\[\/user]/isu",
					[$this, 'convert_user'],
					$text
				);
			}
			while ($textOriginal !== $text);
		}

		if (!isset($this->allow['PROJECT']) || $this->allow['PROJECT'] !== 'N')
		{
			$text = preg_replace_callback(
				"/\[project\s*=\s*([^]]*)](.+?)\[\/project]/isu",
				[ $this, 'convert_project' ],
				$text
			);
		}

		if (!isset($this->allow['DEPARTMENT']) || $this->allow['DEPARTMENT'] !== 'N')
		{
			$text = preg_replace_callback(
				"/\[department\s*=\s*([^]]*)](.+?)\[\/department]/isu",
				[ $this, 'convert_department' ],
				$text
			);
		}

		if (isset($this->allow['TAG']) && $this->allow['TAG'] == 'Y')
		{
			$text = preg_replace_callback(
				$this->getTagPattern(),
				[$this, 'convert_tag'],
				$text
			);
		}

		foreach (GetModuleEvents('main', 'TextParserAfterTags', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [&$text, &$this]);
		}

		if (($this->allow['HTML'] ?? '') != 'Y' || ($this->allow['NL2BR'] ?? '') == 'Y')
		{
			$text = str_replace(["\r\n", "\n"], "<br />", $text);
			$text = preg_replace(
				[
					"/<br \\/>[\\t\\s]*(<\\/table[^>]*>)/isu",
					"/<br \\/>[\\t\\s]*(<thead[^>]*>)/isu",
					"/<br \\/>[\\t\\s]*(<\\/thead[^>]*>)/isu",
					"/<br \\/>[\\t\\s]*(<tfoot[^>]*>)/isu",
					"/<br \\/>[\\t\\s]*(<\\/tfoot[^>]*>)/isu",
					"/<br \\/>[\\t\\s]*(<tbody[^>]*>)/isu",
					"/<br \\/>[\\t\\s]*(<\\/tbody[^>]*>)/isu",
					"/<br \\/>[\\t\\s]*(<tr[^>]*>)/isu",
					"/<br \\/>[\\t\\s]*(<\\/tr[^>]*>)/isu",
					"/<br \\/>[\\t\\s]*(<td[^>]*>)/isu",
					"/<br \\/>[\\t\\s]*(<\\/td[^>]*>)/isu",
				],
				"\\1",
				$text
			);
		}

		$text = str_replace(
			[
				"(c)", "(C)",
				"(tm)", "(TM)", "(Tm)", "(tM)",
				"(r)", "(R)",
			],
			[
				"&#169;", "&#169;",
				"&#153;", "&#153;", "&#153;", "&#153;",
				"&#174;", "&#174;",
			],
			$text
		);

		if (($this->allow['HTML'] ?? '') != 'Y')
		{
			if ($this->maxStringLen > 0)
			{
				$text = preg_replace("/(&#\\d{1,3};)/isu", "<\019\\1>", $text);
				$text = preg_replace_callback("/(?<=^|>)([^<>\\[]+?)(?=<|\\[|$)/isu", [$this, "partWords"], $text);
				$text = preg_replace("/(<\019((&#\\d{1,3};))>)/isu", "\\2", $text);
			}
			$text = preg_replace_callback("/(?<=^|>)([^<>\\[]+?)(?=<|\\[|$)/isu", [$this, "parseSpaces"], $text);
		}

		foreach (GetModuleEvents('main', 'TextParserBeforePattern', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [&$text, &$this]);
		}

		if ($this->preg['counter'] > 0)
		{
			$text = str_replace(array_reverse($this->preg['pattern']), array_reverse($this->preg['replace']), $text);
		}

		foreach (GetModuleEvents('main', 'TextParserAfter', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [&$text, &$this]);
		}

		return trim($text);
	}

	public function defendTags($matches)
	{
		return $this->defended_tags($matches[1]);
	}

	public function defended_tags($text, $tag = 'replace')
	{
		$text = str_replace("\\\"", "\"", $text);
		switch ($tag)
		{
			case 'replace':
				if (($k = array_search($text, $this->preg['replace'])) !== false)
				{
					$text = "<\017#" . $k . ">";
					break;
				}
				$this->preg["pattern"][] = "<\017#" . $this->preg["counter"] . ">";
				$this->preg["replace"][] = $text;
				$text = "<\017#" . $this->preg["counter"] . ">";
				$this->preg["counter"]++;
				break;
		}
		return $text;
	}

	public function convert4mail($text)
	{
		$text = trim($text);
		if ($text == '')
		{
			return '';
		}

		$arPattern = [];
		$arReplace = [];

		$arPattern[] = "/\\[(code|quote)(.*?)\\]/isu";
		$arReplace[] = "\n>================== \\1 ===================\n";

		$arPattern[] = "/\\[\\/(code|quote)(.*?)\\]/isu";
		$arReplace[] = "\n>===========================================\n";

		$arPattern[] = "/\\<WBR[\\s\\/]?\\>/isu";
		$arReplace[] = "";

		$arPattern[] = "/\\[\\*\\]/isu";
		$arReplace[] = "- ";

		$arPattern[] = "/^(\r|\n)+?(.*)$/";
		$arReplace[] = "\\2";

		$arPattern[] = "/\\[b\\](.+?)\\[\\/b\\]/isu";
		$arReplace[] = "\\1";

		$arPattern[] = "/\\[p\\](.*?)\\[\\/p\\]/isu";
		$arReplace[] = "\\1";

		$arPattern[] = "/\\[i\\](.+?)\\[\\/i\\]/isu";
		$arReplace[] = "\\1";

		$arPattern[] = "/\\[u\\](.+?)\\[\\/u\\]/isu";
		$arReplace[] = "_\\1_";

		$arPattern[] = "/\\[s\\](.+?)\\[\\/s\\]/isu";
		$arReplace[] = "_\\1_";

		$arPattern[] = "/\\[(\\/?)(color|font|size|left|right|center)([^\\]]*)\\]/isu";
		$arReplace[] = "";

		$arPattern[] = "/\\[url\\](\\S+?)\\[\\/url\\]/isu";
		$arReplace[] = "(URL: \\1 )";

		$arPattern[] = "/\\[url\\s*=\\s*(\\S+?)\\s*\\](.*?)\\[\\/url\\]/isu";
		$arReplace[] = "\\2 (URL: \\1 )";

		$arPattern[] = "/\\[img([^\\]]*)\\](.+?)\\[\\/img\\]/isu";
		$arReplace[] = "(IMAGE: \\2)";

		$arPattern[] = "/\\[video([^\\]]*)\\](.+?)\\[\\/video[\\s]*\\]/isu";
		$arReplace[] = "(VIDEO: \\2)";

		$arPattern[] = "/\\[(\\/?)list(.*?)\\]/isu";
		$arReplace[] = "\n";

		$arPattern[] = "/\\[user([^\\]]*)\\](.+?)\\[\\/user\\]/isu";
		$arReplace[] = "\\2";

		$arPattern[] = "/\\[project([^\\]]*)\\](.+?)\\[\\/project\\]/isu";
		$arReplace[] = "\\2";

		$arPattern[] = "/\\[department([^\\]]*)\\](.+?)\\[\\/department\\]/isu";
		$arReplace[] = "\\2";

		$arPattern[] = "/\\[DOCUMENT([^\\]]*)\\]/isu";
		$arReplace[] = "";

		$arPattern[] = "/\\[DISK(.+?)\\]/isu";
		$arReplace[] = "";

		$arPattern[] = "/\\[(table)(.*?)\\]/isu";
		$arReplace[] = "\n>================== \\1 ===================";

		$arPattern[] = "/\\[\\/table(.*?)\\]/isu";
		$arReplace[] = "\n>===========================================\n";

		$arPattern[] = "/\\[tr\\]\\s*/isu";
		$arReplace[] = "\n";

		$arPattern[] = "/\\[(\\/?)(tr|td)\\]/isu";
		$arReplace[] = "";

		$text = preg_replace($arPattern, $arReplace, $text);

		$text = str_replace('&shy;', '', $text);
		if (preg_match("/\[cut(([^]])*)]/isu", $text))
		{
			$text = preg_replace(
				[
					"/\[cut(([^]])*)]/isu",
					"/\[\/cut]/isu",
				],
				[
					"\001\\1\002",
					"\003",
				],
				$text
			);
			while (preg_match("/(\001([^\002]*)\002([^\001\002\003]+)\003)/isu", $text))
			{
				$text = preg_replace(
					"/(\001([^\002]*)\002([^\001\002\003]+)\003)/isu",
					"\n>================== CUT ===================\n\\3\n>==========================================\n",
					$text
				);
			}
			$text = preg_replace(
				[
					"/\001([^\002]+)\002/",
					"/\001\002/",
					"/\003/",
				],
				[
					"[cut\\1]",
					"[cut]",
					"[/cut]",
				],
				$text
			);
		}

		static $replacements = [
			"&nbsp;" => " ",
			"&quot;" => "\"",
			"&#092;" => "\\",
			"&#036;" => "\$",
			"&#33;" => "!",
			"&#91;" => "[",
			"&#93;" => "]",
			"&#39;" => "'",
			"&lt;" => "<",
			"&gt;" => ">",
			"&#124;" => '|',
			"&amp;" => "&",
		];
		$text = strtr($text, $replacements);

		return $text;
	}

	public function convertVideo($matches)
	{
		$params = $matches[1];
		$path = $matches[2];

		if ($path == '')
		{
			return '';
		}

		$width = '';
		$height = '';
		$preview = '';
		$provider = '';
		$type = '';
		preg_match("/width=([0-9]+)/isu", $params, $width);
		preg_match("/height=([0-9]+)/isu", $params, $height);

		preg_match("/preview='([^']+)'/isu", $params, $preview);
		if (empty($preview))
		{
			preg_match("/preview=\"([^\"]+)\"/isu", $params, $preview);
		}

		preg_match("/type=(YOUTUBE|RUTUBE|VIMEO|VK|FACEBOOK|INSTAGRAM)/isu", $params, $provider);
		preg_match("/mimetype='([^']+)'/isu", $params, $type);

		$width = intval($width[1] ?? 0);
		$width = ($width > 0 ? $width : 400);
		$height = intval($height[1] ?? 0);
		$height = ($height > 0 ? $height : 300);
		$preview = trim($preview[1] ?? '');
		$preview = ($preview != '' ? $preview : '');
		$provider = isset($provider[1])? mb_strtoupper(trim($provider[1])) : '';
		$type = trim($type[1] ?? '');

		$arFields = [
			'PATH' => $path,
			'WIDTH' => $width,
			'HEIGHT' => $height,
			'PREVIEW' => $preview,
			'TYPE' => $provider,
			'MIME_TYPE' => $type,
			'PARSER_OBJECT' => $this,
		];

		foreach (GetModuleEvents('main', 'TextParserVideoConvert', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [&$arFields, &$this]);
		}

		$video = $this->convert_video($arFields);

		return $this->defended_tags($video);
	}

	protected function convert_video($arParams)
	{
		global $APPLICATION;

		if (
			!is_array($arParams)
			|| $arParams["PATH"] == ''
		)
		{
			return false;
		}
		$trustedProviders = [
			'YOUTUBE',
			'RUTUBE',
			'VIMEO',
			'VK',
			'FACEBOOK',
			'INSTAGRAM',
		];

		ob_start();

		if ($this->type == 'mail')
		{
			$pathEncoded = htmlspecialcharsbx($arParams['PATH']);

			?><a href="<?=$pathEncoded?>"><?=$pathEncoded?></a><?
		}
		elseif (in_array($arParams['TYPE'], $trustedProviders))
		{
			// add missing protocol to get host
			if (str_starts_with($arParams['PATH'], '//'))
			{
				$arParams['PATH'] = 'http:' . $arParams['PATH'];
			}
			$uri = new Uri($arParams['PATH']);
			if (UrlPreview::isHostTrusted($uri) || $uri->getHost() == Application::getInstance()->getContext()->getServer()->getServerName())
			{
				// Replace http://someurl, https://someurl by //someurl
				$arParams['PATH'] = preg_replace("/https?:\\/\\//i", '//', $arParams['PATH']);

				$pathEncoded = htmlspecialcharsbx($arParams['PATH']);

				if ($this->bMobile)
				{
					?><iframe class="bx-mobile-video-frame" src="<?=$pathEncoded?>" allowfullscreen="" frameborder="0" height="100%" width="100%" style="max-width: 600px; min-height: 300px;"></iframe><?
				}
				else
				{
					?><iframe src="<?=$pathEncoded?>" allowfullscreen="" frameborder="0" height="<?=intval($arParams["HEIGHT"])?>" width="<?=intval($arParams["WIDTH"])?>" style="max-width: 100%;"></iframe><?
				}
			}
		}
		else
		{
			$playerParams = $arParams;
			$playerParams['TYPE'] = $arParams['MIME_TYPE'];
			$playerComponent = 'bitrix:player';
			if ($this->bMobile)
			{
				$playerComponent = 'bitrix:mobile.player';
			}

			$APPLICATION->IncludeComponent(
				$playerComponent, '', $playerParams,
				null,
				[
					'HIDE_ICONS' => 'Y',
				]
			);
		}

		return ob_get_clean();
	}

	public function convertEmoticon($matches)
	{
		$array = array_intersect_key($this->smileReplaces, $matches);
		$replacement = reset($array);
		if (!empty($replacement))
		{
			if (($this->allow['CLEAR_SMILES'] ?? '') == 'Y')
			{
				return $this->convert_emoticon($replacement['code']);
			}
			else
			{
				return $this->convert_emoticon(
					$replacement['code'],
					$replacement['image'],
					$replacement['description'],
					$replacement['width'],
					$replacement['height'],
					$replacement['descriptionDecode'],
					$replacement['imageDefinition']
				);
			}
		}
		return $matches[0];
	}

	public function convert_emoticon($code = '', $image = '', $description = '', $width = '', $height = '', $descriptionDecode = false, $imageDefinition = CSmile::IMAGE_SD)
	{
		if ($code == '' || $image == '')
		{
			return '';
		}
		$code = stripslashes($code);
		$description = stripslashes($description);
		$image = stripslashes($image);
		$width = intval($width);
		$height = intval($height);
		if ($descriptionDecode)
		{
			$description = htmlspecialcharsback($description);
		}

		$html = '<img src="' . htmlspecialcharsbx($this->serverName) . $this->pathToSmile . $image . '"'
			. ' border="0"'
			. ' data-code="' . $code . '"'
			. ' data-definition="' . $imageDefinition . '"'
			. ' alt="' . $code . '"'
			. ' style="' . ($width > 0 ? 'width:' . $width . 'px;' : '') . ($height > 0 ? 'height:' . $height . 'px;' : '') . '"'
			. ' title="' . $description . '"'
			. ' class="' . ($this->useTypography ? $this->tagClasses['smiley'] : 'bx-smile') . '" />';
		$cacheKey = md5($html);
		if (!isset($this->preg['cache'][$cacheKey]))
		{
			$this->preg['cache'][$cacheKey] = $this->defended_tags($html);
		}

		return $this->preg['cache'][$cacheKey];
	}

	public function convertCode($matches)
	{
		$text = $matches[2];

		if ($text == '')
		{
			return '';
		}

		$text = str_replace(
			["[nomodify]", "[/nomodify]", "&#91;", "&#93;", "&", "<", ">", "\\r", "\\n", "\\\"", "\\", "[", "]", "  ", "\t"],
			["", "", "[", "]", "&#38;", "&#60;", "&#62;", "&#92;r", "&#92;n", '&#92;"', "&#92;", "&#91;", "&#93;", "&nbsp;&nbsp;", "&nbsp;&nbsp;&nbsp;"],
			$text
		);

		$text = stripslashes($text);
		$text = $this->useTypography ? self::trimLineBreaks($text) : "<pre>" . $text . "</pre>";

		return $this->defended_tags($this->convert_open_tag('code') . $text  . $this->convert_close_tag('code'));
	}

	public function convertQuote($matches)
	{
		return $this->convert_quote_tag($matches[1]);
	}

	public function convert_quote_tag($text = '')
	{
		if ($text == '')
		{
			return '';
		}

		$text = str_replace("\\\"", "\"", $text);

		if ($this->useTypography)
		{
			$text = self::trimLineBreaks($text);
		}

		return $this->convert_open_tag() . $text . $this->convert_close_tag();
	}

	public function convert_spoiler_tag($text, $title = '')
	{
		if (is_array($text))
		{
			$title = $text[1];
			$text = $text[2];
		}

		if (empty($text))
		{
			return '';
		}

		$title = htmlspecialcharsbx(trim(htmlspecialcharsback($title), " =\"\\'"));
		if ($this->type === 'mail')
		{
			return "<dl><dt>" . ($title ?: Loc::getMessage("MAIN_TEXTPARSER_SPOILER")) . "</dt><dd>" . htmlspecialcharsbx($text) . "</dd></dl>";
		}

		return self::renderSpoiler($text, $title, $this->useTypography);
	}

	function convert_cut_tag($text, $title = '')
	{
		if (empty($text))
		{
			return '';
		}

		$title = trim($title);
		$title = ltrim($title, '=');
		$title = trim($title);

		return self::renderSpoiler($text, $title, $this->useTypography);
	}

	public static function renderSpoiler($text, $title = '', $useTypography = false)
	{
		$title = (empty($title) ? Loc::getMessage("MAIN_TEXTPARSER_HIDDEN_TEXT") : $title);

		if ($useTypography)
		{
			return (
				'<details class="ui-typography-spoiler ui-icon-set__scope">' .
					'<summary class="ui-typography-spoiler-title">' . htmlspecialcharsbx($title) . '</summary>' .
					'<div class="ui-typography-spoiler-content" data-spoiler-content="true">' .
						self::trimLineBreaks($text) .
					'</div>' .
				'</details>'
			);
		}

		ob_start();

		?><table class="forum-spoiler"><?
			?><thead onclick="if (this.nextSibling.style.display=='none') { this.nextSibling.style.display=''; BX.addClass(this, 'forum-spoiler-head-open'); } else { this.nextSibling.style.display='none'; BX.removeClass(this, 'forum-spoiler-head-open'); } BX.onCustomEvent('BX.Forum.Spoiler:toggle', [{node: this}]); event.stopPropagation();"><?
				?><tr><?
					?><th><?
						?><div><?=htmlspecialcharsbx($title)?></div><?
					?></th><?
				?></tr><?
			?></thead><?
			?><tbody class="forum-spoiler" style="display:none;"><?
				?><tr><?
					?><td><?=self::trimLineBreaks($text)?></td><?
				?></tr><?
			?></tbody><?
		?></table><?

		return ob_get_clean();
	}

	public function convert_open_tag($marker = 'quote')
	{
		$marker = (mb_strtolower($marker) == 'code' ? 'code' : 'quote');

		$this->{$marker . '_open'}++;
		if ($this->type == 'rss')
		{
			return "\n====" . $marker . "====\n";
		}

		if ($this->useTypography)
		{
			return (
				$marker === 'quote'
					? '<blockquote class="' . $this->tagClasses['quote'] . '">'
					: '<code class="' . $this->tagClasses['code'] . '">'
			);
		}

		return "<div class='" . $marker . "'><table class='" . $marker . "'><tr><td>";
	}

	public function convert_close_tag($marker = 'quote')
	{
		$marker = (mb_strtolower($marker) == 'code' ? 'code' : 'quote');

		if ($this->{$marker . '_open'} == 0)
		{
			$this->{$marker . '_error'}++;
			return '';
		}
		$this->{$marker . '_closed'}++;

		if ($this->type == 'rss')
		{
			return "\n=============\n";
		}

		if ($this->useTypography)
		{
			return (
				$marker === 'quote'
					? '</blockquote>'
					: '</code>'
			);
		}

		return '</td></tr></table></div>';
	}

	public function convertImage($matches)
	{
		return $this->convert_image_tag($matches[2], $matches[1]);
	}

	public function convert_image_tag($url = '', $params = '')
	{
		$url = trim($url);
		if ($url == '')
		{
			return '';
		}

		preg_match("/width=([0-9]+)/isu", $params, $width);
		preg_match("/height=([0-9]+)/isu", $params, $height);
		$width = intval($width[1] ?? 0);
		$height = intval($height[1] ?? 0);

		$bErrorIMG = false;
		if (!preg_match("/^(http|https|ftp|\\/)/iu", $url))
		{
			$bErrorIMG = true;
		}

		$url = htmlspecialcharsbx($url);
		if ($bErrorIMG)
		{
			return '[img]' . $url . '[/img]';
		}

		$strPar = '';
		if ($width > 0)
		{
			if ($width > $this->imageWidth)
			{
				$height = intval($height * ($this->imageWidth / $width));
				$width = $this->imageWidth;
			}
		}
		if ($height > 0)
		{
			if ($height > $this->imageHeight)
			{
				$width = intval($width * ($this->imageHeight / $height));
				$height = $this->imageHeight;
			}
		}
		if ($width > 0)
		{
			$strPar = " width=\"" . $width . "\"";
		}
		if ($height > 0)
		{
			$strPar .= " height=\"" . $height . "\"";
		}

		$serverName = htmlspecialcharsbx($this->serverName);
		if ($this->useTypography)
		{
			$src = $serverName . $url;
			if ($this->serverName == '' || preg_match("/^(http|https|ftp):\\/\\//iu", $url))
			{
				$src = $url;
			}

			$attrs = ' loading="lazy"';
			if ($width > 0)
			{
				$attrs .= " width=\"" . $width . "\"";
				if ($height > 0)
				{
					$attrs .= ' style="aspect-ratio: ' . ($width / $height) . '"';
				}
			}

			$image = (
				'<span class="' . $this->tagClasses['image-container'] . '">' .
					'<img src="' . $src . '" class="' . $this->tagClasses['image'] . '"'. $attrs .'>' .
				'</span>'
			);

			return $this->defended_tags($image);
		}

		$image = '<img src="' . $serverName . $url . '" border="0"' . $strPar . ' data-bx-image="' . $serverName . $url . '" data-bx-onload="Y" />';
		if ($this->serverName == '' || preg_match("/^(http|https|ftp):\\/\\//iu", $url))
		{
			$image = '<img src="' . $url . '" border="0"' . $strPar . ' data-bx-image="' . $url . '" data-bx-onload="Y" />';
		}
		return $this->defended_tags($image);
	}

	public function convertFont($matches)
	{
		return $this->convert_font_attr('font', $matches[1], $matches[2]);
	}

	public function convertFontSize($matches)
	{
		return $this->convert_font_attr('size', $matches[1], $matches[2]);
	}

	public function convertFontColor($matches)
	{
		return $this->convert_font_attr('color', $matches[1], $matches[2]);
	}

	public function stripAllTags($text)
	{
		return preg_replace('|[[/!]*?[^\\[\\]]*?]|i', '', $text);
	}

	public function convert_font_attr($attr, $value = "", $text = "")
	{
		if ($text == '')
		{
			return '';
		}

		$text = str_replace("\\\"", "\"", $text);

		if ($value == '')
		{
			return $text;
		}

		if ($attr == 'size')
		{
			if (mb_strlen($value) > 2 && str_ends_with($value, 'pt'))
			{
				$value = intval(substr($value, 0, -2));
				if ($value <= 0)
				{
					return $text;
				}
				return '<span class="bx-font" style="font-size:' . $value . 'pt; line-height: normal;">' . $text . '</span>';
			}

			$count = count($this->arFontSize);
			if ($count <= 0)
			{
				return $text;
			}
			$value = intval($value > $count ? ($count - 1) : $value);
			//compatibility with old percent values
			$size = (is_numeric($this->arFontSize[$value])? $this->arFontSize[$value] . '%' : $this->arFontSize[$value]);
			return '<span class="bx-font" style="font-size:' . $size . ';">' . $text . '</span>';
		}
		elseif ($attr == 'color')
		{
			$value = preg_replace("/[^\\w#]/", "" , $value);
			return '<span class="bx-font" style="color:' . $value . '">' . $text . '</span>';
		}
		elseif ($attr == 'font')
		{
			$value = preg_replace("/[^\\w\\s\\-,()]/", "" , $value);
			return '<span class="bx-font" style="font-family:' . $value . '">' . $text . '</span>';
		}
		return '';
	}

	public function convert_userfields($matches)
	{
		$vars = get_object_vars($this);
		$vars['TEMPLATE'] = ($this->bMobile ? 'mobile' : $this->type);
		$vars['LAZYLOAD'] = $this->LAZYLOAD;

		$userField = $this->userField;

		$id = $matches[2];

		foreach (GetModuleEvents('main', 'TextParserUserField', true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$id, &$userField, &$vars,  &$this]);
		}

		if ($userField['USER_TYPE']['USER_TYPE_ID'] == 'disk_file' || in_array($id, $userField['VALUE']))
		{
			if (defined('BX_COMP_MANAGED_CACHE'))
			{
				global $CACHE_MANAGER;
				$CACHE_MANAGER->RegisterTag('webdav_element_internal_' . $id);
			}

			return call_user_func_array(
				[$userField['USER_TYPE']['CLASS_NAME'], 'GetPublicViewHTML'],
				[$userField, $id, $matches[3], $vars, $matches]
			);
		}
		return $matches[0];
	}

	public function convert_user($userId = 0, $userName = '')
	{
		static $userTypeList = [];

		if (is_array($userId))
		{
			$userName = $userId[2];
			$userId = $userId[1];
		}

		$userId = (int)$userId;

		$renderParams = [
			'USER_ID' => $userId,
			'USER_NAME' => $userName,
		];

		if ($userId > 0)
		{
			$status = false;

			if (isset($userTypeList[$userId]))
			{
				$status = $userTypeList[$userId];
			}
			else
			{
				if (Loader::includeModule('intranet'))
				{
					$status = \Bitrix\Intranet\Util::getUserStatus($userId);
				}

				$userTypeList[$userId] = $status;
			}

			$pathToUser = (
				!empty($this->userPath)
					? $this->userPath  // forum
					: $this->pathToUser
			);

			if (empty($pathToUser))
			{
				$pathToUser = COption::GetOptionString('main', 'TOOLTIP_PATH_TO_USER', '', SITE_ID);
			}

			switch($status)
			{
				case 'extranet':
					$classAdditional = ' blog-p-user-name-extranet';
					break;
				case 'email':
					$classAdditional = ' blog-p-user-name-email';
					$pathToUser .= '';
					if (
						$this->pathToUserEntityType && $this->pathToUserEntityType != ''
						&& (int)$this->pathToUserEntityId > 0
					)
					{
						$pathToUser .= (!str_contains($pathToUser, '?') ? '?' : '&') . 'entityType=' . $this->pathToUserEntityType . '&entityId=' . intval($this->pathToUserEntityId);
					}
					break;
				case 'collaber':
					$classAdditional = ' blog-p-user-name-collaber';
					break;
				default:
					$classAdditional = '';
			}

			$renderParams = [
				'CLASS_ADDITIONAL' => $classAdditional,
				'PATH_TO_USER' => $pathToUser,
				'USER_ID' => $userId,
				'USER_NAME' => $userName,
			];

			if (
				$status === 'email'
				&& !empty($this->pathToUserEntityType)
				&& !empty($this->pathToUserEntityId)
			)
			{
				$renderParams['TOOLTIP_PARAMS'] = \Bitrix\Main\Web\Json::encode([
					'entityType' => $this->pathToUserEntityType,
					'entityId' => (int)$this->pathToUserEntityId,
				]);
			}
		}

		$res = $this->render_user($renderParams);

		return $this->defended_tags($res);
	}

	protected function render_user($fields)
	{
		$classAdditional = (!empty($fields['CLASS_ADDITIONAL']) ? $fields['CLASS_ADDITIONAL'] : '');
		$pathToUser = (!empty($fields['PATH_TO_USER']) ? $fields['PATH_TO_USER'] : '');
		$userId = (!empty($fields['USER_ID']) ? $fields['USER_ID'] : '');
		$userName = (!empty($fields['USER_NAME']) ? $fields['USER_NAME'] : '');

		$className = $this->useTypography ? $this->tagClasses['mention'] : 'blog-p-user-name';
		if (empty($userId))
		{
			return "<span class=\"{$className}\">{$userName}</span>";
		}

		return '<a class="'. $className . $classAdditional . '"'
			. ' href="' . CComponentEngine::MakePathFromTemplate($pathToUser, ["user_id" => $userId]) . '"'
			. ' bx-tooltip-user-id="' . (!$this->bMobile ? $userId : '') . '"'
			. (!empty($fields['TOOLTIP_PARAMS']) ? ' bx-tooltip-params="' . htmlspecialcharsbx($fields['TOOLTIP_PARAMS']) . '"' : '') . '>'
			. $userName . '</a>';
	}

	public function convert_project(array $matches): string
	{
		static $projectTypeList = [];
		static $extranetProjectIdList = null;

		$projectName = $matches[2];
		$projectId = (int)$matches[1];

		$renderParams = [
			'PROJECT_ID' => $projectId,
			'PROJECT_NAME' => $projectName,
		];

		if ($projectId > 0)
		{
			if ($extranetProjectIdList === null)
			{
				$extranetSiteId = (Loader::includeModule('extranet') ? CExtranet::getExtranetSiteId() : '');
				if (!empty($extranetSiteId))
				{
					$res = \Bitrix\Socialnetwork\WorkgroupSiteTable::getList([
						'filter' => [
							'=SITE_ID' => $extranetSiteId,
						],
						'select' => [ 'GROUP_ID' ],
					]);

					while ($projectSiteFields = $res->fetch())
					{
						$extranetProjectIdList[] = (int)$projectSiteFields['GROUP_ID'];
					}
				}
			}

			$type = false;

			if (isset($projectTypeList[$projectId]))
			{
				$type = $projectTypeList[$projectId];
			}
			else
			{
				if (!empty($extranetProjectIdList) && in_array($projectId, $extranetProjectIdList, true))
				{
					$type = $this->getExternalGroupType($projectId);
				}

				$projectTypeList[$projectId] = $type;
			}

			$pathToProject = $this->getGroupPath($projectId, (array)$extranetProjectIdList);

			switch ($type)
			{
				case 'extranet':
					$classAdditional = ' blog-p-user-name-extranet';
					break;
				case 'collab':
					$classAdditional = ' blog-p-user-name-collab';
					break;
				default:
					$classAdditional = '';
			}

			$renderParams['CLASS_ADDITIONAL'] = $classAdditional;
			$renderParams['PATH_TO_PROJECT'] = $pathToProject;
		}

		$res = $this->render_project($renderParams);

		return $this->defended_tags($res);
	}

	protected function render_project($fields): string
	{
		$classAdditional = ($fields['CLASS_ADDITIONAL'] ?? '');
		$pathToProject = ($fields['PATH_TO_PROJECT'] ?? '');
		$projectId = (int)($fields['PROJECT_ID'] ?? 0);
		$projectName = (string)($fields['PROJECT_NAME'] ?? '');

		$className = $this->useTypography ? $this->tagClasses['mention'] : 'blog-p-user-name';
		if ($projectId <= 0)
		{
			return "<span class=\"{$className}\">{$projectName}</span>";
		}

		return '<a class="' . $className . $classAdditional . '" href="' . CComponentEngine::MakePathFromTemplate($pathToProject, [ 'group_id' => $projectId ]) . '" >' . $projectName . '</a>';
	}

	public function convert_department(array $matches): string
	{
		static $pathToDepartment = null;

		$departmentName = $matches[2];
		$departmentId = (int)$matches[1];

		$renderParams = [
			'DEPARTMENT_ID' => $departmentId,
			'DEPARTMENT_NAME' => $departmentName,
		];

		if ($departmentId > 0)
		{
			if ($pathToDepartment === null)
			{
				// then replace to \Bitrix\Socialnetwork\Helper\Path::get('department_path_template')
				$pathToDepartment = Option::get('main', 'TOOLTIP_PATH_TO_CONPANY_DEPARTMENT', SITE_DIR . 'company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#', SITE_ID);
			}

			$renderParams['PATH_TO_DEPARTMENT'] = $pathToDepartment;
		}

		$res = $this->render_department($renderParams);

		return $this->defended_tags($res);
	}

	protected function render_department($fields): string
	{
		$pathToDepartment = ($fields['PATH_TO_DEPARTMENT'] ?? '');
		$departmentId = (int)($fields['DEPARTMENT_ID'] ?? 0);
		$departmentName = (string)($fields['DEPARTMENT_NAME'] ?? '');

		$className = $this->useTypography ? $this->tagClasses['mention'] : 'blog-p-user-name';
		if ($departmentId <= 0)
		{
			return "<span class=\"{$className}\">{$departmentName}</span>";
		}

		return '<a class="' . $className . '" href="' . CComponentEngine::MakePathFromTemplate($pathToDepartment, [ 'ID' => $departmentId ]) . '" >' . $departmentName . '</a>';
	}

	public function getTagPattern()
	{
		return $this->tagPattern . 'u';
	}

	public static function cleanTag($tag)
	{
		return trim(html_entity_decode(str_replace('&nbsp;', ' ', $tag), (ENT_COMPAT | ENT_HTML401), SITE_CHARSET));
	}

	public function detectTags($text)
	{
		$result = [];

		$text = str_replace("\xC2\xA0", ' ', $text);

		if (preg_match_all($this->getTagPattern(), ' ' . $text, $matches))
		{
			$result = array_unique($matches[2]);
		}

		$result = array_map(
			function($tag) { return CTextParser::cleanTag($tag); },
			$result
		);

		$result = array_filter(
			$result,
			function($tag) { return $tag != ''; }
		);

		return $result;
	}

	public function convert_tag($tag = [])
	{
		$res = '';

		if (
			!is_array($tag)
			|| $tag[2] == ''
		)
		{
			return $res;
		}

		$tagText = self::cleanTag($tag[2]);

		if ($tagText == '')
		{
			return $tag[0];
		}

		$res = htmlentities($tagText, (ENT_COMPAT | ENT_HTML401), SITE_CHARSET);

		$className = $this->useTypography ? $this->tagClasses['hashtag'] : 'bx-inline-tag';
		$res = '<span class="' . $className . '" bx-tag-value="' . $res . '">#' . $res . '</span>';

		return $tag[1].$this->defended_tags($res);
	}

	// Only for public using
	public function wrap_long_words($text = '')
	{
		if ($this->maxStringLen > 0 && !empty($text))
		{
			$text = str_replace([chr(11), chr(12), chr(34), chr(39)], ["", "", chr(11), chr(12)], $text);
			$text = preg_replace_callback("/(?<=^|>)([^<]+)(?=<|$)/isu", [$this, "partWords"], $text);
			$text = str_replace([chr(11), chr(12)], [chr(34), chr(39)], $text);
		}
		return $text;
	}

	public function partWords($matches)
	{
		return $this->part_long_words($matches[1]);
	}

	public function part_long_words($str)
	{
		$word_separator = $this->wordSeparator;
		if (($this->maxStringLen > 0) && (trim($str) != ''))
		{
			$str = str_replace(
				[chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8),
					"&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "&copy;", "&reg;", "&trade;",
					chr(34), chr(39),
				],
				["", "", "", "", "", "", "", "",
					chr(1), "<", ">", chr(2), chr(3), chr(4), chr(5), chr(6),
					chr(7), chr(8),
				],
				$str
			);
			$str = preg_replace_callback(
				"/(?<=[" . $word_separator . "]|^)(([^" . $word_separator . "]+))(?=[" . $word_separator . "]|$)/isu",
				[$this, "cutWords"],
				$str
			);
			$str = str_replace(
				[chr(1), "<", ">", chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8), "&lt;WBR/&gt;", "&lt;WBR&gt;", "&amp;shy;"],
				["&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "&copy;", "&reg;", "&trade;", chr(34), chr(39), "<WBR/>", "<WBR/>", "&shy;"],
				$str
			);
		}
		return $str;
	}

	public function cutWords($matches)
	{
		return $this->cut_long_words($matches[2]);
	}

	public function cut_long_words($str)
	{
		if (($this->maxStringLen > 0) && ($str != ''))
		{
			$str = preg_replace("/([^ \n\r\t\x01]{" . $this->maxStringLen . "})/isu", "\\1<WBR/>&shy;", $str);
		}
		return $str;
	}

	protected function parseSpaces($matches)
	{
		if ($matches[1] != '')
		{
			return preg_replace("/\x20{2}/", "\x20&nbsp;", $matches[1]);
		}
		return $matches[1];
	}

	public function convertAnchor($matches, $attributes = [])
	{
		return $this->convert_anchor_tag($matches[1], (!empty($matches[2]) ? $matches[2] : $matches[1]), $attributes);
	}

	public function convert_anchor_tag($url, $text, $attributes = [])
	{
		$url = trim(str_replace(['[nomodify]', '[/nomodify]'], '', $url));
		$text = trim(str_replace(['[nomodify]', '[/nomodify]'], '', $text));
		$text = ($text == '' ? $url : $text);

		$bTextUrl = ($text == $url);
		$bShortUrl = (($this->allow['SHORT_ANCHOR'] ?? '') == 'Y');

		$text = str_replace("\\\"", "\"", $text);
		$postfix = "";
		$pattern = "/([.,?!;]|&#33;)$/u";
		if ($bTextUrl && preg_match($pattern, $url, $match))
		{
			$postfix = $match[1];
			$url = preg_replace($pattern, '', $url);
			$text = preg_replace($pattern, '', $text);
		}

		$url = preg_replace(
			[
				"/&amp;/u",
				"/javascript:/iu",
				"/[" . chr(12) . "']/u",
				"/&#91;/u",
				"/&#93;/u",
			],
			[
				"&",
				"java script&#58; ",
				"%27",
				"[",
				"]",
			],
			$url
		);

		if (!str_starts_with($url, '/') && !preg_match("/^(" . $this->getAnchorSchemes() . "):/iu", $url))
		{
			$url = 'http://' . $url;
		}
		$text = preg_replace(
			["/&amp;/iu", "/javascript:/iu"],
			["&", "javascript&#58; "],
			$text
		);

		if ($bShortUrl &&
			mb_strlen($text) > $this->maxAnchorLength &&
			preg_match("/^(" . $this->getAnchorSchemes() . "):\\/\\/(\\S+)$/iu", $text, $matches))
		{
			$uri_type = $matches[1];
			$stripped = $matches[2];
			$text = $uri_type . '://' . (mb_strlen($stripped) > $this->maxAnchorLength ?
				mb_substr($stripped, 0, $this->maxAnchorLength - 10) . '...' . mb_substr($stripped, -10) :
				$stripped
			);
		}

		if ($this->anchorType === 'bbcode')
		{
			if ($url === $text)
			{
				$link = '[URL]' . $url . '[/URL]';
			}
			else
			{
				$link = '[URL=' . $url . ']' . $text . '[/URL]';
			}
		}
		else
		{
			$url = $this->defended_tags(htmlspecialcharsbx($url, ENT_COMPAT, false));

			if (!str_contains($text, "<\017"))
			{
				// it could be "defended" tag inside URL code
				$text = htmlspecialcharsbx($text, ENT_COMPAT, false);
			}

			$noFollowAttribute = $this->parser_nofollow == 'Y'? ' rel="nofollow"': '';

			$className = $this->useTypography ? ' class="' . $this->tagClasses['url'] . '"' : '';

			$link = '<a' . $className . ' href="' . $url . '" target="' . $this->link_target . '"' . $noFollowAttribute . $this->convertAttributes($attributes) . ' >' . $text . '</a>';

			if ($noFollowAttribute)
			{
				$link = '<noindex>' . $link . '</noindex>';
			}
		}

		return $link . $postfix;
	}

	protected function convertAttributes($attributes)
	{
		$result = '';
		if (!is_array($attributes))
		{
			return $result;
		}

		foreach ($attributes as $key => $value)
		{
			if (preg_match('#[^a-zA-Z\d-]#', $key))
			{
				continue;
			}

			$result .= ' ' . $key . '="' . htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($value)). '"';
		}

		return $result;
	}

	private function preconvertUrl($matches, $attributes = [])
	{
		return $this->pre_convert_anchor_tag($matches[0], $matches[0], '[url]' . $matches[0] . '[/url]', $attributes);
	}

	public function preconvertAnchor($matches, $attributes = [])
	{
		return $this->pre_convert_anchor_tag($matches[1], $matches[2] ?? '', $matches[0] ?? '', $attributes);
	}

	public function pre_convert_anchor_tag($url, $text = '', $str = '', $attributes = [])
	{
		if (stripos($str, '[url') !== 0)
		{
			$url = $str;
		}
		elseif ($text != '')
		{
			$url = str_replace(['[', ']'], ['%5B', '%5D'], $url);
			$url = '[url=' . $url . ']' . $text . '[/url]';
		}
		else
		{
			$url = '[url]' . $url . '[/url]';
		}

		if (isset($this->defended_urls[$url]))
		{
			return $this->defended_urls[$url];
		}
		else
		{
			$tag = "<\x18#" . count($this->defended_urls) . " " . $this->convertAttributes($attributes) . " " . ">";
			$this->defended_urls[$url] = $tag;

			return $tag;
		}
	}

	public function post_convert_anchor_tag($str)
	{
		if (!empty($this->defended_urls))
		{
			return str_replace(array_reverse(array_values($this->defended_urls)), array_reverse(array_keys($this->defended_urls)), $str);
		}
		return $str;
	}

	public function strip_words($string, $count)
	{
		$splice_pos = null;

		$ar = preg_split("/(<.*?>|\\s+)/s", $string, -1, PREG_SPLIT_DELIM_CAPTURE);
		foreach ($ar as $i => $s)
		{
			if (!str_starts_with($s, '<'))
			{
				$count -= mb_strlen($s);
				if ($count <= 0)
				{
					$splice_pos = $i;
					break;
				}
			}
		}

		if (isset($splice_pos))
		{
			array_splice($ar, $splice_pos+1);
			return implode('', $ar);
		}
		return $string;
	}

	public static function closeTags($html)
	{
		preg_match_all("#<([a-z0-9]+)([^>]*)(?<!/)>#iu", $html, $result);
		$openedtags = array_map('strtolower', $result[1]);

		preg_match_all("#</([a-z0-9]+)>#iu", $html, $result);
		$closedtags = array_map('strtolower', $result[1]);

		$len_opened = count($openedtags);

		if (count($closedtags) == $len_opened)
		{
			return $html;
		}

		$openedtags = array_reverse($openedtags);

		static $tagsWithoutClose = ['input'=>1, 'img'=>1, 'br'=>1, 'hr'=>1, 'meta'=>1, 'area'=>1, 'base'=>1, 'col'=>1, 'embed'=>1, 'keygen'=>1, 'link'=>1, 'param'=>1, 'source'=>1, 'track'=>1, 'wbr'=>1];

		for ($i = 0; $i < $len_opened; $i++)
		{
			if (isset($tagsWithoutClose[$openedtags[$i]]))
			{
				continue;
			}
			if (!in_array($openedtags[$i], $closedtags))
			{
				$html .= '</' . $openedtags[$i] . '>';
			}
			else
			{
				unset($closedtags[array_search($openedtags[$i], $closedtags)]);
			}
		}

		return $html;
	}

	public static function clearAllTags($text)
	{
		$text = strip_tags(trim($text));
		if ($text == '')
		{
			return '';
		}

		if (mb_stripos($text, '<cut') !== false || mb_stripos($text, '[cut') !== false)
		{
			$text = preg_replace([
				"/^(.+?)<cut(.*?)>/isu",
				"/^(.+?)\\[cut(.*?)]/isu",
			], "\\1", $text);
		}
		if (mb_stripos($text, '[quote') !== false)
		{
			while (preg_match("/\\[(?:quote)(?:.*?)](.*?)\\[\\/quote(.*?)]/isu", $text))
			{
				$text = preg_replace(
					[
						"/\\[quote(?:.*?)](.*?)\\[\\/quote(.*?)]/isu",
						"/<quote(?:.*?)>(.*?)<\\/quote(.*?)>/isu",
					],
					"\"\\1\"",
					$text
				);
			}
		}

		$text = preg_replace("/\\[url\\s*=\\s*(\\S+?)\\s*](.*?)\\[\\/url]/isu", "\\2", $text);

		$arPattern = [];
		$arReplace = [];

		$arPattern[] = "/\\<WBR[\\s\\/]?\\>/isu";
		$arReplace[] = "";

		$arPattern[] = "/^(\r|\n)+?(.*)$/";
		$arReplace[] = "\\2";

		$arPattern[] = "/\\<(\\/?)(code|font|color|video)(.*?)\\>/isu";
		$arReplace[] = "";
		$arPattern[] = "/\\[\\/td(.*?)\\]\\[td(.*?)\\]/isu";
		$arReplace[] = " ";
		$arPattern[] = "/\\[(\\/?)(p|b|i|u|s|list|code|quote|size|font|color|url|img|video|td|tr|table|file|document id|disk file id|user|project|left|right|center|justify|\\*)(.*?)\\]/isu";
		$arReplace[] = "";

		return preg_replace($arPattern, $arReplace, $text);
	}

	public function html_cut($html, $size)
	{
		$symbols = strip_tags($html);
		$symbols_len = mb_strlen($symbols);

		if ($symbols_len < mb_strlen($html))
		{
			$strip_text = $this->strip_words($html, $size);

			if ($symbols_len > $size)
			{
				$strip_text = $strip_text . '...';
			}

			$final_text = $this->closetags($strip_text);
		}
		elseif ($symbols_len > $size)
		{
			$final_text = mb_substr($html, 0, $size) . '...';
		}
		else
		{
			$final_text = $html;
		}

		return $final_text;
	}

	public function convertHTMLToBB($html = '', $allow = null)
	{
		if (empty($html))
		{
			return $html;
		}

		$handler = AddEventHandler('main', 'TextParserBeforeTags', ['CTextParser', 'TextParserHTMLToBBHack']);

		$this->allow = array_merge(
			is_array($allow) ? $allow : [
				'ANCHOR' => 'Y',
				'BIU' => 'Y',
				'IMG' => 'Y',
				'QUOTE' => 'Y',
				'CODE' => 'Y',
				'FONT' => 'Y',
				'LIST' => 'Y',
				'SMILES' => 'Y',
				'NL2BR' => 'Y',
				'VIDEO' => 'Y',
				'TABLE' => 'Y',
				'ALIGN' => 'Y',
				'P' => 'Y',
			],
			['HTML' => 'N']
		);

		$html = $this->convertText($html);

		$html = preg_replace("/<br\s*\\/*>/isu", "\n", $html);
		$html = preg_replace("/&nbsp;/isu", '', $html);

		RemoveEventHandler('main', 'TextParserBeforeTags', $handler);

		return $html;
	}

	public static function TextParserHTMLToBBHack($text, $TextParser)
	{
		// Workaround for the wrong default value (see above 'TODO: change to N')
		$TextParser->allow = [
			'P' => 'N',
		];

		return true;
	}

	private static function trimLineBreaks(string $text): string
	{
		return preg_replace("/^\r?\n|\r?\n$/", '', $text);
	}

	private function getExternalGroupType(int $groupId): string
	{
		if (
			!Loader::includeModule('socialnetwork')
			|| !class_exists(GroupProvider::class)
		)
		{
			return 'extranet';
		}

		return GroupProvider::getInstance()->getGroupType($groupId) === Type::Collab ? 'collab' : 'extranet';
	}

	private function getGroupPath(int $groupId, array $extranetGroupIds): string
	{
		$path = Option::get('socialnetwork', 'group_path_template', SITE_DIR . 'workgroups/group/#group_id#/', SITE_ID);

		if (
			!Loader::includeModule('socialnetwork')
			|| !class_exists(UrlManager::class)
		)
		{
			return $path;
		}

		if (!in_array($groupId, $extranetGroupIds, true))
		{
			return $path;
		}

		$type = GroupProvider::getInstance()->getGroupType($groupId);
		if ($type !== Type::Collab)
		{
			return $path;
		}

		$chatId = Workgroup::getChatData(['group_id' => $groupId])[$groupId] ?? null;

		return UrlManager::getCollabUrlById($groupId, ['chatId' => $chatId]);
	}
}
