import { WidgetOptions } from './internal/types';
import { Logger } from './logger';
import { Dom, Event, Loc, Text, Type, ajax as Ajax } from 'main.core';

import './css/style.css';

export class WidgetVue
{
	static runningAppNodes: Set<HTMLElement> = new Set();

	#rootNode: ?HTMLElement = null;
	#template: string;
	#style: ?string;
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
	#frame: ?HTMLIFrameElement = null;
	#logger: Logger;

	#demoData: ?{} = null;
	#useDemoData: boolean = false;
	#blockId: number = 0;

	constructor(options: WidgetOptions): void
	{
		this.#uniqueId = `widget_${Text.getRandom(8)}`;
		this.#logger = new Logger(options.debug || false);

		this.#rootNode = Type.isString(options.rootNode)
			? document.querySelector(options.rootNode)
			: null
		;

		this.#template = Type.isString(options.template) ? options.template : '';

		this.#style = Type.isString(options.style) ? options.style : null;

		this.#demoData = Type.isObject(options.demoData) ? options.demoData : null;
		this.#useDemoData = Type.isBoolean(options.useDemoData) ? options.useDemoData : false;

		this.#lang = options.lang || {};
		this.#blockId = options.blockId ? Text.toNumber(options.blockId) : 0;

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
	mount(): Promise
	{
		return this.#getFrameContent()
			.then((srcDoc) => {
				this.#frame = document.createElement('iframe');
				this.#frame.className = 'landing-widgetvue-iframe';
				this.#frame.sandbox = 'allow-scripts';
				this.#frame.srcdoc = srcDoc;

				this.#frame.onload = () => {
					this.#message('getSize', {}, this.#frame.contentWindow);
				};

				if (
					this.#blockId > 0
					&& this.#rootNode
					&& !WidgetVue.runningAppNodes.has(this.#rootNode)
				)
				{
					const blockWrapper = this.#rootNode.parentElement;
					Dom.clean(blockWrapper);
					Dom.append(this.#frame, blockWrapper);

					WidgetVue.runningAppNodes.add(this.#rootNode);

					this.#bindEvents();
				}
			})
		;
	}

	async #getFrameContent(): Promise<string>
	{
		let content = '';

		const core = await this.#getCoreConfigs();
		content += this.#parseExtensionConfig(core.data);

		const assets = await this.#getAssetsConfigs();
		content += this.#parseExtensionConfig(assets.data);

		content += this.#parseExtensionConfig({
			lang_additional: this.#lang,
		});
		if (this.#style)
		{
			content += `<link rel="stylesheet" href="${this.#style}">`;
		}

		const engineParams = await this.#getEngineParams();

		const appInit = `
			<script>
				BX.ready(function() {
					(new BX.Landing.WidgetVue.Engine(
						${JSON.stringify(engineParams)},
					)).render();
				});
			</script>

			<div id="${this.#uniqueId}">${this.#template}</div>
		`;

		content += appInit;

		return content;
	}

	async #getEngineParams(): Promise<Object>
	{
		const engineParams = {
			id: this.#uniqueId,
			origin: window.location.origin,
			fetchable: this.#fetchable,
			clickable: this.#clickable,
		};

		if (this.#appAllowedByTariff)
		{
			if (this.#useDemoData)
			{
				if (!this.#demoData)
				{
					this.#logger.log('Widget haven\'t demo data and can be render correctly');
				}

				engineParams.data = this.#demoData || {};
			}
			else
			{
				try
				{
					engineParams.data = await this.#fetchData();
				}
				catch (error)
				{
					engineParams.error = error.message || 'error';
				}
			}
		}
		else
		{
			engineParams.error = Loc.getMessage('LANDING_WIDGETVUE_ERROR_PAYMENT_MSGVER_1');
		}

		return engineParams;
	}

	async #getCoreConfigs(): Promise<Object>
	{
		return Ajax.runAction('landing.vibe.getCoreConfig');
	}

	async #getAssetsConfigs(): Promise<Object>
	{
		const extCodes = [
			'landing.widgetvue.engine',
		];

		return Ajax.runAction('landing.vibe.getAssetsConfig', {
			data: {
				extCodes,
			},
		});
	}

	#parseExtensionConfig(ext: Object): string
	{
		const domain = `${document.location.protocol}//${document.location.host}`;
		let html = '';

		if (ext.lang_additional !== undefined)
		{
			html += `<script>BX.message(${JSON.stringify(ext.lang_additional)})</script>`;
		}

		(ext.js || []).forEach((js) => {
			html += `<script src="${domain}${js}"></script>`;
		});

		(ext.css || []).forEach((css) => {
			html += `<link href="${domain}${css}" type="text/css" rel="stylesheet" />`;
		});

		return html;
	}

	#fetchData(params = {}): Promise<Object>
	{
		if (!this.#fetchable)
		{
			this.#logger.log('Fetch data is impossible now (haven`t handler)');

			return Promise.resolve({});
		}

		if (this.#useDemoData)
		{
			return Promise.resolve(this.#demoData || {});
		}

		return Ajax.runAction('landing.vibe.fetchData', {
			data: {
				blockId: this.#blockId,
				params,
			},
		})
			.then((jsonData) => {
				const data = JSON.parse(jsonData.data || []);
				if (data.error)
				{
					throw new Error(data.error);
				}

				return data;
			})

			.catch((fail) => {
				const logMessages = [`Fetch data error!\nWidget ID: ${this.#blockId}`];
				if (Object.keys(params) > 0)
				{
					logMessages.push('\nFetch request params:', params);
				}

				if (Type.isString(fail))
				{
					logMessages.push(`\nError in JSON data: ${fail}`);
				}
				else if (
					Type.isObject(fail)
					&& 'errors' in fail
					&& Type.isArray(fail.errors)
				)
				{
					fail.errors.forEach(error => {
						if (error.message !== undefined)
						{
							logMessages.push(`\nJavaScript error: ${error.message}`);
						}
					});
				}

				this.#logger.log(...logMessages);
				throw new Error(Loc.getMessage('LANDING_WIDGETVUE_ERROR_FETCH'));
			});
	}

	#message(name: string, params: {} = {}, target: Window = window)
	{
		target.postMessage(
			{
				name,
				params,
				origin: this.#uniqueId,
			},
			'*',
		);
	}

	#bindEvents()
	{
		Event.bind(window, 'message', this.#onMessage.bind(this));
	}

	#onMessage(event)
	{
		// todo: need check origin manually?

		if (
			event.data
			&& event.data.origin
			&& event.data.name
			&& event.data.params
			&& Type.isObject(event.data.params)
		)
		{
			if (event.data.origin !== this.#uniqueId)
			{
				return;
			}

			if (event.data.name === 'fetchData')
			{
				this.#fetchData(event.data.params)
					.then((data) => {
						this.#message('setData', {data}, event.source);
					})

					.catch((error) => {
						this.#message('setError', {error}, event.source);
					})
				;
			}

			if (
				event.data.name === 'setSize'
				&& event.data.params.size !== undefined
			)
			{
				this.#frame.height = parseInt(event.data.params.size, 10);
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
				&& Type.isString(event.data.params.path)
			)
			{
				BX.rest.AppLayout.openPath(
					this.#appId,
					{
						path: event.data.params.path,
					},
				);
			}
		}
	}
}
