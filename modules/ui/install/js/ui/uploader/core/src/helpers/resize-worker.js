/* eslint-disable no-new-func */

const ResizeWorker = (): void => {
	self.onmessage = (event: MessageEvent): void => {
		// Hack for Safari. Workers can become unpredictable.
		// Sometimes 'self.postMessage' doesn't emit 'onmessage' event.
		setTimeout((): void => {
			const {
				file,
				options = {},
				getResizedImageSizeSource,
				createImagePreviewCanvasSource,
				sharpenSource,
				shouldSharpenSource,
				/* type, */
			} = event.data.message;

			createImageBitmap(file)
				.then((bitmap: ImageBitmap) => {
					const getResizedImageSize = new Function(`return ${getResizedImageSizeSource}`)();
					const { targetWidth, targetHeight, useOriginalSize } = getResizedImageSize(bitmap, options);

					if (useOriginalSize)
					{
						bitmap.close();

						self.postMessage({
							id: event?.data?.id,
							message: {
								useOriginalSize,
								targetWidth,
								targetHeight,
							},
						}, []);
					}
					else
					{
						const createImagePreviewCanvas = new Function(`return ${createImagePreviewCanvasSource}`)();
						let offscreenCanvas: OffscreenCanvas = createImagePreviewCanvas(bitmap, targetWidth, targetHeight);

						const sharpen = new Function(`return ${sharpenSource}`)();
						const shouldSharpen = new Function(`return ${shouldSharpenSource}`)();
						if (shouldSharpen(bitmap, targetWidth, targetHeight))
						{
							sharpen(offscreenCanvas, targetWidth, targetHeight, 0.2);
						}

						bitmap.close();

						const previewBitmap = offscreenCanvas.transferToImageBitmap();

						offscreenCanvas.width = 0;
						offscreenCanvas.height = 0;
						offscreenCanvas = null;

						self.postMessage({
							id: event?.data?.id,
							message: {
								bitmap: previewBitmap,
								useOriginalSize,
								targetWidth,
								targetHeight,
							},
						}, [previewBitmap]);

						// const { quality = 0.92 } = options;
						// offscreenCanvas.convertToBlob({ quality, type })
						// 	.then((blob: Blob): void => {
						// 		self.postMessage({
						// 			id: event?.data?.id,
						// 			message: {
						// 				blob,
						// 				useOriginalSize,
						// 				targetWidth,
						// 				targetHeight,
						// 			},
						// 		}, []);
						// 	})
						// 	.catch((error): void => {
						// 		console.log('Resize Worker Error (convertToBlob)', error);
						// 		self.postMessage({
						// 			id: event.data.id,
						// 			message: null,
						// 		}, []);
						// 	})
						// ;
					}
				})
				.catch((error): void => {
					// eslint-disable-next-line no-console
					console.log('Uploader: Resize Worker Error (createImageBitmap)', error);
					self.postMessage({
						id: event.data.id,
						message: null,
					}, []);
				})
			;
		}, 0);
	};
};

export default ResizeWorker;
