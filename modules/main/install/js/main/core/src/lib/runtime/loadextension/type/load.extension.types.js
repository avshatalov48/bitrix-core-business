export type ExtensionOptions = {
	extension: string,
	html: ?string,
	config: {[key: string]: string},
};

export type JsItem = {
	isInternal: boolean,
	JS: string,
};

export type Response = {
	status: string,
	errors: Array<any>,
	data: Array<any>,
};

export type State = 'scheduled' | 'load' | 'loaded' | 'error';