import { Type } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { Uploader, UploaderOptions } from 'ui.uploader.core';
import { ref, shallowRef, VueRefValue } from 'ui.vue3';

/**
 * @memberof BX.UI.Uploader
 */
export default class VueUploaderAdapter extends EventEmitter
{
	#uploader: Uploader = null;
	#items: VueRefValue<Array> = null;
	#uploaderError = null;

	constructor(uploaderOptions: UploaderOptions)
	{
		super();
		this.setEventNamespace('BX.UI.Uploader.Vue.Adapter');

		this.#items = ref([]);
		this.#uploaderError = shallowRef(null);

		const options = Type.isPlainObject(uploaderOptions) ? Object.assign({}, uploaderOptions) : {};
		const userEvents = options.events;
		options.events = {
			'File:onAddStart': this.#handleFileAdd.bind(this),
			'File:onRemove': this.#handleFileRemove.bind(this),
			'File:onStateChange': this.#handleFileStateChange.bind(this),
			'onError': this.#handleError.bind(this),
			'onUploadStart': this.#handleUploadStart.bind(this),
			'onUploadComplete': this.#handleUploadComplete.bind(this),
		};

		this.#uploader = new Uploader(options);
		this.#uploader.subscribeFromOptions(userEvents);
	}

	getUploader(): Uploader
	{
		return this.#uploader;
	}

	getItems(): VueRefValue<Array>
	{
		return this.#items;
	}

	getUploaderError(): VueRefValue<Object>
	{
		return this.#uploaderError;
	}

	#getItemsArray(): Array
	{
		return this.#items.value;
	}

	#getItem(id): { [key: string]: any }
	{
		return this.#getItemsArray().find(item => item.id === id);
	}

	#handleFileAdd(event: BaseEvent): void
	{
		const { file } = event.getData();
		const item = file.getState();
		this.#getItemsArray().push(item);

		this.emit('Item:onAdd', { item });
	}

	#handleFileRemove(event: BaseEvent): void
	{
		const { file } = event.getData();

		const position = this.#getItemsArray().findIndex(fileInfo => fileInfo.id === file.getId());
		if (position >= 0)
		{
			const result = this.#getItemsArray().splice(position, 1);
			this.emit('Item:onRemove', { item: result[0] });
		}
	}

	#handleFileStateChange(event: BaseEvent): void
	{
		const { file } = event.getData();
		const item = this.#getItem(file.getId());
		if (item)
		{
			Object.assign(item, file.getState());
		}
	}

	#handleError(event: BaseEvent): void
	{
		const { error } = event.getData();
		this.#uploaderError.value = error.toJSON();

		this.emit('Uploader:onError', event);
	}

	#handleUploadStart(event: BaseEvent): void
	{
		this.emit('Uploader:onUploadStart', event);
	}

	#handleUploadComplete(event: BaseEvent): void
	{
		this.emit('Uploader:onUploadComplete', event);
	}
}
