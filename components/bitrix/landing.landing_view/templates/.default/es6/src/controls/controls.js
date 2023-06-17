import { Dom, Type } from 'main.core';

import { Action } from './controls.action';
import { Loc } from './controls.loc';
import { UI } from './controls.ui';

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
		allowPaste: boolean,
		allowSaveInLibrary: boolean,
	},
	leftContainer?: HTMLElement,
	rightContainer?: HTMLElement,
};

type ExternalControlsOptions = {
	container: HTMLElement,
	iframeWrapper: HTMLElement,
	messages: {[type: string]: string},
};

export class ExternalControls
{

	#action: Action;
	#externalBlocks: Map;
	#container: HTMLElement;
	#iframeWrapper: HTMLElement;
	#currentOpenBlockId: ?number;
	#currentOpenMenuBlock: ?number;

	constructor(options: ExternalControlsOptions)
	{
		options = options || {};

		this.#container = options.container;
		this.#iframeWrapper = options.iframeWrapper;

		if (!Type.isDomNode(this.#container))
		{
			throw new Error("Missed 'container' option as Dom Node.");
		}
		if (!Type.isDomNode(this.#iframeWrapper))
		{
			throw new Error("Missed 'iframe' option as Dom Node.");
		}

		this.#externalBlocks = new Map();
		this.#action = new Action({
			iframe: this.#iframeWrapper.querySelector('iframe'),
		});

		Loc.loadMessages(options.messages);

		window.addEventListener('message', this.#listenChildFrame.bind(this));

		this.#container.addEventListener('click', (event) => {
			this.#action.onHideEditorPanel();
		});

		window.addEventListener('storage', this.#onStorageChange.bind(this));
	}

	/**
	 * Handler on listening child iframe commands.
	 *
	 * @param event
	 */
	#listenChildFrame(event)
	{
		const data = event.data || {};

		if (!data.payload)
		{
			return;
		}

		if (data.action === 'register')
		{
			this.#registerBlocks(data.payload.blocks);
		}
		else if (data.action === 'showcontrols')
		{
			this.#showControls(data.payload.blockId, data.payload.top, data.payload.height);
		}
		else if (data.action === 'changestate')
		{
			this.#changeState(data.payload.blockId, data.payload.state);
		}
		else if (data.action === 'mode')
		{
			this.#onChangeMode(data.payload);
		}
		else if (data.action === 'hideall')
		{
			this.#hideAllControls();
		}
		else if (data.action === 'showblockcontrols')
		{
			this.#hideAllControls();
			this.#hideAndShowControls(data.payload.blockId);
		}
	}

	/**
	 * Handler on listening Storage changing.
	 */
	#onStorageChange()
	{
		const blocks = this.#externalBlocks.values();
		const allowPaste = !!window.localStorage.getItem('landingBlockId');

		for (let i = 0, c = this.#externalBlocks.size; i < c; i++)
		{
			const blockItem = blocks.next().value;

			UI.changePasteMenuItem(blockItem.id, allowPaste);
			this.#updateBlock(
				blockItem.id,
				{...blockItem, permissions: {...blockItem.permissions, allowPaste}}
			);
		}
	}

	/**
	 * Registers Block's controls for current page.
	 *
	 * @param {Array<Block>} blocks Array of Blocks.
	 */
	#registerBlocks(blocks: Array<Block>)
	{
		blocks.map(block => {

			const blockId = block.id;

			// left controls
			block.leftContainer = UI.getLeftContainer({
				designerBlockClick: block.permissions.allowDesignBlock ? () => {
					this.#action.onDesignerBlockClick(blockId);
				} : null,
				styleBlockClick: block.permissions.allowModifyStyles ? () => {
					this.#action.onStyleBlockClick(blockId);
				} : null,
				editBlockClick: block.permissions.allowEditContent ? () => {
					this.#action.onEditBlockClick(blockId);
				} : null,
			});

			// right controls
			block.rightContainer = UI.getRightContainer({
				blockId,
				state: block.state,
				sortDownBlockClick: block.permissions.allowSorting ? () => {
					this.#action.onSortDownBlockClick(blockId);
					this.#hideAllControls();
				} : null,
				sortUpBlockClick: block.permissions.allowSorting ? () => {
					this.#action.onSortUpBlockClick(blockId);
					this.#hideAllControls();
				} : null,
				removeBlockClick: block.permissions.allowRemove ? () => {
					this.#action.onRemoveBlockClick(blockId);
					this.#hideAllControls();
				}: null,
				onOpenAdditionalMenu: (blockId) => {
					this.#currentOpenMenuBlock = blockId;
					setTimeout(() => {
						this.#action.onHideEditorPanel();
					}, 0);
				},
				changeStateClick: block.permissions.allowChangeState ? () => {
					UI.closeBlockAdditionalMenu(blockId);
					this.#action.onChangeStateBlockClick(blockId);
					this.#hideAndShowControls(blockId);
				}: null,
				cutClick: block.permissions.allowRemove ? () => {
					this.#action.onCutBlockClick(blockId);
					this.#hideAllControls();
				}: null,
				copyClick: () => {
					UI.closeBlockAdditionalMenu(blockId);
					this.#action.onCopyBlockClick(blockId);
					this.#hideAndShowControls(blockId);
				},
				pasteClick: block.permissions.allowPaste ? () => {
					UI.closeBlockAdditionalMenu(blockId);
					this.#action.onPasteBlockClick(blockId);
					this.#hideAndShowControls(blockId);
				}: null,
				feedbackClick: () => {
					UI.closeBlockAdditionalMenu(blockId);
					this.#action.onFeedbackClick(blockId);
				},
				saveInLibrary: block.permissions.allowSaveInLibrary ? () => {
					UI.closeBlockAdditionalMenu(blockId);
					this.#action.onSaveInLibraryClick(blockId);
				}: null,
			});

			Dom.append(block.leftContainer, this.#container);
			Dom.append(block.rightContainer, this.#container);

			Dom.hide(block.leftContainer);
			Dom.hide(block.rightContainer);

			this.#externalBlocks.set(blockId, block);
		});
	}

	/**
	 * Creates new block or takes it from Map.
	 *
	 * @param {number} blockId Block id.
	 * @return {Block}
	 */
	#getBlock(blockId: number): ?Block
	{
		return this.#externalBlocks.get(parseInt(blockId));
	}

	/**
	 * Updates Block.
	 *
	 * @param blockId Block id.
	 * @param {Block} data New Block data.
	 */
	#updateBlock(blockId: number, data: Block)
	{
		this.#externalBlocks.set(parseInt(blockId), data);
	}

	/**
	 * Changes state for the Block.
	 *
	 * @param {number} blockId Block id
	 * @param state
	 */
	#changeState(blockId: number, state: boolean)
	{
		const block = this.#getBlock(blockId);
		if (block)
		{
			UI.changeStateMenuItem(blockId, state);
			this.#updateBlock(blockId, {...block, state});
		}
	}

	/**
	 * Hides all controls.
	 */
	#hideAllControls()
	{
		if (this.#currentOpenBlockId)
		{
			const blockItem = this.#externalBlocks.get(this.#currentOpenBlockId);
			Dom.hide(blockItem.leftContainer);
			Dom.hide(blockItem.rightContainer);
		}
		else
		{
			const blocks = this.#externalBlocks.values();
			for (let i = 0, c = this.#externalBlocks.size; i < c; i++)
			{
				const blockItem = blocks.next().value;
				Dom.hide(blockItem.leftContainer);
				Dom.hide(blockItem.rightContainer);
			}
		}

		// if some menu is opened, close it
		if (this.#currentOpenMenuBlock)
		{
			UI.closeBlockAdditionalMenu(this.#currentOpenMenuBlock);
			this.#currentOpenMenuBlock = null;
		}
	}

	/**
	 * Shows controls for block.
	 *
	 * @param {number} blockId Block's id.
	 * @param {number} top Block's top position.
	 * @param {number} height Block's height.
	 */
	#showControls(blockId: number, top: number, height: number)
	{
		const block = this.#getBlock(blockId);

		if (!block)
		{
			return;
		}

		const iframeRect = this.#iframeWrapper.getBoundingClientRect();
		this.#hideAllControls();
		this.#currentOpenBlockId = block.id;
		this.#action.onHideEditorPanel();
		top = parseInt(top);

		// adjust top and bottom borders
		if (top < 0 && height + top > 50)
		{
			height = height + top;
			top = 0;
			Dom.addClass(block.leftContainer, 'hide-top');
			Dom.addClass(block.rightContainer, 'hide-top');
		}
		else
		{
			Dom.removeClass(block.leftContainer, 'hide-top');
			Dom.removeClass(block.rightContainer, 'hide-top');
		}

		Dom.show(block.leftContainer);
		Dom.show(block.rightContainer);

		// adjust top and heights
		block.leftContainer.style.width = iframeRect.left + 'px';
		block.leftContainer.style.top = top + 'px';
		block.leftContainer.style.height = height + 'px';

		block.rightContainer.style.width = iframeRect.left + 'px';
		block.rightContainer.style.left = iframeRect.left + iframeRect.width + 'px';
		block.rightContainer.style.top = top + 'px';
		block.rightContainer.style.height = height + 'px';
	}

	/**
	 * Hides and shows controls (blink) for specific Block.
	 *
	 * @param {blockId} blockId Block id.
	 */
	#hideAndShowControls(blockId: number)
	{
		const block = this.#getBlock(blockId);
		if (block)
		{
			this.#currentOpenBlockId = null;
			Dom.hide(block.leftContainer);
			Dom.hide(block.rightContainer);

			setTimeout(() => {
				this.#currentOpenBlockId = block.id;
				Dom.show(block.leftContainer);
				Dom.show(block.rightContainer);
			}, 500);
		}
	}

	/**
	 * When user toggles mode (internal vs external controls).
	 *
	 * @param {{[type: string]: string}} data New mode data.
	 */
	#onChangeMode(data: {[type: string]: string})
	{
		if (data.type === 'internal')
		{
			this.#hideAllControls();
		}
	}
}
