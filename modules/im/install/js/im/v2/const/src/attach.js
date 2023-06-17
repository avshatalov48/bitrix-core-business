export const AttachType = Object.freeze({
	Delimiter: 'DELIMITER',
	File: 'FILE',
	Grid: 'GRID',
	Html: 'HTML',
	Image: 'IMAGE',
	Link: 'LINK',
	Message: 'MESSAGE',
	Rich: 'RICH_LINK',
	User: 'USER'
});

export const AttachDescription = Object.freeze({
	FIRST_MESSAGE: 'FIRST_MESSAGE',
	SKIP_MESSAGE: 'SKIP_MESSAGE',
});


export type AttachConfig = {
	ID: number,
	DESCRIPTION: string,
	COLOR: string,
	BLOCKS: AttachConfigBlock[]
};

export type AttachConfigBlock = {
	[blockName: string]: Object
};

// message
export type AttachMessageConfig = {
	MESSAGE: string
};

// delimiter
export type AttachDelimiterConfig = {
	DELIMITER: {
		SIZE?: number,
		COLOR?: string
	}
};

// file
export type AttachFileConfig = {
	FILE: AttachFileItemConfig[]
};

export type AttachFileItemConfig = {
	LINK: string,
	NAME?: string,
	SIZE?: number
};

// grid
export type AttachGridConfig = {
	GRID: AttachGridItemConfig[]
};

export type AttachGridItemConfig = {
	DISPLAY: string, // AttachGridItemDisplayType
	NAME: string,
	VALUE: string,
	WIDTH?: number,
	COLOR?: string,
	LINK?: string
};

// html
export type AttachHtmlConfig = {
	HTML: string
};

// image
export type AttachImageConfig = {
	IMAGE: AttachImageItemConfig[]
};

export type AttachImageItemConfig = {
	LINK: string,
	WIDTH?: number,
	HEIGHT?: number,
	NAME?: string,
	PREVIEW?: string
};

// link
export type AttachLinkConfig = {
	LINK: AttachLinkItemConfig[]
};

export type AttachLinkItemConfig = {
	LINK: string,
	NAME?: string,
	DESC?: string,
	HTML?: string,
	PREVIEW?: string,
	WIDTH?: number,
	HEIGHT?: number
};

// rich
export type AttachRichConfig = {
	RICH_LINK: AttachRichItemConfig[]
};

export type AttachRichItemConfig = {
	LINK: string,
	NAME?: string,
	DESC?: string,
	HTML?: string,
	PREVIEW?: string
};

// user
export type AttachUserConfig = {
	USER: AttachRichItemConfig[]
};

export type AttachUserItemConfig = {
	NAME: string,
	AVATAR: string,
	AVATAR_TYPE: string,
	LINK: string
};