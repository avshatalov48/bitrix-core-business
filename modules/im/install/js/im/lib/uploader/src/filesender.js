import { Uploader } from './uploader';
import { UploaderTask } from './uploader-task';

export class FileSender
{
	token: string = null;
	nextDataChunkToSend: number = null;
	readOffset: number = 0;

	constructor(task: UploaderTask, options = {})
	{
		this.diskFolderId = task.diskFolderId;
		this.listener = task.listener;
		this.status = task.status;

		this.taskId = task.taskId;
		this.fileData = task.fileData;
		this.fileName = task.fileName || this.fileData.name;
		this.generateUniqueName = task.generateUniqueName;
		this.chunkSizeInBytes = task.chunkSize;
		this.previewBlob = task.previewBlob || null;
		this.requestToDelete = false;

		this.listener('onStartUpload', {
			id: this.taskId,
			file: this.fileData,
			previewData: this.previewBlob
		});

		this.host = options.host || null;
		this.actionUploadChunk = options.actionUploadChunk || 'disk.api.content.upload';
		this.actionCommitFile = options.actionCommitFile || 'disk.api.file.createByContent';
		this.actionRollbackUpload = options.actionRollbackUpload || 'disk.api.content.rollbackUpload';
		this.customHeaders = options.customHeaders || null;
	}

	uploadContent(): void
	{
		if (this.status === Uploader.STATUSES.CANCELLED)
		{
			return;
		}

		this.status = Uploader.STATUSES.PROGRESS;
		this.readNext();

		const url = `${this.host ? this.host : ""}
			/bitrix/services/main/ajax.php?action=${this.actionUploadChunk}
			&filename=${this.fileName}
			${this.token ? "&token=" + this.token : ""}`;

		const contentRangeHeader = "bytes " + this.readOffset + "-" + (this.readOffset + this.chunkSizeInBytes - 1)
			+ "/" + this.fileData.size;

		this.calculateProgress();

		const headers ={
			"Content-Type": this.fileData.type,
			"Content-Range": contentRangeHeader,
		};

		if (!this.customHeaders)
		{
			headers['X-Bitrix-Csrf-Token'] = BX.bitrix_sessid();
		}
		else //if (this.customHeaders)
		{
			for (const customHeader in this.customHeaders)
			{
				if (this.customHeaders.hasOwnProperty(customHeader))
				{
					headers[customHeader] = this.customHeaders[customHeader];
				}
			}
		}

		fetch(url, {
			method: 'POST',
			headers: headers,
			credentials: "include",
			body: this.nextDataChunkToSend
		})
			.then(response => response.json())
			.then(result => {
				if (result.errors.length > 0)
				{
					this.status = Uploader.STATUSES.FAILED;
					this.listener('onUploadFileError', {id: this.taskId, result: result});
					console.error(result.errors[0].message)
				}
				else if(result.data.token)
				{
					this.token = result.data.token;
					this.readOffset = this.readOffset + this.chunkSizeInBytes;
					if (!this.isEndOfFile())
					{
						this.uploadContent();
					}
					else
					{
						this.createFileFromUploadedChunks();
					}
				}
			}).catch(err => {
				this.status = Uploader.STATUSES.FAILED;
				this.listener('onUploadFileError', {id: this.taskId, result: err});
			}
		);
	}

	deleteContent(): void
	{
		this.status = Uploader.STATUSES.CANCELLED;
		this.requestToDelete = true;

		if (!this.token)
		{
			console.error('Empty token.')
			return;
		}

		const url = `${this.host ? this.host : ""}/bitrix/services/main/ajax.php?
		action=${this.actionRollbackUpload}&token=${this.token}`;

		const headers = {};
		if (!this.customHeaders)
		{
			headers['X-Bitrix-Csrf-Token'] = BX.bitrix_sessid();
		}
		else //if (this.customHeaders)
		{
			for (const customHeader in this.customHeaders)
			{
				if (this.customHeaders.hasOwnProperty(customHeader))
				{
					headers[customHeader] = this.customHeaders[customHeader];
				}
			}
		}

		fetch(url, {
			method: 'POST',
			credentials: "include",
			headers: headers
		})
			.then(response => response.json())
			.then(result => console.log(result))
			.catch(err => console.error(err))
	}

	createFileFromUploadedChunks(): void
	{
		if (!this.token)
		{
			console.error('Empty token.')
			return;
		}

		if (this.requestToDelete)
		{
			return;
		}

		const url = `${this.host ? this.host : ""}/bitrix/services/main/ajax.php?action=${this.actionCommitFile}&filename=${this.fileName}`
			+ "&folderId=" + this.diskFolderId
			+ "&contentId=" + this.token
			+ (this.generateUniqueName ? "&generateUniqueName=true" : "");

		const headers = {
			"X-Upload-Content-Type": this.fileData.type,
		};

		if (!this.customHeaders)
		{
			headers['X-Bitrix-Csrf-Token'] = BX.bitrix_sessid();
		}
		else //if (this.customHeaders)
		{
			for (const customHeader in this.customHeaders)
			{
				if (this.customHeaders.hasOwnProperty(customHeader))
				{
					headers[customHeader] = this.customHeaders[customHeader];
				}
			}
		}

		const formData = new FormData();
		if (this.previewBlob)
		{
			formData.append("previewFile", this.previewBlob, "preview_" + this.fileName + ".jpg");
		}

		fetch(url, {
			method: 'POST',
			headers: headers,
			credentials: "include",
			body: formData
		})
			.then(response => response.json())
			.then(result => {
				this.uploadResult = result;
				if (result.errors.length > 0)
				{
					this.status = Uploader.STATUSES.FAILED;
					this.listener('onCreateFileError', {id: this.taskId, result: result});
					console.error(result.errors[0].message)
				}
				else
				{
					this.calculateProgress();
					this.status = Uploader.STATUSES.DONE;
					this.listener('onComplete', {id: this.taskId, result: result});
				}
			}).catch(err => {
				this.status = Uploader.STATUSES.FAILED;
				this.listener('onCreateFileError', {id: this.taskId, result: err});
			}
		);
	}

	calculateProgress(): void
	{
		this.progress = Math.round((this.readOffset * 100) / this.fileData.size);

		this.listener('onProgress', {
			id: this.taskId,
			progress: this.progress,
			readOffset: this.readOffset,
			fileSize: this.fileData.size,
		});
	}

	readNext(): void
	{
		if ((this.readOffset + this.chunkSizeInBytes) > this.fileData.size)
		{
			this.chunkSizeInBytes = this.fileData.size - this.readOffset;
		}

		this.nextDataChunkToSend = this.fileData.slice(this.readOffset, this.readOffset + this.chunkSizeInBytes);
	}

	isEndOfFile(): boolean
	{
		return (this.readOffset >= this.fileData.size);
	}
}