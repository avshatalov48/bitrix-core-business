import { Dom } from 'main.core';

import { Main } from './main';

type Block = {
	id: number,
	state: boolean,
	permissions: {
		allowDesignBlock: boolean,
		allowModifyStyles: boolean,
		allowEditContent: boolean,
		allowSorting: boolean,
		allowRemove: boolean,
		allowChangeState: boolean,
	},
};

type MobileTop = {
	blockId: number,
	top: number,
	height: number,
};

export class ExternalControls
{
	#postMessages = {
		mode: 'mode',
		register: 'register',
		changeState: 'changestate',
		editorEnable: 'editorenable',
		showControls: 'showcontrols',
		showBlockControls: 'showblockcontrols',
		hideAll: 'hideall',
		backendAction: 'backendaction',
	};

	#currentMobileTop: number = -1;
	#mouseEntered: boolean = false;
	#disableControls: boolean = false;
	#currentMousePosition: number = 0;
	#blocksMobileTops: Array<MobileTop> = [];

	constructor()
	{
		if (Main.isExternalControlsEnabled())
		{
			this.#registerListeners();
		}
	}

	/**
	 * Registers all required listeners.
	 */
	#registerListeners()
	{
		setTimeout(() => {
			this.#registerBlocks();
		}, 0);

		// listening commands from outer frame
		window.addEventListener('message', (event) => {
			if (this.isControlsExternal())
			{
				this.listenExternalCommands(event.data.action, event.data.payload);
			}
		});

		// catching the mouse and scrolling
		document.addEventListener('mouseenter', (event) => {
			this.#mouseEntered = true;
		});
		document.addEventListener('mouseleave', (event) => {
			this.#mouseEntered = false;
		});
		document.addEventListener('mousemove', (event) => {
			this.onMobileMouseMove(event.y);
		});
		document.addEventListener('scroll', () => {
			if (this.#mouseEntered)
			{
				this.recalculateTopsIfExternals();
			}
		});

		// checking when external commands become enabled
		BX.addCustomEvent('BX.Landing.Main:changeControls', (type, topInPercent) =>
		{
			if (type === 'internal')
			{
				this.postExternalCommand(this.#postMessages.hideAll, {});
			}
			else
			{
				// mode switching some time
				setTimeout(() => {
					this.recalculateTops(true);
				}, 400);
			}
		});

		// checking inline editor — enabled or disabled
		BX.addCustomEvent('BX.Landing.Editor:enable', () =>
		{
			this.#disableControls = true;
			if (this.isControlsExternal())
			{
				this.postExternalCommand(this.#postMessages.hideAll, {});
			}
		});
		BX.addCustomEvent('BX.Landing.Editor:disable', () =>
		{
			this.#disableControls = false;
			this.recalculateTopsIfExternals(true);
		});

		// checking that new block was added and any block changed its active status
		BX.addCustomEvent('BX.Landing.Block:onAfterAdd', (event) => {
			setTimeout(() => {
				const blockData = event.getData();
				this.#registerNewBlock(blockData.id);
			}, 500);
		});
		BX.addCustomEvent('BX.Landing.Block:changeState', (blockId, state) => {
			this.postExternalCommand(this.#postMessages.changeState, { blockId, state });
		});

		// form's settings were opened and then closed
		BX.addCustomEvent('BX.Landing.Block:onFormSettingsOpen', () => {
			if (this.isControlsExternal())
			{
				this.postExternalCommand(this.#postMessages.hideAll, {});
			}
			this.#disableControls = true;
		});
		BX.addCustomEvent('BX.Landing.Block:onFormSettingsClose', (blockId) => {
			// after form completely closed
			setTimeout(() => {
				this.#disableControls = false;
				this.recalculateTopsIfExternals(true);
			}, 400);
			this.postExternalCommand(this.#postMessages.hideAll, {});
		});
		BX.addCustomEvent('BX.Landing.Block:onAfterFormSave', (blockId) =>
		{
			setTimeout(() => {
				this.postExternalCommand(this.#postMessages.backendAction, {
					action: 'Landing\\Block::saveForm', data: {block: blockId},
				});
			}, 1000);
		});
		BX.addCustomEvent('BX.Landing.Block:onBlockEditClose', () => {
			this.#disableControls = false;
			this.recalculateTopsIfExternals(true);
		});

		BX.addCustomEvent('BX.Landing.Block:onContentSave', this.recalculateTopsIfExternals.bind(this));
		BX.addCustomEvent('BX.Landing.Block:onDesignerBlockSave', this.recalculateTopsIfExternals.bind(this));
		BX.addCustomEvent('BX.Landing.Block:Card:add', this.recalculateTopsIfExternals.bind(this));
		BX.addCustomEvent('BX.Landing.Block:Card:remove', this.recalculateTopsIfExternals.bind(this));
		BX.addCustomEvent('BX.Landing.Block:afterRemove', this.recalculateTopsIfExternals.bind(this));
		BX.addCustomEvent('BX.Landing.Backend:action', this.onBackendAction.bind(this));
		BX.addCustomEvent('BX.Landing.Backend:batch', this.onBackendAction.bind(this));
	}

	/**
	 * Invokes when backend action occurred.
	 */
	onBackendAction(action, data)
	{
		this.#disableControls = false;
		this.postExternalCommand(this.#postMessages.backendAction, { action, data });
	}

	/**
	 * Creates and returns Block object for sending to external window.
	 *
	 * @param {BX.Landing.} block
	 * @return {Block}
	 */
	#createBlockObject(block: BX.Landing.Block): Block
	{
		return {
			id: parseInt(block.id),
			state: block.isEnabled(),
			permissions: {
				allowDesignBlock: block.isDesignBlockAllowed(),
				allowModifyStyles: block.isStyleModifyAllowed(),
				allowEditContent: block.isEditBlockAllowed(),
				allowSorting: block.isEditBlockAllowed(),
				allowRemove: block.isRemoveBlockAllowed(),
				allowChangeState: block.isChangeStateBlockAllowed(),
				allowPaste: block.isPasteBlockAllowed(),
				allowSaveInLibrary: block.isSaveBlockInLibraryAllowed(),
			}
		};
	}

	/**
	 * Registers all blocks on entire page.
	 */
	#registerBlocks()
	{
		const blocksCollection = BX.Landing.PageObject.getBlocks();
		const data = [];

		[...blocksCollection].map(block => data.push(this.#createBlockObject(block)));

		this.postExternalCommand(this.#postMessages.register, {
			blocks: data,
		});
	}

	/**
	 * Registers new block.
	 *
	 * @param {number} blockId
	 */
	#registerNewBlock(blockId: number)
	{
		const block = BX.Landing.PageObject.getBlocks().get(blockId);
		if (block)
		{
			this.postExternalCommand(this.#postMessages.register, {
				blocks: [this.#createBlockObject(block)],
			});
			// because new block adding some time
			if (this.isControlsExternal()) {
				this.recalculateTops();
			} else {
				this.postExternalCommand(this.#postMessages.hideAll, {});
			}
		}
	}

	/**
	 * Checks that landing controls is external
	 *
	 * @return {boolean}
	 */
	isControlsExternal()
	{
		return Dom.hasClass(document.body, 'landing-ui-external-controls');
	}

	/**
	 * Recalculates block tops.
	 *
	 * @param {boolean} resetMobileTop
	 */
	recalculateTops(resetMobileTop: boolean)
	{
		this.#blocksMobileTops = [];

		if (resetMobileTop)
		{
			this.#currentMobileTop = -1;
		}

		[...document.body.querySelectorAll('.block-wrapper')].map(block => {
			const blockRect = block.getBoundingClientRect();
			if (blockRect.height > 1)// hidden on mobile blocks
			{
				this.#blocksMobileTops.push({
					blockId: parseInt(block.getAttribute('data-id')),
					top: blockRect.top,
					height: blockRect.height,
				});
			}
		});

		this.onMobileMouseMove(this.#currentMousePosition);
	}

	/**
	 * Recalculates block tops only if external controls are enabled.
	 *
	 * @param {boolean} resetMobileTop
	 */
	recalculateTopsIfExternals(resetMobileTop: boolean)
	{
		if (this.isControlsExternal())
		{
			this.recalculateTops(resetMobileTop);
		}
	}

	/**
	 * Call when user moves mouse over the mobile page.
	 *
	 * @param {number} top
	 */
	onMobileMouseMove(top: number)
	{
		if (this.#disableControls || !this.isControlsExternal())
		{
			return;
		}

		if (top <= 0)
		{
			this.#currentMobileTop = -1;
			return;
		}

		this.#currentMousePosition = top;

		for (let i = 0, c = this.#blocksMobileTops.length; i < c; i++)
		{
			if (
				top >= this.#blocksMobileTops[i]['top']
				&& (!this.#blocksMobileTops[i+1] || top < this.#blocksMobileTops[i+1]['top'])
			)
			{
				if (this.#blocksMobileTops[i]['top'] !== this.#currentMobileTop)
				{
					this.#currentMobileTop = this.#blocksMobileTops[i]['top'];

					this.postExternalCommand(this.#postMessages.showControls, {
						blockId: this.#blocksMobileTops[i]['blockId'],
						top: this.#blocksMobileTops[i]['top'],
						height: this.#blocksMobileTops[i]['height'],
					});
				}
				break;
			}
		}
	}

	/**
	 * Sends action with payload to parent window.
	 *
	 * @param {string} action
	 * @param {Object} payload
	 */
	postExternalCommand(action, payload)
	{
		if (window.parent)
		{
			window.parent.postMessage({action, payload}, window.location.origin);
		}
	}

	/**
	 * Receives actions with payload from parent window.
	 *
	 * @param {string} action
	 * @param {Object} payload
	 */
	listenExternalCommands(action, payload)
	{
		const block = BX.Landing.PageObject.getBlocks().get(
			payload?.blockId ? payload.blockId : -1
		);

		if (payload?.blockId && !block)
		{
			return;
		}

		const successCallback = () => {
			setTimeout(() => {
				this.#currentMousePosition = 0;
				this.recalculateTops();
			}, 300);
		};

		switch (action)
		{
			case 'onDesignerBlockClick':
			{
				block.onDesignerBlockClick();
				break;
			}
			case 'onEditBlockClick':
			{
				block.onShowContentPanel();
				break;
			}
			case 'onStyleBlockClick':
			{
				block.onStyleShow();
				break;
			}
			case 'onSortDownBlockClick':
			{
				block.moveDown();
				successCallback();
				break;
			}
			case 'onSortUpBlockClick':
			{
				block.moveUp();
				successCallback();
				break;
			}
			case 'onRemoveBlockClick':
			{
				block.deleteBlock();
				break;
			}
			case 'onChangeStateBlockClick':
			{
				block.onStateChange();
				break;
			}
			case 'onCutBlockClick':
			{
				Main.getInstance().onCutBlock.bind(Main.getInstance(), block)();
				break;
			}
			case 'onCopyBlockClick':
			{
				Main.getInstance().onCopyBlock.bind(Main.getInstance(), block)();
				break;
			}
			case 'onPasteBlockClick':
			{
				Main.getInstance().onPasteBlock.bind(
					Main.getInstance(),
					block,
					(blockId) => {
						setTimeout(() => {
							this.#registerNewBlock(blockId);
						}, 300);
					}
				)();
				break;
			}
			case 'onFeedbackClick':
			{
				block.showFeedbackForm();
				break;
			}
			case 'onSaveInLibraryClick':
			{
				block.saveBlock();
				break;
			}
			case 'onHideEditorPanel':
			{
				BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
				break;
			}
		}
	}
}
