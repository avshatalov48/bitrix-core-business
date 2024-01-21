
export type TabsOptionsType = {
	id: ?string,
	items?: Array<TabOptionsType>,
}

export type TabHeadOptionsType = {
	title: string,
	description: ?string,
	className: ?string,
}

export type TabOptionsType = {
	id?: string,
	sort?: number,
	active?: boolean,
	restricted?: boolean,
	bannerCode?: string,
	helpDeskCode?: string,

	head: TabHeadOptionsType | HTMLElement,
	body: string | Function | Promise | HTMLElement,
}