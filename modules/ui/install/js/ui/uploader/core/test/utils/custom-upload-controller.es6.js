import AbstractUploadController from '../../src/backend/abstract-upload-controller';
import Server from '../../src/backend/server';
import { Type } from 'main.core';
import UploaderError from '../../src/uploader-error';
import type UploaderFile from '../../src/uploader-file';

export default class CustomUploadController extends AbstractUploadController
{
	interval: number = 100;
	raiseError: boolean = false;
	raiseErrorStep: number = 3;
	xhr = null;

	constructor(server: Server, options = {})
	{
		super(server, options);

		if (Type.isNumber(options.interval))
		{
			this.interval = options.interval;
		}

		if (Type.isBoolean(options.raiseError))
		{
			this.raiseError = options.raiseError;
		}

		if (Type.isNumber(options.raiseErrorStep))
		{
			this.raiseErrorStep = options.raiseErrorStep;
		}
	}

	upload(file: UploaderFile)
	{
		const chunkSize = this.getServer().getChunkSize();
		const fileSize = file.getSize();
		let chunkOffset = 0;
		let uploadedBytes = 0;

		let uploadInvokes = 0;
		const upload = () => {
			if (chunkOffset === 0 && fileSize <= chunkSize)
			{
				chunkOffset = fileSize;
				uploadedBytes = fileSize;
			}
			else
			{
				const currentChunkSize = Math.min(chunkSize, fileSize - chunkOffset);
				const nextOffset = chunkOffset + currentChunkSize;

				uploadedBytes += currentChunkSize;
				this.emit('onProgress', { progress: Math.ceil(uploadedBytes / fileSize * 100) });

				chunkOffset = nextOffset;
			}

			uploadInvokes++;
			if (chunkOffset >= fileSize)
			{
				clearInterval(this.xhr);
				this.emit('onUpload', { fileInfo: { serverFileId: 'serverFileId' } });
			}
			else if (this.raiseError && uploadInvokes === this.raiseErrorStep)
			{
				clearInterval(this.xhr);
				this.emit('onError', { error: new UploaderError('CUSTOM_UPLOAD_ERROR') });
			}
		};

		this.xhr = setInterval(upload, this.interval);
	}

	abort(): void
	{
		clearInterval(this.xhr);
	}
}
