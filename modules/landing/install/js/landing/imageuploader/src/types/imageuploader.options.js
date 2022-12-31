type ImageUploaderOptions = {
	uploadParams: {[key: string]: any},
	additionalParams: {[key: string]: any},
	dimensions: {
		width?: number,
		height?: number,
		maxWidth?: number,
		maxHeight?: number,
		minWidth?: number,
		minHeight?: number,
	},
	sizes: Array<string>,
	allowSvg: boolean,
};

export default ImageUploaderOptions;