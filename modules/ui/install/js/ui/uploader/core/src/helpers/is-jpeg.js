const isJpeg = (blob: Blob): boolean => {
	return /^image\/jpeg$/i.test(blob.type);
};

export default isJpeg;
