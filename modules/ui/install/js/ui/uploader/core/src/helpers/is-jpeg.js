const isJpeg = (file: File) => {
	return /^image\/jpeg$/i.test(file.type);
};

export default isJpeg;