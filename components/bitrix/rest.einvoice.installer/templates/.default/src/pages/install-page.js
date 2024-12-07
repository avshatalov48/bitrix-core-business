import { Tag, Loc } from 'main.core';
import { Loader } from 'main.loader';
import { BasePage } from './base-page';
import { AppForm, EventType } from 'rest.app-form';

export class InstallPage extends BasePage
{
	#content: ?HTMLElement;
	#title: ?HTMLElement;
	#loader: ?HTMLElement;

	static getType(): string
	{
		return 'install';
	}

	constructor()
	{
		super();
		this.setEventNamespace('BX.Rest.EInvoiceInstaller.InstallPage');
		this.subscribe('install-app', (event) => {
			this.#getLoader().show();
			const installer = event.data.source;
			event.data.install
				.then((response) => {
					if (!response.status)
					{
						return Promise.reject();
					}

					return AppForm.buildByApp(response.data.id, EventType.INSTALL);
				})
				.then((appForm) => {
					this.#getLoader().hide();
					appForm.show();
					installer.render('selection');
				})
				.catch(() => {
				this.#getLoader().hide();
				this.#onAfterUnsuccessfulInstallApplication();
			});
		});
	}

	#onAfterUnsuccessfulInstallApplication(): void
	{
		this.emit('install-error');
	}

	getContent(): HTMLElement
	{
		if (!this.#content)
		{
			this.#content = Tag.render`
				<div class="bitrix-einvoice-installer-content">
					${this.#getTitle()}
					<div class="bitrix-einvoice-installer-loader-wrapper-install"/>
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
				<div class="bitrix-einvoice-installer-title-install">
					${Loc.getMessage('REST_EINVOICE_INSTALLER_INSTALL_TITLE')}
				</div>
			`;
		}

		return this.#title;
	}

	#getLoader(): Loader
	{
		if (!this.#loader)
		{
			this.#loader = new Loader({
				target: this.getContent().querySelector('.bitrix-einvoice-installer-loader-wrapper-install'),
				size: 90,
				color: '#2FC6F6',
				mode: 'inline',
			});
		}

		return this.#loader;
	}
}
