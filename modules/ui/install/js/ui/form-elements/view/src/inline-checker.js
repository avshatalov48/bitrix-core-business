import { Checker } from './checker';
import { Tag, Type } from 'main.core';

export class InlineChecker extends Checker
{
	hintTitle: String;
	#hintDescElement: HTMLElement;

	constructor(params) {
		super(params);
		this.hintTitle = Type.isStringFilled(params.hintTitle) ? params.hintTitle : '';
		this.#hintDescElement = Tag.render`
			<div class="ui-section__description">
				${this.isChecked() ? this.hintOn : this.hintOff}
			</div>
		`;
	}

	prefixId(): string
	{
		return 'inline_checker_';
	}

	renderContentField(): HTMLElement
	{
		let content = Tag.render`
		<div id="${this.getId()}" class="ui-section__field-switcher --field-separator --align-center">
		<div class="ui-section__field-inline-box">
			<div class="ui-section__field-switcher-box">
				<div class="ui-section__switcher"></div>
				<div class="ui-section__switcher-title">
					${this.getLabel()}
				</div>
			</div>
			<div class="ui-section__field-inline-separator"></div>
			<div class="ui-section__hint">
				<div class="ui-section__title">
					${this.hintTitle}
				</div>
				${this.#hintDescElement}
			</div>
			</div>
		</div>
		`;

		this.switcher.renderTo(content.querySelector('.ui-section__switcher'));

		return content;
	}

	changeHint(isChecked: boolean)
	{
		this.#hintDescElement.innerText = this.getHint(isChecked);
	}
}
