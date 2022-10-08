<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Fileman\Block;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\DOM\Document;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\DOM\CssParser;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);

class Editor
{
	CONST SLICE_SECTION_ID = 'BX_BLOCK_EDITOR_EDITABLE_SECTION';
	CONST BLOCK_PLACE_ATTR = 'data-bx-block-editor-place';
	CONST BLOCK_PHP_ATTR = 'data-bx-editor-php-slice';
	CONST STYLIST_TAG_ATTR = 'data-bx-stylist-container';
	CONST BLOCK_PLACE_ATTR_DEF_VALUE = 'body';
	CONST BLOCK_COUNT_PER_PAGE = 14;

	public $id;
	protected $site;
	protected $url;
	protected $previewUrl;
	protected $saveFileUrl;
	protected $templateType;
	protected $templateId;
	protected $charset;
	protected $isTemplateMode;
	protected $isUserHavePhpAccess;
	protected $useLightTextEditor;
	protected $ownResultId;

	/*
	 * block list
	*/
	public $tools = array();

	/*
	 * block list
	*/
	public $blocks = array();

	protected $componentFilter = array();

	public $componentsAsBlocks = array();

	public $previewModes = array();

	public $tabs = array();

	public $uiPatterns = array(
		'main' => <<<HTML
		#TEXTAREA#
		<div id="bx-block-editor-container-#id#" class="bx-block-editor-container">
			<div class="button-panel">
				#tabs#

				<span class="bx-editor-block-btn-close" title="#MESS_BTN_MIN#">#MESS_BTN_MIN#</span>
				<span class="bx-editor-block-btn-full" title="#MESS_BTN_MAX#">#MESS_BTN_MAX#</span>
				<span data-role="block-editor-tab-btn-get-html" class="bx-editor-block-btn-full bx-editor-block-btn-html-copy" title="#MESS_BTN_HTML_COPY#"></span>
			</div>
			#panels#
		</div>
HTML
		,
		'block' => <<<HTML
		<li data-bx-block-editor-block-status="blank"
			data-bx-block-editor-block-type="#code#"
			class="bx-editor-typecode-#code_class# bx-editor-type-#type_class# bx-block-editor-i-block-list-item"
			title="#desc#"
			>
			<span class="bx-block-editor-i-block-list-item-icon"></span>
			<span class="bx-block-editor-i-block-list-item-name">#name#</span>
		</li>
HTML
		,
		'block_page' => <<<HTML
		<ul class="bx-block-editor-i-block-list">
			#blocks#
		</ul>
HTML
		,
		'tool' => <<<HTML
		<div class="bx-editor-block-tools" data-bx-editor-tool="#group#:#id#">
			<div class="caption">#name#:</div>
			<div class="item">#html#</div>
		</div>
HTML
		,
		'device' => <<<HTML
		<div class="device #class#" data-bx-preview-device-class="#class#" data-bx-preview-device-width="#width#" data-bx-preview-device-height="#height#">
			<span>#MESS_NAME#</span>
		</div>
HTML
		,
		'tab' => <<<HTML
			<span class="bx-editor-block-btn bx-editor-block-btn-#code# #tab_active#">#name#</span>
HTML
		,
		'tab_active' => 'bx-editor-block-btn-active'
		,
		'panel' => <<<HTML
			<div class="bx-editor-block-panel #code#-panel" #panel_hidden#>#html#</div>
HTML
		,
		'panel_hidden' => 'style="display: none;"'
		,
		'panel-edit' => <<<HTML
			<div class="visual-part">
				<div class="shadow">
					<div class="edit-text"></div>
				</div>
				<iframe id="bx-block-editor-iframe-#id#" src="" style="border: none;" width="100%" height="100%"></iframe>
			</div>
			<div class="dialog-part">
				<div style="overflow-x: hidden;">
					<div class="block-list-cont">
						<div class="block-list-tabs">

							<div class="bx-editor-block-tabs">
								<span class="tab-list">
									<span class="tab blocks active">#MESS_BLOCKS#</span>
									<span class="tab styles">#MESS_STYLES#</span>
								</span>
							</div>

							<div class="edit-panel-tabs-style">
								<ul class="bx-block-editor-i-place-list" data-bx-place-name="item"></ul>
							</div>
							<div style="clear: both;"></div>

							<div class="edit-panel-tabs-block">

								<div>#blocks#</div>

								<div style="clear: both;"></div>
								<div class="block-pager adm-nav-pages-block">
									<span class="adm-nav-page adm-nav-page-prev #nav-display#"></span>
									<span class="adm-nav-page adm-nav-page-next #nav-display#"></span>
								</div>

							</div>


							<div style="clear: both;"></div>
						</div>
						<div>

						</div>
					</div>
				</div>
			</div>
			<div class="block-edit-cont">
				<div class="bx-editor-block-form-head">
					<div class="bx-editor-block-form-head-btn">
						<a class="bx-editor-block-tools-btn bx-editor-block-tools-close" title="#MESS_TOOL_SAVE_TITLE#">#MESS_TOOL_SAVE#</a>
						<a class="bx-editor-block-tools-btn bx-editor-block-tools-cancel" title="#MESS_TOOL_CANCEL_TITLE#">#MESS_TOOL_CANCEL#</a>
					</div>

					<div class="block-edit-tabs">
						<div class="block-edit-tabs-inner">
							<span data-bx-block-editor-settings-tab="cont" class="bx-editor-block-tab active">#MESS_TOOL_CONTENT#</span>
							<span data-bx-block-editor-settings-tab="style" class="bx-editor-block-tab">#MESS_TOOL_STYLES#</span>
							<span data-bx-block-editor-settings-tab="prop" class="bx-editor-block-tab">#MESS_TOOL_SETTINGS#</span>
						</div>
					</div>
				</div>

				<div class="block-edit-form-empty">
					#MESS_TOOL_EMPTY#
				</div>

				<div class="block-edit-form">
					#tools#
				</div>
			</div>
HTML
		,
		'panel-preview' => <<<HTML
		<div class="bx-block-editor-preview-container">
			<div class="shadow">
				<div class="edit-text"></div>
				<div class="error-text">#MESS_ACCESS_DENIED#</div>
			</div>
			<div class="devices">
				#devices#
			</div>

			<center>
				<div class="iframe-wrapper">
					<iframe sandbox="allow-same-origin allow-forms" class="preview-iframe" src=""></iframe>
				</div>
			</center>
		</div>

		<div style="clear:both;"></div>
HTML
		,
		'panel-get-html' => <<<HTML
		<textarea style="width: 100%; height: 100%; min-height: 400px;" onfocus="this.select()"></textarea>
HTML
	);

	/**
	 * Return editor object
	 *
	 * @param array $params
	 * @return Editor
	 */
	public static function createInstance($params)
	{
		return new static($params);
	}

	/**
	 * Create editor object.
	 *
	 * @param array $params
	 */
	public function __construct($params)
	{
		$this->id = $params['id'];
		$this->url = $params['url'];
		$this->previewUrl = isset($params['previewUrl']) ? $params['previewUrl'] : '/bitrix/admin/fileman_block_editor.php?action=preview';
		$this->saveFileUrl = isset($params['saveFileUrl']) ? $params['saveFileUrl'] : '/bitrix/admin/fileman_block_editor.php?action=save_file';
		$this->templateType = $params['templateType'];
		$this->templateId = $params['templateId'];
		$this->site = $params['site'];
		$this->charset = $params['charset'];
		$this->isTemplateMode = isset($params['isTemplateMode']) ? (bool) $params['isTemplateMode'] : false;
		$this->useLightTextEditor = isset($params['useLightTextEditor']) ? (bool) $params['useLightTextEditor'] : false;
		$this->isUserHavePhpAccess = isset($params['isUserHavePhpAccess']) ? (bool) $params['isUserHavePhpAccess'] : false;
		$this->ownResultId = isset($params['own_result_id']) ? $params['own_result_id'] : true;

		$this->componentFilter = isset($params['componentFilter']) ? $params['componentFilter'] : array();
		$this->setToolList($this->getDefaultToolList());

		$this->previewModes = array(
			array('CLASS' => 'phone', 'NAME' => Loc::getMessage('BLOCK_EDITOR_PREVIEW_MODE_PHONE'), 'WIDTH' => 320, 'HEIGHT' => 480),
			array('CLASS' => 'tablet', 'NAME' => Loc::getMessage('BLOCK_EDITOR_PREVIEW_MODE_TABLET'), 'WIDTH' => 768, 'HEIGHT' => 1024),
			array('CLASS' => 'desktop', 'NAME' => Loc::getMessage('BLOCK_EDITOR_PREVIEW_MODE_DESKTOP'), 'WIDTH' => 1024, 'HEIGHT' => 768),
		);

		$this->tabs = array(
			'edit' => array('NAME' => Loc::getMessage('BLOCK_EDITOR_TABS_EDIT'), 'ACTIVE' => true),
			'preview' => array('NAME' => Loc::getMessage('BLOCK_EDITOR_TABS_PREVIEW'), 'ACTIVE' => false),
			//'get-html' => array('NAME' => Loc::getMessage('BLOCK_EDITOR_TABS_HTML'), 'ACTIVE' => false),
		);
	}


	/**
	 * Set custom blocks
	 *
	 * @param array $blocks
	 * @return void
	 */
	public function setBlockList(array $blocks)
	{
		$this->blocks = $blocks;

		if(!is_array($this->blocks))
		{
			$this->blocks = array();
		}

		foreach($this->blocks as $key => $block)
		{
			if(!isset($block['TYPE']))
			{
				$block['TYPE'] = $block['CODE'];
			}

			$block['IS_COMPONENT'] = false;
			$block['CLASS'] = $block['CODE'];
			$this->blocks[$key] = $block;
		}

		$componentsNotAsBlocks = array();
		if (!$this->useLightTextEditor)
		{
			$componentList = $this->getComponentList();
			foreach($componentList as $component)
			{
				if(!isset($this->componentsAsBlocks[$component['NAME']]))
				{
					$componentsNotAsBlocks[] = array(
						'TYPE' => 'component',
						'IS_COMPONENT' => true,
						'CODE' => $component['NAME'],
						'NAME' => $component['TITLE'],
						'DESC' => $component['TITLE'] . ".\n" . $component['DESCRIPTION'],
						'HTML' => ''
					);
				}
				else
				{
					$interfaceName = $this->componentsAsBlocks[$component['NAME']]['NAME'];
					$this->blocks[] = array(
						'TYPE' => 'component',
						'IS_COMPONENT' => false,
						'CODE' => $component['NAME'],
						'NAME' => $interfaceName ? $interfaceName : $component['TITLE'],
						'DESC' => $component['DESCRIPTION'],
						'HTML' => ''
					);
				}
			}
		}
		$this->blocks = array_merge($this->blocks, $componentsNotAsBlocks);

	}

	/**
	 * Set custom tools
	 *
	 * @param array $tools
	 * @return void
	 */
	public function setToolList(array $tools)
	{
		$this->tools = $tools;
	}

	/**
	 * Return list of default blocks
	 *
	 * @return array
	 */
	public function getDefaultBlockList()
	{
		return array(
			array(
				'CODE' => 'text',
				'NAME' => Loc::getMessage('BLOCK_EDITOR_BLOCK_TEXT_NAME'),
				'DESC' => Loc::getMessage('BLOCK_EDITOR_BLOCK_TEXT_DESC'),
				'HTML' => Loc::getMessage('BLOCK_EDITOR_BLOCK_TEXT_EXAMPLE')
			),
		);
	}

	/**
	 * Return true if can use Russian services.
	 *
	 * @return bool
	 */
	public static function isAvailableRussian()
	{
		if (!Loader::includeModule('bitrix24'))
		{
			return true;
		}

		return in_array(\CBitrix24::getPortalZone(), array('ru', 'kz', 'by'));
	}

	/**
	 * Return list of default tools, uses for block changing
	 *
	 * @return array
	 */
	public function getDefaultToolList()
	{
		$isUserHavePhpAccess = $this->isUserHavePhpAccess;
		$useLightTextEditor = $this->useLightTextEditor;


		$resultList = array();

		$resultList[] = array(
			'GROUP' => 'cont',
			'ID' => 'html-raw',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_HTML_RAW'),
			'HTML' => '<textarea style="width:600px; height: 400px;" data-bx-editor-tool-input="item"></textarea>',
		);

		$resultList[] = array(
			'GROUP' => 'cont',
			'ID' => 'src',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_SRC'),
			'HTML' => '<input type="hidden" data-bx-editor-tool-input="item" value="">'
				. \Bitrix\Main\UI\FileInput::createInstance((array(
					"id" => "BX_BLOCK_EDITOR_SRC_" . $this->id,
					"name" => "NEW_FILE_EDITOR[n#IND#]",
					"upload" => true,
					"medialib" => true,
					"fileDialog" => true,
					"cloud" => true
				)))->show()
		);

		$resultList[] = array(
			'GROUP' => 'cont',
			'ID' => 'title',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_TITLE'),
			'HTML' => Tools::getControlInput(),
		);

		$resultList[] = array(
			'GROUP' => 'cont',
			'ID' => 'href',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_HREF'),
			'HTML' => Tools::getControlInput(),
		);

		\Bitrix\Main\Loader::includeModule('fileman');
		ob_start();
		?>
		<div class="column" data-bx-editor-column="item">
			<?for ($columnNumber = 1; $columnNumber < 5; $columnNumber++):?>
			<span data-bx-editor-column-number="<?=$columnNumber?>"
				style="display: none;">
				<?=Loc::getMessage('BLOCK_EDITOR_TOOL_COLUMN')?> <?=$columnNumber?>
			</span>
			<?endfor;?>
		</div>
		<?
		if ($useLightTextEditor)
		{
			echo '<div style="color: #bfbfbf; font-size: 17px; padding: 0 0; position: relative;">';

			$editor = new \CHTMLEditor;
			$res = array_merge(
				array(
					'height' => 400,
					'minBodyWidth' => 350,
					'normalBodyWidth' => 555,
					'bAllowPhp' => false,
					'limitPhpAccess' => false,
					'showTaskbars' => false,
					'showNodeNavi' => false,
					'askBeforeUnloadPage' => true,
					'useFileDialogs' => !IsModuleInstalled('intranet'),
					'bbCode' => false,
					'siteId' => SITE_ID,
					'autoResize' => false,
					'autoResizeOffset' => 40,
					'saveOnBlur' => true,
					'controlsMap' => array(
						array('id' => 'placeholder_selector',  'compact' => true, 'sort' => 60),
						array('id' => 'StyleSelector',  'compact' => true, 'sort' => 70),
						array('id' => 'Bold',  'compact' => true, 'sort' => 80),
						array('id' => 'Italic',  'compact' => true, 'sort' => 90),
						array('id' => 'Underline',  'compact' => true, 'sort' => 100),
						array('id' => 'Strikeout',  'compact' => true, 'sort' => 110),
						array('id' => 'RemoveFormat',  'compact' => true, 'sort' => 120),
						array('id' => 'Color',  'compact' => true, 'sort' => 130),
						array('id' => 'FontSelector',  'compact' => false, 'sort' => 135),
						array('id' => 'FontSize',  'compact' => false, 'sort' => 140),
						//array('separator' => true, 'compact' => false, 'sort' => 145),
						array('id' => 'OrderedList',  'compact' => true, 'sort' => 150),
						array('id' => 'UnorderedList',  'compact' => true, 'sort' => 160),
						array('id' => 'AlignList', 'compact' => false, 'sort' => 190),
						//array('separator' => true, 'compact' => false, 'sort' => 200),
						array('id' => 'InsertLink',  'compact' => true, 'sort' => 210),
						//array('id' => 'InsertImage',  'compact' => false, 'sort' => 220),
						//array('id' => 'InsertVideo',  'compact' => true, 'sort' => 230, 'wrap' => 'bx-b-video-'.$arParams["FORM_ID"]),
						//array('id' => 'InsertTable',  'compact' => false, 'sort' => 250),
						//array('id' => 'Code',  'compact' => true, 'sort' => 260),
						//array('id' => 'Quote',  'compact' => true, 'sort' => 270, 'wrap' => 'bx-b-quote-'.$arParams["FORM_ID"]),
						//array('id' => 'Smile',  'compact' => false, 'sort' => 280),
						//array('separator' => true, 'compact' => false, 'sort' => 290),
						array('id' => 'RemoveFormat',  'compact' => false, 'sort' => 310),
						array('id' => 'Fullscreen',  'compact' => false, 'sort' => 320),
						array('id' => 'BbCode',  'compact' => true, 'sort' => 340),
						array('id' => 'More',  'compact' => true, 'sort' => 400)
					)
				),
				array(
					'name' => 'BX_BLOCK_EDITOR_CONTENT_' . $this->id,
					'id' => 'BX_BLOCK_EDITOR_CONTENT_' . $this->id,
					'width' => '100%',
					'arSmilesSet' => array(),
					'arSmiles' => array(),
					'content' => '',
					'fontSize' => '14px',
					'iframeCss' =>
						'.bx-spoiler {border:1px solid #cecece;background-color:#f6f6f6;padding: 8px 8px 8px 24px;color:#373737;border-radius:var(--ui-border-radius-sm, 2px);min-height:1em;margin: 0;}',
				)
			);
			$editor->Show($res);

			echo '</div>';
		}
		else
		{
			\CFileMan::AddHTMLEditorFrame(
				'BX_BLOCK_EDITOR_CONTENT_' . $this->id,
				'',
				false,
				"html",
				array(
					'height' => '200',
					'width' => '100%'
				),
				"N",
				0,
				"",
				'',//'data-bx-editor-tool-input="content"',
				false,
				!$isUserHavePhpAccess,
				false,
				array(
					//'templateID' => $str_SITE_TEMPLATE_ID,
					'componentFilter' => $this->componentFilter,
					'limit_php_access' => !$isUserHavePhpAccess,
					'hideTypeSelector' => true,
					'minBodyWidth' => '420',
					'normalBodyWidth' => '420',
				)
			);
		}

		$resultList[] = array(
			'GROUP' => 'cont',
			'ID' => 'content',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_CONTENT'),
			'HTML' => '<input type="hidden" data-bx-editor-tool-input="item" value="">' . ob_get_clean()
		);

		ob_start();
		?>
		<script type="text/template" id="template-social-item">
			<table style="background-color: #E9E9E9;">
				<tr>
					<td><?=Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT_ADDRESS')?></td>
					<td>
						<input class="href" type="text" value="#href#">
						<select class="preset">
							<option value=""><?=Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT_SELECT')?></option>
							<option value="http://#SERVER_NAME#/"><?=Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT_OURSITE')?></option>
							<?if (self::isAvailableRussian()):?>
								<option value="http://vk.com/"><?=Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT_VK')?></option>
								<option value="http://ok.ru/"><?=Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT_OK')?></option>
							<?endif;?>
							<option value="http://facebook.com/"><?=Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT_FACEBOOK')?></option>
							<option value="http://instagram.com/"><?=Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT_INSTAGRAM')?></option>
							<option value="http://twitter.com/"><?=Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT_TWITTER')?></option>
							<option value="http://"><?=Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT_SITE')?></option>
							<option value="mailto:"><?=Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT_EMAIL')?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td><?=Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT_NAME')?></td>
					<td>
						<input class="name" type="text" value="#name#">
						<input class="delete" type="button" value="<?=Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT_BTN_DELETE')?>">
					</td>
				</tr>
			</table>
			<br/>
		</script>
		<div class="container"></div>
		<input class="add" type="button" value="<?=Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT_BTN_ADD')?>">
		<?
		$resultList[] = array(
			'GROUP' => 'cont',
			'ID' => 'social_content',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_SOCIAL_CONTENT'),
			'HTML' => '<input type="hidden" data-bx-editor-tool-input="item" value="">' . ob_get_clean()
		);

		$resultList[] = array(
			'GROUP' => 'cont',
			'ID' => 'button_caption',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_BUTTON_CAPTION'),
			'HTML' => Tools::getControlInput(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'font-size',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_FONT_SIZE'),
			'HTML' => Tools::getControlFontSize(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'text-align',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_TEXT_ALIGN'),
			'HTML' => Tools::getControlTextAlign(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'border',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_BORDER'),
			'HTML' => '<input type="hidden" data-bx-editor-tool-input="item" id="block_editor_style_border">
				<select id="block_editor_style_border_style">
					<option value="">' . Loc::getMessage('BLOCK_EDITOR_COMMON_NO') . '</option>
					<option value="solid">' . Loc::getMessage('BLOCK_EDITOR_TOOL_BORDER_SOLID') . '</option>
					<option value="dashed">' . Loc::getMessage('BLOCK_EDITOR_TOOL_BORDER_DASHED') . '</option>
					<option value="dotted">' . Loc::getMessage('BLOCK_EDITOR_TOOL_BORDER_DOTTED') . '</option>
				</select>
				<select id="block_editor_style_border_width" style="width: 80px; min-width: 80px;">
					<option value="">' . Loc::getMessage('BLOCK_EDITOR_COMMON_NO') . '</option>
					<option value="1px">1px</option>
					<option value="2px">2px</option>
					<option value="3px">3px</option>
					<option value="4px">4px</option>
					<option value="5px">5px</option>
					<option value="6px">6px</option>
					<option value="7px">7px</option>
				</select>
				<input id="block_editor_style_border_color" type="hidden" class="bx-editor-color-picker">
				<span class="bx-editor-color-picker-view"></span>
				<span class="bx-editor-color-picker-text">' . Loc::getMessage('BLOCK_EDITOR_TOOLS_COLOR') .'</span>
				',
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'background-color',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_BACKGROUND_COLOR'),
			'HTML' => Tools::getControlColor(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'border-radius',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_BORDER_RADIUS'),
			'HTML' => Tools::getControlBorderRadius(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'color',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_COLOR'),
			'HTML' => Tools::getControlColor(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'font-family',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_FONT_FAMILY'),
			'HTML' => Tools::getControlFontFamily(),
		);

		$resultList[] = array(
			'GROUP' => 'prop',
			'ID' => 'align',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_ALIGN'),
			'HTML' => Tools::getControlTextAlign(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'text-decoration',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_TEXT_DECORATION'),
			'HTML' => Tools::getControlTextDecoration(),
		);

		$resultList[] = array(
			'GROUP' => 'prop',
			'ID' => 'align',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_ALIGN'),
			'HTML' => Tools::getControlTextAlign(),
		);

		$resultList[] = array(
			'GROUP' => 'prop',
			'ID' => 'imagetextalign',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_IMAGETEXTALIGN'),
			'HTML' => Tools::getControlSelect(array(
				'left' => Loc::getMessage('BLOCK_EDITOR_CTRL_ALIGN_LEFT'),
				'right' => Loc::getMessage('BLOCK_EDITOR_CTRL_ALIGN_RIGHT')
			), false)
		);

		$resultList[] = array(
			'GROUP' => 'prop',
			'ID' => 'imagetextpart',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_IMAGETEXTPART'),
			'HTML' => Tools::getControlSelect(
				array(
					'1/4' => Loc::getMessage('BLOCK_EDITOR_TOOL_IMAGETEXTPART14'),
					'1/3' => Loc::getMessage('BLOCK_EDITOR_TOOL_IMAGETEXTPART13'),
					'1/2' => Loc::getMessage('BLOCK_EDITOR_TOOL_IMAGETEXTPART12'),
					'2/3' => Loc::getMessage('BLOCK_EDITOR_TOOL_IMAGETEXTPART23')
				),
				false)
		);

		$resultList[] = array(
			'GROUP' => 'prop',
			'ID' => 'height',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_HEIGHT'),
			'HTML' => Tools::getControlInput(),
		);

		$resultList[] = array(
			'GROUP' => 'prop',
			'ID' => 'margin-top',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_MARGIN_TOP'),
			'HTML' => Tools::getControlPaddingBottoms(),
		);

		$resultList[] = array(
			'GROUP' => 'prop',
			'ID' => 'margin-bottom',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_MARGIN_BOTTOM'),
			'HTML' => Tools::getControlPaddingBottoms(),
		);

		$resultList[] = array(
			'GROUP' => 'prop',
			'ID' => 'groupimage-view',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_GROUPIMAGE_VIEW'),
			'HTML' => Tools::getControlSelect(
				array(
					'' => Loc::getMessage('BLOCK_EDITOR_TOOL_GROUPIMAGE_VIEW_2COL'),
					'1' => Loc::getMessage('BLOCK_EDITOR_TOOL_GROUPIMAGE_VIEW_1COL')
				),
				false
			),
		);

		$resultList[] = array(
			'GROUP' => 'prop',
			'ID' => 'column-count',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_COLUMN_COUNT'),
			'HTML' => Tools::getControlSelect(array('1' => '1', '2' => '2', '3' => '3'), false),
		);

		$resultList[] = array(
			'GROUP' => 'prop',
			'ID' => 'paddings',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_PADDINGS'),
			'HTML' => Tools::getControlSelect(
				array(
					'Y' => Loc::getMessage('BLOCK_EDITOR_TOOL_PADDINGS_STANDARD'),
					'N' => Loc::getMessage('BLOCK_EDITOR_TOOL_PADDINGS_WITHOUT')
				),
				false
			),
		);

		$resultList[] = array(
			'GROUP' => 'prop',
			'ID' => 'wide',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_WIDE'),
			'HTML' => Tools::getControlSelect(
				array(
					'N' => Loc::getMessage('BLOCK_EDITOR_TOOL_WIDE_N'),
					'Y' => Loc::getMessage('BLOCK_EDITOR_TOOL_WIDE_Y')
				)
				, false
			),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'bx-stylist-bgcolor',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_BACKGROUND_COLOR'),
			'HTML' => Tools::getControlColor(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'bx-stylist-padding-top',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_MARGIN_TOP'),
			'HTML' => Tools::getControlPaddingBottoms(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'bx-stylist-padding-bottom',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_MARGIN_BOTTOM'),
			'HTML' => Tools::getControlPaddingBottoms(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'bx-stylist-text-color',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_STYLIST_TEXT') . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_COLOR'),
			'HTML' => Tools::getControlColor(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'bx-stylist-text-font-family',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_STYLIST_TEXT') . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_FONT_FAMILY'),
			'HTML' => Tools::getControlFontFamily(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'bx-stylist-text-font-size',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_STYLIST_TEXT') . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_FONT_SIZE'),
			'HTML' => Tools::getControlFontSize(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'bx-stylist-text-font-weight',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_STYLIST_TEXT') . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_FONT_WEIGHT'),
			'HTML' => Tools::getControlFontWeight(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'bx-stylist-text-line-height',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_STYLIST_TEXT') . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_LINE_HEIGHT'),
			'HTML' => Tools::getControlLineHeight(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'bx-stylist-text-text-align',
			'NAME' =>  Loc::getMessage('BLOCK_EDITOR_TOOL_STYLIST_TEXT') . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_TEXT_ALIGN'),
			'HTML' => Tools::getControlTextAlign(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'bx-stylist-a-color',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_STYLIST_LINK') . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_COLOR'),
			'HTML' => Tools::getControlColor(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'bx-stylist-a-font-weight',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_STYLIST_LINK') . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_FONT_WEIGHT'),
			'HTML' => Tools::getControlFontWeight(),
		);

		$resultList[] = array(
			'GROUP' => 'style',
			'ID' => 'bx-stylist-a-text-decoration',
			'NAME' => Loc::getMessage('BLOCK_EDITOR_TOOL_STYLIST_LINK') . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_TEXT_DECORATION'),
			'HTML' => Tools::getControlTextDecoration(),
		);

		for($i = 1; $i <= 4; $i++)
		{
			$resultList[] = array(
				'GROUP' => 'style',
				'ID' => 'bx-stylist-h' . $i . '-color',
				'NAME' => 'H' . $i . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_COLOR'),
				'HTML' => Tools::getControlColor(),
			);

			$resultList[] = array(
				'GROUP' => 'style',
				'ID' => 'bx-stylist-h' . $i . '-font-size',
				'NAME' => 'H' . $i . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_FONT_SIZE'),
				'HTML' => Tools::getControlFontSize(),
			);

			$resultList[] = array(
				'GROUP' => 'style',
				'ID' => 'bx-stylist-h' . $i . '-font-weight',
				'NAME' => 'H' . $i . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_FONT_WEIGHT'),
				'HTML' => Tools::getControlFontWeight(),
			);

			$resultList[] = array(
				'GROUP' => 'style',
				'ID' => 'bx-stylist-h' . $i . '-line-height',
				'NAME' => 'H' . $i . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_LINE_HEIGHT'),
				'HTML' => Tools::getControlLineHeight(),
			);

			$resultList[] = array(
				'GROUP' => 'style',
				'ID' => 'bx-stylist-h' . $i . '-text-align',
				'NAME' => 'H' . $i . ' ' . Loc::getMessage('BLOCK_EDITOR_TOOL_TEXT_ALIGN'),
				'HTML' => Tools::getControlTextAlign(),
			);
		}

		return $resultList;
	}

	/**
	 * Return html of interface part.
	 *
	 * @param string $id
	 * @param array $values
	 * @return string
	 */
	public function getUI($id, array $values)
	{
		if(!array_key_exists($id, $this->uiPatterns) || trim($this->uiPatterns[$id]) == '')
		{
			return '';
		}

		$placeholders = array_keys($values);
		$placeholders = '#' . implode('#,#', $placeholders) . '#';
		$placeholders = explode(',', $placeholders);

		return str_replace($placeholders, array_values($values), $this->uiPatterns[$id]);
	}

	/**
	 * Return html of editor interface without resources.
	 *
	 * @return string
	 */
	public function showEditor()
	{
		$textArea = '';
		$panels = '';
		$tabs = '';
		$blocks = '';
		$tools = '';
		$devices = '';


		foreach(array_chunk($this->blocks, static::BLOCK_COUNT_PER_PAGE) as $blocksPerPage)
		{
			$blocksForPage = '';
			foreach($blocksPerPage as $block)
			{
				$blocksForPage .= $this->getUI('block', array(
					'type_class' => htmlspecialcharsbx($block['IS_COMPONENT'] ? 'component' : 'blockcomponent'),
					'code_class' => htmlspecialcharsbx(str_replace(array(':', '.'), array('-', '-'), $block['CODE'])),
					'type' => htmlspecialcharsbx($block['TYPE']),
					'code' => htmlspecialcharsbx($block['CODE']),
					'name' => htmlspecialcharsbx($block['NAME']),
					'desc' => htmlspecialcharsbx($block['DESC']),
				));
			}

			$blocks .= $this->getUI('block_page', array('blocks' => $blocksForPage));
		}

		foreach($this->tools as $tool)
		{
			$tools .= $this->getUI('tool', array(
				'group' => htmlspecialcharsbx($tool['GROUP']),
				'id' => htmlspecialcharsbx($tool['ID']),
				'name' => htmlspecialcharsbx($tool['NAME']),
				'html' => $tool['HTML'],
			));
		}

		foreach($this->previewModes as $mode)
		{
			$devices .= $this->getUI('device', array(
				'MESS_NAME' => mb_strtoupper(htmlspecialcharsbx($mode['NAME'])),
				'class' => htmlspecialcharsbx($mode['CLASS']),
				'width' => htmlspecialcharsbx($mode['WIDTH']),
				'height' => htmlspecialcharsbx($mode['HEIGHT']),
			));
		}


		if(!$this->ownResultId)
		{
			$this->ownResultId = 'bx-block-editor-result-' . htmlspecialcharsbx($this->id);
			$textArea = '<textarea name="' . htmlspecialcharsbx($this->id) . '" id="' . htmlspecialcharsbx($this->ownResultId)
				.'" style="width:800px;height:900px; display: none;"></textarea>';
		}

		foreach($this->tabs as $tabCode => $tab)
		{
			if(!isset($this->uiPatterns['panel-' . $tabCode]))
			{
				continue;
			}

			$tabs .= $this->getUI('tab', array(
				'code' => htmlspecialcharsbx($tabCode),
				'name' => htmlspecialcharsbx($tab['NAME']),
				'tab_active' => ($tab['ACTIVE'] ? $this->getUI('tab_active', array()) : '')
			));

			$panel = $this->getUI('panel-' . $tabCode, array(
				'id' => htmlspecialcharsbx($this->id),
				'blocks' => $blocks,
				'tools' => $tools,
				'devices' => $devices,
				'nav-display' => count($this->blocks) <= static::BLOCK_COUNT_PER_PAGE ? 'bx-block-hide' : '',
				'MESS_ACCESS_DENIED' => Loc::getMessage('ACCESS_DENIED'),
				'MESS_STYLES' => Loc::getMessage('BLOCK_EDITOR_UI_STYLES'),
				'MESS_BLOCKS' => Loc::getMessage('BLOCK_EDITOR_UI_BLOCKS'),
				'MESS_TOOL_CONTENT' => Loc::getMessage('BLOCK_EDITOR_UI_TOOL_CONTENT'),
				'MESS_TOOL_STYLES' => Loc::getMessage('BLOCK_EDITOR_UI_TOOL_STYLES'),
				'MESS_TOOL_SETTINGS' => Loc::getMessage('BLOCK_EDITOR_UI_TOOL_SETTINGS'),
				'MESS_TOOL_EMPTY' => Loc::getMessage('BLOCK_EDITOR_UI_TOOL_EMPTY'),
				'MESS_TOOL_SAVE' => Loc::getMessage('BLOCK_EDITOR_UI_TOOL_SAVE'),
				'MESS_TOOL_SAVE_TITLE' => Loc::getMessage('BLOCK_EDITOR_UI_TOOL_SAVE_TITLE'),
				'MESS_TOOL_CANCEL' => Loc::getMessage('BLOCK_EDITOR_UI_TOOL_CANCEL'),
				'MESS_TOOL_CANCEL_TITLE' => Loc::getMessage('BLOCK_EDITOR_UI_TOOL_CANCEL_TITLE'),
			));

			$panels .= $this->getUI('panel', array(
				'code' => htmlspecialcharsbx($tabCode),
				'panel_hidden' => (!$tab['ACTIVE'] ? $this->getUI('panel_hidden', array()) : ''),
				'html' => $panel
			));
		}

		return $this->getUI('main', array(
			'TEXTAREA' => $textArea,
			'id' => htmlspecialcharsbx($this->id),
			'tabs' => $tabs,
			'panels' => $panels,
			'MESS_BTN_MAX' => Loc::getMessage('BLOCK_EDITOR_UI_BTN_MAX'),
			'MESS_BTN_MIN' => Loc::getMessage('BLOCK_EDITOR_UI_BTN_MIN'),
			'MESS_BTN_HTML_COPY' => Loc::getMessage('BLOCK_EDITOR_UI_BTN_HTML_COPY'),
		));
	}

	/**
	 * Return html for showing editor and include all resources
	 *
	 * @return string
	 */
	public function show()
	{
		\CJSCore::RegisterExt('block_editor', array(
			'js' => array(
				'/bitrix/js/main/core/core_dragdrop.js',
				'/bitrix/js/fileman/block_editor/dialog.js',
				'/bitrix/js/fileman/block_editor/helper.js',
				'/bitrix/js/fileman/block_editor/editor.js',
			),
			'css' => '/bitrix/js/fileman/block_editor/dialog.css',
			'rel' => ['ui.design-tokens', 'ui.fonts.opensans'],
			'lang' => '/bitrix/modules/fileman/lang/' . LANGUAGE_ID . '/js_block_editor.php',
		));
		\CJSCore::Init(array("block_editor", "color_picker", "clipboard"));

		static $isBlockEditorManagerInited = false;
		$editorBlockTypeListByCode = array();
		if(!$isBlockEditorManagerInited)
		{
			foreach($this->blocks as $block)
			{
				$editorBlockTypeListByCode[$block['CODE']] = $block;
			}
		}

		$jsCreateParams = array(
			'id' => $this->id,
			'url' => $this->url,
			'previewUrl' => $this->previewUrl,
			'saveFileUrl' => $this->saveFileUrl,
			'templateType' => $this->templateType,
			'templateId' => $this->templateId,
			'isTemplateMode' => $this->isTemplateMode,
			'site' => $this->site,
			'charset' => $this->charset
		);


		$result = '';
		if(!$isBlockEditorManagerInited)
		{
			$result .= 'BX.BlockEditorManager.setBlockList(' . \CUtil::PhpToJSObject($editorBlockTypeListByCode) . ");\n";
		}

		$result .= "var blockEditorParams = " . \CUtil::PhpToJSObject($jsCreateParams) . ";\n";
		$result .= "blockEditorParams['context'] = BX('bx-block-editor-container-" . htmlspecialcharsbx($this->id) . "');\n";
		$result .= "blockEditorParams['iframe'] = BX('bx-block-editor-iframe-" . htmlspecialcharsbx($this->id) . "');\n";
		$result .= "blockEditorParams['resultNode'] = BX('" . htmlspecialcharsbx($this->ownResultId) . "');\n";
		$result .= "BX.BlockEditorManager.create(blockEditorParams);\n";

		$result = "\n" . '<script type="text/javascript">BX.ready(function(){' . "\n" . $result . '})</script>' . "\n";
		$result = $this->showEditor() . $result;


		$isBlockEditorManagerInited = true;

		return $result;
	}

	/**
	 * Return received string, that php changed in special format for block editor.
	 *
	 * @param string $html
	 * @param string $charset
	 * @return string $html
	 */
	public static function getHtmlForEditor($html, $charset = null)
	{
		$phpList = \PHPParser::ParseFile($html);
		foreach($phpList as $php)
		{
			$phpFormatted = htmlspecialcharsbx(str_replace(["\r", "\n"], "", $php[2]));
			$id = 'bx_block_php_' . mt_rand();
			$surrogate = '<span id="' . $id . '" ' . self::BLOCK_PHP_ATTR . '="' . ($phpFormatted) . '" class="bxhtmled-surrogate" title=""></span>';
			$html = str_replace($php[2], $surrogate, $html);
		}

		if(!$charset)
		{
			$charset = Application::getInstance()->getContext()->getCulture()->getCharset();
			$charset = 'UTF-8';
		}

		$charsetPlaceholder = '#CHARSET#';
		$html = static::replaceCharset($html, $charsetPlaceholder);
		$html = str_replace($charsetPlaceholder, HtmlFilter::encode($charset), $html);
		$html = Sanitizer::clean($html);

		return $html;
	}

	/**
	 * Replace charset in HTML string.
	 *
	 * @param string $html
	 * @param string $charset
	 * @param bool $add
	 * @return string $html
	 */
	public static function replaceCharset($html, $charset = '#CHARSET#', $add = false)
	{
		$html = preg_replace(
			'/(<meta .*?charset=["\']+?)([^"\']+?)(["\']+?.*?>)/i',
			'$1' . $charset . '$3',
			$html
		);

		$html = preg_replace(
			'/(<meta .*?content=["\']+?[^;]+?;[ ]*?charset=)([^"\']*?)(["\']+?.*?>)/i',
			'$1' . $charset . '$3', $html, 1, $replaceCount
		);
		if($replaceCount === 0 && $add)
		{
			$html = preg_replace(
				'/(<head.*?>)/i',
				'$1<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">',
				$html
			);
		}

		return $html;
	}

	/**
	 * Fill template(as a HTML) by slice content.
	 * Result is string.
	 *
	 * @param string $template
	 * @param string $string Content string.
	 * @param string $encoding
	 * @return string
	 * @deprecated
	 * @see \Bitrix\Fileman\Block\Content\Engine::fillHtmlTemplate
	 */
	public static function fillTemplateBySliceContent($template, $string, $encoding = null)
	{
		return Content\Engine::fillHtmlTemplate($template, $string, $encoding);
	}

	/**
	 * Fill template(as a DOM Document) by content.
	 *
	 * @param Document $document Document.
	 * @param string $string Content string.
	 * @param string $encoding
	 * @return boolean
	 * @deprecated
	 * @see \Bitrix\Fileman\Block\Content\Engine::fillHtmlTemplate
	 */
	public static function fillDocumentBySliceContent(Document $document, $string, $encoding = null)
	{
		return Content\Engine::create($document)->setEncoding($encoding)->setContent($string)->fill();
	}

	/**
	 * Check string for the presence of tag attributes that indicates supporting of block editor
	 *
	 * @param string $content
	 * @return boolean
	 */
	public static function isContentSupported($content)
	{
		if(!$content || mb_strpos($content, Content\Engine::BLOCK_PLACE_ATTR) === false)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Check string for the presence of html
	 *
	 * @param string $content
	 * @return bool
	 */
	public static function isHtmlDocument($content)
	{
		$result = true;
		$content = mb_strtoupper($content);
		if(mb_strpos($content, '<HTML') === false)
		{
			$result = false;
		}
		if(mb_strpos($content, '</HTML') === false)
		{
			$result = false;
		}
		if(mb_strpos($content, '<BODY') === false)
		{
			$result = false;
		}

		return $result;
	}

	/**
	 * Set components filter
	 *
	 * @param array $componentFilter
	 * @return void
	 */
	public function setComponentFilter(array $componentFilter = null)
	{
		$this->componentFilter = $componentFilter;
	}

	protected function getComponentList()
	{
		return static::getComponentListPlain(static::getComponentTree());
	}

	protected function getComponentTree()
	{
		$util = new \CComponentUtil;

		return $util->GetComponentsTree(false, false, $this->componentFilter);
	}

	protected function getComponentListPlain($list)
	{
		$result = array();
		$path = null;

		if(!is_array($list))
		{
			return $result;
		}

		if(isset($list['@']))
		{
			$path = $list['@'];
		}

		if(isset($list['*']))
		{
			$componentList = array();
			foreach($list['*'] as $componentName => $componentData)
			{
				$componentData['TREE_PATH'] = array($path);
				$componentList[$componentName] = $componentData;
			}
			return $componentList;
		}

		if(isset($list['#']))
		{
			foreach($list['#'] as $key => $item)
			{
				$resultItem = static::getComponentListPlain($item);
				if(is_array($resultItem) && is_array($path))
				{
					foreach($resultItem as $componentName => $componentData)
					{
						if(!isset($componentData['TREE_PATH']))
						{
							$componentData['TREE_PATH'] = array();
						}
						$resultItem[$componentName]['TREE_PATH'] = array_merge(array($path), $componentData['TREE_PATH']);
					}
				}

				$result = array_merge($result, $resultItem);
			}
		}

		return $result;
	}

}