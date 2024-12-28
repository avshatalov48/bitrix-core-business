import { WidgetOptions } from './internal/types';
import { Logger } from './logger';
import { Backend } from 'landing.backend';
import { Loc, Text, Type, Dom, Event } from 'main.core';

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
			.then((core) => {
				content += this.#parseExtensionConfig(core);
				content += this.#parseExtensionConfig({
					lang_additional: this.#lang,
				});

				if (this.#style)
				{
					content += `<link rel="stylesheet" href="${this.#style}">`;
				}

				return this.#getAssetsConfigs();
			})

			.then((assets) => {
				content += this.#parseExtensionConfig(assets);

				if (!this.#appAllowedByTariff)
				{
					throw new Error(Loc.getMessage('LANDING_WIDGETVUE_ERROR_PAYMENT'));
				}

				if (this.#useDemoData)
				{
					if (!this.#demoData)
					{
						this.#logger.log('Widget haven\'t demo data and can be render correctly');
					}

					return this.#demoData || {};
				}

				return this.#fetchData();
			})

			.then((data) => {
				engineParams.data = data;
			})

			.catch((error) => {
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

		return Backend.getInstance()
			.action('RepoWidget::fetchData', {
				blockId: this.#blockId,
				params,
			})

			.then((jsonData) => {
				let data = {};
				data = JSON.parse(jsonData);
				if (data.error)
				{
					throw new Error(data.error);
				}

				return data;
			})

			.catch((error) => {
				const logMessages = [`Fetch data error!\nWidget ID: ${this.#blockId}`];
				if (Object.keys(params) > 0)
				{
					logMessages.push('\nFetch request params:', params);
				}

				if (Type.isString(error))
				{
					logMessages.push(`\nError in JSON data: ${error}`);
				}

				else if (Type.isObject(error))
				{
					if (error instanceof Error && error.message)
					{
						logMessages.push(`\nJavaScript error: ${error.message}`);
					}
					else if (error.result && Type.isArray(error.result) && error.result.length > 0)
					{
						logMessages.push('\nError from backend:');
						error.result.forEach((e) => {
							logMessages.push(e);
						});
					}
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
						this.#message('setData', { data }, event.source);
					})

					.catch((error) => {
						this.#message('setError', { error }, event.source);
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
