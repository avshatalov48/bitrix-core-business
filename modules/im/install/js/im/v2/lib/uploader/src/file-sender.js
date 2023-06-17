import {Uploader} from './uploader';
import type {UploaderTask} from './types/uploader-task';

const SENDER_STATUSES: {[string]: number } = {
	PENDING: 0,
	PROGRESS: 1,
	DONE: 2,
	CANCELLED: 3,
	FAILED: 4,
};

export class FileSender
{
	#uploaderTask: UploaderTask;
	#status: number = SENDER_STATUSES.PENDING;
	#listener: Function;

	#requestToDelete: boolean = false;

	#token: string = null;
	#nextDataChunkToSend: number;
	#readOffset: number = 0;

	static UPLOAD_CHUNK_DEFAULT_ACTION = 'disk.api.content.upload';
	static COMMIT_FILE_DEFAULT_ACTION = 'disk.api.file.createByContent';
	static ROLLBACK_UPLOAD_DEFAULT_ACTION = 'disk.api.content.rollbackUpload';

	constructor(task: UploaderTask)
	{
		this.#uploaderTask = task;
		this.#listener = task.listener;
		this.abortController = new AbortController();
		this.#init();
	}

	#init()
	{
		this.#uploaderTask.progress = 0;

		this.#listener(Uploader.EVENTS.startUpload, {
			task: this.#uploaderTask,
		});
	}

	uploadContent(): void
	{
		if (this.#status === SENDER_STATUSES.CANCELLED)
		{
			return;
		}

		this.#status = SENDER_STATUSES.PROGRESS;
		this.#readNext();
		this.#updateProgress();

		const url = this.#getUploadContentEndpoint();
		const headers = {
			'Content-Type': this.#uploaderTask.fileData.type,
			'Content-Range': this.#getContentRangeHeader(),
			'X-Bitrix-Csrf-Token': this.#getBitrixSessid(),
		};

		const requestParams = {
			url,
			headers,
			body: this.#nextDataChunkToSend,
			signal: this.abortController.signal
		};

		this.#sendPostRequest(requestParams).then(response => {
			if (response.errors.length > 0)
			{
				this.#status = SENDER_STATUSES.FAILED;
				this.#listener(Uploader.EVENTS.uploadFileError, {
					task: this.#uploaderTask,
					result: response
				});
				console.error(response.errors[0].message);
			}
			else if (response.data.token)
			{
				this.#token = response.data.token;
				this.#readOffset += this.#uploaderTask.chunkSize;
				if (!this.#isEndOfFile())
				{
					this.uploadContent();
				}
				else
				{
					this.#createFileFromUploadedChunks();
				}
			}
		}).catch(error => {
			console.warn('error', error);
			this.#status = SENDER_STATUSES.FAILED;
			this.#listener(Uploader.EVENTS.uploadFileError, {
				task: this.#uploaderTask,
				result: error
			});
		});
	}

	deleteContent(): void
	{
		this.abortController.abort();
		this.#status = SENDER_STATUSES.CANCELLED;
		this.#requestToDelete = true;

		if (!this.#token)
		{
			console.error('Empty token.');
			return;
		}

		const url = this.#getDeleteContentEndpoint();
		const headers = {
			'X-Bitrix-Csrf-Token': this.#getBitrixSessid()
		};

		this.#sendPostRequest({url, headers}).catch(error => console.error(error));
	}

	#createFileFromUploadedChunks(): void
	{
		if (!this.#token)
		{
			console.error('Empty token.');
			return;
		}

		if (this.#requestToDelete)
		{
			return;
		}

		const url = this.#getCreateFileEndpoint();
		const headers = {
			'X-Upload-Content-Type': this.#uploaderTask.fileData.type,
			'X-Bitrix-Csrf-Token': this.#getBitrixSessid()
		};

		const body = new FormData();
		if (this.#uploaderTask.previewBlob)
		{
			body.append('previewFile', this.#uploaderTask.previewBlob, `preview_${this.#getFileName()}.jpg`);
		}

		this.#sendPostRequest({url, headers, body}).then(response => {
			if (response.errors.length > 0)
			{
				this.#status = SENDER_STATUSES.FAILED;
				this.#listener(Uploader.EVENTS.createFileError, {
					task: this.#uploaderTask,
					result: response
				});

				console.error(response.errors[0].message);
			}
			else
			{
				this.#updateProgress();
				this.#status = SENDER_STATUSES.DONE;
				this.#listener(Uploader.EVENTS.complete, {
					task: this.#uploaderTask,
					result: response
				});
			}
		}).catch(error => {
			this.#status = SENDER_STATUSES.FAILED;
			this.#listener(Uploader.EVENTS.createFileError, {
				task: this.#uploaderTask,
				result: error
			});
		});
	}

	#updateProgress(): void
	{
		this.#uploaderTask.progress = Math.round((this.#readOffset * 100) / this.#uploaderTask.fileData.size);

		this.#listener(Uploader.EVENTS.progressUpdate, {
			task: this.#uploaderTask,
		});
	}

	#readNext(): void
	{
		if ((this.#readOffset + this.#uploaderTask.chunkSize) > this.#uploaderTask.fileData.size)
		{
			this.#uploaderTask.chunkSize = this.#uploaderTask.fileData.size - this.#readOffset;
		}

		this.#nextDataChunkToSend = this.#uploaderTask.fileData.slice(
			this.#readOffset,
			this.#readOffset + this.#uploaderTask.chunkSize
		);
	}

	#isEndOfFile(): boolean
	{
		return this.#readOffset >= this.#uploaderTask.fileData.size;
	}

	#getUploadContentEndpoint(): string
	{
		const token = this.#token ? `&token=${this.#token}` : '';

		return `
			${this.#getBaseEndpoint(FileSender.UPLOAD_CHUNK_DEFAULT_ACTION)}
			&filename=${this.#getFileName()}
			${token}
		`;
	}

	#getCreateFileEndpoint(): string
	{
		const generateUniqueName = this.#uploaderTask.generateUniqueName ? '&generateUniqueName=true' : '';

		return `
			${this.#getBaseEndpoint(FileSender.COMMIT_FILE_DEFAULT_ACTION)}
			&filename=${this.#getFileName()}
			&folderId=${this.#uploaderTask.diskFolderId}
			&contentId=${this.#token}
			${generateUniqueName}
		`;
	}

	#getDeleteContentEndpoint(): string
	{
		return `${this.#getBaseEndpoint(FileSender.ROLLBACK_UPLOAD_DEFAULT_ACTION)}&token=${this.#token}`;
	}

	#getBaseEndpoint(action: string): string
	{
		return `/bitrix/services/main/ajax.php?action=${action}`;
	}

	#getFileName(): string
	{
		return encodeURIComponent(this.#uploaderTask.fileName || this.#uploaderTask.fileData.name);
	}

	#sendPostRequest(request: {url: string, headers: Object, body: Object, signal: ?AbortSignal}): Promise
	{
		const {url, headers, body, signal} = request;

		const requestPrams = {
			method: 'POST',
			credentials: 'include',
			headers: headers,
		};

		if (signal)
		{
			requestPrams.signal = this.abortController.signal;
		}

		if (body)
		{
			requestPrams.body = body;
		}

		return new Promise((resolve, reject) => {
			fetch(url, requestPrams).then(response => resolve(response.json())).catch(error => reject(error));
		});
	}

	#getContentRangeHeader(): string
	{
		const range = this.#readOffset + this.#uploaderTask.chunkSize - 1;

		return `bytes ${this.#readOffset}-${range}/${this.#uploaderTask.fileData.size}`;
	}

	#getBitrixSessid(): string
	{
		// eslint-disable-next-line bitrix-rules/no-bx
		return BX.bitrix_sessid();
	}

	isPending(): boolean
	{
		return this.#status === SENDER_STATUSES.PENDING;
	}

	getTaskId(): string
	{
		return this.#uploaderTask.taskId;
	}
}