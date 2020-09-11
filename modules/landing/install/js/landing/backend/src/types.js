export type Site = {
	ACTIVE: 'N' | 'Y',
	CODE: string,
	CREATED_BY_ID: string,
	DATE_CREATE: string,
	DATE_MODIFY: string,
	DELETED: 'N' | 'Y',
	DESCRIPTION: ?string,
	DOMAIN_ID: string,
	ID: string,
	LANDING_ID_404: ?string,
	LANDING_ID_503: ?string,
	LANDING_ID_INDEX: ?string,
	LANG: ?string,
	MODIFIED_BY_ID: ?string,
	SMN_SITE_ID: ?string,
	TITLE: string,
	TPL_ID: string,
	TYPE: 'STORE' | 'PAGE',
	XML_ID: string,
};

export type Landing = {
	ACTIVE: 'N' | 'Y',
	CODE: string,
	CREATED_BY_ID: string,
	DATE_CREATE: string,
	DATE_MODIFY: string,
	DATE_PUBLIC: ?string,
	DELETED: 'N' | 'Y',
	DESCRIPTION: ?string,
	FOLDER: 'N' | 'Y',
	FOLDER_ID: ?string,
	ID: string,
	INITIATOR_APP_CODE: ?string,
	IS_AREA: boolean,
	MODIFIED_BY_ID: ?string,
	PREVIEW: string,
	PUBLIC: 'N' | 'Y',
	RULE: ?string,
	SITEMAP: 'N' | 'Y',
	SITE_ID: string,
	TITLE: string,
	TPL_CODE: string,
	TPL_ID: ?string,
	XML_ID: string,
};

export type Block = {
	active: boolean,
	code: string,
	content: string,
	css: Array<string>,
	id: number,
	js: Array<string>,
	lid: string,
	meta: {[key: string]: any},
	name: string,
}

export type Template = {
	ACTIVE: boolean,
	APP_CODE: string,
	AVAILABLE: boolean,
	DATA: {[key: string]: any},
	DESCRIPTION: string,
	ID: string,
	PREVIEW: string,
	PREVIEW2X: string,
	PREVIEW3X: string,
	PUBLICATION: boolean,
	REST: number,
	SECTION: Array<string>,
	SINGLETON: boolean,
	SORT: number,
	TITLE: string,
	TYPE: "PAGE" | "STORE",
	XML_ID: string,
};

export type CreatePageOptions = {
	title: string,
	siteId?: number,
	code?: string,
};

export type SourceResponse = {
	result: any,
	type: "success" | "error",
	sessid: string,
};

export type PreparedResponse = {
	result: any,
	type: "success" | "error",
	sessid: string,
	status: number,
	authorized: boolean,
};