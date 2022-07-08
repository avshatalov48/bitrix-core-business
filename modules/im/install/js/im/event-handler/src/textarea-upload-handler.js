import { EventEmitter } from 'main.core.events';
import { EventType, FileStatus, RestMethod as ImRestMethod, RestMethodHandler as ImRestMethodHandler } from 'im.const';
import { Uploader } from 'im.lib.uploader';
import { Logger } from 'im.lib.logger';
import { Type } from 'main.core';

/**
 * @notice define getActionUploadChunk and getActionCommitFile methods for custom upload methods (e.g. videoconference)
 * @notice redefine addMessageWithFile for custom headers (e.g. videoconference)
 */
export class TextareaUploadHandler
{
	controller: Object = null;
	restClient: Object = null;
	uploader: Uploader = null;
	isRequestingDiskFolderId: boolean = false;

	constructor($Bitrix)
	{
		this.controller = $Bitrix.Data.get('controller');
		this.restClient = $Bitrix.RestClient.get();

		this.initUploader();

		this.onTextareaFileSelectedHandler = this.onTextareaFileSelected.bind(this);
		this.addMessageWithFileHandler = this.addMessageWithFile.bind(this);
		this.onClickOnUploadCancelHandler = this.onClickOnUploadCancel.bind(this);

		EventEmitter.subscribe(EventType.textarea.fileSelected, this.onTextareaFileSelectedHandler);
		EventEmitter.subscribe(EventType.uploader.addMessageWithFile, this.addMessageWithFileHandler);
		EventEmitter.subscribe(EventType.dialog.clickOnUploadCancel, this.onClickOnUploadCancelHandler);
	}

	initUploader()
	{
		this.uploader = new Uploader({
			generatePreview: true,
			sender: this.getUploaderSenderOptions()
		});

		this.uploader.subscribe('onStartUpload', this.onStartUploadHandler.bind(this));
		this.uploader.subscribe('onProgress', this.onProgressHandler.bind(this));
		this.uploader.subscribe('onSelectFile', this.onSelectFileHandler.bind(this));
		this.uploader.subscribe('onComplete', this.onCompleteHandler.bind(this));
		this.uploader.subscribe('onUploadFileError', this.onUploadFileErrorHandler.bind(this));
		this.uploader.subscribe('onCreateFileError', this.onCreateFileErrorHandler.bind(this));
	}

	commitFile(params, message)
	{
		this.restClient.callMethod(ImRestMethod.imDiskFileCommit, {
			chat_id: params.chatId,
			upload_id: params.uploadId,
			message: params.messageText,
			template_id: params.messageId,
			file_template_id: params.fileId,
		}, null, null).then(response => {
			this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFileCommit, response, message);
		}).catch(error => {
			this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFileCommit, error, message);
		});

		return true;
	}

	setUploadError(chatId, fileId, messageId = 0)
	{
		this.controller.store.dispatch('files/update', {
			chatId: chatId,
			id: fileId,
			fields: {
				status: FileStatus.error,
				progress: 0
			}
		});
		if (messageId)
		{
			this.controller.store.dispatch('messages/actionError', {
				chatId: chatId,
				id: messageId,
				retry: false,
			});
		}
	}

	onTextareaFileSelected({data: event})
	{
		const fileInput = event && event.fileChangeEvent && event.fileChangeEvent.target.files.length > 0 ? event.fileChangeEvent : '';
		if (!fileInput)
		{
			return false;
		}

		this.uploadFile(fileInput);
	}

	addMessageWithFile(event)
	{
		const message = event.getData();
		if (!this.getDiskFolderId())
		{
			this.requestDiskFolderId(message.chatId).then(() => {
				this.addMessageWithFile(event);
			}).catch(error => {
				Logger.error('addMessageWithFile error', error);
				return false;
			});

			return false;
		}

		this.uploader.addTask({
			taskId: message.file.id,
			fileData: message.file.source.file,
			fileName: message.file.source.file.name,
			generateUniqueName: true,
			diskFolderId: this.getDiskFolderId(),
			previewBlob: message.file.previewBlob,
		});
	}

	uploadFile(event)
	{
		if (!event)
		{
			return false;
		}

		this.uploader.addFilesFromEvent(event);
	}

	destroy()
	{
		if (this.uploader)
		{
			this.uploader.unsubscribeAll();
		}
		EventEmitter.unsubscribe(EventType.textarea.fileSelected, this.onTextareaFileSelectedHandler);
		EventEmitter.unsubscribe(EventType.uploader.addMessageWithFile, this.addMessageWithFileHandler);
		EventEmitter.unsubscribe(EventType.dialog.clickOnUploadCancel, this.onClickOnUploadCancelHandler);
	}

	getChatId()
	{
		return this.controller.store.state.application.dialog.chatId;
	}

	getDialogId()
	{
		return this.controller.store.state.application.dialog.dialogId;
	}

	getDiskFolderId()
	{
		return this.controller.store.state.application.dialog.diskFolderId;
	}

	getCurrentUser()
	{
		return this.controller.store.getters['users/get'](this.controller.store.state.application.common.userId, true);
	}

	getMessageByFileId(fileId, eventData)
	{
		const chatMessages = this.controller.store.getters['messages/get'](this.getChatId());
		const messageWithFile = chatMessages.find(message => {
			if (Type.isArray(message.params?.FILE_ID))
			{
				return message.params.FILE_ID.includes(fileId);
			}

			return false;
		});

		if (!messageWithFile)
		{
			return;
		}

		return {
			id: messageWithFile.id,
			chatId: messageWithFile.chatId,
			dialogId: this.getDialogId(),
			text: messageWithFile.text,
			file: {id: fileId, source: eventData, previewBlob: eventData.previewData},
			sending: true
		};
	}

	requestDiskFolderId(chatId)
	{
		return new Promise((resolve, reject) =>
		{
			if (this.isRequestingDiskFolderId || this.getDiskFolderId())
			{
				this.isRequestingDiskFolderId = false;
				resolve();

				return;
			}

			this.isRequestingDiskFolderId = true;

			this.restClient.callMethod(ImRestMethod.imDiskFolderGet, {chat_id: chatId}).then(response => {
				this.isRequestingDiskFolderId = false;
				this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFolderGet, response);
				resolve();
			}).catch(error => {
				this.isRequestingDiskFolderId = false;
				this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFolderGet, error);
				reject(error);
			});
		});
	}

	// Uploader handlers
	onStartUploadHandler(event)
	{
		const eventData = event.getData();
		Logger.log('Uploader: onStartUpload', eventData);

		this.controller.store.dispatch('files/update', {
			chatId: this.getChatId(),
			id: eventData.id,
			fields: {
				status: FileStatus.upload,
				progress: 0
			}
		});
	}

	onProgressHandler(event)
	{
		const eventData = event.getData();
		Logger.log('Uploader: onProgress', eventData);

		this.controller.store.dispatch('files/update', {
			chatId: this.getChatId(),
			id: eventData.id,
			fields: {
				status: FileStatus.upload,
				progress: (eventData.progress === 100 ? 99 : eventData.progress),
			}
		});
	}

	onSelectFileHandler(event)
	{
		const eventData = event.getData();
		const file = eventData.file;
		Logger.log('Uploader: onSelectFile', eventData);

		let fileType = 'file';
		if (file.type.toString().startsWith('image'))
		{
			fileType = 'image';
		}
		else if (file.type.toString().startsWith('video'))
		{
			fileType = 'video';
		}

		this.controller.store.dispatch('files/add', {
			chatId: this.getChatId(),
			authorId: this.getCurrentUser().id,
			name: file.name,
			type: fileType,
			extension: file.name.split('.').splice(-1)[0],
			size: file.size,
			image: !eventData.previewData? false: {
				width: eventData.previewDataWidth,
				height: eventData.previewDataHeight,
			},
			status: FileStatus.progress,
			progress: 0,
			authorName: this.getCurrentUser().name,
			urlPreview: eventData.previewData ? URL.createObjectURL(eventData.previewData) : '',
		}).then(fileId => {
			EventEmitter.emit(EventType.textarea.sendMessage, {
				text: '',
				file: { id: fileId, source: eventData, previewBlob: eventData.previewData }
			});
		});
	}

	onCompleteHandler(event)
	{
		const eventData = event.getData();
		Logger.log('Uploader: onComplete', eventData);

		this.controller.store.dispatch('files/update', {
			chatId: this.getChatId(),
			id: eventData.id,
			fields: {
				status: FileStatus.wait,
				progress: 100
			}
		});

		const messageWithFile = this.getMessageByFileId(eventData.id, eventData);
		const fileType = this.controller.store.getters['files/get'](this.getChatId(), messageWithFile.file.id, true).type;

		this.commitFile({
			chatId: this.getChatId(),
			uploadId: eventData.result.data.file.id,
			messageText: messageWithFile.text,
			messageId: messageWithFile.id,
			fileId: messageWithFile.file.id,
			fileType
		}, messageWithFile);
	}

	onUploadFileErrorHandler(event)
	{
		const eventData = event.getData();
		Logger.log('Uploader: onUploadFileError', eventData);

		const messageWithFile = this.getMessageByFileId(eventData.id, eventData);
		if (messageWithFile)
		{
			this.setUploadError(this.getChatId(), messageWithFile.file.id, messageWithFile.id);
		}
	}

	onCreateFileErrorHandler(event)
	{
		const eventData = event.getData();
		Logger.log('Uploader: onCreateFileError', eventData);

		const messageWithFile = this.getMessageByFileId(eventData.id, eventData);
		if (messageWithFile)
		{
			this.setUploadError(this.getChatId(), messageWithFile.file.id, messageWithFile.id);
		}
	}

	onClickOnUploadCancel({data: event})
	{
		const fileId = event.file.id;
		const fileData = event.file;
		const messageWithFile = this.getMessageByFileId(fileId, fileData);

		if (!messageWithFile)
		{
			return;
		}

		this.uploader.deleteTask(fileId);

		this.controller.store.dispatch('messages/delete', {
			chatId: this.getChatId(),
			id: messageWithFile.id,
		}).then(() => {
			this.controller.store.dispatch('files/delete', {
				chatId: this.getChatId(),
				id: messageWithFile.file.id,
			});
		});
	}

	getActionCommitFile(): ?string
	{
		return null;
	}

	getActionUploadChunk(): ?string
	{
		return null;
	}

	getUploaderSenderOptions()
	{
		return {
			actionUploadChunk: this.getActionUploadChunk(),
			actionCommitFile: this.getActionCommitFile(),
		};
	}
}