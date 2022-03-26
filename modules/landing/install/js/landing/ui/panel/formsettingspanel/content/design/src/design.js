import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {HeaderCard} from 'landing.ui.card.headercard';
import {Loc} from 'landing.loc';
import {MessageCard} from 'landing.ui.card.messagecard';
import {Button, ButtonColor} from 'ui.buttons';
import {Dom} from 'main.core';
import {FormSettingsPanel} from 'landing.ui.panel.formsettingspanel';

export default class Design extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.Design');

		const header = new HeaderCard({
			title: Loc.getMessage('LANDING_FORM_SETTINGS_DESIGN_TITLE'),
		});

		const message = new MessageCard({
			id: 'designMessage',
			header: Loc.getMessage('LANDING_FORM_SETTINGS_DESIGN_MESSAGE_TITLE'),
			description: Loc.getMessage('LANDING_FORM_SETTINGS_DESIGN_MESSAGE_TEXT'),
			restoreState: true,
			angle: false,
		});

		this.addItem(header);
		this.addItem(message);

		Dom.append(this.getButton().render(), this.getLayout());
	}

	getButton(): Button
	{
		return this.cache.remember('button', () => {
			return new Button({
				text: Loc.getMessage('LANDING_FORM_SETTINGS_DESIGN_BUTTON_LABEL'),
				color: ButtonColor.LIGHT_BORDER,
				onclick: () => {
					FormSettingsPanel.getInstance().onFormDesignButtonClick();
				},
			});
		});
	}
}