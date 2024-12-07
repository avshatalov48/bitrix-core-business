import { Type } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { ref, shallowRef, VueRefValue } from 'ui.vue3';

import {
	Uploader,
	UploaderFile,
	UploaderEvent,
	UploaderError,
	type UploaderOptions,
	type UploaderFileInfo,
} from 'ui.uploader.core';

/**
 * @memberof BX.UI.Uploader
 */
export default class VueUploaderAdapter extends EventEmitter
{
	#uploader: Uploader = null;
	#items: VueRefValue<Array> = null;
	#uploaderError = null;
	#removeFilesFromServer: boolean = true;

	constructor(uploaderOptions: UploaderOptions | Uploader)
	{
		super();
		this.setEventNamespace('BX.UI.Uploader.Vue.Adapter');

		this.#items = ref([]);
		this.#uploaderError = shallowRef(null);

		const events = {
			[UploaderEvent.FILE_ADD_START]: this.#handleFileAdd.bind(this),
			[UploaderEvent.FILE_REMOVE]: this.#handleFileRemove.bind(this),
			[UploaderEvent.FILE_STATE_CHANGE]: this.#handleFileStateChange.bind(this),
			[UploaderEvent.FILE_COMPLETE]: this.#handleFileComplete.bind(this),
			[UploaderEvent.FILE_ERROR]: this.#handleFileError.bind(this),
			[UploaderEvent.ERROR]: this.#handleError.bind(this),
			[UploaderEvent.UPLOAD_START]: this.#handleUploadStart.bind(this),
			[UploaderEvent.UPLOAD_COMPLETE]: this.#handleUploadComplete.bind(this),
		};

		if (uploaderOptions instanceof Uploader)
		{
			this.#uploader = uploaderOptions;
			if (this.#uploader.getFileCount() > 0)
			{
				throw new Error('VueUploaderAdapter: an uploader have some files. We cannot create an adapter.');
			}

			// Resubscribe events because adapter events must be first
			Object.keys(events).forEach((eventName) => {
				const currentListeners = [...this.#uploader.getListeners(eventName).keys()];
				this.#uploader.unsubscribeAll(eventName);
				this.#uploader.subscribe(eventName, events[eventName]);
				currentListeners.forEach((listener) => {
					this.#uploader.subscribe(eventName, listener);
				});
			});
		}
		else
		{
			const options: UploaderOptions = Type.isPlainObject(uploaderOptions) ? { ...uploaderOptions } : {};
			const userEvents = options.events;
			options.events = events;
			this.#uploader = new Uploader(options);
			this.#uploader.subscribeFromOptions(userEvents);
		}
	}

	getUploader(): Uploader
	{
		return this.#uploader;
	}

	getReactiveItems(): VueRefValue<Array>
	{
		return this.#items;
	}

	getUploaderError(): VueRefValue<Object>
	{
		return this.#uploaderError;
	}

	getItems(): UploaderFileInfo[]
	{
		return this.#items.value;
	}

	getItem(id): ?UploaderFileInfo
	{
		return this.getItems().find((item: UploaderFileInfo): boolean => item.id === id) || null;
	}

	setRemoveFilesFromServerWhenDestroy(value: boolean = true): void
	{
		this.#removeFilesFromServer = value;
	}

	destroy(): void
	{
		this.#uploader.destroy({ removeFilesFromServer: this.#removeFilesFromServer });
		this.#uploader = null;
	}

	#handleFileAdd(event: BaseEvent): void
	{
		const file: UploaderFile = event.getData().file;
		const item: UploaderFileInfo = file.getState();

		this.emit('Item:onBeforeAdd', { item });
		this.getItems().push(item);
		this.emit('Item:onAdd', { item });
	}

	#handleFileRemove(event: BaseEvent): void
	{
		const file: UploaderFile = event.getData().file;

		const position: number = this.getItems().findIndex((fileInfo: UploaderFileInfo): boolean => {
			return fileInfo.id === file.getId();
		});

		if (position >= 0)
		{
			const result: UploaderFileInfo[] = this.getItems().splice(position, 1);
			this.emit('Item:onRemove', { item: result[0] });
		}
	}

	#handleFileStateChange(event: BaseEvent): void
	{
		const file: UploaderFile = event.getData().file;
		const item: ?UploaderFileInfo = this.getItem(file.getId());
		if (item)
		{
			Object.assign(item, file.getState());
		}
	}

	#handleFileComplete(event: BaseEvent): void
	{
		const file: UploaderFile = event.getData().file;
		const item: UploaderFileInfo = file.getState();

		this.emit('Item:onComplete', { item });
	}

	#handleFileError(event: BaseEvent): void
	{
		const file: UploaderFile = event.getData().file;
		const error: UploaderError = event.getData().error;
		const item: UploaderFileInfo = file.getState();

		this.emit('Item:onError', { item, error });
	}

	#handleError(event: BaseEvent): void
	{
		const { error } = event.getData();
		this.#uploaderError.value = error.toJSON();

		this.emit('Uploader:onError', new BaseEvent({ data: event.getData() }));
	}

	#handleUploadStart(event: BaseEvent): void
	{
		this.emit('Uploader:onUploadStart', new BaseEvent({ data: event.getData() }));
	}

	#handleUploadComplete(event: BaseEvent): void
	{
		this.emit('Uploader:onUploadComplete', new BaseEvent({ data: event.getData() }));
	}
}
