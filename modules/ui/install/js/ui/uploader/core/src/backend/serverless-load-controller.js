import { Type } from 'main.core';

import AbstractLoadController from './abstract-load-controller';
import UploaderError from '../uploader-error';

import type Server from './server';
import type UploaderFile from '../uploader-file';

export default class ServerlessLoadController extends AbstractLoadController
{
	constructor(server: Server, options: { [key: string]: any } = {})
	{
		super(server, options);
	}

	load(file: UploaderFile): void
	{
		if (Type.isStringFilled(file.getName()))
		{
			this.emit('onProgress', { progress: 100 });
			this.emit('onLoad', { fileInfo: file });
		}
		else
		{
			this.emit('onError', { error: new UploaderError('WRONG_FILE_SOURCE') });
		}
	}

	abort(): void
	{
		// silent abort
	}
}
