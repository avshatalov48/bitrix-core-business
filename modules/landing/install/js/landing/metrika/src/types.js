export type AnalyticsOptions = {
	category: string,
	event: string,
	c_section?: string,
	c_sub_section?: string,
	c_element?: string,
	type?: string,
	params: {[key: string]: string|number},
};
