import { Tag, Loc, Text } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { PopupComponentsMaker } from 'ui.popupcomponentsmaker';
import { Button } from 'ui.buttons';
import type { FormConfiguration } from '../types';
import { AppConfig } from '../types';

export class EInvoiceAppButton extends EventEmitter
{
	#popup: ?PopupComponentsMaker;
	#apps: Array<AppConfig>;
	#popupContent: ?Array;
	#formConfiguration: FormConfiguration;

	constructor(apps: Array<AppConfig>, formConfiguration: FormConfiguration)
	{
		super();
		this.setEventNamespace('BX.Rest.EInvoiceAppButton');
		this.#apps = apps;
		this.#formConfiguration = formConfiguration;
	}

	getContent(): HTMLElement
	{
		const button = new Button({
			text: Loc.getMessage('REST_EINVOICE_INSTALLER_SELECTION_BUTTON'),
			size: Button.Size.LARGE,
			color: Button.Color.SUCCESS,
			dropdown: true,
			className: 'bitrix-einvoice-installer-button-select',
			onclick: (event) => {
				const popup = this.#getPopup(event.button);
				popup.getPopup().setMaxWidth(event.button.offsetWidth);
				popup.getPopup().toggle();
			},
		});

		return button.getContainer();
	}

	#getPopup(bindElement: HTMLElement): PopupComponentsMaker
	{
		if (this.#popup)
		{
			return this.#popup;
		}

		this.#popup = new PopupComponentsMaker({
			target: bindElement,
			content: this.#getPopupContent(),
			useAngle: false,
		});
		this.#popup.getPopup().setOffset({
			offsetLeft: 0,
			offsetTop: 0,
		});
		this.subscribe('popup-close', () => {
			this.#popup.close();
		});

		return this.#popup;
	}

	#getPopupContent(): Array
	{
		if (this.#popupContent)
		{
			return this.#popupContent;
		}

		this.#popupContent = [];
		this.#apps.forEach((app) => {
			const onclick = () => {
				this.emit('popup-close');
				this.emit('click-app', new BaseEvent({
					data: {
						code: app.code,
						name: app.name,
					},
				}));
			};
			this.#popupContent.push({
				html: Tag.render`
					<div onclick="${onclick}" class="bitrix-einvoice-installer-app-wrapper">
						<div class="bitrix-einvoice-installer-app-name">
							${Text.encode(app.name)}
						</div>
					</div>
				`,
			});
		});

		const showForm = () => {
			this.#showFormForOffer();
			this.emit('popup-close');
		};
		this.#popupContent.push({
			html: Tag.render`
				<div onclick="${showForm}" class="bitrix-einvoice-installer-app-wrapper --form">
					${Loc.getMessage('REST_EINVOICE_INSTALLER_SELECTION_BUTTON_OFFER')}
				</div>
			`,
		});

		return this.#popupContent;
	}

	#showFormForOffer(): void
	{
		BX.UI.Feedback.Form.open({
			id: 'b5309667',
			forms: [
				{ zones: ['es'], id: 676, lang: 'es', sec: 'uthphh' },
				{ zones: ['de'], id: 670, lang: 'de', sec: 'gk89kt' },
				{ zones: ['com.br'], id: 668, lang: 'br', sec: 'kuelnm' },
			],
			defaultForm: { id: 674, lang: 'en', sec: '5iorws' },
			presets: {
				...this.#formConfiguration,
				sender_page: document.location.href,
			},
		});
	}
}
