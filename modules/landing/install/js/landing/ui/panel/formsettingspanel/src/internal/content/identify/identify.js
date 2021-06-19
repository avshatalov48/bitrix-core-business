import {HeaderCard} from 'landing.ui.card.headercard';
import {Loc} from 'landing.loc';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {MessageCard} from 'landing.ui.card.messagecard';
import {Reflection} from 'main.core';

import messageIcon from './images/icon.svg';

export default class Identify extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.Identify');

		const header = new HeaderCard({
			title: Loc.getMessage('LANDING_IDENTIFY_HEADER'),
		});

		const message = new MessageCard({
			header: Loc.getMessage('LANDING_IDENTIFY_MESSAGE_HEADER'),
			description: Loc.getMessage('LANDING_IDENTIFY_MESSAGE_DESCRIPTION'),
			icon: messageIcon,
			angle: false,
			closeable: false,
			more: () => {
				const helper = Reflection.getClass('top.BX.Helper');
				if (helper)
				{
					BX.Helper.show('redirect=detail&code=12802786');
				}
			},
		});

		this.addItem(header);
		this.addItem(message);
	}
}