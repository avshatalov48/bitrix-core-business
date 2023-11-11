const isImage = (blob: Blob): boolean => {
	return /^image\/[\d.a-z-]+$/i.test(blob.type);
};

export default isImage;
