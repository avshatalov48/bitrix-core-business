import { Loc, Dom } from 'main.core';
import { ButtonManager, Button } from 'ui.buttons';
import { MessageBox } from 'ui.dialogs.messagebox';

export class Buttons
{
	container: HTMLElement;
	callbacks: Object;
	saveButton: Button;
	removeButton: Button;

	constructor(container: HTMLElement, callbacks: Object)
	{
		this.callbacks = callbacks;
		this.container = container;

		const saveButtonNode = container.querySelector('#ui-button-panel-save');
		if (saveButtonNode)
		{
			saveButtonNode.addEventListener('click', this.handleSaveButtonClick.bind(this));
			this.saveButton = ButtonManager.createFromNode(saveButtonNode);
		}

		const removeButtonNode = container.querySelector('#ui-button-panel-remove');
		if (removeButtonNode)
		{
			removeButtonNode.addEventListener('click', this.handleRemoveButtonClick.bind(this));
			this.removeButton = ButtonManager.createFromNode(removeButtonNode);
		}
	}

	handleSaveButtonClick(e): void
	{
		const clearState = () => {
			this.saveButton.setWaiting(false);
			Dom.removeClass(this.saveButton.getContainer(), 'ui-btn-wait');
		};

		this.callbacks.onSave().then(clearState).catch(clearState);
	}

	handleRemoveButtonClick(e): void
	{
		const clearState = () => {
			this.removeButton.setWaiting(false);
			Dom.removeClass(this.removeButton.getContainer(), 'ui-btn-wait');
		};

		MessageBox.confirm(
			Loc.getMessage('IBLOCK_PROPERTY_DETAILS_REMOVE_POPUP_MESSAGE'),
			() => {
				this.callbacks.onRemove().then(clearState).catch(clearState);

				return true;
			},
			null,
			() => {
				clearState();

				return true;
			}
		);
	}
}
