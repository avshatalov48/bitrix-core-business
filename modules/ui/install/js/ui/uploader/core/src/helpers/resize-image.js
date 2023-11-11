import { Browser, Type } from 'main.core';

import createWorker from './create-worker';
import BitmapWorker from './bitmap-worker';
import ResizeWorker from './resize-worker';
import loadImage from './load-image';
import createImagePreviewCanvas from './create-image-preview-canvas';
import createImagePreview from './create-image-preview';
import getCanvasToBlobType from './get-canvas-to-blob-type';
import renameFileToMatchMimeType from './rename-file-to-match-mime-type';
import createFileFromBlob from './create-file-from-blob';
import getResizedImageSize from './get-resized-image-size';
import { sharpen, shouldSharpen } from './sharpen';

import type { ResizeImageOptions } from '../types/resize-image-options';
import type { ResizeImageResult } from '../types/resize-image-result';
import convertCanvasToBlob from './convert-canvas-to-blob';

let canCreateImageBitmap = (
	'createImageBitmap' in window
	&& !Type.isUndefined(window.ImageBitmap)
	&& ImageBitmap.prototype
	&& ImageBitmap.prototype.close
);

if (canCreateImageBitmap && Browser.isSafari())
{
	const ua = navigator.userAgent.toLowerCase();
	const regex = /version\/([\d.]+)/i;
	const result = regex.exec(ua);
	if (result && result[1] && result[1] < '16.4')
	{
		// Webkit bug https://bugs.webkit.org/show_bug.cgi?id=223326
		canCreateImageBitmap = false;
	}
}

const createImagePreviewCanvasSource = createImagePreviewCanvas.toString();
const getResizedImageSizeSource = getResizedImageSize.toString();
const sharpenSource = sharpen.toString();
const shouldSharpenSource = shouldSharpen.toString();
const canUseOffscreenCanvas: boolean = canCreateImageBitmap && !Type.isUndefined(window.OffscreenCanvas);

// eslint-disable-next-line max-lines-per-function
const resizeImage = (source: Blob | File, options: ResizeImageOptions): Promise<ResizeImageResult> => {
	// eslint-disable-next-line max-lines-per-function
	return new Promise((resolve, reject) => {
		if (canUseOffscreenCanvas)
		{
			const resizeWorker = createWorker(ResizeWorker);
			const type: string = getCanvasToBlobType(source, options);

			resizeWorker.post(
				{
					file: source,
					type,
					options,
					createImagePreviewCanvasSource,
					getResizedImageSizeSource,
					sharpenSource,
					shouldSharpenSource,
				},
				(message): void => {
					resizeWorker.terminate();
					if (message)
					{
						const { bitmap, targetWidth, targetHeight, useOriginalSize } = message;
						if (useOriginalSize)
						{
							resolve({
								preview: source,
								width: targetWidth,
								height: targetHeight,
							});
						}
						else
						{
							let canvas: HTMLCanvasElement = document.createElement('canvas');
							canvas.width = bitmap.width;
							canvas.height = bitmap.height;
							const context = canvas.getContext('bitmaprenderer');
							context.transferFromImageBitmap(bitmap);

							// console.log('bitmaprenderer');

							const { quality = 0.92 } = options;
							convertCanvasToBlob(canvas, type, quality).then((blob) => {
								let preview: Blob = blob;
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
							}).catch((error) => {
								// eslint-disable-next-line no-console
								console.log('Uploader: convertCanvasToBlob error', error);
								loadImageDataFallback();
							}).finally(() => {
								canvas.width = 0;
								canvas.height = 0;
								canvas = null;

								bitmap.close();
							});

							// let preview: Blob = blob;
							// if (Type.isFile(source))
							// {
							// 	// File type could be changed pic.gif -> pic.jpg
							// 	const newFileName = renameFileToMatchMimeType(source.name, type);
							// 	preview = createFileFromBlob(blob, newFileName);
							// }
							//
							// resolve({
							// 	preview,
							// 	width: targetWidth,
							// 	height: targetHeight,
							// });
						}
					}
					else
					{
						loadImageDataFallback();
					}
				},
			);
		}
		else if (canCreateImageBitmap)
		{
			const bitmapWorker = createWorker(BitmapWorker);
			bitmapWorker.post(
				{ file: source },
				(imageBitmap: ImageBitmap): void => {
					bitmapWorker.terminate();
					if (imageBitmap)
					{
						handleImageLoad(imageBitmap);
					}
					else
					{
						loadImageDataFallback();
					}
				},
			);
		}
		else
		{
			loadImageDataFallback();
		}

		function handleImageLoad(imageData: ImageBitmap | HTMLImageElement): void
		{
			const { useOriginalSize, targetWidth, targetHeight } = getResizedImageSize(imageData, options);
			if (useOriginalSize)
			{
				// if it was ImageBitmap
				if ('close' in imageData)
				{
					imageData.close();
				}

				resolve({
					preview: source,
					width: targetWidth,
					height: targetHeight,
				});
			}
			else
			{
				const mimeType: string = getCanvasToBlobType(source, options);
				createImagePreview(imageData, { ...options, mimeType })
					.then(({ blob, width, height }): void => {
						let preview: Blob = blob;
						if (Type.isFile(source))
						{
							// File type could be changed pic.gif -> pic.jpg
							const newFileName = renameFileToMatchMimeType(source.name, mimeType);
							preview = createFileFromBlob(blob, newFileName);
						}

						resolve({ preview, width, height });
					})
					.catch((error) => {
						reject(error);
					})
					.finally(() => {
						// if it was ImageBitmap
						if ('close' in imageData)
						{
							imageData.close();
						}
					})
				;
			}
		}

		function loadImageDataFallback(): void
		{
			// eslint-disable-next-line no-console
			console.log('Uploader: resize image fallback');
			loadImage(source)
				.then(({ image }) => {
					handleImageLoad(image);
				})
				.catch((error) => {
					reject(error);
				})
			;
		}
	});
};

export default resizeImage;
