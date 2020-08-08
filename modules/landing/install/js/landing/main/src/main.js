import {Event, Type, Dom, Cache, Tag, Text} from 'main.core';
import {Env} from 'landing.env';
import {Loc} from 'landing.loc';
import {Content} from 'landing.ui.panel.content';
import {SliderHacks} from 'landing.sliderhacks';
import {PageObject} from 'landing.pageobject';
import hasBlock from './internal/has-block';
import hasCreateButton from './internal/has-create-button';
import onAnimationEnd from './internal/on-animation-end';
import isEmpty from './internal/is-empty';

const LANG_RU = 'ru';
const LANG_BY = 'by';
const LANG_KZ = 'kz';
const LANG_LA = 'la';
const LANG_DE = 'de';
const LANG_BR = 'br';
const LANG_UA = 'ua';

BX.Landing.getMode = () => 'edit';

/**
 * @memberOf BX.Landing
 */
export class Main extends Event.EventEmitter
{
	static TYPE_PAGE = 'PAGE';
	static TYPE_STORE = 'STORE';

	static getMode()
	{
		return 'edit';
	}

	static createInstance(id: number)
	{
		const rootWindow = BX.Landing.PageObject.getRootWindow();
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

	constructor(id: number)
	{
		super();
		this.setEventNamespace('BX.Landing.Main');

		const options = Env.getInstance().getOptions();

		this.id = id;
		this.options = Object.freeze(options);
		this.blocksPanel = null;
		this.currentBlock = null;
		this.loadedDeps = {};
		this.cache = new Cache.MemoryCache();

		this.onSliderFormLoaded = this.onSliderFormLoaded.bind(this);
		this.onBlockDelete = this.onBlockDelete.bind(this);

		BX.addCustomEvent('Landing.Block:onAfterDelete', this.onBlockDelete);

		this.adjustEmptyAreas();

		if (this.options.blocks)
		{
			if (!this.blocksPanel)
			{
				this.blocksPanel = this.createBlocksPanel();
				this.onBlocksListCategoryChange(this.options.default_section);
				this.blocksPanel.layout.hidden = true;
				Dom.append(this.blocksPanel.layout, document.body);
			}

			this.blocksPanel.content.hidden = false;
		}

		BX.Landing.UI.Panel.StatusPanel.setLastModified(options.lastModified);
		BX.Landing.UI.Panel.StatusPanel.getInstance().show();
	}

	hideBlocksPanel()
	{
		if (this.blocksPanel)
		{
			return this.blocksPanel.hide();
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
	 * Appends block
	 * @param {addBlockResponse} data
	 * @param {boolean} [withoutAnimation]
	 * @returns {HTMLElement}
	 */
	appendBlock(data, withoutAnimation)
	{
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
	 * Shows blocks list panel
	 * @param {BX.Landing.Block} block
	 * @param {HTMLElement} [area]
	 * @param [button]
	 */
	showBlocksPanel(block, area, button)
	{
		this.currentBlock = block;
		this.currentArea = area;
		this.blocksPanel.show();

		this.disableAddBlockButtons();

		if (!!area && !!button)
		{
			this.onCreateButtonMouseout(area, button);
		}
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
		if (!this.sliderFeedbackInited)
		{
			this.sliderFeedbackInited = true;
			this.sliderFeedback = new Content('slider_feedback', {
				title: Loc.getMessage('LANDING_PANEL_FEEDBACK_TITLE'),
				className: 'landing-ui-panel-feedback',
			});
			Dom.append(this.sliderFeedback.layout, document.body);
			this.sliderFormLoader = new BX.Loader({target: this.sliderFeedback.content});
			this.sliderFormLoader.show();
			this.initFeedbackForm();
		}

		data.bitrix24 = this.options.server_name;
		data.siteId = this.options.site_id;
		data.siteUrl = this.options.url;
		data.siteTemplate = this.options.xml_id;
		data.productType = this.options.productType || 'Undefined';
		data.typeproduct = (() => {
			if (this.options.params.type === 'GROUP')
			{
				return 'KNOWLEDGE_GROUP';
			}

			return this.options.params.type;
		})();

		const form = this.getFeedbackFormOptions();

		window.b24formFeedBack({
			id: form.id,
			lang: form.lang,
			sec: form.sec,
			type: 'slider_inline',
			node: this.sliderFeedback.content,
			handlers: {
				load: this.onSliderFormLoaded.bind(this),
			},
			presets: Type.isPlainObject(data) ? data : {},
		});

		this.sliderFeedback.show();
	}


	/**
	 * Gets feedback form options
	 * @return {{id: string, sec: string, lang: string}}
	 */
	// eslint-disable-next-line class-methods-use-this
	getFeedbackFormOptions()
	{
		const currentLanguage = Loc.getMessage('LANGUAGE_ID');
		let options = {id: '16', sec: '3h483y', lang: 'en'};

		switch (currentLanguage)
		{
			case LANG_RU:
			case LANG_BY:
			case LANG_KZ:
				options = {id: '8', sec: 'x80yjw', lang: 'ru'};
				break;
			case LANG_LA:
				options = {id: '14', sec: 'wu561i', lang: 'la'};
				break;
			case LANG_DE:
				options = {id: '10', sec: 'eraz2q', lang: 'de'};
				break;
			case LANG_BR:
				options = {id: '12', sec: 'r6wvge', lang: 'br'};
				break;
			case LANG_UA:
				options = {id: '18', sec: 'd9e09o', lang: 'ua'};
				break;
			default:
				break;
		}

		return options;
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
		})(window, document, 'https://landing.bitrix24.ru/bitrix/js/crm/form_loader.js', 'b24formFeedBack');
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
	 * Handles event on blocks list category change
	 * @param {string} category - Category id
	 */
	onBlocksListCategoryChange(category)
	{
		this.blocksPanel.content.hidden = false;

		this.blocksPanel.sidebarButtons.forEach((button) => {
			const action = button.id === category ? 'add' : 'remove';
			button.layout.classList[action]('landing-ui-active');
		});

		this.blocksPanel.content.innerHTML = '';

		if (category === 'last')
		{
			if (!this.lastBlocks)
			{
				this.lastBlocks = Object.keys(this.options.blocks.last.items);
			}

			this.lastBlocks = [...new Set(this.lastBlocks)];

			this.lastBlocks.forEach((blockKey) => {
				const block = this.getBlockFromRepository(blockKey);
				this.blocksPanel.appendCard(this.createBlockCard(blockKey, block));
			});

			return;
		}

		Object.keys(this.options.blocks[category].items).forEach((blockKey) => {
			const block = this.options.blocks[category].items[blockKey];
			this.blocksPanel.appendCard(this.createBlockCard(blockKey, block));
		});

		if (this.blocksPanel.content.scrollTop)
		{
			requestAnimationFrame(() => {
				this.blocksPanel.content.scrollTop = 0;
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
	 */
	onPasteBlock(block)
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
					return this.addBlock(res[action].result.content);
				});
		}
	}


	/**
	 * Adds block from server response
	 * @param {addBlockResponse} res
	 * @param {boolean} [preventHistory = false]
	 * @param {boolean} [withoutAnimation = false]
	 * @return {Promise<T>}
	 */
	addBlock(res, preventHistory, withoutAnimation)
	{
		if (this.lastBlocks)
		{
			this.lastBlocks.unshift(res.manifest.code);
		}

		const self = this;
		const block = this.appendBlock(res, withoutAnimation);

		return this.loadBlockDeps(res)
			.then((blockRes) => {
				if (!Type.isBoolean(preventHistory) || preventHistory === false)
				{
					let lid = null;
					let id = null;

					if (self.currentBlock)
					{
						lid = self.currentBlock.lid;
						id = self.currentBlock.id;
					}

					if (self.currentArea)
					{
						lid = Dom.attr(self.currentArea, 'data-landing');
						id = Dom.attr(self.currentArea, 'data-site');
					}

					// Add history entry
					BX.Landing.History.getInstance().push(
						new BX.Landing.History.Entry({
							block: blockRes.id,
							selector: `#block${blockRes.id}`,
							command: 'addBlock',
							undo: '',
							redo: {
								currentBlock: id,
								lid,
								code: blockRes.manifest.code,
							},
						}),
					);
				}

				self.currentBlock = null;
				self.currentArea = null;

				const blockId = parseInt(res.id);
				const oldBlock = BX.Landing.PageObject.getBlocks().get(blockId);

				if (oldBlock)
				{
					Dom.remove(oldBlock.node);
					BX.Landing.PageObject.getBlocks().remove(oldBlock);
				}

				// Init block entity
				void new BX.Landing.Block(block, {
					id: blockId,
					requiredUserAction: res.requiredUserAction,
					manifest: res.manifest,
					access: res.access,
					active: Text.toBoolean(res.active),
					anchor: res.anchor,
					dynamicParams: res.dynamicParams,
				});

				return self.runBlockScripts(res)
					.then(() => {
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
	onAddBlock(blockCode, restoreId, preventHistory)
	{
		const id = Text.toNumber(restoreId);

		this.hideBlocksPanel();

		return this.showBlockLoader()
			.then(this.loadBlock(blockCode, id))
			.then((res) => {
				return new Promise((resolve) => {
					setTimeout(() => {
						resolve(res);
					}, 500);
				});
			})
			.then((res) => {
				const p = this.addBlock(res, preventHistory);
				this.adjustEmptyAreas();
				void this.hideBlockLoader();
				this.enableAddBlockButtons();
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
		const insertAfterCurrentBlock = (
			this.currentBlock
			&& this.currentBlock.node
			&& this.currentBlock.node.parentNode
		);

		if (insertAfterCurrentBlock)
		{
			Dom.insertAfter(element, this.currentBlock.node);
			return;
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

		return resPromise;
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
	 * @returns {Function}
	 */
	loadBlock(blockCode, restoreId)
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
			};

			const fields = {
				ACTIVE: 'Y',
				CODE: blockCode,
				AFTER_ID: this.currentBlock ? this.currentBlock.id : 0,
				RETURN_CONTENT: 'Y',
			};

			if (!restoreId)
			{
				requestBody.fields = fields;
				return BX.Landing.Backend.getInstance()
					.action('Landing::addBlock', requestBody, {code: blockCode});
			}

			requestBody = {
				undeleete: {
					action: 'Landing::markUndeletedBlock',
					data: {
						lid,
						block: restoreId,
					},
				},
				getContent: {
					action: 'Block::getContent',
					data: {
						block: restoreId,
						lid,
						fields,
						editMode: 1,
					},
				},
			};

			return BX.Landing.Backend.getInstance()
				.batch('Landing::addBlock', requestBody, {code: blockCode})
				.then((res) => {
					res.getContent.result.id = restoreId;
					return res.getContent.result;
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
}