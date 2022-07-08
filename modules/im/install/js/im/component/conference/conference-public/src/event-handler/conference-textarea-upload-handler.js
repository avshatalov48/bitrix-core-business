import { TextareaUploadHandler } from 'im.event-handler';
import { Logger } from 'im.lib.logger';

export class ConferenceTextareaUploadHandler extends TextareaUploadHandler
{
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

		message.chatId = this.getChatId();

		this.setUploaderCustomHeaders();

		this.uploader.addTask({
			taskId: message.file.id,
			fileData: message.file.source.file,
			fileName: message.file.source.file.name,
			generateUniqueName: true,
			diskFolderId: this.getDiskFolderId(),
			previewBlob: message.file.previewBlob,
		});
	}

	setUploaderCustomHeaders()
	{
		if (!this.uploader.senderOptions.customHeaders)
		{
			this.uploader.senderOptions.customHeaders = {};
		}
		this.uploader.senderOptions.customHeaders['Call-Auth-Id'] = this.getUserHash();
		this.uploader.senderOptions.customHeaders['Call-Chat-Id'] = this.getChatId();
	}

	getUserHash(): string
	{
		return this.controller.store.state.conference.user.hash;
	}

	getActionCommitFile(): ?string
	{
		return 'im.call.disk.commit';
	}

	getActionUploadChunk(): ?string
	{
		return 'im.call.disk.upload';
	}
}