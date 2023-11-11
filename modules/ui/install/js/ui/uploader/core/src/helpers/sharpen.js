export const sharpen = (
	canvas: HTMLCanvasElement | OffscreenCanvas,
	width: number,
	height: number,
	mixFactor: number,
): void => {
	const context: CanvasRenderingContext2D = canvas.getContext('2d');
	const weights: number[] = [0, -1, 0, -1, 5, -1, 0, -1, 0];
	const katet: number = Math.round(Math.sqrt(weights.length));
	const half: number = Math.trunc(katet * 0.5);
	const destinationData: ImageData = context.createImageData(width, height);
	const destinationBuffer: Uint8ClampedArray = destinationData.data;
	const sourceBuffer: Uint8ClampedArray = context.getImageData(0, 0, width, height).data;
	let y: number = height;
	while (y--)
	{
		let x: number = width;
		while (x--)
		{
			const sy: number = y;
			const sx: number = x;
			const dstOff: number = (y * width + x) * 4;
			let red = 0;
			let green = 0;
			let blue = 0;
			// let alpha = 0;

			for (let cy = 0; cy < katet; cy++)
			{
				for (let cx = 0; cx < katet; cx++)
				{
					const scy: number = sy + cy - half;
					const scx: number = sx + cx - half;
					if (scy >= 0 && scy < height && scx >= 0 && scx < width)
					{
						const srcOff: number = (scy * width + scx) * 4;
						const wt: number = weights[cy * katet + cx];
						red += sourceBuffer[srcOff] * wt;
						green += sourceBuffer[srcOff + 1] * wt;
						blue += sourceBuffer[srcOff + 2] * wt;
						// alpha += sourceBuffer[srcOff + 3] * wt;
					}
				}
			}

			destinationBuffer[dstOff] = red * mixFactor + sourceBuffer[dstOff] * (1 - mixFactor);
			destinationBuffer[dstOff + 1] = green * mixFactor + sourceBuffer[dstOff + 1] * (1 - mixFactor);
			destinationBuffer[dstOff + 2] = blue * mixFactor + sourceBuffer[dstOff + 2] * (1 - mixFactor);
			destinationBuffer[dstOff + 3] = sourceBuffer[dstOff + 3];
		}
	}

	context.putImageData(destinationData, 0, 0);
};

export const shouldSharpen = (imageData: CanvasImageSource, width: number, height: number): boolean => {
	const scaleX: number = width / imageData.width;
	const scaleY: number = height / imageData.height;

	const scale: number = Math.min(scaleX, scaleY);

	// if target scale is less than half
	return scale < 0.5;
};
