import {Event} from 'main.core';

type Preview = {
	blob?: Blob,
	width?: number,
	height?: number
};

export const PreviewManager = {
	get(file: File): Promise<Preview>
	{
		return new Promise((resolve, reject) =>
		{
			if (file instanceof File)
			{
				if (file.type.startsWith('video'))
				{
					PreviewManager.getVideoPreviewBlob(file)
						.then(blob => PreviewManager.getImageDimensions(blob))
						.then(result => resolve(result))
						.catch(error => reject(error));
				}
				else if (file.type.startsWith('image'))
				{
					const blob = new Blob([file], {type: file.type});
					PreviewManager.getImageDimensions(blob)
						.then(result => resolve(result))
						.catch(error => reject(error));
				}
				else
				{
					resolve({});
				}
			}
			else
			{
				reject(new Error('Parameter "file" is not instance of "File"'));
			}
		});
	},

	getImageDimensions(fileBlob: Blob): Promise<Preview>
	{
		return new Promise((resolve, reject) => {
			if (!fileBlob)
			{
				reject(new Error('getImageDimensions: fileBlob can\'t be empty'));
			}

			const image: HTMLImageElement = new Image();
			Event.bind(image, 'load', () => {
				resolve({
					blob: fileBlob,
					width: image.width,
					height: image.height
				});
			});

			Event.bind(image, 'error', () => {
				reject();
			});

			image.src = URL.createObjectURL(fileBlob);
		});
	},

	getVideoPreviewBlob(blob: Blob, seekTime: number = 10): Promise<Blob>
	{
		return new Promise((resolve, reject) => {
			const video: HTMLVideoElement = document.createElement('video');
			video.setAttribute('src', URL.createObjectURL(blob));
			video.load();

			Event.bind(video, 'error', (error) => {
				reject(new Error(`Error while loading video file: ${error}`));
			});

			Event.bind(video, 'loadedmetadata', () => {
				if (video.duration < seekTime)
				{
					seekTime = 0;
				}

				video.currentTime = seekTime;

				Event.bind(video, 'seeked', () => {
					const canvas = document.createElement('canvas');
					canvas.width = video.videoWidth;
					canvas.height = video.videoHeight;
					const context = canvas.getContext('2d');
					context.drawImage(video, 0, 0, canvas.width, canvas.height);
					context.canvas.toBlob(
						resultBlob => resolve(resultBlob),
						'image/jpeg',
						1
					);
				});
			});
		});
	},
};