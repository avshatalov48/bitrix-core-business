const createImagePreview = (data, width: number, height: number) => {
	width = Math.round(width);
	height = Math.round(height);

	const canvas = document.createElement('canvas');
	canvas.width = width;
	canvas.height = height;

	const context = canvas.getContext('2d');
	// context.imageSmoothingQuality = 'high';
	context.drawImage(data, 0, 0, width, height);

	return canvas;
};

export default createImagePreview;