import {Event, Tag, Type, Text} from "main.core";
import {BaseField} from "./base-field";

export class TextInput extends BaseField
{
	defaultValue: string;
	hintTitle: string;
	placeholder: string;
	#inputNode: HTMLElement;

	constructor(params)
	{
		super(params);
		this.defaultValue = Type.isStringFilled(params.value) ? params.value : '';
		this.hintTitle = Type.isStringFilled(params.hintTitle) ? params.hintTitle : '';
		this.placeholder = Type.isStringFilled(params.placeholder) ? params.placeholder : '';
		this.inputDefaultWidth = Type.isBoolean(params.inputDefaultWidth) ? params.inputDefaultWidth : '';
		this.#inputNode = Tag.render`<input 
			value="${Text.encode(this.defaultValue)}" 
			name="${Text.encode(this.getName())}" 
			type="text" 
			class="ui-ctl-element" 
			placeholder="${Text.encode(this.placeholder)}"
			${this.isEnable() ? '' : 'readonly'}
		>`;

		if (!this.isEnable())
		{
			Event.bind(this.#inputNode, 'click', (event) => {
				event.preventDefault();
				if (!Type.isNil(this.getHelpMessage()))
				{
					this.getHelpMessage().show();
				}
			});
		}

		this.getInputNode().addEventListener('keydown', () => {
			this.getInputNode().form.dispatchEvent(new window.Event('change'));
		});
	}

	prefixId(): string
	{
		return 'text_';
	}

	getInputNode(): HTMLElement
	{
		return this.#inputNode;
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
