const isImage = (blob: Blob) => {
	return /^image\/[a-z0-9.-]+$/i.test(blob.type);
};

export default isImage;