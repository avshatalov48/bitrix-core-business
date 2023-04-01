const isJpeg = (blob: Blob) => {
	return /^image\/jpeg$/i.test(blob.type);
};

export default isJpeg;