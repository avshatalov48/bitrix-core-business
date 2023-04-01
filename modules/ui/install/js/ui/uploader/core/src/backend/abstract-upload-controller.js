import { EventEmitter } from 'main.core.events';
import type Server from './server';
import type UploaderFile from '../uploader-file';
import { Type } from 'main.core';

export default class AbstractUploadController extends EventEmitter
{
	#server: Server;
	#options: { [key: string]: any };

	constructor(server: Server, options: { [key: string]: any } = {})
	{
		super();
		this.setEventNamespace('BX.UI.Uploader.UploadController');

		this.#server = server;
		this.#options = options;
	}

	getServer(): Server
	{
		return this.#server;
	}

	getOptions(): { [key: string]: any }
	{
		return this.#options;
	}

	getOption(option: string, defaultValue?: any): any
	{
		if (!Type.isUndefined(this.#options[option]))
		{
			return this.#options[option];
		}
		else if (!Type.isUndefined(defaultValue))
		{
			return defaultValue;
		}

		return null;
	}

	upload(file: UploaderFile): void
	{
		throw new Error('You must implement upload() method.');
	}

	abort(): void
	{
		throw new Error('You must implement abort() method.');
	}
}
