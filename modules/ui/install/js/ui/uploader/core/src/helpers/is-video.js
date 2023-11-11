const isVideo = (blob: Blob) => {
	return /^video\/[\d.a-z-]+$/i.test(blob.type);
};

export default isVideo;
