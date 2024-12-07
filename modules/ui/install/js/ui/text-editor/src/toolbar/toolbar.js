/* eslint-disable no-underscore-dangle */
import { Dom, Tag, Type, Event } from 'main.core';
import { MemoryCache, type BaseCache } from 'main.core.cache';

import 'ui.icon-set.editor';
import { UNFORMATTED } from '../constants';

import {
	SELECTION_CHANGE_COMMAND,
	COMMAND_PRIORITY_CRITICAL,
	BLUR_COMMAND,
	FOCUS_COMMAND,
	$getSelection,
	$isRangeSelection,
	$getRoot,
	type RangeSelection,
	type LexicalNode,
	type ElementNode,
} from 'ui.lexical.core';

import { $findMatchingParent, $getNearestNodeOfType, mergeRegister } from 'ui.lexical.utils';
import { $isListNode, ListNode } from 'ui.lexical.list';
import { $isAutoLinkNode, $isLinkNode } from 'ui.lexical.link';
import { $isCodeTokenNode } from '../plugins/code';

import { type TextEditor } from '../text-editor';
import { TextEditorLexicalNode } from '../types/text-editor-lexical-node';
import type { ToolbarOptions, ToolbarItem } from '../types/toolbar-options';
import Separator from './separator';
import Button from './button';

import './toolbar.css';

export default class Toolbar
{
	#textEditor: TextEditor = null;
	#items: ToolbarItem[] = [];
	#rendered: boolean = false;
	#moreBtn: Button = null;
	#refs: BaseCache<HTMLElement> = new MemoryCache();
	#resizeObserver: ResizeObserver = null;
	#timeoutId: number = null;
	#removeListeners: Function = null;

	constructor(textEditor: TextEditor, options: ToolbarOptions)
	{
		this.#textEditor = textEditor;

		const toolbarOptions: ToolbarOptions = Type.isArray(options) ? options : [];
		this.#fillFromOptions(toolbarOptions);

		if (this.#items.length > 0)
		{
			this.#removeListeners = this.#registerListeners();
			this.#resizeObserver = new ResizeObserver(this.#handleResize.bind(this));
		}
	}

	renderTo(container: HTMLElement): void
	{
		if (this.isRendered())
		{
			return;
		}

		if (Type.isElementNode(container))
		{
			this.#items.forEach((item: ToolbarItem) => {
				Dom.append(item.render(), this.getItemsContainer());
			});

			Dom.append(this.getContainer(), container);

			if (this.#resizeObserver !== null)
			{
				this.#resizeObserver.observe(this.getContainer());
			}

			this.#rendered = true;
		}
	}

	isEmpty(): boolean
	{
		return this.#items.length === 0;
	}

	getContainer(): HTMLElement
	{
		return this.#refs.remember('container', () => {
			return Tag.render`
				<div class="ui-text-editor-toolbar-container">
					${this.getItemsContainer()}
					${this.getMoreBtnContainer()}
				</div>
			`;
		});
	}

	getItemsContainer(): HTMLElement
	{
		return this.#refs.remember('items-container', () => {
			return Tag.render`
				<div class="ui-text-editor-toolbar-items"></div>
			`;
		});
	}

	getMoreBtnContainer(): HTMLElement
	{
		return this.#refs.remember('more-btn-container', () => {
			return Tag.render`
				<div class="ui-text-editor-toolbar-more-btn">
				${this.getMoreBtn().render()}
				</div>
			`;
		});
	}

	getMoreBtn(): Button
	{
		if (this.#moreBtn === null)
		{
			const resetAnimation = () => {
				Event.unbind(this.getItemsContainer(), 'transitionend', resetAnimation);
				Dom.style(this.getItemsContainer(), { height: null });
				Dom.removeClass(this.getItemsContainer(), '--animating');
			};

			this.#moreBtn = new Button();
			this.#moreBtn.setContent('<span class="ui-text-editor-toolbar-more-btn-icon"></span>');
			this.#moreBtn.subscribe('onClick', (): void => {
				Event.unbind(this.getItemsContainer(), 'transitionend', resetAnimation);

				if (Dom.hasClass(this.getContainer(), '--expanded'))
				{
					Dom.style(this.getItemsContainer(), { height: `${this.getItemsContainer().scrollHeight}px` });
					requestAnimationFrame(() => {
						Dom.removeClass(this.getContainer(), '--expanded');
						Dom.addClass(this.getItemsContainer(), '--animating');
						Dom.style(this.getItemsContainer(), { height: null });
					});
				}
				else
				{
					Dom.addClass(this.getItemsContainer(), '--animating');
					Dom.style(this.getItemsContainer(), { height: `${this.getItemsContainer().scrollHeight}px` });
					Dom.addClass(this.getContainer(), '--expanded');
				}

				Event.bind(this.getItemsContainer(), 'transitionend', resetAnimation);
			});
		}

		return this.#moreBtn;
	}

	getItems(): ToolbarItem[]
	{
		return this.#items;
	}

	isRendered(): boolean
	{
		return this.#rendered;
	}

	destroy(): boolean
	{
		if (this.#removeListeners !== null)
		{
			this.#removeListeners();
		}

		if (this.#resizeObserver !== null)
		{
			this.#resizeObserver.disconnect();
			this.#resizeObserver = null;
		}

		if (this.isRendered())
		{
			Dom.remove(this.getContainer());
		}

		if (this.#timeoutId)
		{
			clearTimeout(this.#timeoutId);
		}

		this.#items = null;
		this.#refs = null;
	}

	#registerListeners(): () => {}
	{
		return mergeRegister(
			this.#textEditor.registerCommand(
				SELECTION_CHANGE_COMMAND,
				() => {
					this.update();

					return false;
				},
				COMMAND_PRIORITY_CRITICAL,
			),
			this.#textEditor.registerCommand(
				FOCUS_COMMAND,
				(): boolean => {
					if (this.#timeoutId)
					{
						clearTimeout(this.#timeoutId);
						this.#timeoutId = null;
					}

					return false;
				},
				COMMAND_PRIORITY_CRITICAL,
			),
			this.#textEditor.registerCommand(
				BLUR_COMMAND,
				(): boolean => {
					if (this.#timeoutId)
					{
						clearTimeout(this.#timeoutId);
					}

					this.#timeoutId = setTimeout((): void => {
						const activeElement = document.activeElement;
						const rootElement = this.#textEditor.getScrollerContainer();
						if (activeElement === null || !rootElement.contains(activeElement))
						{
							this.reset();
						}
					}, 400);

					return false;
				},
				COMMAND_PRIORITY_CRITICAL,
			),
			this.#textEditor.registerUpdateListener(() => {
				this.update();
			}),
			this.#textEditor.registerEditableListener(() => {
				this.update();
			}),
		);
	}

	#fillFromOptions(options: ToolbarOptions)
	{
		options.forEach((item: ToolbarItem) => {
			if (item === '|')
			{
				this.#items.push(new Separator());
			}
			else
			{
				const component = this.#textEditor.getComponentRegistry().create(item);
				if (component === null)
				{
					// eslint-disable-next-line no-console
					console.warn(`TextEditor Toolbar: "${item}" component doesn't exist.`);
				}
				else
				{
					this.#items.push(component);
				}
			}
		});
	}

	#handleResize(entries: ResizeObserverEntry[])
	{
		if (this.getContainer().offsetWidth === 0 || Dom.hasClass(this.getItemsContainer(), '--animating'))
		{
			return;
		}

		const lastItem: ?ToolbarItem = this.#items.at(-1);
		if (!lastItem || lastItem.getContainer().offsetTop >= lastItem.getContainer().offsetHeight)
		{
			Dom.addClass(this.getContainer(), '--overflowed');
		}
		else
		{
			Dom.removeClass(this.getContainer(), ['--overflowed', '--expanded']);
		}
	}

	update(): void
	{
		this.#textEditor.getEditorState().read((): void => {
			let selection: RangeSelection = $getSelection();
			if (!$isRangeSelection(selection))
			{
				selection = null;
			}

			let unformattedNode = null;
			if (selection !== null)
			{
				unformattedNode = $findMatchingParent(
					selection.anchor.getNode(),
					(node: TextEditorLexicalNode): boolean => {
						return (node.__flags & UNFORMATTED) !== 0;
					},
				);
			}

			const blockTypes: Set<string> = selection === null ? new Set() : this.#getSelectionBlockTypes(selection);
			const isReadOnly: boolean = !this.#textEditor.isEditable();

			this.#items.forEach((item: Button) => {
				if (!(item instanceof Button))
				{
					return;
				}

				// First let's figure out a disabled status
				if (item.hasOwnDisableCallback())
				{
					item.setDisabled(item.invokeDisableCallback());
				}
				else if (isReadOnly)
				{
					item.disable();
				}
				else if (unformattedNode !== null && item.shouldDisableInsideUnformatted())
				{
					item.disable();
				}
				else
				{
					item.enable();
				}

				// Now set an active status
				if (item.isDisabled())
				{
					item.setActive(false);
				}
				else if (item.hasFormat())
				{
					const format = item.getFormat();
					item.setActive(selection === null ? false : selection.hasFormat(format));
				}
				else if (item.getBlockType() !== null)
				{
					item.setActive(blockTypes.has(item.getBlockType()));
				}
			});
		});
	}

	reset(): void
	{
		this.#items.forEach((item: Button) => {
			if (item instanceof Button)
			{
				item.setActive(false);
			}
		});
	}

	#getSelectionBlockTypes(selection: RangeSelection): Set<string>
	{
		const anchorNode = selection.anchor.getNode();
		const blockTypes = new Set();
		let currentNode: ElementNode | LexicalNode | null = anchorNode;
		while (currentNode !== $getRoot() && currentNode !== null)
		{
			const blockType = this.#getBlockType(currentNode);
			blockTypes.add(blockType);
			currentNode = currentNode.getParent();
		}

		return blockTypes;
	}

	#getBlockType(node: LexicalNode): string | null
	{
		if ($isListNode(node))
		{
			const listNode: ListNode = node;
			const parentList = $getNearestNodeOfType(listNode, ListNode);

			return parentList ? parentList.getListType() : listNode.getListType();
		}

		if ($isLinkNode(node) || $isAutoLinkNode(node))
		{
			return 'link';
		}

		if ($isCodeTokenNode(node))
		{
			return 'code';
		}

		return node.getType();
	}
}
