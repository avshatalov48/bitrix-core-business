const isVideo = (blob: Blob) => {
	return /^video\/[a-z0-9.-]+$/i.test(blob.type);
};

export default isVideo;