import { Browser, Type } from 'main.core';

import createWorker from './create-worker';
import BitmapWorker from './bitmap-worker';
import loadImage from './load-image';
import createImagePreview from './create-image-preview';
import getCanvasToBlobType from './get-canvas-to-blob-type';
import renameFileToMatchMimeType from './rename-file-to-match-mime-type';
import createFileFromBlob from './create-file-from-blob';
import convertCanvasToBlob from './convert-canvas-to-blob';
import getResizedImageSize from './get-resized-image-size';

import type { ResizeImageOptions } from '../types/resize-image-options';
import type { ResizeImageResult } from '../types/resize-image-result';

let canCreateImageBitmap = (
	'createImageBitmap' in window
	&& typeof ImageBitmap !== 'undefined'
	&& ImageBitmap.prototype
	&& ImageBitmap.prototype.close
);

if (canCreateImageBitmap && Browser.isSafari())
{
	const ua = navigator.userAgent.toLowerCase();
	const regex = new RegExp('version\\/([0-9.]+)', 'i');
	const result = regex.exec(ua);
	if (result && result[1] && result[1] < '16.4')
	{
		// Webkit bug https://bugs.webkit.org/show_bug.cgi?id=223326
		canCreateImageBitmap = false;
	}
}

const resizeImage = (source: Blob | File, options: ResizeImageOptions): Promise<ResizeImageResult> => {
	return new Promise((resolve, reject) => {
		const loadImageDataFallback = () => {
			loadImage(source)
				.then(({ image }) => {
					handleImageLoad(image);
				})
				.catch(error => {
					reject(error);
				})
			;
		};

		const handleImageLoad = (imageData: ImageBitmap | HTMLImageElement) => {
			const { targetWidth, targetHeight, useOriginalSize } = getResizedImageSize(imageData, options);
			if (useOriginalSize)
			{
				if ('close' in imageData)
				{
					imageData.close();
				}

				resolve({
					preview: source,
					width: imageData.width,
					height: imageData.height,
				});

				return;
			}

			const canvas = createImagePreview(imageData, targetWidth, targetHeight);

			// if it was ImageBitmap
			if ('close' in imageData)
			{
				imageData.close();
			}

			const { quality = 0.92, mimeType, mimeTypeMode } = options;
			const type = getCanvasToBlobType(source, mimeType, mimeTypeMode);

			convertCanvasToBlob(canvas, type, quality)
				.then((blob: Blob) => {
					let preview = blob;
					if (Type.isFile(source))
					{
						// File type could be changed pic.gif -> pic.jpg
						const newFileName = renameFileToMatchMimeType(source.name, type);
						preview = createFileFromBlob(blob, newFileName);
					}

					resolve({
						preview,
						width: targetWidth,
						height: targetHeight,
					});
				})
				.catch((error) => {
					reject(error);
				})
			;
		};

		if (canCreateImageBitmap)
		{
			const bitmapWorker = createWorker(BitmapWorker);
			bitmapWorker.post({ file: source },
				(imageBitmap: ImageBitmap) => {
					bitmapWorker.terminate();
					if (imageBitmap)
					{
						handleImageLoad(imageBitmap);
					}
					else
					{
						loadImageDataFallback();
					}
				}
			);
		}
		else
		{
			loadImageDataFallback();
		}
	});
};

export default resizeImage;