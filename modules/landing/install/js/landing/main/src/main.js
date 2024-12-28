import {Type, Dom, Cache, Tag, Text, Runtime} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Env} from 'landing.env';
import {Loc} from 'landing.loc';
import {Content} from 'landing.ui.panel.content';
import {SaveBlock} from 'landing.ui.panel.saveblock';
import {SliderHacks} from 'landing.sliderhacks';
import {PageObject} from 'landing.pageobject';
import hasBlock from './internal/has-block';
import hasCreateButton from './internal/has-create-button';
import onAnimationEnd from './internal/on-animation-end';
import isEmpty from './internal/is-empty';
import {ExternalControls} from './external.controls';
import {Backend} from 'landing.backend';

BX.Landing.getMode = () => 'edit';

/**
 * @memberOf BX.Landing
 */
export class Main extends EventEmitter
{
	static TYPE_PAGE = 'PAGE';
	static TYPE_STORE = 'STORE';
	static TYPE_KNOWLEDGE = 'KNOWLEDGE';
	static TYPE_GROUP = 'GROUP';

	static getMode()
	{
		return 'edit';
	}

	static createInstance(id: number)
	{
		const rootWindow = BX.Landing.PageObject.getRootWindow();
		if (rootWindow.BX.Landing.Main.instance)
		{
			rootWindow.BX.Landing.Main.instance.clear();
		}
		rootWindow.BX.Landing.Main.instance = new BX.Landing.Main(id);
	}

	static getInstance(): Main
	{
		const rootWindow = BX.Landing.PageObject.getRootWindow();
		rootWindow.BX.Reflection.namespace('BX.Landing.Main');
		if (rootWindow.BX.Landing.Main.instance)
		{
			return rootWindow.BX.Landing.Main.instance;
		}

		rootWindow.BX.Landing.Main.instance = new Main(-1);

		return rootWindow.BX.Landing.Main.instance;
	}

	/**
	 * Returns true, if current page is Editor.
	 * @return {boolean}
	 */
	static isEditorMode()
	{
		return Dom.hasClass(document.body, 'landing-editor');
	}

	/**
	 * Returns true, if external controls is enabled.
	 * @return {boolean}
	 */
	static isExternalControlsEnabled()
	{
		return Dom.hasClass(document.body, 'enable-external-controls');
	}

	/**
	 * Returns in percent scroll position of page.
	 *
	 * @return {number}
	 */
	static topInPercent(): number
	{
		const scrollHeight = Math.max(
			document.body.scrollHeight, document.documentElement.scrollHeight,
			document.body.offsetHeight, document.documentElement.offsetHeight,
			document.body.clientHeight, document.documentElement.clientHeight
		);

		const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;

		return scrollTop / scrollHeight * 100;
	}

	/**
	 * Landing ID
	 * @type {number}
	 */
	id: number;

	constructor(id: number)
	{
		super();
		this.setEventNamespace('BX.Landing.Main');

		const options = Env.getInstance().getOptions();

		this.id = id;
		this.options = Object.freeze(options);
		this.blocks = this.options.blocks;
		this.currentBlock = null;
		this.isDesignBlockModeFlag = this.options["design_block"] === true;
		this.loadedDeps = {};
		this.cache = new Cache.MemoryCache();
		this.externalControls = new ExternalControls;

		this.onSliderFormLoaded = this.onSliderFormLoaded.bind(this);
		this.onBlockDelete = this.onBlockDelete.bind(this);

		BX.addCustomEvent('Landing.Block:onAfterDelete', this.onBlockDelete);

		this.adjustEmptyAreas();

		BX.Landing.UI.Panel.StatusPanel.setLastModified(options.lastModified);
		if (!this.isDesignBlockModeFlag)
		{
			BX.Landing.UI.Panel.StatusPanel.getInstance().show();
		}

		const pageType = Env.getInstance().getType();
		if (
			pageType === Main.TYPE_KNOWLEDGE
			|| pageType === Main.TYPE_GROUP
		)
		{
			const mainArea = document.querySelector('.landing-main');
			if (Type.isDomNode(mainArea))
			{
				Dom.addClass(mainArea, 'landing-ui-collapse');
			}
		}
	}

	clear(): void
	{
		BX.removeCustomEvent('Landing.Block:onAfterDelete', this.onBlockDelete);
	}

	isCrmFormPage(): boolean
	{
		return Env.getInstance().getSpecialType() === 'crm_forms';
	}

	isDesignBlockMode()
	{
		return this.isDesignBlockModeFlag;
	}

	getSaveBlockPanel(): Content
	{
		const panel = new SaveBlock('save_block_panel', {block: this.currentBlock});
		panel.layout.hidden = true;
		panel.content.hidden = false;
		Dom.append(panel.layout, window.parent.document.body);

		return panel;
	}

	getBlocksPanel(): Content
	{
		return this.cache.remember('blockPanel', () => {
			const blocksPanel = this.createBlocksPanel();
			setTimeout(() => {
				if (blocksPanel.sidebarButtons.get(this.options.default_section))
				{
					blocksPanel.sidebarButtons.get(this.options.default_section).layout.click();
				}
				else
				{
					[...blocksPanel.sidebarButtons][0].layout.click();
				}
			});
			blocksPanel.layout.hidden = true;
			blocksPanel.content.hidden = false;
			Dom.append(blocksPanel.layout, window.parent.document.body);

			return blocksPanel;
		});
	}

	hideBlocksPanel()
	{
		if (this.getBlocksPanel())
		{
			return this.getBlocksPanel().hide();
		}

		return Promise.resolve();
	}

	getLayoutAreas(): Array<HTMLElement>
	{
		return this.cache.remember('layoutAreas', () => {
			return [
				...document.body.querySelectorAll('.landing-header'),
				...document.body.querySelectorAll('.landing-sidebar'),
				...document.body.querySelectorAll('.landing-main'),
				...document.body.querySelectorAll('.landing-footer'),
			];
		});
	}

	/**
	 * Creates insert block button
	 * @param {HTMLElement} area
	 * @return {BX.Landing.UI.Button.Plus}
	 */
	createInsertBlockButton(area: HTMLElement)
	{
		const button = new BX.Landing.UI.Button.Plus('insert_first_block', {
			text: Loc.getMessage('ACTION_BUTTON_CREATE'),
		});

		button.on('click', this.showBlocksPanel.bind(this, null, area, button));
		button.on('mouseover', this.onCreateButtonMouseover.bind(this, area, button));
		button.on('mouseout', this.onCreateButtonMouseout.bind(this, area, button));

		return button;
	}

	onCreateButtonMouseover(area: HTMLElement, button)
	{
		if (
			Dom.hasClass(area, 'landing-header')
			|| Dom.hasClass(area, 'landing-footer')
		)
		{
			const areas = this.getLayoutAreas();

			if (areas.length > 1)
			{
				const createText = Loc.getMessage('ACTION_BUTTON_CREATE');

				if (Dom.hasClass(area, 'landing-main'))
				{
					button.setText(
						`${createText} ${Loc.getMessage('LANDING_ADD_BLOCK_TO_MAIN')}`,
					);
				}

				if (Dom.hasClass(area, 'landing-header'))
				{
					button.setText(
						`${createText} ${Loc.getMessage('LANDING_ADD_BLOCK_TO_HEADER')}`,
					);
				}

				if (Dom.hasClass(area, 'landing-sidebar'))
				{
					button.setText(
						`${createText} ${Loc.getMessage('LANDING_ADD_BLOCK_TO_SIDEBAR')}`,
					);
				}

				if (Dom.hasClass(area, 'landing-footer'))
				{
					button.setText(
						`${createText} ${Loc.getMessage('LANDING_ADD_BLOCK_TO_FOOTER')}`,
					);
				}

				clearTimeout(this.fadeTimeout);
				this.fadeTimeout = setTimeout(() => {
					Dom.addClass(area, 'landing-area-highlight');

					areas
						.filter((currentArea) => currentArea !== area)
						.forEach((currentArea) => {
							Dom.addClass(currentArea, 'landing-area-fade');
						});
				}, 400);
			}
		}
	}

	onCreateButtonMouseout(area, button)
	{
		clearTimeout(this.fadeTimeout);

		if (Dom.hasClass(area, 'landing-header')
			|| Dom.hasClass(area, 'landing-footer'))
		{
			const areas = this.getLayoutAreas();

			if (areas.length > 1)
			{
				button.setText(Loc.getMessage('ACTION_BUTTON_CREATE'));
				areas.forEach((currentArea) => {
					Dom.removeClass(currentArea, 'landing-area-highlight');
					Dom.removeClass(currentArea, 'landing-area-fade');
				});
			}
		}
	}

	initEmptyArea(area: HTMLElement)
	{
		if (area)
		{
			area.innerHTML = '';
			Dom.append(this.createInsertBlockButton(area).layout, area);
			Dom.addClass(area, 'landing-empty');
		}
	}


	// eslint-disable-next-line class-methods-use-this
	destroyEmptyArea(area: HTMLElement)
	{
		if (area)
		{
			const button = area.querySelector('button[data-id="insert_first_block"]');

			if (button)
			{
				Dom.remove(button);
			}

			Dom.removeClass(area, 'landing-empty');
		}
	}


	/**
	 * Adjusts areas
	 */
	adjustEmptyAreas()
	{
		this.getLayoutAreas()
			.filter((area) => {
				return hasBlock(area) && hasCreateButton(area);
			})
			.forEach(this.destroyEmptyArea, this);

		this.getLayoutAreas()
			.filter((area) => {
				return !hasBlock(area) && !hasCreateButton(area);
			})
			.forEach(this.initEmptyArea, this);

		const main = document.body.querySelector('main.landing-edit-mode');
		const isAllEmpty = !this.getLayoutAreas().some(hasBlock);

		if (main)
		{
			if (isAllEmpty)
			{
				Dom.addClass(main, 'landing-empty');
				return;
			}

			Dom.removeClass(main, 'landing-empty');
		}
	}


	/**
	 * Enables landing controls
	 */
	// eslint-disable-next-line class-methods-use-this
	enableControls()
	{
		Dom.removeClass(document.body, 'landing-ui-hide-controls');
	}


	/**
	 * Disables landing controls
	 */
	// eslint-disable-next-line class-methods-use-this
	disableControls()
	{
		Dom.addClass(document.body, 'landing-ui-hide-controls');
	}


	/**
	 * Checks that landing controls is enabled
	 * @return {boolean}
	 */
	// eslint-disable-next-line class-methods-use-this
	isControlsEnabled()
	{
		return !Dom.hasClass(document.body, 'landing-ui-hide-controls');
	}

	/**
	 * Makes landing controls internal.
	 */
	// eslint-disable-next-line class-methods-use-this
	makeControlsInternal()
	{
		BX.onCustomEvent('BX.Landing.Main:changeControls', ['internal', Main.topInPercent()]);
		Dom.removeClass(document.body, 'landing-ui-external-controls');
	}

	/**
	 * Makes landing controls external.
	 */
	// eslint-disable-next-line class-methods-use-this
	makeControlsExternal()
	{
		BX.onCustomEvent('BX.Landing.Main:changeControls', ['external', Main.topInPercent()]);
		Dom.addClass(document.body, 'landing-ui-external-controls');
	}

	/**
	 * Checks that landing controls is external.
	 * @return {boolean}
	 */
	// eslint-disable-next-line class-methods-use-this
	isControlsExternal()
	{
		return Dom.hasClass(document.body, 'landing-ui-external-controls');
	}

	/**
	 * Set device code in body data-attribute.
	 * @param {string} code
	 */
	setDeviceCode(code: string)
	{
		document.body.setAttribute('data-device', code);
	}

	/**
	 * Get device code from body attribute.
	 * @return {string}
	 */
	getDeviceCode(): ?string
	{
		return document.body.getAttribute('data-device');
	}

	/**
	 * Set BX classes to mark this landing frame as mobile (touch) device
	 */
	setTouchDevice()
	{
		Dom.removeClass(document.documentElement, 'bx-no-touch');
		Dom.addClass(document.documentElement, 'bx-touch');
	}

	/**
	 * Set BX classes to mark this landing frame as desktop (no touch) device
	 */
	setNoTouchDevice()
	{
		Dom.removeClass(document.documentElement, 'bx-touch');
		Dom.addClass(document.documentElement, 'bx-no-touch');
	}


	/**
	 * Appends block
	 * @param {addBlockResponse} data
	 * @param {boolean} [withoutAnimation]
	 * @returns {HTMLElement}
	 */
	appendBlock(data, withoutAnimation)
	{
		if (!this.isAllowedAppendBlock(data))
		{
			return Tag.render``;
		}

		const block = Tag.render`${data.content}`;
		block.id = `block${data.id}`;

		if (!withoutAnimation)
		{
			Dom.addClass(block, 'landing-ui-show');
			onAnimationEnd(block, 'showBlock').then(() => {
				Dom.removeClass(block, 'landing-ui-show');
			});
		}

		this.insertToBlocksFlow(block);

		return block;
	}

	/**
	 * Check if the block can be appended
	 * @param {addBlockResponse} data
	 * @returns {boolean} - Returns true if the block can be appended, otherwise false
	 */
	isAllowedAppendBlock(data)
	{
		const type = BX.Landing.Env.getInstance().getType().toLowerCase();
		let allowedBlockTypes = data.manifest.block.type ?? [];
		if (
			type === 'mainpage'
			|| allowedBlockTypes.includes('mainpage')
		)
		{
			if (Type.isString(allowedBlockTypes))
			{
				allowedBlockTypes = [allowedBlockTypes];
			}

			if (!allowedBlockTypes.includes(type))
			{
				return false;
			}
		}

		return true;
	}


	/**
	 * Shows blocks list panel
	 * @param {BX.Landing.Block} block
	 * @param {HTMLElement} [area]
	 * @param [button]
	 * @param [insertBefore]
	 */
	showBlocksPanel(block, area, button, insertBefore)
	{
		this.currentBlock = block;
		this.currentArea = area;
		this.insertBefore = insertBefore;

		BX.Landing.UI.Panel.EditorPanel.getInstance().hide();

		if (this.isCrmFormPage() || this.isControlsExternal())
		{
			const rootWindow = PageObject.getRootWindow();
			Dom.append(this.getBlocksPanel().layout, rootWindow.document.body);
			Dom.append(this.getBlocksPanel().overlay, rootWindow.document.body);
		}

		this.getBlocksPanel().show();
		this.disableAddBlockButtons();

		if (!!area && !!button)
		{
			this.onCreateButtonMouseout(area, button);
		}
	}

	showSaveBlock(block)
	{
		this.currentBlock = block;
		this.getSaveBlockPanel().show();
	}

	disableAddBlockButtons()
	{
		PageObject.getBlocks().forEach((block) => {
			const panel = block.panels.get('create_action');
			if (panel)
			{
				const button = panel.buttons.get('insert_after');
				if (button)
				{
					button.disable();
				}
			}
		});
	}

	enableAddBlockButtons()
	{
		PageObject.getBlocks().forEach((block) => {
			const panel = block.panels.get('create_action');
			if (panel)
			{
				const button = panel.buttons.get('insert_after');
				if (button)
				{
					button.enable();
				}
			}
		});
	}

	/**
	 * Creates blocks list panel
	 * @returns {BX.Landing.UI.Panel.Content}
	 */
	createBlocksPanel()
	{
		const {blocks} = this.options;
		const categories = Object.keys(blocks);

		const panel = new Content('blocks_panel', {
			title: Loc.getMessage('LANDING_CONTENT_BLOCKS_TITLE'),
			className: 'landing-ui-panel-block-list',
			scrollAnimation: true,
		});

		panel.subscribe('onCancel', () => {
			this.enableAddBlockButtons();
		});

		categories.forEach((categoryId) => {
			const hasItems = !isEmpty(blocks[categoryId].items);
			const isPopular = categoryId === 'popular';
			const isSeparator = blocks[categoryId].separator;

			if ((hasItems && !isPopular) || isSeparator)
			{
				panel.appendSidebarButton(
					this.createBlockPanelSidebarButton(categoryId, blocks[categoryId]),
				);
			}
		});

		panel.appendSidebarButton(
			new BX.Landing.UI.Button.SidebarButton('feedback_button', {
				className: 'landing-ui-button-sidebar-feedback',
				text: Loc.getMessage('LANDING_BLOCKS_LIST_FEEDBACK_BUTTON'),
				onClick: this.showFeedbackForm.bind(this),
			}),
		);

		return panel;
	}


	/**
	 * Shows feedback form
	 * @param data
	 */
	showSliderFeedbackForm(data = {})
	{
		Runtime.loadExtension('ui.feedback.form').then(() => {
			const data = {};
			data.bitrix24 = this.options.server_name;
			data.siteId = this.options.site_id;
			data.siteUrl = this.options.url;
			data.siteTemplate = this.options.xml_id;
			data.productType = this.options.productType || 'Undefined';
			data.typeproduct = (() =>
			{
				if (this.options.params.type === Main.TYPE_GROUP)
				{
					return 'KNOWLEDGE_GROUP';
				}

				return this.options.params.type;
			})();

			BX.UI.Feedback.Form.open(
				{
					id: Math.random()+'',
					forms: this.getFeedbackFormOptions(),
					presets: data,
				}
			);
		});

	}


	/**
	 * Gets feedback form options
	 * @return {{id: string, sec: string, lang: string}}
	 */
	// eslint-disable-next-line class-methods-use-this
	getFeedbackFormOptions()
	{
		return [
			{zones: ['en', 'eu', 'in', 'uk'], id: 16, lang: 'en', sec: '3h483y'},
			{zones: ['ru', 'by', 'kz'], id: 8, lang: 'ru', sec: 'x80yjw'},
			{zones: ['ua'], id: 18, lang: 'ua', sec: 'd9e09o'},
			{zones: ['la', 'co', 'mx'], id: 14, lang: 'la', sec: 'wu561i'},
			{zones: ['de'], id: 10, lang: 'de', sec: 'eraz2q'},
			{zones: ['com.br', 'br'], id: 12, lang: 'br', sec: 'r6wvge'},
		];
	}


	/**
	 * Handles feedback loaded event
	 */
	onSliderFormLoaded()
	{
		this.sliderFormLoader.hide();
	}


	/**
	 * Shows feedback form for blocks list panel
	 */
	showFeedbackForm()
	{
		this.showSliderFeedbackForm({target: 'blocksList'});
	}


	/**
	 * Initialises feedback form
	 */
	// eslint-disable-next-line class-methods-use-this
	initFeedbackForm()
	{
		const rootWindow = PageObject.getRootWindow();
		((w, d, u, b) => {
			w.Bitrix24FormObject = b; w[b] = w[b] || function() {
				// eslint-disable-next-line prefer-rest-params
				arguments[0].ref = u;
				// eslint-disable-next-line prefer-rest-params
				(w[b].forms = w[b].forms || []).push(arguments[0]);
			};
			if (w[b].forms) return;
			const s = d.createElement('script');
			const r = 1 * new Date(); s.async = 1; s.src = `${u}?${r}`;
			const h = d.getElementsByTagName('script')[0]; h.parentNode.insertBefore(s, h);
		})(rootWindow, rootWindow.document, 'https://product-feedback.bitrix24.com/bitrix/js/crm/form_loader.js', 'b24formFeedBack');
	}


	/**
	 * Creates blocks list panel sidebar button
	 * @param {string} category
	 * @param {object} options
	 * @returns {BX.Landing.UI.Button.SidebarButton}
	 */
	createBlockPanelSidebarButton(category, options)
	{
		return new BX.Landing.UI.Button.SidebarButton(category, {
			text: options.name,
			child: !options.separator,
			className: options.new ? 'landing-ui-new-section' : '',
			onClick: this.onBlocksListCategoryChange.bind(this, category),
		});
	}

	/**
	 * Adds dynamically new block to the category.
	 * @param {string} category Category code.
	 * @param {{code: string, name: string, preview: string, section: Array<string>}} block Block data.
	 */
	addNewBlockToCategory(category, block)
	{
		if (this.blocks[category])
		{
			const blockCode = block['codeOriginal'] || block['code'];
			if (category === 'last')
			{
				if (!this.lastBlocks)
				{
					this.lastBlocks = Object.keys(this.blocks.last.items);
				}
				this.lastBlocks.unshift(blockCode);
			}
			else
			{
				this.blocks[category].items[blockCode] = block;
			}
			this.onBlocksListCategoryChange(category);
		}
	}

	removeBlockFromList(blockCode: string)
	{
		let removed = false;
		for (let category in this.blocks)
		{
			if (this.blocks[category].items[blockCode] !== undefined)
			{
				delete this.blocks[category].items[blockCode];
				removed = true;
			}
		}
		if (this.lastBlocks.indexOf(blockCode) !== -1)
		{
			this.lastBlocks.splice(this.lastBlocks.indexOf(blockCode), 1);
			removed = true;
		}

		// refresh panel
		if (removed)
		{
			const activeCategoryButton = this.getBlocksPanel().sidebarButtons.find((button) => {
				return Dom.hasClass(button.layout, 'landing-ui-active');
			});
			if (activeCategoryButton)
			{
				this.onBlocksListCategoryChange(activeCategoryButton.id);
			}
		}
	}

	/**
	 * Returns page's template code if exists.
	 * @return {string|null}
	 */
	getTemplateCode()
	{
		let { tplCode } = Env.getInstance().getOptions();
		if (tplCode.indexOf('@') > 0)
		{
			tplCode = tplCode.split('@')[1];
		}
		if (!tplCode || tplCode.length <= 0)
		{
			tplCode = null;
		}
		return tplCode;
	}


	/**
	 * Handles event on blocks list category change
	 * @param {string} category - Category id
	 */
	onBlocksListCategoryChange(category)
	{
		const templateCode = this.getTemplateCode();
		this.getBlocksPanel().content.hidden = false;

		this.getBlocksPanel().sidebarButtons.forEach((button) => {
			const action = button.id === category ? 'add' : 'remove';
			button.layout.classList[action]('landing-ui-active');
		});

		this.getBlocksPanel().content.innerHTML = '';

		if (category === 'last')
		{
			if (!this.lastBlocks)
			{
				this.lastBlocks = Object.keys(this.blocks.last.items);
			}

			this.lastBlocks = [...new Set(this.lastBlocks)];

			this.lastBlocks.forEach((blockKey) => {
				const block = this.getBlockFromRepository(blockKey);
				this.getBlocksPanel().appendCard(this.createBlockCard(blockKey, block));
			});

			return;
		}

		Object.keys(this.blocks[category].items).forEach((blockKey) => {
			const block = this.blocks[category].items[blockKey];
			const blockTplCode = (block['tpl_code'] && block['tpl_code'].length > 0) ? block['tpl_code'] : null;
			if (
				!templateCode || !blockTplCode ||
				(blockTplCode && blockTplCode === templateCode)
			)
			{
				this.getBlocksPanel().appendCard(this.createBlockCard(blockKey, block));
			}
		});

		if (this.getBlocksPanel().content.scrollTop)
		{
			requestAnimationFrame(() => {
				this.getBlocksPanel().content.scrollTop = 0;
			});
		}
	}

	// eslint-disable-next-line consistent-return
	getBlockFromRepository(code)
	{
		const {blocks} = this.options;
		const categories = Object.keys(blocks);
		const category = categories.find((categoryId) => {
			return code in blocks[categoryId].items;
		});

		if (category)
		{
			return blocks[category].items[code];
		}
	}

	/**
	 * Handles copy block event
	 * @param {BX.Landing.Block} block
	 */
	// eslint-disable-next-line class-methods-use-this
	onCopyBlock(block)
	{
		window.localStorage.landingBlockId = block.id;
		window.localStorage.landingBlockName = block.manifest.block.name;
		window.localStorage.landingBlockAction = 'copy';

		try
		{
			window.localStorage.requiredUserAction = JSON.stringify(
				block.requiredUserActionOptions,
			);
		}
		catch (err)
		{
			window.localStorage.requiredUserAction = '';
		}
	}


	/**
	 * Handles cut block event
	 * @param {BX.Landing.Block} block
	 */
	// eslint-disable-next-line class-methods-use-this
	onCutBlock(block)
	{
		window.localStorage.landingBlockId = block.id;
		window.localStorage.landingBlockName = block.manifest.block.name;
		window.localStorage.landingBlockAction = 'cut';

		try
		{
			window.localStorage.requiredUserAction = JSON.stringify(
				block.requiredUserActionOptions,
			);
		}
		catch (err)
		{
			window.localStorage.requiredUserAction = '';
		}

		BX.Landing.PageObject.getBlocks().remove(block);
		Dom.remove(block.node);
		BX.onCustomEvent('Landing.Block:onAfterDelete', [block]);
	}


	/**
	 * Handles paste block event
	 * @param {BX.Landing.Block} block
	 * @param {() => {}} callback
	 */
	onPasteBlock(block, callback)
	{
		if (window.localStorage.landingBlockId)
		{
			let action = 'Landing::copyBlock';

			if (window.localStorage.landingBlockAction === 'cut')
			{
				action = 'Landing::moveBlock';
			}

			const requestBody = {};

			requestBody[action] = {
				action,
				data: {
					lid: block.lid || BX.Landing.Main.getInstance().id,
					block: window.localStorage.landingBlockId,
					params: {
						AFTER_ID: block.id,
						RETURN_CONTENT: 'Y',
					},
				},
			};

			BX.Landing.Backend.getInstance()
				.batch(action, requestBody, {action})
				.then((res) => {
					this.currentBlock = block;
					return this.addBlock(res[action].result.content, false, false, callback);
				});
		}
	}


	/**
	 * Adds block from server response
	 * @param {addBlockResponse} res
	 * @param {boolean} [withoutAnimation = false]
	 * @param {boolean} [insertBefore = false]
	 * @param {() => {}} callback
	 * @return {Promise<T>}
	 */
	addBlock(res, withoutAnimation, insertBefore = false, callback)
	{
		if (this.lastBlocks)
		{
			this.lastBlocks.unshift(res.manifest.codeOriginal || res.manifest.code);
		}

		const self = this;
		const block = this.appendBlock(res, withoutAnimation);

		return this.loadBlockDeps(res)
			.then((blockRes) => {
				self.currentBlock = null;
				self.currentArea = null;

				const blockId = parseInt(res.id);
				const allOldBlocks = BX.Landing.PageObject.getBlocks();
				if (allOldBlocks)
				{
					allOldBlocks.forEach((oldBlock) => {
						if (oldBlock.id === blockId)
						{
							Dom.remove(oldBlock.node);
							BX.Landing.PageObject.getBlocks().remove(oldBlock);
						}
					});
				}

				// Init block entity
				void new BX.Landing.Block(block, {
					id: blockId,
					sections: res.sections,
					requiredUserAction: res.requiredUserAction,
					manifest: res.manifest,
					access: res.access,
					active: Text.toBoolean(res.active),
					php: res.php,
					designed: res.designed,
					anchor: res.anchor,
					dynamicParams: res.dynamicParams,
					repoId: res.repoId,
				});

				return self.runBlockScripts(res)
					.then(() => {
						if (callback)
						{
							callback(blockId);
						}
						return block;
					});
			})
			.catch((err) => {
				console.warn(err);
			});
	}


	/**
	 * Handles edd block event
	 * @param {string} blockCode
	 * @param {*} [restoreId]
	 * @param {?boolean} [preventHistory = false]
	 * @return {Promise<BX.Landing.Block>}
	 */
	onAddBlock(blockCode, restoreId, preventHistory: ?boolean  = false)
	{
		const id = Text.toNumber(restoreId);
		this.hideBlocksPanel();

		return this.showBlockLoader()
			.then(this.loadBlock(blockCode, id, preventHistory))
			.then((res) => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve(res);
					}, 500);
				});
			})
			.then((res) => {
				res.manifest.codeOriginal = blockCode;
				const p = this.addBlock(res, false, this.insertBefore);
				this.insertBefore = false;
				this.adjustEmptyAreas();
				void this.hideBlockLoader();
				this.enableAddBlockButtons();
				BX.onCustomEvent('BX.Landing.Block:onAfterAdd', res);
				this.sendAnalyticsData('onAddBlock', res);
				return p;
			});
	}


	/**
	 * Inserts element to blocks flow.
	 * Element can be inserted after current block or after last block
	 * @param {HTMLElement} element
	 */
	insertToBlocksFlow(element)
	{
		const isCurrentBlockAvailable = (
			this.currentBlock
			&& this.currentBlock.node
			&& this.currentBlock.node.parentNode
		);

		if (isCurrentBlockAvailable && !this.insertBefore)
		{
			Dom.insertAfter(element, this.currentBlock.node);
			return;
		}

		if (isCurrentBlockAvailable && this.insertBefore)
		{
			Dom.insertBefore(element, this.currentBlock.node);
		}

		Dom.prepend(element, this.currentArea);
	}


	/**
	 * Gets block loader
	 * @return {HTMLElement}
	 */
	getBlockLoader()
	{
		if (!this.blockLoader)
		{
			this.blockLoader = new BX.Loader({size: 60});
			this.blockLoaderContainer = Dom.create('div', {
				props: {className: 'landing-block-loader-container'},
				children: [this.blockLoader.layout],
			});
		}

		return this.blockLoaderContainer;
	}


	/**
	 * Shows block loader
	 * @return {Function}
	 */
	showBlockLoader()
	{
		this.insertToBlocksFlow(this.getBlockLoader());
		this.blockLoader.show();
		return Promise.resolve();
	}


	/**
	 * Hides block loader
	 * @return {Function}
	 */
	hideBlockLoader()
	{
		Dom.remove(this.getBlockLoader());
		this.blockLoader = null;
		return Promise.resolve();
	}


	/**
	 * Loads block dependencies
	 * @param {addBlockResponse} data
	 * @returns {Promise<addBlockResponse>}
	 */
	loadBlockDeps(data)
	{
		const ext = BX.processHTML(data.content_ext);

		if (BX.type.isArray(ext.SCRIPT))
		{
			ext.SCRIPT = ext.SCRIPT.filter((item) => {
				return !item.isInternal;
			});
		}

		if (BX.type.isObject(data.lang))
		{
			Loc.setMessage(data.lang);
		}

		let loadedScripts = 0;
		const scriptsCount = (data.js.length + ext.SCRIPT.length + ext.STYLE.length + data.css.length);
		let resPromise = null;

		if (!this.loadedDeps[data.manifest.code] && scriptsCount > 0)
		{
			resPromise = new Promise(((resolve) => {
				function onLoad()
				{
					loadedScripts += 1;

					if (loadedScripts === scriptsCount)
					{
						resolve(data);
					}
				}

				if (scriptsCount > loadedScripts)
				{
					// Load extensions files
					ext.SCRIPT.forEach((item) => {
						if (!item.isInternal)
						{
							BX.loadScript(item.JS, onLoad);
						}
					});

					ext.STYLE.forEach((item) => {
						BX.loadScript(item, onLoad);
					});

					// Load block files
					data.css.forEach((item) => {
						BX.loadScript(item, onLoad);
					});

					data.js.forEach((item) => {
						BX.loadScript(item, onLoad);
					});
				}
				else
				{
					onLoad();
				}

				this.loadedDeps[data.manifest.code] = true;
			}));
		}
		else
		{
			resPromise = Promise.resolve(data);
		}

		return resPromise.then(data => {
			if (BX.type.isArray(data.assetStrings))
			{
				const head = document.head;
				data.assetStrings.forEach(string => {
					const element = Tag.render`${string}`;
					Dom.insertAfter(element, head.lastChild);
				});
			}

			return data;
		});
	}

	/**
	 * Executes block scripts
	 * @param data
	 * @return {Promise}
	 */
	// eslint-disable-next-line class-methods-use-this
	runBlockScripts(data)
	{
		return new Promise(((resolve) => {
			const scripts = BX.processHTML(data.content).SCRIPT;

			if (scripts.length)
			{
				BX.ajax.processScripts(scripts, undefined, () => {
					resolve(data);
				});
			}
			else
			{
				resolve(data);
			}
		}));
	}


	/**
	 * Load new block from server
	 * @param {string} blockCode
	 * @param {int} [restoreId]
	 * @param {boolean} [preventHistory = false]
	 * @returns {Function}
	 */
	loadBlock(blockCode, restoreId, preventHistory)
	{
		return () => {
			let lid = this.id;
			let siteId = this.options.site_id;

			if (this.currentBlock)
			{
				lid = this.currentBlock.lid;
				siteId = this.currentBlock.siteId;
			}

			if (this.currentArea)
			{
				lid = Dom.attr(this.currentArea, 'data-landing');
				siteId = Dom.attr(this.currentArea, 'data-site');
			}

			let requestBody = {
				lid,
				siteId,
				preventHistory: preventHistory ? 1 : 0,
			};

			const fields = {
				ACTIVE: 'Y',
				CODE: blockCode,
				AFTER_ID: this.currentBlock ? this.currentBlock.id : 0,
				RETURN_CONTENT: 'Y',
			};

			if (!Type.isBoolean(preventHistory) || preventHistory === false)
			{
				// Change history steps
				BX.Landing.History.getInstance().push();
			}

			if (!restoreId)
			{
				requestBody.fields = fields;
				return Backend
					.getInstance()
					.action('Landing::addBlock', requestBody, {code: blockCode})
					.then(result => {
						if (this.insertBefore)
						{
							return Backend
								.getInstance()
								.action('Landing::upBlock', {
									lid,
									siteId,
									block: result.id,
								})
								.then(() => {
									return result;
								});
						}

						return result;
					});
			}

			return BX.Landing.Backend.getInstance()
				.action('Block::getContent', {
					block: restoreId,
					lid,
					fields,
					editMode: 1,
				})
				.then((res) => {
					res.id = restoreId;
					return res;
				});
		};
	}


	/**
	 * Creates block preview card
	 * @param {string} blockKey - Block key (folder name)
	 * @param {{name: string, [preview]: ?string, [new]: ?boolean}} block - Object with block data
	 * @param {string} [mode]
	 * @returns {BX.Landing.UI.Card.BlockPreviewCard}
	 */
	createBlockCard(blockKey, block, mode)
	{
		return new BX.Landing.UI.Card.BlockPreviewCard({
			title: block.name,
			image: block.preview,
			code: blockKey,
			app_expired: block.app_expired,
			favorite: !!block.favorite,
			favoriteMy: !!block.favoriteMy,
			repo_id: block.repo_id,
			mode,
			isNew: block.new === true,
			onClick: this.onAddBlock.bind(this, blockKey),
		});
	}


	/**
	 * Handles block delete event
	 */
	onBlockDelete(block)
	{
		if (!block.parent.querySelector('.block-wrapper'))
		{
			this.adjustEmptyAreas();
		}
		this.sendAnalyticsData('onDeleteBlock', block);
	}


	/**
	 * Shows page overlay
	 */
	// eslint-disable-next-line class-methods-use-this
	showOverlay()
	{
		const main = document.querySelector('main.landing-edit-mode');
		if (main)
		{
			Dom.addClass(main, 'landing-ui-overlay');
		}
	}


	/**
	 * Hides page overlay
	 */
	// eslint-disable-next-line class-methods-use-this
	hideOverlay()
	{
		const main = document.querySelector('main.landing-edit-mode');
		if (main)
		{
			Dom.removeClass(main, 'landing-ui-overlay');
		}
	}

	reloadSlider(url: string): Promise<any>
	{
		return SliderHacks.reloadSlider(url, window.parent);
	}

	sendAnalyticsData(action, data)
	{
		const code = data.manifest.code;
		const block = this.getBlockFromRepository(code);
		let analyticsCategory = '';
		let p2 = '';
		let analyticsEvent = '';
		const type = BX.Landing.Env.getInstance().getType();
		if (type === 'MAINPAGE')
		{
			analyticsCategory = 'vibe';
			if (action === 'onAddBlock')
			{
				analyticsEvent = 'add_widget';
			}

			if (action === 'onDeleteBlock')
			{
				analyticsEvent = 'delete_widget';
			}
			const widgetCode = code.replaceAll(/[._]/g, '-');
			p2 = `widget-id_${widgetCode}`;
		}
		else
		{
			analyticsCategory = 'site'; // site ||  shop || kb
			if (action === 'onAddBlock')
			{
				analyticsEvent = 'add_block';
			}

			if (action === 'onDeleteBlock')
			{
				analyticsEvent = 'delete_block';
			}
			const blockCode = code.replaceAll(/[._]/g, '-');
			p2 = `widget-id_${blockCode}`;
		}
		let itemType = '';
		let p1 = '';
		if (block.repo_id)
		{
			itemType = 'partner'; // partner || local
			if (block.app_code)
			{
				p1 = block.app_code.replaceAll(/[._]/g, '-'); // appCode || local
			}
		}
		else
		{
			itemType = 'system';
			p1 = 'system';
		}

		BX.UI.Analytics.sendData({
			tool: 'landing',
			category: analyticsCategory,
			event: analyticsEvent,
			type: itemType,
			p1,
			p2,
		});
	}
}