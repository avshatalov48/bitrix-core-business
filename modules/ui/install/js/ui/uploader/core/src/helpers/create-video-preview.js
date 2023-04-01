import { Event } from 'main.core';
import getResizedImageSize from './get-resized-image-size';
import type { ResizeImageOptions } from '../types/resize-image-options';
import convertCanvasToBlob from './convert-canvas-to-blob';
import createImagePreview from './create-image-preview';

const createVideoPreview = (
	blob: Blob,
	options: ResizeImageOptions = { width: 300, height: 3000 },
	seekTime: number = 10
): Promise => {
	return new Promise((resolve, reject) => {
		const video: HTMLVideoElement = document.createElement('video');
		video.setAttribute('src', URL.createObjectURL(blob));
		video.load();

		Event.bind(video, 'error', (error) => {
			reject('Error while loading video file', error);
		});

		Event.bind(video, 'loadedmetadata', () => {
			if (video.duration < seekTime)
			{
				seekTime = 0;
			}

			video.currentTime = seekTime;

			Event.bind(video, 'seeked', () => {
				const imageData = { width: video.videoWidth, height: video.videoHeight };
				const { targetWidth, targetHeight } = getResizedImageSize(imageData, options);
				if (!targetWidth || !targetHeight)
				{
					reject();
					return;
				}

				const canvas = createImagePreview(video, targetWidth, targetHeight);
				const { quality = 0.92, mimeType = 'image/jpeg' } = options;
				convertCanvasToBlob(canvas, mimeType, quality)
					.then((blob: Blob) => {
						resolve({
							preview: blob,
							width: targetWidth,
							height: targetHeight,
						});
					})
					.catch(() => {
						reject();
					})
				;
			});
		});
	});
};

export default createVideoPreview;