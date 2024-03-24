import { Dom, Event, Tag, Text, Type } from 'main.core';
import 'ui.icon-set.actions';

type Params = {
	name: string,
	canEdit: boolean,
	onChange: void,
}

export class EditTitle
{
	#params: Params;
	#layout: {
		editTitleContainer: HTMLElement,
		titleText: HTMLElement,
		editTitleButton: HTMLElement,
		editTitleInput: HTMLInputElement,
	};

	constructor(params: Params)
	{
		this.#layout = {};
		this.#params = params;
	}

	render(): HTMLElement
	{
		this.#layout.editTitleContainer = Tag.render`
			<div class="sn-group-settings__title-edit-container">
				<div class="sn-group-settings__title-edit">
					${this.#renderTitleText()}
					${this.#renderEditTitleButton()}
				</div>
				<div class="
					ui-ctl ui-ctl-textbox ui-ctl--w100 ui-ctl--transp ui-ctl-no-border ui-ctl-xs ui-ctl-no-padding
					sn-group-settings__title-edit-input
				">
					${this.#renderEditTitleInput()}
				</div>
			</div>
		`;

		return this.#layout.editTitleContainer;
	}

	#renderTitleText(): HTMLElement
	{
		this.#layout.titleText = Tag.render`
			<div class="sn-group-settings__title-edit-text">${Text.encode(this.#params.name)}</div>
		`;

		return this.#layout.titleText;
	}

	#renderEditTitleButton(): HTMLElement|string
	{
		if (this.#params.canEdit !== true)
		{
			return '';
		}

		this.#layout.editTitleButton = Tag.render`
			<div class="ui-icon-set --pencil-40"></div>
		`;

		Event.bind(this.#layout.editTitleButton, 'click', () => this.#startEditing());

		return this.#layout.editTitleButton;
	}

	#renderEditTitleInput(): HTMLInputElement
	{
		this.#layout.editTitleInput = Tag.render`
			<input type="text" value="${Text.encode(this.#params.name)}" class="ui-ctl-element">
		`;

		Event.bind(this.#layout.editTitleInput, 'blur', () => this.#stopEditing());

		Event.bind(this.#layout.editTitleInput, 'keydown', (event: KeyboardEvent) => {
			if (event.key === 'Enter')
			{
				this.#stopEditing();
			}
		});

		return this.#layout.editTitleInput;
	}

	#startEditing()
	{
		Dom.addClass(this.#layout.editTitleContainer, '--editing');
		this.#layout.editTitleInput.focus();
		this.#layout.editTitleInput.setSelectionRange(999, 999);
		this.#layout.editTitleInput.scrollTo({
			left: this.#layout.editTitleInput.scrollWidth,
			behavior: 'smooth',
		});
	}

	#stopEditing()
	{
		const name = this.#layout.editTitleInput.value.trim();

		if (Type.isStringFilled(name) && this.#params.name !== name)
		{
			this.setTitle(name);
			this.#params.onChange(this.#params.name);
		}
		else
		{
			this.setTitle(this.#params.name);
		}

		Dom.removeClass(this.#layout.editTitleContainer, '--editing');
	}

	setTitle(title: string): void
	{
		this.#layout.editTitleInput.value = title;
		this.#params.name = title;
		this.#layout.titleText.innerHTML = Text.encode(this.#params.name);
	}
}