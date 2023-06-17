import {EventEmitter} from 'main.core.events';
import {Type, Extension} from 'main.core';
import {FileSender} from './file-sender';

import type {UploaderTask} from './types/uploader-task';
import type {UploaderOptions} from './types/uploader-options';

export class Uploader extends EventEmitter
{
	#queue: UploaderTask[] = [];
	#inputNode: HTMLElement | HTMLElement[];
	#dropNode: HTMLElement | HTMLElement[];
	#fileMaxSize: number = 0;

	#isCloud: boolean;
	#phpUploadMaxFilesize: number;
	#phpPostMaxSize: number;

	static EVENTS: {[string]: string } = {
		startUpload: 'startUpload',
		progressUpdate: 'progressUpdate',
		complete: 'complete',
		fileMaxSizeExceeded: 'fileMaxSizeExceeded',
		createFileError: 'createFileError',
		uploadFileError: 'uploadFileError',
	};

	static BOX_MIN_CHUNK_SIZE = 1024 * 1024; //1Mb
	static CLOUD_MIN_CHUNK_SIZE = 1024 * 1024 * 5; //5Mb
	static CLOUD_MAX_CHUNK_SIZE = 1024 * 1024 * 100; //100Mb

	constructor(options: UploaderOptions = {})
	{
		super();
		this.setEventNamespace('BX.Messenger.V2.Lib.Uploader');

		this.#handleUploaderOptions(options);
		this.#initSettings();
	}

	addTask(task: UploaderTask): void
	{
		if (!this.checkTaskParams(task))
		{
			return;
		}

		task.chunkSize = this.#calculateChunkSize(task.chunkSize);
		task.listener = (eventName, data) => this.onUploadEvent(eventName, data);

		this.#queue.push(new FileSender(task));
		this.checkUploadQueue();
	}

	deleteTask(taskId: string): void
	{
		if (!taskId)
		{
			return;
		}

		this.#queue = this.#queue.filter((queueItem: FileSender) => {
			if (queueItem.getTaskId() === taskId)
			{
				queueItem.deleteContent();

				return false;
			}

			return true;
		});
	}

	getTask(taskId: string): ?UploaderTask
	{
		const task = this.queue.find(queueItem => queueItem.taskId === taskId);

		return task || null;
	}

	checkUploadQueue(): void
	{
		if (this.#queue.length === 0)
		{
			return;
		}

		const inProgressTasks = this.#queue.filter((queueTask: FileSender) => queueTask.isPending());
		if (inProgressTasks.length > 0)
		{
			inProgressTasks[0].uploadContent();
		}
	}

	onUploadEvent(eventName: string, event: Object)
	{
		this.emit(eventName, event);
		this.checkUploadQueue();
	}

	checkTaskParams(task: UploaderTask): boolean
	{
		if (!task.taskId)
		{
			console.error('Empty TaskId.');

			return false;
		}

		if (!task.fileData)
		{
			console.error('Empty file data.');

			return false;
		}

		if (!task.diskFolderId)
		{
			console.error('Empty disk folder id.');

			return false;
		}

		if (this.#fileMaxSize && this.#fileMaxSize < task.fileData.size)
		{
			this.emit(Uploader.EVENTS.fileMaxSizeExceeded, {
				maxFileSizeLimit: this.#fileMaxSize,
				task: task,
			});

			return false;
		}

		return true;
	}

	#calculateChunkSize(chunk = 0): number
	{
		const maxAvailableBoxChinkSize = Math.min(this.#phpPostMaxSize, this.#phpUploadMaxFilesize);

		const minChunkSize = this.#isCloud ? Uploader.CLOUD_MIN_CHUNK_SIZE : Uploader.BOX_MIN_CHUNK_SIZE;
		const maxChunkSize = this.#isCloud ? Uploader.CLOUD_MAX_CHUNK_SIZE : maxAvailableBoxChinkSize;

		return Math.min(Math.max(chunk, minChunkSize), maxChunkSize);
	}

	#handleUploaderOptions(options: UploaderOptions)
	{
		if (options.inputNode instanceof HTMLInputElement || Type.isArrayFilled(options.inputNode))
		{
			this.#inputNode = options.inputNode;
		}

		if (options.dropNode instanceof HTMLElement || Type.isArrayFilled(options.dropNode))
		{
			this.#dropNode = options.dropNode;
		}
	}

	#initSettings()
	{
		const settings = Extension.getSettings('im.v2.lib.uploader');

		this.#isCloud = settings.get('isCloud');
		this.#phpUploadMaxFilesize = settings.get('phpUploadMaxFilesize');
		this.#phpPostMaxSize = settings.get('phpPostMaxSize');
	}
}

export {PreviewManager} from './classes/preview-manager';