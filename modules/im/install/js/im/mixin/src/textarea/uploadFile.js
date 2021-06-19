import { EventEmitter } from 'main.core.events';
import { EventType, FileStatus, RestMethod as ImRestMethod, RestMethodHandler as ImRestMethodHandler } from "im.const";
import { Uploader } from "im.lib.uploader";
import { Logger } from "im.lib.logger";


/**
 * @notice creates uploader instance when dialog is inited (dialog.init in model)
 * @notice define actionUploadChunk and actionCommitFile fields for custom upload methods (e.g. videoconference)
 * @notice redefine addMessageWithFile for custom headers (e.g. videoconference)
 */
export const TextareaUploadFile = {
	created()
	{
		EventEmitter.subscribe(EventType.textarea.fileSelected, this.onTextareaFileSelected);
	},
	beforeDestroy()
	{
		if (this.uploader)
		{
			this.uploader.unsubscribeAll();
		}
		EventEmitter.unsubscribe(EventType.textarea.fileSelected, this.onTextareaFileSelected);
	},
	computed:
	{
		dialogInited()
		{
			if (!this.dialog)
			{
				return false;
			}

			return this.dialog.init;
		}
	},
	watch:
	{
		dialogInited(newValue)
		{
			if (newValue === true)
			{
				this.initUploader();
			}
		}
	},
	methods: {
		onTextareaFileSelected({data: event})
		{
			let fileInput = event && event.fileChangeEvent && event.fileChangeEvent.target.files.length > 0 ? event.fileChangeEvent : '';
			if (!fileInput)
			{
				return false;
			}

			this.uploadFile(fileInput);
		},
		addMessageWithFile(message)
		{
			this.stopWriting();

			this.uploader.addTask({
				taskId: message.file.id,
				fileData: message.file.source.file,
				fileName: message.file.source.file.name,
				generateUniqueName: true,
				diskFolderId: this.diskFolderId,
				previewBlob: message.file.previewBlob,
			});
		},

		//uploader
		uploadFile(event)
		{
			if (!event)
			{
				return false;
			}

			this.uploader.addFilesFromEvent(event);
		},
		initUploader()
		{
			this.uploader = new Uploader({
				generatePreview: true,
				sender: {
					actionUploadChunk: this.actionUploadChunk,
					actionCommitFile: this.actionCommitFile,
				}
			});

			this.uploader.subscribe('onStartUpload', event => {
				const eventData = event.getData();
				Logger.log('Uploader: onStartUpload', eventData);

				this.$store.dispatch('files/update', {
					chatId: this.chatId,
					id: eventData.id,
					fields: {
						status: FileStatus.upload,
						progress: 0
					}
				});
			});

			this.uploader.subscribe('onProgress', (event) => {
				const eventData = event.getData();
				Logger.log('Uploader: onProgress', eventData);

				this.$store.dispatch('files/update', {
					chatId: this.chatId,
					id: eventData.id,
					fields: {
						status: FileStatus.upload,
						progress: (eventData.progress === 100 ? 99 : eventData.progress),
					}
				});
			});

			this.uploader.subscribe('onSelectFile', (event) => {
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

				this.$store.dispatch('files/add', {
					chatId: this.chatId,
					authorId: this.userId,
					name: file.name,
					type: fileType,
					extension: file.name.split('.').splice(-1)[0],
					size: file.size,
					image: !eventData.previewData? false: {
						width: eventData.previewDataWidth,
						height: eventData.previewDataHeight,
					},
					status: FileStatus.wait,
					progress: 0,
					authorName: this.getCurrentUser().name,
					urlPreview: eventData.previewData? URL.createObjectURL(eventData.previewData) : "",
				}).then(fileId => {
					this.addMessageOnClient('', {id: fileId, source: eventData, previewBlob: eventData.previewData})
				});
			});

			this.uploader.subscribe('onComplete', (event) => {
				const eventData = event.getData();
				Logger.log('Uploader: onComplete', eventData);

				this.$store.dispatch('files/update', {
					chatId: this.chatId,
					id: eventData.id,
					fields: {
						status: FileStatus.wait,
						progress: 100
					}
				});

				const message = this.messagesToSend.find(message => {
					if (message.file)
					{
						return message.file.id === eventData.id;
					}

					return false;
				});
				const fileType = this.$store.getters['files/get'](this.chatId, message.file.id, true).type;

				this.fileCommit({
					chatId: this.chatId,
					uploadId: eventData.result.data.file.id,
					messageText: message.text,
					messageId: message.id,
					fileId: message.file.id,
					fileType
				}, message);
			});

			this.uploader.subscribe('onUploadFileError', (event) => {
				const eventData = event.getData();
				Logger.log('Uploader: onUploadFileError', eventData);

				const message = this.messagesToSend.find(message => {
					if (message.file)
					{
						return message.file.id === eventData.id;
					}

					return false;
				});

				this.fileError(this.chatId, message.file.id, message.id);
			});

			this.uploader.subscribe('onCreateFileError', (event) => {
				const eventData = event.getData();
				Logger.log('Uploader: onCreateFileError', eventData);

				const message = this.messagesToSend.find(message => {
					if (message.file)
					{
						return message.file.id === eventData.id;
					}

					return false;
				});

				this.fileError(this.chatId, message.file.id, message.id);
			});

			return new Promise((resolve, reject) => resolve());
		},
		fileCommit(params, message)
		{
			this.getRestClient().callMethod(ImRestMethod.imDiskFileCommit, {
				chat_id: params.chatId,
				upload_id: params.uploadId,
				message: params.messageText,
				template_id: params.messageId,
				file_template_id: params.fileId,
			}, null, null, ).then(response => {
				this.executeRestAnswer(ImRestMethodHandler.imDiskFileCommit, response, message);
			}).catch(error => {
				this.executeRestAnswer(ImRestMethodHandler.imDiskFileCommit, error, message);
			});

			return true;
		},
		fileError(chatId, fileId, messageId = 0)
		{
			this.$store.dispatch('files/update', {
				chatId: chatId,
				id: fileId,
				fields: {
					status: FileStatus.error,
					progress: 0
				}
			});
			if (messageId)
			{
				this.$store.dispatch('messages/actionError', {
					chatId: chatId,
					id: messageId,
					retry: false,
				});
			}
		}
	}
};