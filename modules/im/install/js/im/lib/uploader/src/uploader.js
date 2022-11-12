import { FileSender } from './filesender';
import { EventEmitter } from "main.core.events";
import type { UploaderTask } from './uploader-task';
import type { UploaderResultTask } from "./uploader-result-task";
import {Type} from 'main.core.minimal';

export class Uploader extends EventEmitter
{
	queue: Array<UploaderTask> = [];
	isCloud: string = BX.message.isCloud;
	phpUploadMaxFilesize: number = BX.message.phpUploadMaxFilesize;
	phpPostMaxSize: number = BX.message.phpPostMaxSize;

	static STATUSES: Object = {
		PENDING: 0,
		PROGRESS: 1,
		DONE: 2,
		CANCELLED: 3,
		FAILED: 4,
	};
	static BOX_MIN_CHUNK_SIZE = 1024 * 1024; //1Mb
	static CLOUD_MIN_CHUNK_SIZE = 1024 * 1024 * 5; //5Mb
	static CLOUD_MAX_CHUNK_SIZE = 1024 * 1024 * 100; //100Mb

	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Messenger.Lib.Uploader');
		this.generatePreview = options.generatePreview || false;

		if (options)
		{
			this.inputNode = options.inputNode || null;
			this.dropNode = options.dropNode || null;

			this.fileMaxSize = options.fileMaxSize || null;
			this.fileMaxWidth = options.fileMaxWidth || null;
			this.fileMaxHeight = options.fileMaxHeight || null;

			if (options.sender)
			{
				this.senderOptions = {
					host: options.sender.host,
					actionUploadChunk: options.sender.actionUploadChunk,
					actionCommitFile: options.sender.actionCommitFile,
					actionRollbackUpload: options.sender.actionRollbackUpload,
					customHeaders: options.sender.customHeaders || null,
				}
			}

			this.assignInput();
			this.assignDrop();
		}
	}

	setInputNode(node)
	{
		if (node instanceof HTMLInputElement || Array.isArray(node))
		{
			this.inputNode = node;
			this.assignInput();
		}
	}

	addFilesFromEvent(event)
	{
		Array.from(event.target.files).forEach(file => {
			this.emitSelectedFile(file);
		});
	}

	getPreview(file: File): Promise
	{
		return new Promise((resolve, reject) =>
		{
			if (!this.generatePreview)
			{
				resolve();
			}

			if (file instanceof File)
			{
				if (file.type.startsWith('video'))
				{
					Uploader.getVideoPreviewBlob(file, 10)
						.then(blob => this.getImageDimensions(blob))
						.then(result => resolve(result))
						.catch(reason => reject(reason));
				}
				else if (file.type.startsWith('image'))
				{
					const blob = new Blob([file],{type: file.type});
					this.getImageDimensions(blob).then(result => resolve(result));
				}
				else
				{
					resolve();
				}
			}
			else
			{
				reject("Parameter 'file' is not instance of 'File'");
			}
		})
	}

	addTask(task: UploaderTask): void
	{
		if (!this.isModernBrowser())
		{
			console.warn('Unsupported browser!')
			return;
		}

		if (!this.checkTaskParams(task))
		{
			return;
		}

		task.chunkSize = this.calculateChunkSize(task.chunkSize);

		task.listener = (event, data) => (this.onUploadEvent(event, data));
		task.status = Uploader.STATUSES.PENDING;

		const fileSender = new FileSender(task, this.senderOptions);
		this.queue.push(fileSender);
		this.checkUploadQueue();
	}

	deleteTask(taskId: string): void
	{
		if (!taskId)
		{
			return;
		}

		this.queue = this.queue.filter(queueItem => {
			if (queueItem.taskId === taskId)
			{
				queueItem.deleteContent();
				return false;
			}

			return true;
		});
	}

	getTask(taskId: string): UploaderResultTask
	{
		const task = this.queue.find(queueItem => queueItem.taskId === taskId);
		if (task)
		{
			return {
				id: task.id,
				diskFolderId: task.diskFolderId,
				fileData: task.fileData,
				fileName: task.fileName,
				progress: task.progress,
				readOffset: task.readOffset,
				status: task.status,
				token: task.token,
				uploadResult: task.uploadResult,
			};
		}

		return null;
	}

	static getVideoPreviewBlob(file: File, seekTime: number = 0): Promise
	{
		return new Promise((resolve, reject) => {
			const videoPlayer = document.createElement('video');
			videoPlayer.setAttribute('src', URL.createObjectURL(file));
			videoPlayer.load();
			videoPlayer.addEventListener('error', (error) => {
				reject("Error while loading video file", error);
			});
			videoPlayer.addEventListener('loadedmetadata', () => {
				if (videoPlayer.duration < seekTime)
				{
					seekTime = 0;
					// reject("Too big seekTime for the video.");
					// return;
				}
				videoPlayer.currentTime = seekTime;
				videoPlayer.addEventListener('seeked', () => {
					const canvas = document.createElement("canvas");
					canvas.width = videoPlayer.videoWidth;
					canvas.height = videoPlayer.videoHeight;
					const context = canvas.getContext("2d");
					context.drawImage(videoPlayer, 0, 0, canvas.width, canvas.height);
					context.canvas.toBlob(
						blob => resolve(blob),
						"image/jpeg",
						1
					);
				});
			});
		});
	}

	checkUploadQueue(): void
	{
		if (this.queue.length > 0)
		{
			const inProgressTasks = this.queue.filter(queueTask => queueTask.status === Uploader.STATUSES.PENDING);
			if (inProgressTasks.length > 0)
			{
				inProgressTasks[0].uploadContent();
			}
		}
	}

	onUploadEvent(event: string, data: Object)
	{
		this.emit(event, data);
		this.checkUploadQueue();
	}

	checkTaskParams(task: UploaderTask)
	{
		if (!task.taskId)
		{
			console.error('Empty Task ID.')
			return false;
		}

		if (!task.fileData)
		{
			console.error('Empty file data.')
			return false;
		}

		if (!task.diskFolderId)
		{
			console.error('Empty disk folder ID.')
			return false;
		}

		if (this.fileMaxSize && this.fileMaxSize < task.fileData.size)
		{
			const data = {
				maxFileSizeLimit: this.fileMaxSize,
				file: task.fileData,
			};
			this.emit('onFileMaxSizeExceeded', data);

			return false;
		}

		return true;
	}

	calculateChunkSize(taskChunkSize: number): number
	{
		if (Type.isUndefined(this.isCloud)) // widget case
		{
			return taskChunkSize;
		}

		let chunk = 0;
		if (taskChunkSize)
		{
			chunk = taskChunkSize;
		}

		if (this.isCloud === 'Y')
		{
			chunk  = (chunk < Uploader.CLOUD_MIN_CHUNK_SIZE) ? Uploader.CLOUD_MIN_CHUNK_SIZE : chunk;
			chunk  = (chunk > Uploader.CLOUD_MAX_CHUNK_SIZE) ? Uploader.CLOUD_MAX_CHUNK_SIZE : chunk;
		}
		else //if(this.isCloud === 'N')
		{
			const maxBoxChunkSize = Math.min(this.phpPostMaxSize, this.phpUploadMaxFilesize);

			chunk  = (chunk < Uploader.BOX_MIN_CHUNK_SIZE) ? Uploader.BOX_MIN_CHUNK_SIZE : chunk;
			chunk  = (chunk > maxBoxChunkSize) ? maxBoxChunkSize : chunk;
		}

		return chunk;
	}

	isModernBrowser(): boolean
	{
		return typeof (fetch) !== 'undefined';
	}

	assignInput()
	{
		if (this.inputNode instanceof HTMLInputElement)
		{
			this.setOnChangeEventListener(this.inputNode);
		}
		else if (Array.isArray(this.inputNode))
		{
			this.inputNode.forEach(node => {
				if (node instanceof HTMLInputElement)
				{
					this.setOnChangeEventListener(node);
				}
			});
		}
	}

	setOnChangeEventListener(inputNode: HTMLInputElement)
	{
		inputNode.addEventListener('change', (event) => {
			this.addFilesFromEvent(event);
		}, false);
	}

	assignDrop()
	{
		if (this.dropNode instanceof HTMLElement)
		{
			this.setDropEventListener(this.dropNode);
		}
		else if (Array.isArray(this.dropNode))
		{
			this.dropNode.forEach(node => {
				if (node instanceof HTMLElement)
				{
					this.setDropEventListener(node);
				}
			});
		}
	}

	setDropEventListener(dropNode: HTMLElement)
	{
		dropNode.addEventListener('drop', (event) => {
			event.preventDefault();
			event.stopPropagation();

			Array.from(event.dataTransfer.files).forEach(file => {
				this.emitSelectedFile(file);
			});
		}, false);
	}

	emitSelectedFile(file: File)
	{
		const data = { file: file };
		this.getPreview(file).then(previewData => {
			if (previewData)
			{
				data['previewData'] = previewData.blob;
				data['previewDataWidth'] = previewData.width;
				data['previewDataHeight'] = previewData.height;

				if (this.fileMaxWidth || this.fileMaxHeight)
				{
					const isMaxWidthExceeded = (this.fileMaxWidth === null ? false : this.fileMaxWidth < data['previewDataWidth']);
					const isMaxHeightExceeded = (this.fileMaxHeight === null ? false : this.fileMaxHeight < data['previewDataHeight']);
					if (isMaxWidthExceeded || isMaxHeightExceeded)
					{
						const eventData = {
							maxWidth: this.fileMaxWidth,
							maxHeight: this.fileMaxHeight,
							fileWidth: data['previewDataWidth'],
							fileHeight: data['previewDataHeight'],
						};
						this.emit('onFileMaxResolutionExceeded', eventData);
						return false;
					}
				}
			}
			this.emit('onSelectFile', data);
		}).catch(err => {
			console.warn(`Couldn't get preview for file ${file.name}. Error: ${err}`);
			this.emit('onSelectFile', data);
		});
	}

	getImageDimensions(fileBlob: Blob)
	{
		return new Promise ((resolved, rejected) => {
			if (!fileBlob)
			{
				rejected('getImageDimensions: fileBlob can\'t be empty');
			}

			const img = new Image();
			img.onload = () => {
				resolved({
					blob: fileBlob,
					width: img.width,
					height: img.height
				})
			};
			img.onerror = () => {
				rejected();
			};
			img.src = URL.createObjectURL(fileBlob);
		})
	}
}
