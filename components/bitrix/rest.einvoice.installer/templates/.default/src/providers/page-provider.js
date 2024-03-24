import type { EInvoiceInstallerOptions } from '../types';
import { BasePage } from '../pages/base-page';
import { ErrorPage } from '../pages/error-page';
import { InstallPage } from '../pages/install-page';
import { SelectionPage } from '../pages/selection-page';
import { BaseEvent, EventEmitter } from 'main.core.events';

export class PageProvider extends EventEmitter
{
	#selectionPage: SelectionPage;
	#installPage: InstallPage;
	#errorPage: ErrorPage;
	#options: EInvoiceInstallerOptions;
	#types: string[];

	constructor(options: EInvoiceInstallerOptions)
	{
		super();
		this.setEventNamespace('BX.Rest.EInvoiceInstaller.PageProvider');
		this.#options = options;
		this.#types = [
			SelectionPage.getType(),
			InstallPage.getType(),
			ErrorPage.getType(),
		];
	}

	exist(type: string): boolean
	{
		return this.#types.includes(type);
	}

	getPageByType(type: string): BasePage
	{
		switch (type)
		{
			case SelectionPage.getType():
				return this.#getSelectionPage();
			case InstallPage.getType():
				return this.#getInstallPage();
			case ErrorPage.getType():
				return this.#getErrorPage();
			default:
				throw new Error('Incorrect page type');
		}
	}

	#getSelectionPage(): SelectionPage
	{
		if (!this.#selectionPage)
		{
			this.#selectionPage = new SelectionPage(this.#options.apps, this.#options.formConfiguration);
			this.#registerPageHandlers(this.#selectionPage);
			this.#selectionPage.subscribe('start-install-app', () => {
				this.emit('render', new BaseEvent({
					data: {
						type: InstallPage.getType(),
					}
				}));
			})
		}

		return this.#selectionPage;
	}

	#getInstallPage(): InstallPage
	{
		if (!this.#installPage)
		{
			this.#installPage = new InstallPage();
			this.#registerPageHandlers(this.#installPage);
		}

		return this.#installPage;
	}

	#getErrorPage(): ErrorPage
	{
		if (!this.#errorPage)
		{
			this.#errorPage = new ErrorPage();
			this.#registerPageHandlers(this.#errorPage);
		}

		return this.#errorPage;
	}

	#registerPageHandlers(page: BasePage): void
	{
		page.subscribe('go-back', () => {
			this.emit('render', new BaseEvent({
				data: {
					type: SelectionPage.getType(),
				},
			}));
		});
		page.subscribe('install-error', () => {
			this.emit('render', new BaseEvent({
				data: {
					type: ErrorPage.getType(),
				},
			}));
		});
	}
}
