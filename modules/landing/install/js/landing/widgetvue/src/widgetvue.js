import {WidgetOptions} from './internal/types';
import {Backend} from 'landing.backend';
import {Loc, Text, Type, Dom, Event} from 'main.core';

import './css/style.css';

export class WidgetVue
{
	static runningAppNodes: Set<HTMLElement> = new Set();

	#rootNode: ?HTMLElement = null;
	#template: string;
	#lang: {[key: string]: string} = {};
	#appId: number = 0;
	#appAllowedByTariff: boolean = true;
	#fetchable: boolean = false;
	#clickable: boolean = false;

	/**
	 * Unique string for every widget
	 * @type {string}
	 */
	#uniqueId: string;
	// #rootContent: ?string = null;
	#frame: ?Window = null;

	#defaultData: ?{} = null;
	#blockId: number = 0;

	// #application: VueCreateAppResult;
	// #contentComponent: Object;

	// #widgetOptions: {};

	constructor(options: WidgetOptions): void
	{
		this.#uniqueId = 'widget' + Text.getRandom(8);

		this.#rootNode = Type.isString(options.rootNode)
			? document.querySelector(options.rootNode)
			: null
		;

		this.#template = Type.isString(options.template) ? options.template : '';

		// this.#rootContent = this.#rootNode ? this.#rootNode.innerHTML : null;

		this.#defaultData = Type.isObject(options.data) ? options.data : null;
		this.#lang = options.lang || {};
		this.#blockId = options.blockId ? Text.toNumber(options.blockId) : 0;

		// const isEditMode = Type.isFunction(BX.Landing.getMode) && BX.Landing.getMode() === 'edit';
		// this.#widgetOptions.clickable = !isEditMode;

		this.#appId = options.appId ? Text.toNumber(options.appId) : 0;
		this.#appAllowedByTariff = (this.#appId && Type.isBoolean(options.appAllowedByTariff))
			? options.appAllowedByTariff
			: true
		;

		this.#fetchable = Type.isBoolean(options.fetchable) ? options.fetchable : false;
		const isEditMode = Type.isFunction(BX.Landing.getMode) && BX.Landing.getMode() === 'edit';
		this.#clickable = !isEditMode;
	}

	/**
	 * Create frame with widget content
	 * @returns {Promise|*}
	 */
	mount()
	{
		return this.#getFrameContent()
			.then(srcDoc => {
				this.#frame = document.createElement('iframe');
				this.#frame.className = 'landing-widgetvue-iframe';
				this.#frame.sandbox = 'allow-scripts';
				this.#frame.srcdoc = srcDoc;

				if (
					this.#blockId > 0
					&& this.#rootNode
					&& !WidgetVue.runningAppNodes.has(this.#rootNode)
				)
				{
					const blockWrapper = this.#rootNode.parentElement;
					Dom.clean(blockWrapper);
					Dom.append(this.#frame, blockWrapper);

					this.#bindEvents();
				}
			})
		;
	}

	#getFrameContent(): Promise<string>
	{
		let content = '';

		const engineParams = {
			id: this.#uniqueId,
			origin: window.location.origin,
			fetchable: this.#fetchable,
			clickable: this.#clickable,
		};

		return this.#getCoreConfigs()
			.then(core => {
				content += this.#parseExtensionConfig(core);
				content += this.#parseExtensionConfig({
					lang_additional: this.#lang,
				});

				return this.#getAssetsConfigs();
			})

			.then(assets => {
				content += this.#parseExtensionConfig(assets);

				if (!this.#appAllowedByTariff)
				{
					throw new Error(Loc.getMessage('LANDING_WIDGETVUE_ERROR_PAYMENT'));
				}

				if (this.#defaultData)
				{
					return this.#defaultData;
				}

				return this.#fetchData();
			})

			.then(data => {
				engineParams.data = data;
			})

			.catch(error => {
				engineParams.error = error.message || 'error';
			})

			.then(() => {
				const appInit = `
					<script>
						BX.ready(function() {
							(new BX.Landing.WidgetVue.Engine(
								${JSON.stringify(engineParams)}
							)).render();
						});
					</script>
					
					<div id="${this.#uniqueId}">${this.#template}</div>
				`;

				content += appInit;

				return content;
			})
		;
	}

	#getCoreConfigs(): Promise<Object>
	{
		const extCodes = [
			'main.core',
			'ui.design-tokens',
		];
		const tplCodes = [
			'bitrix24',
		];

		return Backend.getInstance()
			.action(
				'Block::getAssetsConfig',
				{
					extCodes,
					tplCodes,
				},
			)
		;
	}

	#getAssetsConfigs(): Promise<Object>
	{
		const extCodes = [
			'landing.widgetvue.engine',
		];

		return Backend.getInstance()
			.action(
				'Block::getAssetsConfig',
				{ extCodes },
			)
		;
	}

	#parseExtensionConfig(ext: Object): string
	{
		const domain = `${document.location.protocol}//${document.location.host}`;
		let html = '';

		if (ext.lang_additional !== undefined)
		{
			html += `<script>BX.message(${JSON.stringify(ext.lang_additional)})</script>`;
		}

		(ext.js || []).forEach(js => {
			html += `<script src="${domain}${js}"></script>`;
		});

		(ext.css || []).forEach(css => {
			html += `<link href="${domain}${css}" type="text/css" rel="stylesheet" />`;
		});

		return html;
	}

	#fetchData(params = {}): Promise<Object>
	{
		if (!this.#fetchable)
		{
			console.info('Fetch data is impossible now (haven`t handler)');

			return Promise.resolve({});
		}

		return Backend.getInstance()
			.action('RepoWidget::fetchData', {
				blockId: this.#blockId,
				params,
			})

			.then(jsonData => {
				let data = {};
				try
				{
					data = JSON.parse(jsonData);
					if (data.error)
					{
						throw new Error(Loc.getMessage('LANDING_WIDGETVUE_ERROR_FETCH'), data.error);
					}
				}
				catch (error)
				{
					throw new Error(Loc.getMessage('LANDING_WIDGETVUE_ERROR_FETCH'), error);
				}

				return data;
			})
		;
	}

	#bindEvents()
	{
		Event.bind(window, 'message', this.#onMessage.bind(this));
	}

	#onMessage(event)
	{
		if (event.data && event.data.name && event.data.params)
		{
			if (event.data.name === 'fetchData')
			{
				this.#fetchData(event.data.params)
					.then(data => {
						event.source.postMessage(
							{
								name: 'setData',
								params: {
									data,
								},
							},
							'*',
						);
					})

					.catch(error => {
						event.source.postMessage(
							{
								name: 'setError',
								params: {
									error,
								},
							},
							'*',
						);
					})
				;
			}

			if (
				event.data.name === 'setSize'
				&& event.data.params.size !== undefined
			)
			{
				this.#frame.height = parseInt(event.data.params.size);
			}

			if (
				event.data.name === 'openApplication'
				&& this.#appId > 0
			)
			{
				const params = Type.isObject(event.data.params) ? event.data.params : {};
				BX.rest.AppLayout.openApplication(
					this.#appId,
					params,
				);
			}

			if (
				event.data.name === 'openPath'
				&& Type.isString(event.data.path)
			)
			{
				// todo: change open function
				const url = new URL(event.data.path, window.location.origin);
				if (url.origin === window.location.origin)
				{
					window.open(url.href, '_blank');
				}
			}
		}
	}
}
