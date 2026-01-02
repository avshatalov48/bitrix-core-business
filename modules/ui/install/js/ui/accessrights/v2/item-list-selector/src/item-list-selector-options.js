export type Item = {
	id: string;
	title: string;
	selected: boolean;
	sort: number
}

export type ItemListSelectorOptions = {
	maxSelected: number,
	targetNode: HTMLElement,
	title: string,
	subtitle: string,
	items: Array<Item>,
	events: { [key: string]: (event: Event) => void },
};
