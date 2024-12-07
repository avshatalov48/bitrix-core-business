import { Text, Tag, Type, Dom } from 'main.core';
import { TextInput } from './text-input';

export class TextInputInline extends TextInput
{
	#hintDesc: string;
	#hintBlock: HTMLElement;

	constructor(params)
	{
		super(params);
		this.valueColor = Type.isBoolean(params.valueColor) === true ? '--color-blue' : '';
		this.#hintDesc = Type.isStringFilled(params.hintDesc) ? params.hintDesc : '';
		this.#hintBlock = Tag.render`<div></div>`;
		this.getInputNode().addEventListener('keyup', (event) => {
			Dom.clean(this.#hintBlock);
			Dom.append(this.renderHint(), this.#hintBlock);
		});
	}

	renderContentField(): HTMLElement
	{
		const lockElement = this.isEnable ? null : this.renderLockElement();

		let content = Tag.render`
			<div id="${this.getId()}" class="ui-section__field-selector --field-separator">
				<div class="ui-section__field-container">			
					<div class="ui-section__field-label_box">
						<label for="${Text.encode(this.getName())}" class="ui-section__field-label">${this.getLabel()}</label> 
						${lockElement}
					</div>
					<div class="ui-section__field-inline-box">
						<div class="ui-section__field">
							<div class="ui-ctl ui-ctl-textbox ui-ctl-block ui-ctl-w100">
								${this.getInputNode()}
							</div>
						</div>
						<div class="ui-section__field-inline-separator"></div>
						${this.#hintBlock}
					</div>
					${this.renderErrors()}
				</div>
			</div>
		`;

		Dom.append(this.renderHint(), this.#hintBlock);

		return content;
	}

	prefixId(): string
	{
		return 'text_inline_';
	}

	renderHint(): HTMLElement
	{
		return Tag.render`
			<div class="ui-section__hint">
				<div class="ui-section__title">${this.hintTitle}</div>
				<div class="ui-section__value ${this.valueColor}">${Text.encode(this.getInputNode().value)}</div>
				<div class="ui-section__description">${this.#hintDesc}</div>
			</div>
		`;
	}
}
