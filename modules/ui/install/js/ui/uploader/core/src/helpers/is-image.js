const isImage = (file: File) => {
	return /^image\/[a-z0-9.-]+$/i.test(file.type);
};

export default isImage;