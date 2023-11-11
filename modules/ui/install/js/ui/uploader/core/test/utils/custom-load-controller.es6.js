import { Type } from 'main.core';

import AbstractLoadController from '../../src/backend/abstract-load-controller';
import Server from '../../src/backend/server';
import UploaderError from '../../src/uploader-error';
import type UploaderFile from '../../src/uploader-file';

export default class CustomLoadController extends AbstractLoadController
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

	load(file: UploaderFile)
	{
		const chunkSize = this.getServer().getChunkSize();
		const fileSize = file.getSize();
		let chunkOffset = 0;
		let loadedBytes = 0;
		let loadInvokes = 0;

		const load = () => {
			if (chunkOffset === 0 && fileSize <= chunkSize)
			{
				chunkOffset = fileSize;
				loadedBytes = fileSize;
			}
			else
			{
				const currentChunkSize = Math.min(chunkSize, fileSize - chunkOffset);
				const nextOffset = chunkOffset + currentChunkSize;

				loadedBytes += currentChunkSize;
				this.emit('onProgress', { progress: Math.ceil(loadedBytes / fileSize * 100) });

				chunkOffset = nextOffset;
			}

			loadInvokes++;
			if (chunkOffset >= fileSize)
			{
				clearInterval(this.xhr);
				this.emit('onLoad', {
					fileInfo: {
						serverFileId: 1077,
						type: 'image/jpeg',
						name: '9r78040i8tr391jj3eaz1oju5z78njv1.jpg',
						size: 275_427,
						width: 800,
						height: 800,
						downloadUrl: '/download/?fileId=1077',
						serverPreviewUrl: '/preview/?fileId=1077',
						serverPreviewWidth: 0,
						serverPreviewHeight: 0,
					},
				});
			}
			else if (this.raiseError && loadInvokes === this.raiseErrorStep)
			{
				clearInterval(this.xhr);
				this.emit('onError', { error: new UploaderError('CUSTOM_LOAD_ERROR') });
			}
		};

		this.xhr = setInterval(load, this.interval);
	}

	abort(): void
	{
		clearInterval(this.xhr);
	}
}
