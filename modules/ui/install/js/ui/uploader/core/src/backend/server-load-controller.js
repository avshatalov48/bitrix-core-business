import AbstractLoadController from './abstract-load-controller';
import type Server from './server';
import type UploaderFile from '../uploader-file';
import { loadMultiple, abort } from './server-load-multiple';

export default class ServerLoadController extends AbstractLoadController
{
	#file: UploaderFile = null;

	constructor(server: Server, options: { [key: string]: any } = {})
	{
		super(server, options);
	}

	load(file: UploaderFile): void
	{
		if (this.getServer().getController())
		{
			this.#file = file;
			loadMultiple(this, file);
		}
		else
		{
			this.emit('onProgress', { file, progress: 100 });
			this.emit('onLoad', { fileInfo: null });
		}
	}

	abort(): void
	{
		if (this.getServer().getController() && this.#file)
		{
			abort(this, this.#file);
		}
	}
}