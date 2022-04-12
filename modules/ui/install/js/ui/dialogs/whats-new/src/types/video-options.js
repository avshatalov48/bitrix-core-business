export type VideoSourceOptions = {
	type: string,
	src: string,
};

export type VideoOptions = {
	attrs?: {[key: string]: any},
	sources: VideoSourceOptions[],
};