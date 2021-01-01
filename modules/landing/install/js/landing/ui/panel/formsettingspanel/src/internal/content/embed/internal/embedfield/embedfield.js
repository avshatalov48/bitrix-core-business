import {Dom, Tag, Event, Text} from 'main.core';
import {BaseField} from 'landing.ui.field.basefield';
import {Loc} from 'landing.loc';
import {PageObject} from 'landing.pageobject';
import {Button} from 'ui.buttons';

import './css/style.css';

export class EmbedField extends BaseField
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.EmbedField');
		this.setLayoutClass('landing-ui-field-embed');

		Dom.clean(this.layout);
		Dom.append(this.getHeader(), this.layout);
		Dom.append(this.input, this.layout);

		Event.bind(this.input, 'click', this.onInputClick.bind(this));
	}

	getCopyButton(): Button
	{
		return this.cache.remember('copyButton', () => {
			return new Button({
				text: Loc.getMessage('LANDING_FORM_EMBED_COPY_BUTTON_LABEL'),
				size: Button.Size.SMALL,
				color: Button.Color.PRIMARY,
				onclick: this.onCopyButtonClick.bind(this),
			});
		});
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
		button.setText(Loc.getMessage('LANDING_FORM_EMBED_COPIED_BUTTON_LABEL'));

		Dom.addClass(this.getHeader(), 'landing-ui-field-embed-header-copied');
	}

	onInputClick(event: MouseEvent)
	{
		event.preventDefault();
		this.selectCode();
	}

	selectCode()
	{
		PageObject
			.getRootWindow()
			.getSelection()
			.selectAllChildren(this.input);
	}

	getHeader(): HTMLDivElement
	{
		return this.cache.remember('header', () => {
			return Tag.render`
				<div class="landing-ui-field-embed-header">
					<div class="landing-ui-field-embed-header-button">
						${this.getCopyButton().render()}
					</div>
				</div>
			`;
		});
	}

	setValue(value: * = '')
	{
		this.input.innerHTML = Text.encode(value);
	}
}