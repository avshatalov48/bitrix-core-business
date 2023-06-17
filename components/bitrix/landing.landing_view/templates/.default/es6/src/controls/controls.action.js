import { Type } from 'main.core';

type ActionOptions = {
	iframe: HTMLIFrameElement,
};

export class Action
{
	#iframe: HTMLIFrameElement;

	constructor(options: ActionOptions)
	{
		this.#iframe = options.iframe;

		if (!Type.isDomNode(this.#iframe))
		{
			throw new Error("Missed 'frame' option as iFrame Element.");
		}
	}

	/**
	 * Sends action with payload to child window.
	 *
	 * @param {string} action Command to internal iframe.
	 * @param {Object} payload Command's payload.
	 */
	#postInternalCommand(action, payload)
	{
		this.#iframe.contentWindow.postMessage({action, payload}, window.location.origin);
	}

	/**
	 * Handles on Designer click.
	 *
	 * @param {number} blockId Block id.
	 */
	onDesignerBlockClick(blockId: number)
	{
		this.#postInternalCommand('onDesignerBlockClick', {blockId});
	}

	/**
	 * Handles on Style Block click.
	 *
	 * @param {number} blockId Block id.
	 */
	onStyleBlockClick(blockId: number)
	{
		this.#postInternalCommand('onStyleBlockClick', {blockId});
	}

	/**
	 * Handles on Edit Block click.
	 *
	 * @param {number} blockId Block id.
	 */
	onEditBlockClick(blockId: number)
	{
		this.#postInternalCommand('onEditBlockClick', {blockId});
	}

	/**
	 * Handles on Down Block click.
	 *
	 * @param {number} blockId Block id.
	 */
	onSortDownBlockClick(blockId: number)
	{
		this.#postInternalCommand('onSortDownBlockClick', {blockId});
	}

	/**
	 * Handles on Up Block click.
	 *
	 * @param {number} blockId Block id.
	 */
	onSortUpBlockClick(blockId: number)
	{
		this.#postInternalCommand('onSortUpBlockClick', {blockId});
	}

	/**
	 * Handles on Remove Block click.
	 *
	 * @param {number} blockId Block id.
	 */
	onRemoveBlockClick(blockId: number)
	{
		this.#postInternalCommand('onRemoveBlockClick', {blockId});
	}

	/**
	 * Handles on Change State Block click.
	 *
	 * @param {number} blockId Block id.
	 */
	onChangeStateBlockClick(blockId: number)
	{
		this.#postInternalCommand('onChangeStateBlockClick', {blockId});
	}

	/**
	 * Handles on Cut Block click.
	 *
	 * @param {number} blockId Block id.
	 */
	onCutBlockClick(blockId: number)
	{
		this.#postInternalCommand('onCutBlockClick', {blockId});
	}

	/**
	 * Handles on Copy Block click.
	 *
	 * @param {number} blockId Block id.
	 */
	onCopyBlockClick(blockId: number)
	{
		this.#postInternalCommand('onCopyBlockClick', {blockId});
	}

	/**
	 * Handles on Paste Block click.
	 *
	 * @param {number} blockId Block id.
	 */
	onPasteBlockClick(blockId: number)
	{
		this.#postInternalCommand('onPasteBlockClick', {blockId});
	}

	/**
	 * Handles on Feedback click.
	 *
	 * @param {number} blockId Block id.
	 */
	onFeedbackClick(blockId: number)
	{
		this.#postInternalCommand('onFeedbackClick', {blockId});
	}

	/**
	 * Handles on Save In Library click.
	 *
	 * @param {number} blockId Block id.
	 */
	onSaveInLibraryClick(blockId: number)
	{
		this.#postInternalCommand('onSaveInLibraryClick', {blockId});
	}

	/**
	 * Hide opened editor panel.
	 */
	onHideEditorPanel()
	{
		this.#postInternalCommand('onHideEditorPanel');
	}
}
