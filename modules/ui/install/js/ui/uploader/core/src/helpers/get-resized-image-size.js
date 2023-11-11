import type { ResizeImageOptions } from '../types/resize-image-options';

type ImageData = ImageBitmap | HTMLImageElement | { width: number, height: number };
type ResizedImageSizeResult = { targetWidth: number, targetHeight: number, useOriginalSize: boolean };

const getResizedImageSize = (imageData: ImageData, options: ResizeImageOptions): ResizedImageSizeResult => {
	const { mode = 'contain', upscale = false } = options;
	let { width, height } = options;

	if (!width && !height)
	{
		return {
			targetWidth: 0,
			targetHeight: 0,
			useOriginalSize: true,
		};
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
			return {
				targetWidth: imageData.width,
				targetHeight: imageData.height,
				useOriginalSize: true,
			};
		}

		width = imageData.width * ratio;
		height = imageData.height * ratio;
	}

	/*
	if (mode === 'crop')
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
	}
	*/

	return {
		targetWidth: Math.round(width),
		targetHeight: Math.round(height),
		useOriginalSize: false,
	};
};

export default getResizedImageSize;
