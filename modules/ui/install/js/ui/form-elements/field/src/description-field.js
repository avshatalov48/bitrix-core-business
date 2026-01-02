import { BaseSettingsElement } from './base-settings-element';
import { Type } from 'main.core';

export class DescriptionField extends BaseSettingsElement
{
	#description: string;
	#helpdesk: {
		code: string,
		text: string,
	};

	constructor(params) {
		super(params);
		this.#description = Type.isStringFilled(params.description) ? params.description : '';
		this.#helpdesk = Type.isObject(params.helpdesk) ? params.helpdesk : '';
	}

	render(): HTMLElement
	{
		const alert = new BX.UI.Alert({
			text: `
				${this.#description}
				${this.#getHelpdeskLink()}
			`,
			inline: true,
			size: BX.UI.Alert.Size.SMALL,
			color: BX.UI.Alert.Color.PRIMARY,
			animated: true,
		});

		return alert.getContainer();
	}

	#getHelpdeskLink(): string
	{
		if (
			this.#helpdesk
			&& Type.isStringFilled(this.#helpdesk.code)
			&& Type.isStringFilled(this.#helpdesk.text)
		)
		{
			return `
				<a class="ui-section__link" onclick="top.BX.Helper.show('redirect=detail&code=${this.#helpdesk.code}')">
					${this.#helpdesk.text}
				</a>
			`;
		}

		return '';
	}
}
