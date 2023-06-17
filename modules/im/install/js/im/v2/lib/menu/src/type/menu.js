export type MenuItem = {
	text?: string,
	href?: string,
	html?: string,
	title?: string,
	disabled?: boolean,
	delimiter?: boolean,
	onclick?: () => {} | string,
	items?: MenuItem[]
};