// We can't use createImageBitmap due to the bug in Chrome (https://bugs.chromium.org/p/chromium/issues/detail?id=1220671).
// Chrome doesn't respect JPEG Orientation.

// Use 'bitmaprenderer' and transferFromImageBitmap to make a blob from the ImageBitmap;
/*
	const canvas = document.createElement('canvas');
	canvas.width = bitmap.width;
	canvas.height = bitmap.height;
	const ctx = canvas.getContext('bitmaprenderer');
	if (ctx)
	{
		ctx.transferFromImageBitmap(bitmap);
	}
	else
	{
		// twice in memory...
		canvas.getContext('2d').drawImage(bitmap, 0, 0);
	}
*/

export const ResizeWorkerOld = function() {
	self.onmessage = (event: MessageEvent) => {
		const { file, options = {}, calcTargetSizeFn } = event.data.message;
		self.createImageBitmap(file)
			.then((bitmap: ImageBitmap) => {
				// eslint-disable-next-line no-new-func
				const calcTargetSize = new Function(`return ${calcTargetSizeFn.toString()}`)();
				const { targetWidth, targetHeight } = calcTargetSize(bitmap, options);
				const resizeOptions = { resizeWidth: targetWidth, resizeHeight: targetHeight };

				self.createImageBitmap(bitmap, resizeOptions).then((previewBitmap: ImageBitmap) => {
					bitmap.close();
					self.postMessage({
						id: event.data.id,
						message: {
							bitmap: previewBitmap,
							targetWidth,
							targetHeight,
						},
					}, [previewBitmap]);
				}).catch((error) => {
					// eslint-disable-next-line no-console
					console.warn('worker error', error);
					self.postMessage({ id: event.data.id, message: null });
				});

				/*
				const canvas = new OffscreenCanvas(targetWidth, targetHeight);
				const context = canvas.getContext('2d');
				context.imageSmoothingQuality = 'high';
				context.drawImage(bitmap, 0, 0, targetWidth, targetHeight);
				const previewBitmap = canvas.transferToImageBitmap();
				*/
			})
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.warn('worker error', error);
				self.postMessage({ id: event.data.id, message: null });
			})
		;
	};
};
