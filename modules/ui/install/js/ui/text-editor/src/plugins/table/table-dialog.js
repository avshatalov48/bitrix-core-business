import { Dom, Tag, Type } from 'main.core';
import { type BaseCache, MemoryCache } from 'main.core.cache';
import { type BaseEvent, EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import './table-dialog.css';

export type TableDialogOptions = {
	targetNode?: HTMLElement,
	events?: Object<string, (event: BaseEvent) => {}>,
}

export default class TableDialog extends EventEmitter
{
	#popup: Popup = null;
	#targetNode: HTMLElement = null;
	#refs: BaseCache<HTMLElement> = new MemoryCache();
	#lastSelectedBox: HTMLElement = null;

	constructor(dialogOptions)
	{
		super();
		this.setEventNamespace('BX.UI.TextEditor.TableDialog');

		const options: TableDialogOptions = Type.isPlainObject(dialogOptions) ? dialogOptions : {};

		this.setTargetNode(options.targetNode);
		this.subscribeFromOptions(options.events);
	}

	show(): void
	{
		this.getPopup().adjustPosition({ forceBindPosition: true });
		this.getPopup().show();
	}

	hide(): void
	{
		this.getPopup().close();
	}

	isShown(): boolean
	{
		return this.#popup !== null && this.#popup.isShown();
	}

	destroy(): void
	{
		this.getPopup().destroy();
	}

	setTargetNode(container: HTMLElement): void
	{
		if (Type.isElementNode(container))
		{
			this.#targetNode = container;
		}
	}

	getTargetNode(): HTMLElement | null
	{
		return this.#targetNode;
	}

	getPopup(): Popup
	{
		if (this.#popup === null)
		{
			const targetNode = this.getTargetNode();
			const rect = targetNode.getBoundingClientRect();
			const targetNodeWidth = rect.width;

			this.#popup = new Popup({
				autoHide: true,
				closeByEsc: true,
				padding: 0,
				content: Tag.render`
					<div class="ui-text-editor-table-dialog" onclick="${this.#handleClick.bind(this)}">
						${this.getGridContainer()}
						${this.getCaptionContainer()}
					</div>
				`,
				bindElement: this.getTargetNode(),
				events: {
					onClose: () => {
						this.emit('onClose');
					},
					onDestroy: () => {
						this.emit('onDestroy');
					},
					onShow: (event) => {
						const popup = event.getTarget();
						const popupWidth = popup.getPopupContainer().offsetWidth;
						const offsetLeft = (targetNodeWidth / 2) - (popupWidth / 2);
						const angleShift = Popup.getOption('angleLeftOffset') - Popup.getOption('angleMinTop');

						popup.setAngle({ offset: popupWidth / 2 - angleShift });
						popup.setOffset({ offsetLeft: offsetLeft + Popup.getOption('angleLeftOffset') });

						this.#lastSelectedBox = null;
						this.#highlightBoxes(1, 1);
					},
				},
			});
		}

		return this.#popup;
	}

	getGridContainer(): HTMLElement
	{
		return this.#refs.remember('grid', () => {
			const buttons = [];
			for (let index = 0; index < 100; index++)
			{
				const row = Math.floor(index / 10);
				const column = index % 10;

				buttons.push(Tag.render`
					<button
						class="ui-text-editor-table-dialog-box"
						data-column="${column + 1}"
						data-row="${row + 1}"
					></button>
				`);
			}

			return Tag.render`
				<div 
					class="ui-text-editor-table-dialog-grid" 
					onmousemove="${this.#handleMouseMove.bind(this)}"
				>${buttons}</div>
			`;
		});
	}

	getCaptionContainer(): HTMLElement
	{
		return this.#refs.remember('caption', () => {
			return Tag.render`<div class="ui-text-editor-table-dialog-caption"></div>`;
		});
	}

	#handleMouseMove(event: MouseEvent)
	{
		if (this.#lastSelectedBox !== event.target && Dom.hasClass(event.target, 'ui-text-editor-table-dialog-box'))
		{
			const { row, column } = event.target.dataset;
			this.#highlightBoxes(row, column);
			this.#lastSelectedBox = event.target;
		}
	}

	#handleClick(event: MouseEvent)
	{
		if (this.#lastSelectedBox)
		{
			const { row, column } = this.#lastSelectedBox.dataset;
			this.emit('onSelect', { rows: row, columns: column });
		}
	}

	#highlightBoxes(rows: number, columns: number)
	{
		let index = 0;
		for (const box of this.getGridContainer().children)
		{
			const boxRow = Math.floor(index / 10);
			const boxColumn = index % 10;
			const selected = boxRow < rows && boxColumn < columns;
			if (selected)
			{
				Dom.addClass(box, '--selected');
			}
			else
			{
				Dom.removeClass(box, '--selected');
			}

			index++;
		}

		this.getCaptionContainer().textContent = rows && columns ? `${rows} x ${columns}` : '';
	}
}
