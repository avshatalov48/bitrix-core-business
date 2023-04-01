import AbstractLoadController from './abstract-load-controller';
import Server from './server';
import UploaderFile from '../uploader-file';
import { Type } from 'main.core';
import UploaderError from '../uploader-error';

export default class ClientLoadController extends AbstractLoadController
{
	constructor(server: Server, options: { [key: string]: any } = {})
	{
		super(server, options);
	}

	load(file: UploaderFile): void
	{
		if (Type.isFile(file.getBinary()))
		{
			this.emit('onProgress', { file, progress: 100 });
			this.emit('onLoad', { fileInfo: file });
		}
		else
		{
			this.emit('onError', { error: new UploaderError('WRONG_FILE_SOURCE') });
		}
	}

	abort(): void
	{

	}

}