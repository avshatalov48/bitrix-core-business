import { Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';

export class BasePage extends EventEmitter
{
	constructor()
	{
		super();
		this.setEventNamespace('BX.Rest.EInvoiceInstaller.Page');
	}

	getContent(): HTMLElement
	{
		throw new Error('Must be implemented in a child class');
	}

	getIcon(): HTMLElement
	{
		return Tag.render`
			<div class="bitrix-einvoice-installer-main-icon-wrapper">
				<div class="bitrix-einvoice-installer-main-icon"></div>
			</div>
		`;
	}

	static getType(): string
	{
		throw new Error('Must be implemented in a child class');
	}
}
