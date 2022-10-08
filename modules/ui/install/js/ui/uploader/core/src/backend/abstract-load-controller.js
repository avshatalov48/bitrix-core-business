import { EventEmitter } from 'main.core.events';
import type Server from './server';
import type UploaderFile from '../uploader-file';

export default class AbstractLoadController extends EventEmitter
{
	constructor(server: Server)
	{
		super();
		this.setEventNamespace('BX.UI.Uploader.LoadController');

		this.server = server;
	}

	getServer(): Server
	{
		return this.server;
	}

	load(file: UploaderFile): void
	{
		throw new Error('You must implement load() method.');
	}

	abort(): void
	{
		throw new Error('You must implement abort() method.');
	}
}
