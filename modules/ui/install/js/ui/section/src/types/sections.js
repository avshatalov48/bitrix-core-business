export type SectionParams = {
	title?: string,
	titleIconClasses?: string,
	iconArrowDown?: string,
	iconArrowTop?: string,
	iconArrowRight?: string,
	bodyActive?: string,
	isOpen?: boolean,
	canCollapse?: boolean,
	id?: string,
	isEnable?: boolean,
	bannerCode?: string,
	singleLink: ?{
		href?: string,
		isSidePanel?: boolean,
	},
}