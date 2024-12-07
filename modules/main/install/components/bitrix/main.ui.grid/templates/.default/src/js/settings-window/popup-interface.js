export interface PopupInterface {
	sortItems(): void;
	reset(): void;
	show(): void;
	close(): void;
	createPopup(): void;
	getSelectedColumns(): void;
	getStickedColumns(): string[];
	isForAll(): boolean;
	getPopupItems(): HTMLCollection;
	getItems(): [];
	select(id: string, value: boolean): void;
	saveColumns(columns: [], callback: Function): void;
}
