import {Event, Tag, Type, Text} from "main.core";
import {BaseField} from "./base-field";

export class TextInput extends BaseField
{
	defaultValue: string;
	hintTitle: string;
	placeholder: string;
	#inputNode: HTMLElement;
	#maxlength: number;

	constructor(params)
	{
		super(params);
		this.defaultValue = Type.isStringFilled(params.value) ? params.value : '';
		this.hintTitle = Type.isStringFilled(params.hintTitle) ? params.hintTitle : '';
		this.placeholder = Type.isStringFilled(params.placeholder) ? params.placeholder : '';
		this.#maxlength = Type.isInteger(params.maxlength) ? params.maxlength : 255;
		this.inputDefaultWidth = Type.isBoolean(params.inputDefaultWidth) ? params.inputDefaultWidth : '';
		this.inputType = Type.isStringFilled(params.type) ? params.type : 'text';

		if (!this.isEnable())
		{
			Event.bind(this.getInputNode(), 'click', (event) => {
				event.preventDefault();
				if (!Type.isNil(this.getHelpMessage()))
				{
					this.getHelpMessage().show();
				}
			});
		}

		if (this.isEnable())
		{
			this.getInputNode().addEventListener('input', () => {
				this.getInputNode().form.dispatchEvent(new window.Event('change'));
			});
		}
	}

	prefixId(): string
	{
		return 'text_';
	}

	getValue(): string
	{
		return this.getInputNode().value;
	}

	getInputNode(): HTMLElement
	{
		this.#inputNode ??= this.#renderInputNode();

		return this.#inputNode;
	}

	#renderInputNode(): HTMLElement
	{
		return Tag.render`
			<input
				value="${Text.encode(this.defaultValue)}" 
				name="${Text.encode(this.getName())}" 
				type="${this.inputType}" 
				class="ui-ctl-element ${this.isEnable() ? '' : '--readonly'}" 
				placeholder="${Text.encode(this.placeholder)}"
				maxlength="${parseInt(this.#maxlength, 10)}"
				${this.isEnable() ? '' : 'readonly'}
			>
		`;
	}

	renderContentField(): HTMLElement
	{
		const lockElement = !this.isEnable ? this.renderLockElement() : null;

		return Tag.render`
			<div id="${this.getId()}" class="ui-section__field-selector">
				<div class="ui-section__field-container">
					<div class="ui-section__field-label_box">
						<label for="${this.getName()}" class="ui-section__field-label">
							${this.getLabel()}
						</label> 
						${lockElement}
					</div>
					<div class="ui-ctl ui-ctl-textbox ui-ctl-block ${this.inputDefaultWidth ? '' : 'ui-ctl-w100'}">
						${this.getInputNode()}
					</div>
					${this.renderErrors()}
				</div>
				<div class="ui-section__hint">
					${this.hintTitle}
				</div>
			</div>
		`;
	}
}
