import { EventEmitter } from 'main.core.events';
import type Server from './server';

export default class AbstractUploadController extends EventEmitter
{
	#server: Server;

	constructor(server: Server)
	{
		super();
		this.setEventNamespace('BX.UI.Uploader.UploadController');

		this.#server = server;
	}

	getServer(): Server
	{
		return this.#server;
	}

	upload(file: File): void
	{
		throw new Error('You must implement upload() method.');
	}

	abort(): void
	{
		throw new Error('You must implement abort() method.');
	}
}
