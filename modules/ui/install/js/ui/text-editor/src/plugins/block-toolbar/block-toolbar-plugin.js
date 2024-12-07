import { Dom, Event, Tag, Type, Text } from 'main.core';
import { HIDE_DIALOG_COMMAND } from '../../commands';
import BasePlugin from '../base-plugin';
import { type TextEditor } from '../../text-editor';

import {
	COMMAND_PRIORITY_HIGH,
	COMMAND_PRIORITY_LOW,
	DRAGOVER_COMMAND,
	DROP_COMMAND,
	$getNearestNodeFromDOMNode,
	$getNodeByKey,
	$getRoot,
	type RootNode,
} from 'ui.lexical.core';

import { mergeRegister } from 'ui.lexical.utils';

import './block-toolbar.css';

const Direction = {
	DOWNWARD: 1,
	UPWARD: -1,
	INDETERMINATE: 0,
};

const DRAG_DATA_FORMAT = 'application/x-ui-text-editor-drag-block';

export class BlockToolbarPlugin extends BasePlugin
{
	#draggableBlockElement: HTMLElement = null;
	#lastBlockElementIndex: number = Infinity;
	#lastTargetElement: HTMLElement = null;
	#container: HTMLElement = null;
	#dropLine: HTMLElement = null;
	#isDragging = false;

	#bodyDragDropHandler: Function = null;
	#bodyDragOverHandler: Function = null;

	constructor(editor: TextEditor)
	{
		super(editor);

		this.cleanUpRegister(
			this.#registerEvents(),
			this.#registerListeners(),
		);

		this.#bodyDragDropHandler = (event: DragEvent) => {
			this.getEditor().dispatchCommand(DROP_COMMAND, event);
		};

		this.#bodyDragOverHandler = (event: DragEvent) => {
			// prevent default to allow drop
			event.preventDefault();
		};

		Dom.append(this.getContainer(), this.getEditor().getScrollerContainer());
		Dom.append(this.getDropLine(), this.getEditor().getScrollerContainer());
	}

	static getName(): string
	{
		return 'BlockToolbar';
	}

	#registerEvents(): () => void
	{
		const scroller: HTMLElement = this.getEditor().getScrollerContainer();
		const onMouseMove = this.#handleMouseMove.bind(this);
		const onMouseLeave = this.#handleMouseLeave.bind(this);
		Event.bind(scroller, 'mousemove', onMouseMove);
		Event.bind(scroller, 'mouseleave', onMouseLeave);

		return (): void => {
			Event.unbind(scroller, 'mousemove', onMouseMove);
			Event.unbind(scroller, 'mouseleave', onMouseLeave);
		};
	}

	#registerListeners(): () => void
	{
		return mergeRegister(
			this.getEditor().registerCommand(
				DRAGOVER_COMMAND,
				this.#handleDragOver.bind(this),
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				DROP_COMMAND,
				this.#handleDragDrop.bind(this),
				COMMAND_PRIORITY_HIGH,
			),
			this.getEditor().registerTextContentListener((): void => {
				this.#setDraggableBlockElement(null);
				this.#updatePosition();
			}),
		);
	}

	#handleMouseMove(event: MouseEvent): void
	{
		if (!this.getEditor().isEditable())
		{
			return;
		}

		const target = event.target;
		if (!(target instanceof HTMLElement))
		{
			this.#setDraggableBlockElement(null);

			return;
		}

		if (target.closest('.ui-text-editor-block-toolbar') !== null)
		{
			return;
		}

		const element = this.#findBlockElement(event);
		this.#setDraggableBlockElement(element);
	}

	#handleMouseLeave(): void
	{
		this.#setDraggableBlockElement(null);
	}

	#findBlockElement(event): HTMLElement | null
	{
		const scroller = this.getEditor().getScrollerContainer();
		const anchorElementRect = scroller.getBoundingClientRect();
		let blockElem: HTMLElement | null = null;

		this.getEditor().getEditorState().read(() => {
			const root: RootNode = $getRoot();
			const topLevelNodeKeys = root.getChildrenKeys();
			let index: number = this.#getCurrentIndex(topLevelNodeKeys.length);
			let direction: number = Direction.INDETERMINATE;

			while (index >= 0 && index < topLevelNodeKeys.length)
			{
				const key: string = topLevelNodeKeys[index];
				const elem: HTMLElement | null = this.getEditor().getElementByKey(key);
				if (elem === null)
				{
					break;
				}

				const domRect = elem.getBoundingClientRect();
				const { marginLeft, marginRight, marginTop, marginBottom } = window.getComputedStyle(elem);

				const rect = new DOMRect(
					anchorElementRect.left + parseFloat(marginLeft),
					domRect.y - parseFloat(marginTop),
					domRect.width + parseFloat(marginRight),
					domRect.height + parseFloat(marginBottom),
				);

				const { x, y } = event;
				const isOnTopSide = y < rect.top;
				const isOnBottomSide = y > rect.bottom;
				const isOnLeftSide = x < rect.left;
				const isOnRightSide = x > rect.right;
				const contains = !isOnTopSide && !isOnBottomSide && !isOnLeftSide && !isOnRightSide;
				if (contains)
				{
					blockElem = elem;
					this.#lastBlockElementIndex = index;

					break;
				}

				if (direction === Direction.INDETERMINATE)
				{
					if (isOnTopSide)
					{
						direction = Direction.UPWARD;
					}
					else if (isOnBottomSide)
					{
						direction = Direction.DOWNWARD;
					}
					else
					{
						// stop search block element
						direction = Infinity;
					}
				}

				index += direction;
			}
		});

		return blockElem;
	}

	#getCurrentIndex(keysLength: number): number
	{
		if (keysLength === 0)
		{
			return Infinity;
		}

		if (this.#lastBlockElementIndex >= 0 && this.#lastBlockElementIndex < keysLength)
		{
			return this.#lastBlockElementIndex;
		}

		return Math.floor(keysLength / 2);
	}

	#updatePosition(): void
	{
		if (this.#draggableBlockElement === null)
		{
			Dom.style(this.getContainer(), {
				opacity: 0,
				transform: 'translateY(-10000px)',
			});
		}
		else
		{
			// const styles: CSSStyleDeclaration = window.getComputedStyle(this.#draggableBlockElement);
			// const lineHeight: number = Text.toNumber(styles.lineHeight);
			// const toolbarHeight: number = this.getContainer().offsetHeight;
			// const offset = lineHeight > 0 ? (lineHeight - toolbarHeight) / 2 : 3;

			const offset = Text.toNumber(Dom.style(this.#draggableBlockElement, 'margin-top'));
			const top: number = this.#draggableBlockElement.offsetTop + offset;

			Dom.style(this.getContainer(), {
				opacity: 1,
				transform: `translateY(${top}px)`,
			});
		}
	}

	#setDraggableBlockElement(element: HTMLElement | null): void
	{
		const changed = this.#draggableBlockElement !== element;
		this.#draggableBlockElement = element;

		if (changed)
		{
			this.#updatePosition();
		}
	}

	getContainer(): HTMLElement
	{
		if (this.#container === null)
		{
			this.#container = Tag.render`
				<div class="ui-text-editor-block-toolbar">
					<div 
						class="ui-text-editor-block-drag-icon" 
						draggable="true"
						ondragstart="${this.#handleDragStart.bind(this)}" 
						ondragend="${this.#handleDragEnd.bind(this)}"
					>
						<div 
							class="ui-icon-set --more-points" 
							style="--ui-icon-set__icon-size: 24px; margin-left: -4px"
						></div>
					</div>
				</div>
			`;
		}

		return this.#container;
	}

	getDropLine(): HTMLElement
	{
		if (this.#dropLine === null)
		{
			this.#dropLine = Tag.render`
				<div class="ui-text-editor-block-drop-line"></div>
			`;
		}

		return this.#dropLine;
	}

	#handleDragStart(event: DragEvent): void
	{
		const dataTransfer = event.dataTransfer;
		if (!dataTransfer || this.#draggableBlockElement === null)
		{
			return;
		}

		this.getEditor().dispatchCommand(HIDE_DIALOG_COMMAND);

		dataTransfer.setDragImage(this.#draggableBlockElement, 0, 0);

		let nodeKey = '';
		this.getEditor().update(() => {
			const node = $getNearestNodeFromDOMNode(this.#draggableBlockElement);
			if (node)
			{
				nodeKey = node.getKey();
			}
		});

		dataTransfer.setData(DRAG_DATA_FORMAT, nodeKey);
		this.#isDragging = true;

		Event.bind(document.body, 'drop', this.#bodyDragDropHandler);
		Event.bind(document.body, 'dragover', this.#bodyDragOverHandler);
	}

	#handleDragEnd(event: DragEvent): void
	{
		this.#isDragging = false;
		this.#hideDropLine();

		Event.unbind(document.body, 'drop', this.#bodyDragDropHandler);
		Event.unbind(document.body, 'dragover', this.#bodyDragOverHandler);
	}

	#handleDragOver(event: DragEvent): boolean
	{
		if (this.#isDragging === false)
		{
			return false;
		}

		const hasFiles = event.dataTransfer.types.includes('Files');
		if (hasFiles || !(event.target instanceof HTMLElement))
		{
			return false;
		}

		const targetBlockElement = this.#findBlockElement(event);
		if (targetBlockElement === null)
		{
			return false;
		}

		this.#lastTargetElement = targetBlockElement;

		this.#showDropLine(targetBlockElement, event);
		event.preventDefault();

		return true;
	}

	#handleDragDrop(event: DragEvent): boolean
	{
		if (this.#isDragging === false)
		{
			return false;
		}

		const hasFiles = event.dataTransfer.types.includes('Files');
		const dragData = event.dataTransfer?.getData(DRAG_DATA_FORMAT) || '';
		if (hasFiles || !(event.target instanceof HTMLElement) || !Type.isStringFilled(dragData))
		{
			return false;
		}

		const draggedNode = $getNodeByKey(dragData);
		if (!draggedNode || !(event.target instanceof HTMLElement))
		{
			return false;
		}

		const targetBlockElement = this.#findBlockElement(event) || this.#lastTargetElement;
		if (!targetBlockElement)
		{
			return false;
		}

		const targetNode = $getNearestNodeFromDOMNode(targetBlockElement);
		if (!targetNode)
		{
			return false;
		}

		Event.unbind(document.body, 'drop', this.#bodyDragDropHandler);
		Event.unbind(document.body, 'dragover', this.#bodyDragOverHandler);

		if (targetNode === draggedNode)
		{
			return true;
		}

		const { top: targetBlockElemTop, height: targetBlockElemHeight } = targetBlockElement.getBoundingClientRect();
		const shouldInsertAfter = event.clientY - targetBlockElemTop > targetBlockElemHeight / 2;
		if (shouldInsertAfter)
		{
			targetNode.insertAfter(draggedNode);
		}
		else
		{
			// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
			targetNode.insertBefore(draggedNode);
		}

		this.#setDraggableBlockElement(null);

		return true;
	}

	#showDropLine(targetBlockElement: HTMLElement, event: DragEvent)
	{
		const { top: targetBlockElemTop, height: targetBlockElemHeight } = targetBlockElement.getBoundingClientRect();
		const targetStyle: CSSStyleDeclaration = window.getComputedStyle(targetBlockElement);
		const relativePosition: DOMRect = Dom.getRelativePosition(targetBlockElement, targetBlockElement.offsetParent);

		let lineTop: number = relativePosition.top;
		const showAtBottom = event.clientY - targetBlockElemTop > targetBlockElemHeight / 2;
		if (showAtBottom)
		{
			lineTop += targetBlockElemHeight + parseFloat(targetStyle.marginBottom) * 1.5;
		}
		else
		{
			lineTop += parseFloat(targetStyle.marginTop) / 2;
		}

		const DROP_LINE_HALF_HEIGHT = 2;
		const CONTENT_EDITABLE_AREA_PADDING = 16;
		const top: number = lineTop - DROP_LINE_HALF_HEIGHT;

		Dom.style(this.getDropLine(), {
			opacity: 0.4,
			left: `${CONTENT_EDITABLE_AREA_PADDING}px`,
			right: `${CONTENT_EDITABLE_AREA_PADDING}px`,
			transform: `translateY(${top}px)`,
		});
	}

	#hideDropLine(): void
	{
		Dom.style(this.getDropLine(), {
			opacity: 0,
			transform: 'translate(-10000px, -10000px)',
		});
	}

	destroy(): void
	{
		super.destroy();

		Dom.remove(this.getContainer());
		Dom.remove(this.getDropLine());
	}
}
