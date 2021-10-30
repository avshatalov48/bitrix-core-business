interface BlockManifest
{
	app_code: boolean;
	description: string;
	name: string;
	namespace: string;
	new: boolean;
	preview: string;
	repo_id: boolean | number;
	requires_updates: boolean;
	restricted: boolean;
	section: Array<string>;
	type: Array<string>;
	version: string;
}

interface CategoryManifest
{
	app_code: boolean;
	items: {[code: string]: BlockManifest};
	name: string;
	new: boolean;
	separator: boolean;
}

interface ReferenceManifest
{
	id: string,
	type: string,
	name: string,
	actions: Array<{type: string, name: string}>,
}

interface SourceManifest
{
	id: string,
	default: {[key: string]: string},
	name: string,
	references: Array<ReferenceManifest>,
	settings: {[settingsKey: string]: any},
	sort: Array<{id: string, name: string}>,
	url: {[urlId: string]: string},
}

interface StyleManifest
{
	name: string,
	property: string,
	type: string,
	items: {name: string, value: string},
}

interface EnvOptions
{
	blocks: {[category: string]: CategoryManifest},
	features: {[feature]: boolean},
	folder_id: number,
	help: {[helpId: string]: string},
	hooks: {[hookId: string]: Array<any>},
	lastModified: number,
	pages_count: number,
	params: {
		draftMode: boolean,
		editor: {
			externalUrlTarget: '_self' | '_blank' | '_top',
		},
		sef_url: {[maskId: string]: string},
		type: 'KNOWLEDGE' | 'SITES'
	},
	placements: {[category: string]: Array<any>},
	productionType: string,
	rights: Array<string>,
	server_name: string,
	site_id: number,
	sites_count: number | string,
	sources: Array<SourceManifest>,
	style: {
		[namespace: string]: {
			group: {
				[groupId: string]: Array<string>,
			},
			style: {
				[styleId: string]: Array<StyleManifest>,
			}
		}
	},
	syspages: {[pageId: string]: {landing_id: number, name: string}},
	url: string,
	version: string,
	xml_id: string,
	default_section: string,
	specialType: string,
	design_block: string,
	design_block_allowed: boolean,
	mainOptions: {
		saveOriginalFileName: boolean,
	},
}

export {
	EnvOptions,
	BlockManifest,
	CategoryManifest,
	ReferenceManifest,
	SourceManifest,
	StyleManifest,
};
