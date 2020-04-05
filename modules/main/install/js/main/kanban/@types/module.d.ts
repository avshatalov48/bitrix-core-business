declare module 'main.kanban'
{
	namespace Kanban
	{
		class Pagination
		{
			constructor(column: Column);
			init();
			adjust();
			loadItems();
			onPromiseFulfilled(result);
			onPromiseRejected(reason);
			processPromiseResult(result);
			getColumn(): Column;
			getPage(): number;
			getTopButton(): HTMLElement;
			getBottomButton(): HTMLElement;
			getLoader(): HTMLElement;
			showLoader();
			hideLoader();
			scrollUp();
			scrollDown();
			stopScroll();
		}

		class DropZone
		{
			static DEFAULT_COLOR: string;

			constructor(options: {
				id: string | number,
				name?: string,
				color?: string,
				data?: object,
			});
			getId(): string | number;
			setOptions(options: {
				name?: string,
				color?: string,
				data?: object,
			});
			setName(name: string);
			getName(): string | null;
			setColor(color: string);
			getColor(): string;
			getData(): object;
			setData(): object;
			getGridData(): object;
			setDropZoneArea(area: DropZoneArea);
			getDropZoneArea(): DropZoneArea;
			getGrid(): Grid;
			makeDroppable();
			setActive();
			unsetActive();
			setCaptured();
			unsetCaptured();
			onDragEnter(itemNode: HTMLElement, z, y);
			onDragEnter(onDragLeave: HTMLElement, z, y);
			onDragEnter(onDragDrop: HTMLElement, z, y);
			onDragEnter(item: Item);
			animateRemove(itemNode: HTMLElement);
			restore();
			empty();
			getContainer(): HTMLElement;
			getNameContainer(): HTMLElement;
			getCancelLink(): HTMLElement;
			handleCancelClick(event);
			getBgContainer(): HTMLElement;
			render(): HTMLElement;
		}

		class DropZoneEvent
		{
			constructor(options: object);
			allowAction();
			denyAction();
			isActionAllowed();
			setItem(item: Item);
			getItem(): Item;
			setDropZone(dropZone: DropZone);
			getDropZone(): DropZone;
		}

		class DropZoneArea
		{
			constructor(grid: Grid, options: object);
			getGrid(): Grid;
			addDropZone(options: object): DropZone | null;
			updateDropZone(dropZone: DropZone, options: object): boolean;
			removeDropZone(zoneId: string | number): Item;
			render(): HTMLElement;
			getDropZone(propZone: DropZone | string | number): DropZone | null;
			getDropZones(): Array<DropZone>;
			getDropZonesCount(): number;
			getDropZoneType(className: string): DropZone;
			getDropZoneTimeout(): number;
			getContainer(): HTMLElement;
			emptyAll();
			show();
			hide();
			setActive();
			unsetActive();
		}

		class DragMode
		{
			static NONE: number;
			static ITEM: number;
			static COLUMN: number;
		}

		class DragEvent
		{
			allowAction();
			denyAction();
			isActionAllowed(): boolean;
			setItem(item: Item);
			getItem(): Item;
			setTargetItem(item: Item);
			getTargetItem(): Item;
			setTargetColumn(column: Column);
			getTargetColumn(): Column;
		}

		class Item
		{
			constructor(options: {
				id: string | number,
				data: object,
			});
			getId(): string | number;
			getColumnId(): string | number;
			setColumnId(id: string | number);
			getColumn(): Column | null;
			setGrid(grid: Grid);
			getGrid(): Grid;
			setOptions(options: {
				droppable?: boolean,
				draggable?: boolean,
				countable?: boolean,
				visible?: boolean,
				data?: object,
			});
			getData(): object;
			setData(data: object);
			isCountable(): boolean;
			isVisible(): boolean;
			getGridData(): object;
			renderLayout(): HTMLElement;
			getContainer(): HTMLElement;
			getDragTarget(): HTMLElement;
			getDragElement(): HTMLElement;
			getBodyContainer(): HTMLElement;
			render(): HTMLElement;
			dispose();
			makeDraggable();
			makeDroppable();
			disableDragging();
			enableDragging();
			disableDropping();
			isDraggable(): boolean;
			isDroppable(): boolean;
			onDragStart();
			onDragStop(x: number, y: number);
			onDrag(x: number, y: number);
			onDragEnter(itemNode: HTMLElement, x: number, y: number);
			onDragLeave(itemNode: HTMLElement, x: number, y: number);
			onDragDrop(itemNode: HTMLElement, x: number, y: number);
			onItemDragEnd(itemNode: HTMLElement, x: number, y: number);
			showDragTarget(height: number);
			hideDragTarget();
		}

		class DraftItem extends Item
		{
			render(): HTMLElement;
			setGrid(grid: Grid);
			getDraftTextArea(): HTMLTextAreaElement;
			applyDraftEditMode();
			onItemAddedFulfilled(result);
			onItemAddedRejected(error);
			removeDraftItem();
			focusDraftTextArea();
			handleDraftTextAreaBlur();
			handleDraftTextAreaKeyDown(event: KeyboardEvent);
		}

		class Grid
		{
			constructor(options: {
				renderTo: HTMLElement,
				columns?: Array<Column>,
				items?: Array<Item>,
				dropzones?: Array<DropZone>,
				events?: {[key: string]: (event) => void},
				itemType?: string,
				columnType?: string,
				canAddColumn?: boolean,
				canEditColumn?: boolean,
				canSortColumn?: boolean,
				canRemoveColumn?: boolean,
				canAddItem?: boolean,
				canSortItem?: boolean,
				dropZoneType?: string,
				dropZoneTimeout?: number,
				bgColor?: string,
				data?: {[key: string]: any},
				messages?: {[key: string]: any},
			});
			addColumn(options: object): Column | null;
			removeColumn(column: Column | string | number): boolean;
			updateColumn(column: Column | string | number, options: object);
			getNextColumnSibling(column: Column): Column | null;
			getPreviousColumnSibling(column: Column): Column | null;
			addItem(options: {
				id: string | number,
				columnId: string | number,
				type: string,
				targetId: string | number,
			}): Item | null
			removeItem(itemId: Item | string | number): Item | null;
			removeColumnItems(column: Column | string | number);
			removeItems();
			updateItem(item: Item | string | number, options: object);
			hideItem(item: Item | string | number): boolean;
			unhideItem(item: Item | string | number): boolean;
			getColumn(column: Column | string | number): Column | null;
			getColumns(): Array<Column>;
			getColumnsCount(): number;
			getColumnIndex(column: Column | string | number): number;
			getItem(item: Item | string | number): Item | null;
			getItemByElement(itemNode: HTMLElement): Item | null;
			getItems(): {[key: string]: Item}
			getItemType(className: string): Item;
			getColumnType(className: string): Column;
			getDropZoneArea(): DropZoneArea;
			getData(): object;
			setData(data: object);
			getBgColor(): string;
			getBgColorStyle(): string;
			getOptions(): object;
			loadData(json: {
				columns: Array<Column>,
				items: Array<Item>,
				dropZones: Array<DropZone>,
			});
			draw();
			renderLayout();
			isRendered();
			setRenderStatus();
			getLeftEar(): HTMLElement;
			getRightEar(): HTMLElement;
			getRenderToContainer(): HTMLElement;
			getOuterContainer(): HTMLElement;
			getInnerContainer(): HTMLElement;
			getGridContainer(): HTMLElement;
			getEmptyStub(): HTMLElement;
			getLoader(): HTMLElement;
			adjustLayout();
			adjustEars();
			adjustWidth();
			adjustHeight();
			adjustEmptyStub();
			moveItem(item, targetColumn, beforeItem);
			moveColumn(column: Column | string | number, targetColumn: Column | string | number): boolean;
			canAddColumns(): boolean;
			canEditColumns(): boolean;
			canSortColumns(): boolean;
			canRemoveColumns(): boolean;
			canAddItems(): boolean;
			canSortItems(): boolean;
			scrollToRight(): boolean;
			scrollToLeft(): boolean;
			stopAutoScroll(): boolean;
			getDragMode(): DragMode;
			getDragModeCode(code: string): any;
			setDragMode(mode: DragMode);
			resetDragMode(mode: DragMode);
			onItemDragStart(item: Item);
			onItemDragStop(item: Item);
			onColumnDragStart(column: Column);
			onColumnDragStop(column: Column);
			getEventPromise(eventName: string, eventArgs: Array<any>, onFulfilled, onRejected): Promise<any>;
			fadeOut();
			fadeIn();
			getMessage(messageId: string): string;
		}

		class Column
		{
			constructor(options: {
				id: string | number,
				name?: string,
				color?: string,
				data?: {[key: string]: any},
				total?: number,
			});
			getId(): string | number;
			setOptions(options: {
				name?: string,
				color?: string,
				data?: {[key: string]: any},
				total?: number,
			});
			setColor(color: string);
			getColor(): string;
			setGrid(grid: Grid);
			getGrid(): Grid;
			getPaginations(): Pagination | null;
			addItem(item: Item, beforeItem: Item);
			getItems(): Array<Item>;
			getItemsCount(): number;
			getFirstItem(onlyVisible: boolean): Item | null;
			getLastItem(onlyVisible: boolean): Item | null;
			getNextItemSibling(currentItem: Item | string | number, onlyVisible: boolean): Item | null;
			getPreviousItemSibling(currentItem: Item | string | number, onlyVisible: boolean): Item | null;
			removeItem(item: Item): boolean;
			removeItems();
			setName(name: string);
			getName(): string;
			getData(): {[key: string]: any} | null;
			setData(data: {[key: string]: any});
			getGridData(): {[key: string]: any};
			isEditable(): boolean;
			isSortable(): boolean;
			isRemovable(): boolean;
			canAddItems(): boolean;
			getTotal(): number;
			incrementTotal(value: number);
			decrementTotal(value: number);
			freezeTotal();
			unfreezeTotal();
			setTotal(total: number);
			refreshTotal();
			hasLoading(): boolean;
			getIndex(): number;
			render(): HTMLElement;
			renderTitle(): HTMLElement;
			getDefaultTitleLayout(): HTMLElement;
			getColumnTitle(): HTMLElement;
			getTotalItem(): HTMLElement;
			getEditButton(): HTMLElement;
			getCustomTitleButtons(): null;
			getRemoveButton(): HTMLElement;
			getEditForm(): HTMLElement;
			getTitleTextBox(): HTMLElement;
			getFillColorButton(): HTMLElement;
			switchToEditMode();
			applyEditMode();
			handleTextBoxBlur(event: any);
			stopTextBoxBlur();
			focusTextBox();
			handleTextBoxKeyDown(event: any);
			handleRemoveButtonClick(event: any);
			showColorPicker();
			getColorPicker(): any;
			onColorSelected(color: string): any;
			getConfirmDialog(): any;
			handleConfirmButtonClick();
			showRemoveConfirmDialog();
			handleAddColumnButtonClick(event: any);
			renderSubTitle(): HTMLElement;
			handleAddItemButtonClick();
			getDraftItem(): DraftItem;
			addDraftItem(item: DraftItem): DraftItem| null;
			removeDraftItem();
			getContainer(): HTMLElement;
			getHeader(): HTMLElement;
			getBody(): HTMLElement;
			getTitleContainer(): HTMLElement;
			getSubTitle(): HTMLElement;
			getAddColumnButton(): HTMLElement;
			getItemsContainer(): HTMLElement;
			getDragTarget(): HTMLElement;
			blockPageScroll(event: any);
			makeDraggable();
			makeDroppable();
			disableDragging();
			enableDragging();
			disableDropping();
			enableDropping();
			isDraggable(): boolean;
			isDroppable(): boolean;
			onDragEnter(item: HTMLElement, x, y): boolean;
			onDragLeave(item: HTMLElement, x, y): boolean;
			onDragDrop(item: HTMLElement, x, y): boolean;
			onItemDragEnd(item: HTMLElement, x, y): boolean;
			onColumnDragStart();
			onColumnDrag(x, y);
			onColumnDragStop(x, y);
			getRectArea(): ClientRect;
			resetRectArea();
			showDragTarget(height: number);
			hideDragTarget();
		}
	}
}