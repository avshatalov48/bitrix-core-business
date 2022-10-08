const loadImage = (file: File | Blob) => new Promise((resolve, reject) => {

	const image = document.createElement('img');
	const url = URL.createObjectURL(file);
	image.src = url;

	image.onerror = error => {
		URL.revokeObjectURL(image.src);
		reject(error);
	};

	image.onload = () => {
		URL.revokeObjectURL(url);
		resolve({
			width: image.naturalWidth,
			height: image.naturalHeight,
			image
		});
	};
});

export default loadImage;