import { Dom, Event, Loc, Tag, Text, Type } from 'main.core';
import 'ui.icon-set.actions';

type Params = {
	description: string,
	canEdit: boolean,
	onChange: void,
};

export class EditDescription
{
	#params: Params;
	#layout: {
		descriptionContainer: HTMLElement,
		descriptionText: HTMLElement,
		descriptionEditButton: HTMLElement,
		descriptionTextarea: HTMLTextAreaElement,
	};

	constructor(params: Params)
	{
		this.#params = params;
		this.#layout = {};
	}

	render(): HTMLElement
	{
		this.#layout.descriptionContainer = Tag.render`
			<div class="sn-group-settings__description-container">
				<div class="sn-group-settings__description">
					${this.#renderDescriptionEditText()}
				</div>
				${this.#renderDescriptionTextarea()}
			<div>
		`;

		return this.#layout.descriptionContainer;
	}

	#renderDescriptionEditText(): HTMLElement
	{
		this.#layout.descriptionText = Tag.render`
			<div>
				${this.#getDescriptionText()}
				${this.#renderDescriptionEditButton()}
			</div>
		`;

		return this.#layout.descriptionText;
	}

	#renderDescriptionEditButton(): HTMLElement|string
	{
		if (this.#params.canEdit !== true)
		{
			return '';
		}

		this.#layout.descriptionEditButton = Tag.render`
			<div class="ui-icon-set --pencil-40"></div>
		`;

		Event.bind(this.#layout.descriptionEditButton, 'click', () => this.#startEditing());

		return this.#layout.descriptionEditButton;
	}

	#renderDescriptionTextarea(): HTMLElement
	{
		this.#layout.descriptionTextarea = Tag.render`
			<textarea
				class="sn-group-settings__description-textarea"
				maxlength="20000"
				rows="1"
			>${Text.encode(this.#params.description)}</textarea>
		`;

		Event.bind(this.#layout.descriptionTextarea, 'focus', () => this.#adjustTextareaHeight());

		Event.bind(this.#layout.descriptionTextarea, 'blur', () => this.#stopEditing());

		Event.bind(this.#layout.descriptionTextarea, 'keydown', (event: KeyboardEvent) => {
			if (event.key === 'Enter')
			{
				this.#stopEditing();
			}
		});

		Event.bind(this.#layout.descriptionTextarea, 'input', () => this.#adjustTextareaHeight());

		return this.#layout.descriptionTextarea;
	}

	#startEditing()
	{
		Dom.addClass(this.#layout.descriptionContainer, '--editing');
		this.#layout.descriptionTextarea.focus();
		this.#layout.descriptionTextarea.setSelectionRange(20000, 20000);
	}

	#stopEditing()
	{
		Dom.removeClass(this.#layout.descriptionContainer, '--editing');
		this.#layout.descriptionTextarea.value = this.#layout.descriptionTextarea.value.trim();
		if (this.#params.description !== this.#layout.descriptionTextarea.value)
		{
			this.#params.description = this.#layout.descriptionTextarea.value;
			this.#layout.descriptionText.innerHTML = this.#getDescriptionText();
			this.#layout.descriptionText.append(this.#layout.descriptionEditButton);
			this.#params.onChange(this.#params.description);
		}
	}

	#getDescriptionText()
	{
		if (Type.isStringFilled(this.#params.description?.trim()))
		{
			return Text.encode(this.#params.description);
		}

		return Loc.getMessage('SN_GROUP_SETTINGS_NO_DESCRIPTION');
	}

	#adjustTextareaHeight(): void
	{
		Dom.style(this.#layout.descriptionTextarea, 'height', 0);
		Dom.style(this.#layout.descriptionTextarea, 'maxHeight', 0);

		const height = this.#layout.descriptionTextarea.scrollHeight;
		Dom.style(this.#layout.descriptionTextarea, 'height', `${height}px`);
		Dom.style(this.#layout.descriptionTextarea, 'maxHeight', `${height}px`);
	}
}