import AbstractRemoveController from './abstract-remove-controller';
import { removeMultiple } from './remove-multiple';

import type UploaderFile from '../uploader-file';

export default class RemoveController extends AbstractRemoveController
{
	remove(file: UploaderFile): void
	{
		removeMultiple(this, file);
	}
}
