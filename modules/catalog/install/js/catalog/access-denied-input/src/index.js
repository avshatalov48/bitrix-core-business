import {Tag, Loc} from 'main.core';
import './style.css';

export class AccessDeniedInput
{
	hint: string;
	text: string;
	isReadOnly: boolean;

	constructor(options)
	{
		this.text = options.text || Loc.getMessage('CATALOG_ACCESS_DENIED_INPUT_TEXT');
		this.hint = options.hint;
		this.isReadOnly = options.isReadOnly === true;
	}

	renderTo(node: Element): void
	{
		const className =
			this.isReadOnly
				? 'ui-ctl-no-border catalog-access-denied-input-readonly'
				: 'ui-ctl-disabled catalog-access-denied-input'
		;
		const block = Tag.render`
		<div
			class="ui-ctl ui-ctl-w100 ui-ctl-before-icon ui-ctl-after-icon ${className}"
			data-hint="${this.hint}"
			data-hint-no-icon
		>
			<div class="ui-ctl-before catalog-access-denied-input-lock"></div>
			<div class="ui-ctl-after catalog-access-denied-input-hint"></div>
			<div class="ui-ctl-element">${this.text}</div>
		</div>
		`;

		node.innerHTML = '';
		node.appendChild(block);

		if (this.hint)
		{
			BX.UI.Hint.createInstance({
				popupParameters: {
					angle: {
						offset: 100,
					},
				},
			}).init();
		}
	}
}
