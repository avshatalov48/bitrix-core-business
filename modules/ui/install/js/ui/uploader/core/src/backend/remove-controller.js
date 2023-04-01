import AbstractRemoveController from './abstract-remove-controller';
import { removeMultiple } from './remove-multiple';

import type Server from './server';
import type UploaderFile from '../uploader-file';

export default class RemoveController extends AbstractRemoveController
{
	constructor(server: Server)
	{
		super(server);
	}

	remove(file: UploaderFile): void
	{
		removeMultiple(this, file);
	}
}