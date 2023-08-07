const createImagePreviewCanvas = (imageSource: CanvasImageSource, width: number, height: number): HTMLCanvasElement | OffscreenCanvas => {
	width = Math.round(width);
	height = Math.round(height);

	const isPageContext: boolean =
		typeof(window) !== 'undefined'
		&& typeof(document) !== 'undefined'
		&& typeof(parent) !== 'undefined'
	;

	const createCanvas = (width: number, height: number): HTMLCanvasElement | OffscreenCanvas => {
		if (isPageContext)
		{
			const canvas: HTMLCanvasElement = document.createElement('canvas');
			canvas.width = width;
			canvas.height = height;

			return canvas;
		}
		else
		{
			return new OffscreenCanvas(width, height);
		}
	};

	if (imageSource.height <= height && imageSource.width <= width)
	{
		const canvas: HTMLCanvasElement = createCanvas(width, height);
		const context: CanvasRenderingContext2D = canvas.getContext('2d');
		context.imageSmoothingQuality = 'high';
		context.drawImage(imageSource, 0, 0, width, height);

		return canvas;
	}
	else
	{
		if (imageSource.height > imageSource.width)
		{
			width = Math.floor(height * (imageSource.width / imageSource.height));
		}
		else
		{
			height = Math.floor(width * (imageSource.height / imageSource.width));
		}

		let currentImageWidth: number = Math.floor(imageSource.width);
		let currentImageHeight: number = Math.floor(imageSource.height);
		let currentImageSource: CanvasImageSource = imageSource;
		let resizingCanvas: HTMLCanvasElement = null;

		while (currentImageWidth * 0.5 > width)
		{
			const halfImageWidth: number = Math.floor(currentImageWidth * 0.5);
			const halfImageHeight: number = Math.floor(currentImageHeight * 0.5);

			resizingCanvas = createCanvas(halfImageWidth, halfImageHeight);
			const resizingCanvasContext: CanvasRenderingContext2D = resizingCanvas.getContext('2d');
			resizingCanvasContext.imageSmoothingQuality = 'high';

			resizingCanvasContext.drawImage(
				currentImageSource,
				0,
				0,
				currentImageWidth,
				currentImageHeight,
				0,
				0,
				halfImageWidth,
				halfImageHeight
			);

			currentImageWidth = halfImageWidth;
			currentImageHeight = halfImageHeight;
			currentImageSource = resizingCanvas;
		}

		const outputCanvas: HTMLCanvasElement = createCanvas(width, height);
		const outputCanvasContext: CanvasRenderingContext2D = outputCanvas.getContext('2d');
		outputCanvasContext.imageSmoothingQuality = 'high';
		outputCanvasContext.drawImage(
			resizingCanvas === null ? imageSource : resizingCanvas,
			0,
			0,
			currentImageWidth,
			currentImageHeight,
			0,
			0,
			width,
			height
		);

		if (resizingCanvas)
		{
			resizingCanvas.width = 0;
			resizingCanvas.height = 0;
			resizingCanvas = null;

			currentImageSource.width = 0;
			currentImageSource.height = 0;
			currentImageSource = null;
		}

		return outputCanvas;
	}
};

export default createImagePreviewCanvas;
