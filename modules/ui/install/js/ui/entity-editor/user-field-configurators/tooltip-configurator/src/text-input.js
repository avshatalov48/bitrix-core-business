import { Type, Tag, Loc, util, Dom } from 'main.core';
import type { TooltipConfigurator } from './tooltip-configurator';
import './style.css';

export class TextInput
{
	#id: string;
	#inputText: string;
	#labelText: string;
	#configurator: TooltipConfigurator;
	#errorLayout: HTMLElement;
	#inputLayout: HTMLInputElement;
	#wrapper: HTMLElement;

	constructor(
		id: string,
		configurator: TooltipConfigurator,
		inputText: string,
	)
	{
		this.#id = id !== '' ? id : util.getRandomString(4);
		this.#configurator = configurator;
		this.#inputText = inputText;

		this.#labelText = Loc.getMessage('UI_ENTITY_EDITOR_UF_CONFIGURATORS_TOOLTIP_INPUT_LABEL');
		this.#errorLayout = null;
		this.#inputLayout = null;
		this.#wrapper = null;
	}

	prepareLayout(): HTMLElement
	{
		this.#inputLayout = Tag.render`
			<textarea 
				class="ui-ctl-element"
				rows="3"
				id="${this.#id}"
			>${this.#inputText}</textarea>
		`;

		const label = Tag.render`
			<label class="ui-entity-editor-block-title">
				${this.#labelText}
			</label>
		`;

		this.#wrapper = Tag.render`
			<div class="ui-entity-editor-new-field-visibility-wrapper">
				${label}
				${this.#inputLayout}
			</div>
		`;

		return this.#wrapper;
	}

	renderError(errorText): void
	{
		if (Type.isNull(this.#errorLayout))
		{
			this.#errorLayout = Tag.render`
				<label class="ui-entity-editor-user-field-setting-error-text"></label>
			`;

			this.#wrapper.append(this.#errorLayout);
		}

		this.#errorLayout.innerHTML = errorText;
	}

	adjustVisibility(): void
	{
		if (this.#configurator.isInputEnabled())
		{
			Dom.show(this.#wrapper);
		}
		else
		{
			Dom.hide(this.#wrapper);
		}
	}

	getInputText(): string
	{
		return this.#inputLayout.value;
	}
}
