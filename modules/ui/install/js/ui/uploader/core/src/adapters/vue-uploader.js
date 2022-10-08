import { Dom, Type } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { Uploader, UploaderFile, UploaderOptions, UploaderStatus } from 'ui.uploader.core';
import { BitrixVue, VueCreateAppResult } from 'ui.vue';

/**
 * @memberof BX.UI.Uploader
 */
export default class VueUploader
{
	#uploader: Uploader = null;
	#vueApp = null;

	constructor(uploaderOptions: UploaderOptions, vueOptions = {})
	{
		const context = this;
		this.#vueApp = BitrixVue.createApp({
			data() {
				return {
					items: [],
					rootComponentId: null,
					multiple: true,
					acceptOnlyImages: false,
				};
			},
			mixins: [
				vueOptions,
			],
			provide: {
				getUploader() {
					return context.#uploader;
				},
				getWidget() {
					return context;
				}
			},
			methods: {
				getUploader() {
					return context.#uploader;
				},
				getWidget() {
					return context;
				},
			},
			// language=Vue
			template: `
				<component
					:is="rootComponentId"
					:items="items"
				/>
			`,
		});

		const options = Type.isPlainObject(uploaderOptions) ? Object.assign({}, uploaderOptions) : {};
		const userEvents = options.events;
		options.events = {
			'File:onAddStart': this.handleFileAdd.bind(this),
			'File:onRemove': this.handleFileRemove.bind(this),
			'File:onUploadProgress': this.handleFileUploadProgress.bind(this),
			'File:onStateChange': this.handleFileStateChange.bind(this),
			'File:onError': this.handleFileError.bind(this),
			'onError': this.handleError.bind(this),
			'onUploadStart': this.handleUploadStart.bind(this),
			'onUploadComplete': this.handleUploadComplete.bind(this),
		};

		this.#uploader = new Uploader(options);
		this.#uploader.subscribeFromOptions(userEvents);

		this.#vueApp.multiple = this.#uploader.isMultiple();
		this.#vueApp.acceptOnlyImages = this.#uploader.shouldAcceptOnlyImages();
		this.#vueApp.rootComponentId = this.getRootComponentId();
	}

	getVueOptions(): { [key: string]: any }
	{
		return {};
	}

	getRootComponentId(): ?Function
	{
		return null;
	}

	getUploader(): Uploader
	{
		return this.#uploader;
	}

	getVueApp(): VueCreateAppResult
	{
		return this.#vueApp;
	}

	renderTo(node: HTMLElement): void
	{
		if (Type.isDomNode(node))
		{
			const container = Dom.create('div');
			node.appendChild(container);

			if (!this.getUploader().getHiddenFieldsContainer())
			{
				this.getUploader().setHiddenFieldsContainer(node);
			}

			this.getVueApp().mount(container);
		}
	}

	remove(id): void
	{
		this.getUploader().removeFile(id);
	}

	getItems(): Array
	{
		return this.getVueApp().items;
	}

	getItem(id)
	{
		return this.getItems().find(item => item.id === id);
	}

	createItemFromFile(file: UploaderFile): { [key: string]: any }
	{
		const item = file.getState();
		item.progress = 0;

		return item;
	}

	handleFileAdd(event: BaseEvent): void
	{
		const { file, error } = event.getData();
		const item = this.createItemFromFile(file);
		this.getItems().push(item);

		this.getVueApp().$Bitrix.eventEmitter.emit('Item:onAdd', { item });
	}

	handleFileRemove(event: BaseEvent): void
	{
		const { file } = event.getData();

		const position = this.getItems().findIndex(fileInfo => fileInfo.id === file.getId());
		if (position >= 0)
		{
			const result = this.getItems().splice(position, 1);

			this.getVueApp().$Bitrix.eventEmitter.emit('Item:onRemove', { item: result[0] });
		}
	}

	handleFileError(event: BaseEvent): void
	{
		const { file, error } = event.getData();

		const item = this.getItem(file.getId());
		item.error = error;
	}

	handleFileUploadProgress(event: BaseEvent): void
	{
		const { file, progress } = event.getData();
		const item = this.getItem(file.getId());
		if (item)
		{
			item.progress = progress;
		}
	}

	handleFileStateChange(event: BaseEvent): void
	{
		const { file } = event.getData();
		const item = this.getItem(file.getId());
		if (item)
		{
			Object.assign(item, file.getState());
		}
	}

	handleError(event: BaseEvent): void
	{
		this.getVueApp().$Bitrix.eventEmitter.emit('Uploader:onError', event);
	}

	handleUploadStart(event: BaseEvent): void
	{
		this.getVueApp().$Bitrix.eventEmitter.emit('Uploader:onUploadStart', event);
	}

	handleUploadComplete(event: BaseEvent): void
	{
		this.getVueApp().$Bitrix.eventEmitter.emit('Uploader:onUploadComplete', event);
	}
}
