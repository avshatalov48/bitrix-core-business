import { Type } from 'main.core';
import createBlobFromDataUri from './create-blob-from-data-uri';

const canvasPrototype = window.HTMLCanvasElement && window.HTMLCanvasElement.prototype;
const hasToBlobSupport = window.HTMLCanvasElement && canvasPrototype.toBlob;
const canUseOffscreenCanvas: boolean = !Type.isUndefined(window.OffscreenCanvas);

const convertCanvasToBlob = (canvas: HTMLCanvasElement, type: string, quality: number): Promise<Blob> => {
	return new Promise((resolve, reject) => {
		if (canUseOffscreenCanvas && (canvas instanceof OffscreenCanvas))
		{
			canvas.convertToBlob({ type, quality }).then((blob: Blob) => {
				resolve(blob);
			}).catch((error) => {
				reject(error);
			});
		}
		else if (hasToBlobSupport)
		{
			canvas.toBlob((blob: Blob) => {
				resolve(blob);
			}, type, quality);
		}
		else
		{
			const blob = createBlobFromDataUri(canvas.toDataURL(type, quality));

			resolve(blob);
		}
	});
};

export default convertCanvasToBlob;
