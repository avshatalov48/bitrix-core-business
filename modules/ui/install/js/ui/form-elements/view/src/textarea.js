import { Tag, Text, Type } from 'main.core';
import { BaseField } from './base-field';

type TextareaParams = {
	value: string,
	hintTitle: string,
	placeholder: string,
	inputDefaultWidth: boolean,
	resizeOnlyY: boolean,
	resizeOnlyX: boolean,
}

export class TextArea extends BaseField
{
	#defaultValue: string;
	#hintTitle: string;
	#placeholder: string;
	#inputDefaultWidth: boolean;
	#resizeOnlyY: boolean;
	#resizeOnlyX: boolean;

	#node: HTMLElement;

	constructor(params: TextareaParams)
	{
		super(params);

		this.setEventNamespace('UI.Form.Textarea');

		this.#defaultValue = Type.isStringFilled(params.value) ? params.value : '';
		this.#hintTitle = Type.isStringFilled(params.hintTitle) ? params.hintTitle : '';
		this.#placeholder = Type.isStringFilled(params.placeholder) ? params.placeholder : '';
		this.#inputDefaultWidth = Type.isBoolean(params.inputDefaultWidth) ? params.inputDefaultWidth : false;
		this.#resizeOnlyY = Type.isBoolean(params.resizeOnlyY) ? params.resizeOnlyY : false;
		this.#resizeOnlyX = Type.isBoolean(params.resizeOnlyX) ? params.resizeOnlyX : false;
	}

	prefixId(): string
	{
		return 'textarea_';
	}

	getValue(): string
	{
		return this.getNode().value;
	}

	renderContentField(): HTMLElement
	{
		const lockElement = this.isEnable ? null : this.renderLockElement();

		let resizeUiClass = this.#resizeOnlyY ? 'ui-ctl-resize-y' : '';
		if (resizeUiClass === '')
		{
			resizeUiClass = this.#resizeOnlyX ? 'ui-ctl-resize-x' : '';
		}
		const defaultWidthUIClass = this.#inputDefaultWidth ? '' : 'ui-ctl-w100';

		return Tag.render`
			<div id="${this.getId()}" class="ui-section__field-selector">
				<div class="ui-section__field-container">
					<div class="ui-section__field-label_box">
						<label for="${this.getName()}" class="ui-section__field-label">
							${this.getLabel()}
						</label> 
						${lockElement}
					</div>
					<div class="ui-ctl ui-ctl-textarea ui-form-textarea ${resizeUiClass} ${defaultWidthUIClass}">
						${this.getNode()}
					</div>
					${this.renderErrors()}
				</div>
				<div class="ui-section__hint">
					${this.#hintTitle}
				</div>
			</div>
		`;
	}

	getNode(): HTMLTextAreaElement
	{
		this.#node ??= this.#renderNode();

		return this.#node;
	}

	#renderNode(): HTMLTextAreaElement
	{
		const node = Tag.render`
			<textarea
				class="ui-ctl-element"
				name="${Text.encode(this.getName())}"
				placeholder="${Text.encode(this.#placeholder)}"
				${this.isEnable() ? '' : 'readonly'}
			></textarea>
		`;

		node.value = this.#defaultValue;

		return node;
	}
}
