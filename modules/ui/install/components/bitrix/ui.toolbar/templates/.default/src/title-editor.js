import { bind, Dom, Event } from 'main.core';

export type TitleEditorOptions = {
	active: boolean;
	selector: HTMLElement;
	disabled?: boolean;
	defaultTitle?: string;
}

export const TitleEditorEvents = {
	beforeStartEditing: 'beforeStartEditing',
	startEditing: 'startEditing',
	finishEditing: 'finishEditing',
};

export class TitleEditor extends Event.EventEmitter
{
	// #dataContainer: ?HTMLElement;
	#initialTitle: string;
	#defaultTitle: string;
	#toolbarNode: ?HTMLElement;
	#titleNode: ?HTMLElement;
	#inputNode: ?HTMLInputElement;
	// #dataNode: ?HTMLElement;
	#editTitleButtonNode: ?HTMLElement;
	#editTitleResultButtonsContainer: ?HTMLElement;
	#titleIconButtonsContainer: ?HTMLElement;
	#saveTitleButton: ?HTMLElement;
	#cancelTitleEditButton: ?HTMLElement;
	#isInit: boolean = false;

	constructor(options: TitleEditorOptions)
	{
		super(options);
		this.setEventNamespace('UI.Toolbar.TitleEditor');
		this.#init(options);
	}

	#init(params: TitleEditorOptions)
	{
		// if (!params.selector)
		// {
		// 	return;
		// }
		//
		// this.dataContainer = document.querySelector(params.selector);
		// if (!this.dataContainer)
		// {
		// 	return;
		// }
		//
		// Dom.style(this.dataContainer, 'display', 'none');

		this.#toolbarNode = document.getElementById('uiToolbarContainer');
		this.#titleNode = document.querySelector('.ui-wrap-title-name');
		this.#inputNode = document.querySelector('.ui-toolbar-edit-title-input');
		this.#editTitleButtonNode = document.querySelector('.ui-toolbar-edit-title-button');
		this.#editTitleResultButtonsContainer = document.getElementById('ui-toolbar-title-edit-result-buttons');
		this.#saveTitleButton = document.getElementById('ui-toolbar-save-title-button');
		this.#cancelTitleEditButton = document.getElementById('ui-toolbar-cancel-title-edit-button');
		this.#titleIconButtonsContainer = document.getElementById('ui-toolbar-title-item-box-buttons');
		this.#initialTitle = this.#titleNode.textContent;
		this.#defaultTitle = params.defaultTitle;

		// bind(this.dataNode, 'bxchange', this.onDataNodeChange.bind(this));
		bind(this.#editTitleButtonNode, 'click', this.startEdit.bind(this));
		bind(this.#inputNode, 'keyup', this.onKeyUp.bind(this));
		bind(this.#inputNode, 'blur', (event) => {
			if (event.relatedTarget === this.#cancelTitleEditButton)
			{
				this.cancelEdit();

				return;
			}

			this.finishEdit();
		});
		bind(this.#saveTitleButton, 'click', this.finishEdit.bind(this));
		bind(this.#cancelTitleEditButton, 'click', this.cancelEdit.bind(this));

		this.#isInit = true;

		if (!params.disabled)
		{
			this.enable();
		}
	}

	enable(isDisable: boolean = false)
	{
		if (!this.#isInit)
		{
			return;
		}

		this.changeDisplay(this.#editTitleButtonNode, isDisable === false);
		// this.titleNode.textContent = isDisable === false
		// 	? (this.dataNode.value ?? this.defaultTitle)
		// 	: this.initialTitle
		// ;

		this.#titleNode.textContent = this.#initialTitle;
	}

	disable(): void
	{
		this.enable(true);
	}

	// onDataNodeChange()
	// {
	// 	this.#titleNode.textContent = this.#dataNode.value;
	// }

	onKeyUp(event: KeyboardEvent): boolean
	{
		if (event.key === 'Enter')
		{
			this.finishEdit();
			event.preventDefault();

			return false;
		}

		return true;
	}

	startEdit(): void
	{
		// this.inputNode.value = this.dataNode.value || this.titleNode.textContent;

		const event = new Event.BaseEvent();
		this.emit(TitleEditorEvents.beforeStartEditing, event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		this.#inputNode.value = this.#titleNode.textContent;
		this.changeDisplay(this.#titleNode, false);
		this.changeDisplay(this.#editTitleButtonNode, false);
		this.changeDisplay(this.#inputNode, true);
		this.changeDisplay(this.#titleIconButtonsContainer, false);
		Dom.addClass(this.#editTitleResultButtonsContainer, '--show');
		Dom.addClass(this.#toolbarNode, '--title-editing');

		this.#inputNode.focus();

		this.emit(TitleEditorEvents.startEditing);
	}

	finishEdit()
	{
		// this.dataNode.value = this.inputNode.value;
		this.#titleNode.textContent = this.#inputNode.value;
		this.changeDisplay(this.#inputNode, false);
		this.changeDisplay(this.#editTitleButtonNode, true);
		this.changeDisplay(this.#titleNode, true);
		this.changeDisplay(this.#titleIconButtonsContainer, true);
		Dom.removeClass(this.#editTitleResultButtonsContainer, '--show');
		Dom.removeClass(this.#toolbarNode, '--title-editing');

		this.emit(TitleEditorEvents.finishEditing, {
			updatedTitle: this.#inputNode.value,
		});
	}

	cancelEdit(): void
	{
		this.changeDisplay(this.#inputNode, false);
		this.changeDisplay(this.#editTitleButtonNode, true);
		this.changeDisplay(this.#titleNode, true);
		this.changeDisplay(this.#titleIconButtonsContainer, true);
		Dom.removeClass(this.#editTitleResultButtonsContainer, '--show');
		Dom.removeClass(this.#toolbarNode, '--title-editing');
	}

	changeDisplay(node: HTMLElement, isShow: boolean): string
	{
		const displayValue = isShow ? '' : 'none';

		Dom.style(node, 'display', displayValue);

		return displayValue;
	}
}
