import { Tag, Loc } from 'main.core';
import { EInvoiceAppButton } from '../buttons/einvoice-app-button';
import type { FormConfiguration } from '../types';
import { BasePage } from './base-page';
import { AppConfig } from '../types';

export class SelectionPage extends BasePage
{
	#content: ?HTMLElement;
	#title: ?HTMLElement;
	#description: ?HTMLElement;
	#button: ?HTMLElement;
	#apps: Array<AppConfig>;
	#formConfiguration: FormConfiguration;
	#moreInformation: ?HTMLElement;

	constructor(apps: Array<AppConfig>, formConfiguration: FormConfiguration)
	{
		super();
		this.#apps = apps;
		this.#formConfiguration = formConfiguration;
		this.setEventNamespace('BX.Rest.EInvoiceInstaller.SelectionPage');
	}

	static getType(): string
	{
		return 'selection';
	}

	getContent(): HTMLElement
	{
		if (!this.#content)
		{
			this.#content = Tag.render`
				<div class="bitrix-einvoice-installer-content">
					${this.#getTitle()}
					${this.#getDescription()}
					${this.#getButton()}
					${this.#getMoreInformation()}
				</div>
			`;
		}

		return this.#content;
	}

	#getTitle(): HTMLElement
	{
		if (!this.#title)
		{
			this.#title = Tag.render`
				<div class="bitrix-einvoice-installer-title__wrapper">
					<div class="bitrix-einvoice-installer-title__main-text">
						${Loc.getMessage('REST_EINVOICE_INSTALLER_SELECTION_TITLE')}
					</div>
				</div>
			`;
		}

		return this.#title;
	}

	#getDescription(): HTMLElement
	{
		if (!this.#description)
		{
			this.#description = Tag.render`
				<div class="bitrix-einvoice-installer-description">
					${Loc.getMessage('REST_EINVOICE_INSTALLER_SELECTION_DESCRIPTION')}
				</div>
			`;
		}

		return this.#description;
	}

	#getButton(): HTMLElement
	{
		if (this.#button)
		{
			return this.#button;
		}

		const buttonConstructor = new EInvoiceAppButton(this.#apps, this.#formConfiguration);
		this.#button = buttonConstructor.getContent();
		buttonConstructor.subscribe('click-app', (event) => {
			this.emit('start-install-app', event);
		});

		return this.#button;
	}

	#getMoreInformation(): HTMLElement
	{
		if (this.#moreInformation)
		{
			return this.#moreInformation;
		}

		const onclick = () => {
			top.BX.Helper.show('redirect=detail&code=19312840');
		};

		this.#moreInformation = Tag.render`
			<div class="bitrix-einvoice-installer-more-information-wrapper">
				<div onclick="${onclick}" class="bitrix-einvoice-installer-more-information-link">
					${Loc.getMessage('REST_EINVOICE_INSTALLER_MORE')}
				</div>
			</div>
		`;

		return this.#moreInformation;
	}
}