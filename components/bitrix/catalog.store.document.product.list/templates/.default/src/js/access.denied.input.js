import {Tag, Loc} from 'main.core';

export class AccessDeniedInput
{
	hint: string;
	text: string;
	isReadOnly: boolean;

	constructor(options)
	{
		this.text = options.text || Loc.getMessage('CATALOG_DOCUMENT_PRODUCT_LIST_ACCESS_DENIED_TEXT');
		this.hint = options.hint;
		this.isReadOnly = options.isReadOnly === true;
	}

	renderTo(node: Element): void
	{
		const className =
			this.isReadOnly
				? 'ui-ctl-no-border catalog-document-product-list-access-denied-readonly'
				: 'ui-ctl-disabled catalog-document-product-list-access-denied'
		;
		const block = Tag.render`
		<div
			class="ui-ctl ui-ctl-w100 ui-ctl-before-icon ui-ctl-after-icon ${className}"
			data-hint="${this.hint}"
			data-hint-no-icon
		>
			<div class="ui-ctl-before catalog-document-product-list-access-denied-lock"></div>
			<div class="ui-ctl-after catalog-document-product-list-access-denied-hint"></div>
			<div class="ui-ctl-element">${this.text}</div>
		</div>
		`;

		node.innerHTML = '';
		node.appendChild(block);

		BX.UI.Hint.createInstance({
			popupParameters: {
				angle: {
					offset: 100,
				},
			},
		}).init();
	}
}
