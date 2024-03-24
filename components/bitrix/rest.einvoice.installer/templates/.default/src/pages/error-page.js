import { Tag, Loc } from 'main.core';
import { Button } from 'ui.buttons';
import { BasePage } from './base-page';

export class ErrorPage extends BasePage
{
	#content: HTMLElement;
	#button: HTMLElement;

	constructor()
	{
		super();
		this.setEventNamespace('BX.Rest.EInvoiceInstaller.ErrorPage');
	}

	static getType(): string
	{
		return 'error';
	}

	getContent(): HTMLElement
	{
		if (!this.#content)
		{
			this.#content = Tag.render`
				<div class="bitrix-einvoice-installer-content --error-content">
					<div class="bitrix-einvoice-installer-error-icon"></div>
					<div class="bitrix-einvoice-installer-title-install">${Loc.getMessage('REST_EINVOICE_INSTALLER_ERROR_TITLE')}</div>
					${this.#getButton()}
				</div>
			`;
		}

		return this.#content;
	}

	#getButton(): HTMLElement
	{
		if (!this.#button)
		{
			this.#button = (new Button({
				text: Loc.getMessage('REST_EINVOICE_INSTALLER_ERROR_BUTTON'),
				size: Button.Size.LARGE,
				color: Button.Color.SUCCESS,
				className: 'bitrix-einvoice-installer-button-try-again',
				onclick: () => {
					this.emit('go-back');
				},
			})).getContainer();
		}

		return this.#button;
	}
}