export type ElementListRestResult = {
	backgroundResult: BackgroundListRestResult,
	maskResult: MaskListRestResult
};

export type BackgroundListRestResult = {
	backgrounds: {
		default: BackgroundRestResult[],
		custom: BackgroundRestResult[]
	},
	infoHelperParams: {
		availableDomainList: string[],
		demoStatus: string,
		frameUrlTemplate: string,
		trialableFutureList: string[]
	},
	limits: LimitRestResult[],
	upload: {
		folderId: number
	}
};

export type BackgroundRestResult = {
	id: string,
	background: string,
	isSupported: boolean,
	isVideo: boolean,
	preview: string,
	title: string
};

export type LimitRestResult = {
	id: string,
	articleCode: string,
	active: boolean
};

export type MaskListRestResult = {
	masks: MaskRestResult[]
};

export type MaskRestResult = {
	active: boolean,
	id: string,
	mask: string,
	background: string,
	preview: string,
	title: string
};