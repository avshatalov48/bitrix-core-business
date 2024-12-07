import { ajax, Dom } from 'main.core';
import { PageProvider } from './providers/page-provider';
import type { EInvoiceInstallerOptions } from './types';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Listener } from 'rest.listener';
import { AppForm } from 'rest.app-form';

export class EInvoiceInstaller extends EventEmitter
{
	#options: EInvoiceInstallerOptions;
	#pageProvider: PageProvider;
	#formListener: Listener;

	constructor(options: EInvoiceInstallerOptions)
	{
		super();
		this.setEventNamespace('BX.Rest.EInvoiceInstaller');
		this.#options = options;
		this.render('selection');
		this.#getPageProvider().getPageByType('selection').subscribe('start-install-app', this.#onStartInstall.bind(this));
	}

	render(pageType: string): void
	{
		if (this.#getPageProvider().exist(pageType))
		{
			Dom.clean(this.#options.wrapper);
			const page = this.#getPageProvider().getPageByType(pageType);
			Dom.append(page.getContent(), this.#options.wrapper);

			if (pageType !== 'error')
			{
				Dom.append(page.getIcon(), this.#options.wrapper);
			}
		}
	}

	#installApplicationByCode(code: string): Promise
	{
		return ajax.runComponentAction('bitrix:rest.einvoice.installer', 'installApplicationByCode', {
			mode: 'class',
			data: {
				code: code,
			},
		});
	}

	#onStartInstall(event: BaseEvent): void
	{
		this.#getPageProvider().getPageByType('install').emit('install-app', new BaseEvent({
			data: {
				source: this,
				code: event.data.code,
				name: event.data.name,
				install: this.#installApplicationByCode(event.data.code),
			},
		}));
	}

	#getPageProvider(): PageProvider
	{
		if (!this.#pageProvider)
		{
			this.#pageProvider = new PageProvider(this.#options);
			this.#pageProvider.subscribe('render', (event) => {
				this.render(event.data.type);
			});
		}

		return this.#pageProvider;
	}
}