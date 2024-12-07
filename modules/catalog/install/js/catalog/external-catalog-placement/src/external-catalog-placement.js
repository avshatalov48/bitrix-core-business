import { Tag, Dom, ajax, Runtime, Extension } from 'main.core';
import { EventEmitter } from 'main.core.events';

export class ExternalCatalogPlacement
{
	static #instance: ExternalCatalogPlacement = null;
	static #CODE: string = 'CATALOG_EXTERNAL_PRODUCT';
	#appSid: ?string = null;
	#initializePromise = null;
	#isInitialized: boolean = false;
	#initializationError: Object = null;

	static LOAD_PLACEMENT_ERROR: string = 'LOAD_PLACEMENT_ERROR';
	static REGISTER_PLACEMENT_ERROR: string = 'REGISTER_PLACEMENT_ERROR';
	static LOAD_PLACEMENT_LAYOUT_ERROR: string = 'LOAD_PLACEMENT_LAYOUT_ERROR';

	static RESPONSE_TIMEOUT = 20000;

	static create(): ExternalCatalogPlacement
	{
		if (this.#instance)
		{
			return this.#instance;
		}

		this.#instance = new this();

		return this.#instance;
	}

	initialize(): Promise
	{
		if (!this.#initializePromise)
		{
			this.#initializePromise = new Promise((resolve, reject) => {
				this
					.#loadPlacement()
					.then((response) => this.#registerPlacement(response))
					.then((data) => this.#loadPlacementLayout(data))
					.then((response) => this.#runPlacementLayout(response))
					.then(() => this.#waitForOnReadyEvent())
					.then(() => {
						this.#isInitialized = true;
						resolve();
					})
					.catch((error) => {
						this.#isInitialized = true;
						this.#initializationError = error;
						reject(error);
					});
			});
		}

		return this.#initializePromise;
	}

	reset(): void
	{
		this.#initializePromise = null;
	}

	#loadPlacement(): Promise
	{
		if (Extension.getSettings('catalog.external-catalog-placement').get('is1cPlanRestricted', false))
		{
			return Promise.reject({
				reason: 'tariff',
			});
		}

		return new Promise((resolve, reject) => {
			ajax.runComponentAction(
				'bitrix:app.placement',
				'getComponent',
				{
					data: {
						placementId: ExternalCatalogPlacement.#CODE,
					},
				},
			)
				.then((response) => resolve(response))
				.catch(() => {
					reject({
						reason: ExternalCatalogPlacement.LOAD_PLACEMENT_ERROR,
					});
				})
			;
		});
	}

	#registerPlacement(response: Object): Promise
	{
		return new Promise((resolve, reject) => {
			const node = Tag.render`<div style="display: none; overflow: hidden;"></div>`;
			Dom.append(node, document.body);
			Runtime.html(
				node,
				response.data.html,
				{
					callback: () => setTimeout(() => {
						const appLayout = BX.Reflection.getClass('BX.rest.AppLayout');
						const placement = appLayout ? appLayout.getPlacement(ExternalCatalogPlacement.#CODE) : null;

						if (placement)
						{
							resolve({
								placement,
								placementInterface: BX.rest.AppLayout.initializePlacement(ExternalCatalogPlacement.#CODE),
							});

							return;
						}

						reject({
							reason: ExternalCatalogPlacement.REGISTER_PLACEMENT_ERROR,
						});
					}, 10),
				},
			);
		});
	}

	#loadPlacementLayout(data: Object): Promise
	{
		return new Promise((resolve, reject) => {
			// eslint-disable-next-line no-param-reassign
			data.placementInterface.prototype.onReady = (eventData) => {
				EventEmitter.emit('Catalog:ProductSelectorPlacement:onReady', eventData);
			};

			// eslint-disable-next-line no-param-reassign
			data.placementInterface.prototype.onProductCreated = (eventData) => {
				EventEmitter.emit('Catalog:ProductSelectorPlacement:onProductCreated', eventData);
			};

			// eslint-disable-next-line no-param-reassign
			data.placementInterface.prototype.onProductUpdated = (eventData) => {
				EventEmitter.emit('Catalog:ProductSelectorPlacement:onProductUpdated', eventData);
			};

			// eslint-disable-next-line no-param-reassign
			data.placementInterface.prototype.onProductsFound = (eventData) => {
				EventEmitter.emit('Catalog:ProductSelectorPlacement:onProductsFound', eventData);
			};

			data.placementInterface.prototype.events.push(
				'Catalog:ProductSelectorPlacement:onNeedProductCreate',
				'Catalog:ProductSelectorPlacement:onNeedProductUpdate',
				'Catalog:ProductSelectorPlacement:onNeedSearchProducts',
			);

			ajax.runComponentAction(
				'bitrix:app.layout',
				'getComponent',
				{
					data: {
						placementId: data.placement.param.current,
						placementOptions: null,
					},
				},
			)
				.then((response) => {
					resolve(response);
				})
				.catch(() => {
					reject({
						reason: ExternalCatalogPlacement.LOAD_PLACEMENT_LAYOUT_ERROR,
					});
				});
		});
	}

	#runPlacementLayout(response: Object): Promise
	{
		return new Promise((resolve) => {
			this.#appSid = response.data.componentResult.APP_SID;

			const iframeNode = Tag.render`
				<div
					data-app-sid="${this.#appSid}"
					style="display: none; overflow: hidden"
				>
				</div>
			`;
			Dom.append(iframeNode, document.body);
			Runtime.html(iframeNode, response.data.html);

			resolve();
		});
	}

	#waitForOnReadyEvent(): Promise
	{
		return new Promise((resolve, reject) => {
			EventEmitter.subscribe(
				'Catalog:ProductSelectorPlacement:onReady',
				() => {
					resolve();
				},
			);
			setTimeout(() => reject({ reason: 'timeout' }), ExternalCatalogPlacement.RESPONSE_TIMEOUT);
		});
	}

	getAppSidId(): ?string
	{
		return this.#appSid;
	}

	isInitialized(): boolean
	{
		return this.#isInitialized;
	}

	isInitializedSuccessfully(): boolean
	{
		return this.#isInitialized && this.getInitializationError() === null;
	}

	getInitializationError(): ?Object
	{
		return this.#initializationError;
	}
}
