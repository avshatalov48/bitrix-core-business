import createWorker from './create-worker';
import BitmapWorker from './bitmap-worker';
import loadImage from './load-image';
import createImagePreview from './create-image-preview';
import renameFileToMatchMimeType from './rename-file-to-match-mime-type';
import createFileFromBlob from './create-file-from-blob';
import convertCanvasToBlob from './convert-canvas-to-blob';

type ResizeImageOptions = {
	mode?: 'contain' | 'crop' | 'force',
	upscale?: boolean,
	width?: number,
	height?: number,
	quality?: number,
};

const canCreateImageBitmap = (
	'createImageBitmap' in window
	&& typeof ImageBitmap !== 'undefined'
	&& ImageBitmap.prototype
	&& ImageBitmap.prototype.close
);

const resizeImage = (file: File, options: ResizeImageOptions) => {
	return new Promise((resolve, reject) => {
		const loadImageDataFallback = () => {
			loadImage(file)
				.then(({ image }) => {
					handleImageLoad(image);
				})
				.catch(error => {
					reject(error);
				})
			;
		};

		const handleImageLoad = (imageData: ImageBitmap | HTMLImageElement) => {
			const { targetWidth, targetHeight } = calcTargetSize(imageData, options);
			if (!targetWidth || !targetHeight)
			{
				if ('close' in imageData)
				{
					imageData.close();
				}

				resolve({
					preview: file,
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

			const { quality = 0.92, mimeType = 'image/jpeg' } = options;
			const type = /jpeg|png|webp/.test(file.type) ? file.type : mimeType;

			convertCanvasToBlob(canvas, type, quality)
				.then((blob: Blob) => {
					const newFileName = renameFileToMatchMimeType(file.name, type);
					const preview = createFileFromBlob(blob, newFileName);

					resolve({
						preview,
						width: targetWidth,
						height: targetHeight,
					});
				})
				.catch(() => {
					reject();
				})
			;
		};

		if (canCreateImageBitmap)
		{
			const bitmapWorker = createWorker(BitmapWorker);
			bitmapWorker.post({ file },
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

const calcTargetSize = (imageData: ImageBitmap | HTMLImageElement, options = {}) => {
	let { mode = 'contain', upscale = false, width, height } = options;

	const result = {
		targetWidth: 0,
		targetHeight: 0,
	};

	if (!width && !height)
	{
		return result;
	}

	if (width === null)
	{
		width = height;
	}
	else if (height === null)
	{
		height = width;
	}

	if (mode !== 'force')
	{
		const ratioWidth = width / imageData.width;
		const ratioHeight = height / imageData.height;
		let ratio = 1;

		if (mode === 'cover')
		{
			ratio = Math.max(ratioWidth, ratioHeight);
		}
		else if (mode === 'contain')
		{
			ratio = Math.min(ratioWidth, ratioHeight);
		}

		// if image is too small, exit here with original image
		if (ratio > 1 && upscale === false)
		{
			return result;
		}

		width = imageData.width * ratio;
		height = imageData.height * ratio;
	}

	/*if (mode === 'crop')
	{
		const sourceImageRatio = sourceImageWidth / sourceImageHeight;
		const targetRatio = targetWidth / targetHeight;

		if (sourceImageRatio > targetRatio)
		{
			const newWidth = sourceImageHeight * targetRatio;
			srcX = (sourceImageWidth - newWidth) / 2;
			sourceImageWidth = newWidth;
		}
		else
		{
			const newHeight = sourceImageWidth / targetRatio;
			srcY = (sourceImageHeight - newHeight) / 2;
			sourceImageHeight = newHeight;
		}

		context.drawImage(image, srcX, srcY, sourceImageWidth, sourceImageHeight, 0, 0, targetWidth, targetHeight);
	}*/

	result.targetWidth = Math.round(width);
	result.targetHeight = Math.round(height);

	return result;
};