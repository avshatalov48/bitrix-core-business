import { Event, Tag } from 'main.core';
import { Popup } from 'main.popup';
import { BitrixVue, VueCreateAppResult } from 'ui.vue3';
import { locMixin } from 'ui.vue3.mixins.loc-mixin';

import { App } from './component/app';

export type WizardParams = {
	groupId: number,
	redirectAfterSuccess: boolean,
};

export class Wizard
{
	#params: WizardParams;
	#application: ?VueCreateAppResult = null;
	#wizardPopup: ?Popup = null;
	#layout: {
		popupContainer: HTMLElement,
		wizardContainer: HTMLElement,
	} = {};

	constructor(params: WizardParams)
	{
		this.#params = params;
	}

	async show(): Promise<void>
	{
		return new Promise((resolve, reject) => {
			this.#showPopup(resolve)
				.then(() => {
					resolve();
				})
				.catch((error) => {
					reject(error);
				});
		});
	}

	#showPopup(): Promise<void>
	{
		const windowScrollHandler = () => this.#wizardPopup.adjustPosition();

		return new Promise((resolve) => {
			this.#wizardPopup = new Popup({
				cacheable: false,
				width: 665,
				height: 439,
				borderRadius: 'var(--ui-border-radius-3xl)',
				angle: false,
				content: this.#renderPopupContent(),
				closeByEsc: false,
				autoHide: false,
				closeIcon: false,
				padding: 0,
				contentPadding: 0,
				contentBorderRadius: '18px',
				animation: 'fading-slide',
				overlay: true,
				className: 'socialnetwork-collab-converter-wizard-popup',
				events: {
					onAfterPopupShow: async (popup: Popup) => {
						Event.bind(window, 'scroll', windowScrollHandler);
						this.#layout.popupContainer = popup.getPopupContainer();

						await this.#mountApplication(this.#layout.wizardContainer);

						this.#subscribe();

						resolve();
					},
					onPopupAfterClose: (popup: Popup) => {
						Event.unbind(window, 'scroll', windowScrollHandler);
						this.#unmountApplication();

						this.#unsubscribe();

						popup.destroy();
					},
				},
			});

			this.#wizardPopup.show();
		});
	}

	#renderPopupContent(): HTMLElement
	{
		this.#layout.wizardContainer = Tag.render`
			<div class="socialnetwork-collab-converter-wizard-popup-content"></div>
		`;

		return this.#layout.wizardContainer;
	}

	async #mountApplication(container: HTMLElement): Promise<void>
	{
		const application = BitrixVue.createApp(App, this.#params);
		application.mixin(locMixin);
		application.mount(container);

		this.#application = application;
	}

	#unmountApplication(): void
	{
		this.#application.unmount();
	}

	#subscribe(): void
	{
		Event.EventEmitter.subscribe('socialnetwork:collab:converter:close', this.#close);
		Event.EventEmitter.subscribe('socialnetwork:collab:converter:success', this.#handleSuccess);
	}

	#unsubscribe(): void
	{
		Event.EventEmitter.unsubscribe('socialnetwork:collab:converter:close', this.#close);
		Event.EventEmitter.unsubscribe('socialnetwork:collab:converter:success', this.#handleSuccess);
	}

	#close = (): void => {
		this.#wizardPopup.close();
	};

	#handleSuccess = (event): void => {
		if (this.#params.redirectAfterSuccess)
		{
			const collab = event.data.collab;
			top.document.location.href = collab.url;
		}
	};
}
