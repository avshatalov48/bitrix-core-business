import {BaseField} from 'landing.ui.field.basefield';
import {Tag, Dom} from 'main.core';
import {Button} from 'ui.buttons';
import {Loc} from 'landing.loc';
import {Link} from 'landing.ui.component.link';
import {PageObject} from 'landing.pageobject';

import './css/style.css';

type CopyLinkFieldOptions = {
	link: string,
};

export default class CopyLinkField extends BaseField
{
	constructor(options: CopyLinkFieldOptions)
	{
		super(options);
		this.setLayoutClass('landing-ui-field-copy-link');

		Dom.clean(this.layout);
		Dom.append(this.getLink(), this.layout);
		Dom.append(this.getCopyButton().render(), this.layout);
	}

	getLink()
	{
		return this.cache.remember('link', () => {
			const link = new Link({
				text: this.options.link,
				href: this.options.link,
				target: '_blank',
			});
			return Tag.render`
				<div class="landing-ui-field-copy-link-preview">
					${link.getLayout()}
				</div>
			`;
		});
	}

	getCopyButton(): Button
	{
		return this.cache.remember('copyButton', () => {
			return new Button({
				color: Button.Color.PRIMARY,
				size: Button.Size.SMALL,
				text: Loc.getMessage('LANDING_FORM_EMBED_PUB_COPY_LINK_BUTTON_LABEL'),
				onclick: this.onCopyButtonClick.bind(this),
			});
		});
	}

	selectCode()
	{
		PageObject
			.getRootWindow()
			.getSelection()
			.selectAllChildren(this.getLink());
	}

	onCopyButtonClick()
	{
		this.selectCode();
		const rootWindow = PageObject.getRootWindow();

		rootWindow.document.execCommand('copy');
		rootWindow.getSelection().removeAllRanges();

		const button = this.getCopyButton();
		button.setColor(Button.Color.LINK);
		button.setIcon(Button.Icon.DONE);
		button.setText(Loc.getMessage('LANDING_FORM_EMBED_PUB_COPIED_LINK_BUTTON_LABEL'));
	}
}