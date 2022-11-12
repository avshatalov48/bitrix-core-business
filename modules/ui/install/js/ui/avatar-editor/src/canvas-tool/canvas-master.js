import {Dom, Event, Loc} from 'main.core';
import {ImageType, CanvasDefault} from './canvas-default';
import CanvasLoader from './canvas-loader';

export default class CanvasMaster extends CanvasDefault
{
	#fileName: ?String;
	load(file): Promise
	{
		this.emit('onLoad');
		this.#fileName = null;
		return new Promise((resolve, reject) => {
			this.reset();
			CanvasLoader.loadFile(
				file,
				(imageNode) => {
					this.#fileName = imageNode.name;
					this.set(imageNode);
					resolve();
				},
				() => {
					this.emit('onError', Loc.getMessage('JS_AVATAR_EDITOR_ERROR_IMAGE_DEPLOYING'));
					reject();
				}
			);
		});
	}

	#applyNameAndExtensionToBlob(result: Blob): Blob
	{
		result.name = this.#fileName || 'image';
		let ext = result.name.split('.').pop().toLowerCase();
		ext = (ext === result.name ? '' : ext);

		if (result.type === 'image/png' && ext !== 'png')
		{
			if ('jpg,bmp,jpeg,jpe,gif,png,webp'.lastIndexOf(ext) > 0)
			{
				result.name = result.name.substr(0, result.name.lastIndexOf('.'));
			}
			result.name = [(result.name || 'image'), 'png'].join('.');
		}
		return result;
	}

	getBlob(): Promise
	{
		return new Promise((resolve, reject) => {
			this.packBlob()
				.then((blob) => {
					this.#applyNameAndExtensionToBlob(blob);
					resolve({blob});
				})
				.catch(reject);
		});
	}
}
