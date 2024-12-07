import { BitrixVue, VueCreateAppResult } from 'ui.vue3';
import { Type, Runtime, Loc, Event } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { Loader } from 'main.loader';

import { Content } from './components/content';
import { Message } from './components/message';
import { Error } from './components/error';
import { EngineOptions } from './internal/types';

import './css/style.css';

export class Engine
{
	#parentOrigin: ?string = '';
	#id: string = '';

	#rootNode: ?HTMLElement;
	#data: ?{};
	#error: ?string;

	#fetchable: boolean = false;
	#clickable: boolean = false;

	#application: VueCreateAppResult;
	#contentComponent: Object;

	constructor(options: EngineOptions): void
	{
		this.#id = Type.isString(options.id) ? options.id : '';
		this.#rootNode = document.querySelector(`#${this.#id}`);
		this.#parentOrigin = Type.isString(options.origin) ? options.origin : null;

		this.#data = Type.isObject(options.data) ? options.data : null;
		this.#error = Type.isString(options.error) ? options.error : null;

		this.#fetchable = Type.isBoolean(options.fetchable) ? options.fetchable : false;
		this.#clickable = Type.isBoolean(options.clickable) ? options.clickable : false;

		this.#contentComponent = Runtime.clone(Content);
	}

	render()
	{
		if (this.#rootNode)
		{
			this.loader = new Loader({
				target: this.#rootNode,
			});

			this.#contentComponent.template = this.#rootNode.innerHTML || '';
			this.#contentComponent.template = `<div>${this.#contentComponent.template}</div>`;
			this.#bindEvents();
			this.#createApp();
		}
	}

	showLoader()
	{
		this.loader.show();
	}

	hideLoader()
	{
		this.loader.hide();
	}

	fetch(params: {} = {})
	{
		this.#message(
			'fetchData',
			params,
		);
	}

	openApplication(params: {} = {})
	{
		this.#message(
			'openApplication',
			params,
		);
	}

	openPath(path: string)
	{
		this.#message(
			'openPath',
			path,
		);
	}

	#message(name: string, params: {} = {})
	{
		window.parent.postMessage(
			{
				name,
				params,
			},
			this.#parentOrigin,
		);
	}

	#bindEvents() {
		Event.bind(window, 'message', this.#onMessage.bind(this));
	}

	#onMessage(event)
	{
		if (event.data && event.data.name && event.data.params)
		{
			if (
				event.data.name === 'setData'
				&& Type.isObject(event.data.params.data)
			)
			{
				EventEmitter.emit('landing:widgetvue:engine:onSetData', {
					data: event.data.params.data,
				});
			}

			if (
				event.data.name === 'setError'
				&& Type.isObject(event.data.params.error)
				&& Type.isString(event.data.params.error.message)
			)
			{
				EventEmitter.emit('landing:widgetvue:engine:onError', {
					message: event.data.params.error.message,
				});
			}

			this.#refreshFrameSize();
		}
	}

	#refreshFrameSize()
	{
		this.#message(
			'setSize',
			{
				size: document.body.offsetHeight,
			},
		);
	}

	#createApp(): void
	{
		const context = this;
		const defaultError = this.#error ? { message: this.#error } : null;

		this.#application = BitrixVue.createApp({
			name: this.#id,

			components: {
				Message, Error, Content: this.#contentComponent,
			},

			props: {
				defaultData: {
					type: Object, default: null,
				},
			},

			data()
			{
				return {
					message: null,
					error: defaultError,
				};
			},

			created()
			{
				this.$bitrix.eventEmitter.subscribe('landing:widgetvue:engine:startContentLoad', this.onShowLoader);
				this.$bitrix.eventEmitter.subscribe('landing:widgetvue:engine:endContentLoad', this.onHideLoader);
				this.$bitrix.eventEmitter.subscribe('landing:widgetvue:engine:onMessage', this.onShowMessage);
				this.$bitrix.eventEmitter.subscribe('landing:widgetvue:engine:onHideMessage', this.onHideMessage);
				this.$bitrix.eventEmitter.subscribe('landing:widgetvue:engine:onError', this.onShowError);
				EventEmitter.subscribe('landing:widgetvue:engine:onError', this.onShowError);
			},

			beforeUnmount()
			{
				this.$bitrix.eventEmitter.unsubscribe('landing:widgetvue:engine:startContentLoad', this.onShowLoader);
				this.$bitrix.eventEmitter.unsubscribe('landing:widgetvue:engine:endContentLoad', this.onHideLoader);
				this.$bitrix.eventEmitter.unsubscribe('landing:widgetvue:engine:onMessage', this.onShowMessage);
				this.$bitrix.eventEmitter.unsubscribe('landing:widgetvue:engine:onHideMessage', this.onHideMessage);
				this.$bitrix.eventEmitter.unsubscribe('landing:widgetvue:engine:onError', this.onShowError);
				EventEmitter.unsubscribe('landing:widgetvue:engine:onError', this.onShowError);
			},

			methods: {
				onShowLoader()
				{
					// todo: move loader to comp
					this.$bitrix.Application.get().showLoader();
				},

				onHideLoader()
				{
					// todo: move loader to comp
					this.$bitrix.Application.get().hideLoader();
				},

				onShowMessage(event: BaseEvent)
				{
					const message = event.getData()?.message || null;
					this.message = message ? { message } : null;
				},

				onHideMessage()
				{
					this.message = null;
				},

				onShowError(event: BaseEvent)
				{
					const message = event.getData()?.message || null;
					this.error = message ? { message } : null;
				},
			},

			beforeCreate(): void
			{
				this.$bitrix.Application.set(context);
			},

			template: `
				<div>
					<Error
						v-show="error !== null"
						v-bind="error && error.message !== null ? error : {}"
					/>
					<Message
						v-show="message !== null"
						v-bind="message && message.message !== null ? message : {}"
					/>
					<Content
						v-show="message === null && error === null"
						
						:defaultData="defaultData"
						:fetchable=${this.#fetchable}
						:clickable=${this.#clickable}
					/>
				</div>
			`,
		}, {
			defaultData: this.#data,
		});

		this.#application.mount(this.#rootNode);
	}
}
