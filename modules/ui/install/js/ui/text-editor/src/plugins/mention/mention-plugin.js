import { Type, Runtime, Loc, Event, Dom } from 'main.core';
import type { BBCodeElementNode } from 'ui.bbcode.model';

import type {
	BBCodeConversionOutput,
	BBCodeConversion,
	BBCodeImportConversion,
	BBCodeExportConversion,
	BBCodeExportOutput,
} from '../../bbcode';
import { DIALOG_VISIBILITY_COMMAND, HIDE_DIALOG_COMMAND } from '../../commands';
import { $getSelectionPosition } from '../../helpers/get-selection-position';
import Button from '../../toolbar/button';
import type { SchemeValidationOptions } from '../../types/scheme-validation-options';

import BasePlugin from '../base-plugin';
import { $createMentionNode, MentionNode } from './mention-node';

import type { Dialog, DialogOptions } from 'ui.entity-selector';
import type { BaseEvent } from 'main.core.events';
import { type TextEditor } from '../../text-editor';

import {
	$getSelection,
	$isRangeSelection,
	$isTextNode,
	$createTextNode,
	createCommand,
	$createParagraphNode,
	$insertNodes,
	$isRootOrShadowRoot,
	COMMAND_PRIORITY_EDITOR,
	COMMAND_PRIORITY_LOW,
	KEY_ARROW_DOWN_COMMAND,
	KEY_ARROW_UP_COMMAND,
	KEY_ENTER_COMMAND,
	KEY_ESCAPE_COMMAND,
	KEY_TAB_COMMAND,
	KEY_DOWN_COMMAND,
	type RangeSelection,
	type LexicalNode,
	type TextNode,
	type LexicalCommand,
} from 'ui.lexical.core';

import { $wrapNodeInElement, mergeRegister } from 'ui.lexical.utils';

import './mention.css';

export type QueryMatch = {
	leadOffset: number;
	matchingString: string;
	replaceableString: string;
};

const PUNCTUATION = '\\.,\\+\\*\\?\\$\\@\\|#{}\\(\\)\\^\\-\\[\\]\\\\/!%\'"~=<>_:;';
const TRIGGERS = ['@', '+'].join('');

// Chars we expect to see in a mention (non-space, non-punctuation).
const VALID_CHARS = `[^${TRIGGERS}${PUNCTUATION}\\s]`;

// Non-standard series of chars. Each series must be preceded and followed by
// a valid char.
const VALID_JOINS = (
	'(?:'
	+ '\\.[ |$]|' // E.g. "r. " in "Mr. Smith"
	+ ' |' // E.g. " " in "Josh Duck"
	+ `[${PUNCTUATION}]|` // E.g. "-' in "Salier-Hellendag"
	+ ')'
);

const LENGTH_LIMIT = 25;

const mentionRegex = new RegExp(
	'(^|\\s|\\()('
	+ `[${TRIGGERS}]`
	+ `((?:${VALID_CHARS}${VALID_JOINS}){0,${LENGTH_LIMIT}})`
	+ ')$',
);

export type InsertMentionPayload = {
	entityId: string,
	id: string | number,
	text: string,
	before?: string,
	after?: string,
};

export const INSERT_MENTION_COMMAND: LexicalCommand<InsertMentionPayload> = createCommand('INSERT_MENTION_COMMAND');
export const INSERT_MENTION_DIALOG_COMMAND: LexicalCommand<InsertMentionPayload> = createCommand('INSERT_MENTION_DIALOG_COMMAND');

export class MentionPlugin extends BasePlugin
{
	#dialog: Dialog = null;
	#lastQueryMatch: QueryMatch = null;
	#mentionListening: boolean = false;
	#removeKeyboardCommandsLock: Function = null;
	#removeUpdateListener: Function = null;
	#onEditorScroll: Function = this.#handleEditorScroll.bind(this);
	#lastPosition: { left: number, top: number } = null;
	#timeoutId: number = null;
	#triggerByAtSign: boolean = false;

	#dialogOptions: DialogOptions = null;
	#entities: Set<string> = new Set();

	constructor(editor: TextEditor)
	{
		super(editor);

		const entities = editor.getOption('mention.entities', []);
		this.#entities = Type.isArrayFilled(entities) ? new Set(entities) : new Set();

		const dialogOptions = editor.getOption('mention.dialogOptions');
		if (Type.isPlainObject(dialogOptions))
		{
			this.#dialogOptions = dialogOptions;
			if (Type.isArrayFilled(this.#dialogOptions.entities))
			{
				for (const entity of this.#dialogOptions.entities)
				{
					if (Type.isPlainObject(entity) && Type.isStringFilled(entity.id))
					{
						this.#entities.add(entity.id);
					}
				}
			}

			this.#registerKeyDownListener();
		}

		if (this.#entities.size > 0)
		{
			this.#registerCommands();
			this.#registerComponents();
		}
	}

	static getName(): string
	{
		return 'Mention';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode>>
	{
		return [MentionNode];
	}

	importBBCode(): BBCodeImportConversion | null
	{
		if (this.#entities.size > 0)
		{
			const map = {};
			for (const entityId of this.#entities)
			{
				map[entityId] = (): BBCodeConversion => ({
					conversion: this.#convertMentionElement,
					priority: 0,
				});
			}

			return map;
		}

		return null;
	}

	exportBBCode(): BBCodeExportConversion
	{
		return {
			mention: (lexicalNode: MentionNode): BBCodeExportOutput => {
				const scheme = this.getEditor().getBBCodeScheme();

				return {
					node: scheme.createElement({
						name: lexicalNode.getEntityId(),
						value: lexicalNode.getId(),
						inline: true,
					}),
				};
			},
		};
	}

	validateScheme(): SchemeValidationOptions | null
	{
		return {
			nodes: [{
				nodeClass: MentionNode,
			}],
			bbcodeMap: {
				mention: '#mention',
			},
		};
	}

	shouldTriggerByAtSign(): boolean
	{
		return this.#triggerByAtSign;
	}

	#registerCommands(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerCommand(
				INSERT_MENTION_COMMAND,
				(payload: InsertMentionPayload) => {
					if (
						!Type.isPlainObject(payload)
						|| !Type.isStringFilled(payload.entityId)
						|| !Type.isStringFilled(payload.text)
						|| (!Type.isStringFilled(payload.id) && !Type.isNumber(payload.id))
					)
					{
						return false;
					}

					if (!this.#entities.has(payload.entityId))
					{
						console.error(`TextEditor: MentionPlugin: entity id "${payload.entityId}" was not found.`);

						return false;
					}

					const mentionNode = $createMentionNode(payload.entityId, payload.id);
					mentionNode.append($createTextNode(payload.text));

					const nodesToInsert = [];
					if (Type.isStringFilled(payload.before))
					{
						nodesToInsert.push($createTextNode(payload.before));
					}

					nodesToInsert.push(mentionNode);

					if (Type.isStringFilled(payload.after))
					{
						nodesToInsert.push($createTextNode(payload.after));
					}

					$insertNodes(nodesToInsert);
					if ($isRootOrShadowRoot(mentionNode.getParentOrThrow()))
					{
						$wrapNodeInElement(mentionNode, $createParagraphNode).selectEnd();
					}

					return true;
				},
				COMMAND_PRIORITY_EDITOR,
			),
			this.getEditor().registerCommand(
				INSERT_MENTION_DIALOG_COMMAND,
				(payload): boolean => {
					const selection: RangeSelection = $getSelection();
					if (!$isRangeSelection(selection))
					{
						return false;
					}

					this.getEditor().update(
						() => {
							const currentText = this.#getTextUpToAnchor(selection);
							let needSpace = currentText !== null && !/(\s|\()$/.test(currentText);
							if (needSpace)
							{
								const anchor = selection.anchor;
								const anchorNode = anchor.getNode();
								if (anchorNode.getIndexWithinParent() === 0 && anchor.offset === 0)
								{
									needSpace = false;
								}
							}

							selection.insertText(needSpace ? ' @' : '@');
						},
						{
							onUpdate: () => {
								this.getEditor().update(() => {
									const match: null | QueryMatch = this.#getQueryMatch($getSelection());
									if (match !== null && !this.#isSelectionOnEntityBoundary(match.leadOffset))
									{
										this.#openDialog(match);
									}
								});
							},
						},
					);

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				HIDE_DIALOG_COMMAND,
				(payload): boolean => {
					if (!payload || payload.sender !== 'mention')
					{
						this.#hideDialog();
					}

					return false;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				DIALOG_VISIBILITY_COMMAND,
				(): boolean => {
					return this.isDialogVisible();
				},
				COMMAND_PRIORITY_LOW,
			),
		);
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('mention', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --mention"></span>');
			button.setTooltip(Loc.getMessage('TEXT_EDITOR_BTN_MENTION'));
			button.disableInsideUnformatted();
			button.subscribe('onClick', (): void => {
				if (this.isDialogVisible())
				{
					return;
				}

				this.getEditor().focus(() => {
					this.getEditor().dispatchCommand(INSERT_MENTION_DIALOG_COMMAND);
				});
			});

			return button;
		});
	}

	#convertMentionElement(node: BBCodeElementNode): BBCodeConversionOutput
	{
		return {
			node: $createMentionNode(node.getName(), node.getValue()),
		};
	}

	#registerKeyDownListener(): void
	{
		this.#triggerByAtSign = true;

		const keyDownListener = (event: KeyboardEvent) => {
			if (this.#mentionListening)
			{
				if (event.key === 'Escape' || event.key === 'Enter')
				{
					this.#stopMentionListening();
				}
			}
			else if (!this.#mentionListening && (event.key === '+' || event.key === '@'))
			{
				this.#timeoutId = setTimeout((): void => {
					this.getEditor().update((): void => {
						const selection: RangeSelection = $getSelection();
						const match: null | QueryMatch = this.#getQueryMatch(selection);
						if (match !== null && !this.#isSelectionOnEntityBoundary(match.leadOffset))
						{
							this.#openDialog(match);
						}
					});
				}, 300);
			}

			return false;
		};

		this.cleanUpRegister(
			this.getEditor().registerCommand(KEY_DOWN_COMMAND, keyDownListener, COMMAND_PRIORITY_LOW),
		);
	}

	#registerTextContentListener(): void
	{
		this.#unregisterTextContentListener();

		this.#removeUpdateListener = this.getEditor().registerTextContentListener(
			this.#textContentListener.bind(this),
		);
	}

	#unregisterTextContentListener(): void
	{
		if (this.#removeUpdateListener !== null)
		{
			this.#removeUpdateListener();
			this.#removeUpdateListener = null;
		}
	}

	#textContentListener(): void
	{
		this.getEditor().getEditorState().read(() => {
			const selection: RangeSelection = $getSelection();
			const match: null | QueryMatch = this.#getQueryMatch(selection);
			if (match !== null && !this.#isSelectionOnEntityBoundary(match.leadOffset))
			{
				this.#openDialog(match);
			}
			else
			{
				this.#hideDialog();
			}
		});
	}

	#startMentionListening(): void
	{
		this.#mentionListening = true;
		this.#registerTextContentListener();
	}

	#stopMentionListening(): void
	{
		this.#mentionListening = false;
		this.#unregisterTextContentListener();
	}

	#getQueryMatch(selection: RangeSelection, minMatchLength: number = 0): null | QueryMatch
	{
		if (!$isRangeSelection(selection) || !selection.isCollapsed())
		{
			return null;
		}

		const anchor = selection.anchor;
		const anchorNode = anchor.getNode();
		if (!$isTextNode(anchorNode) || !anchorNode.isSimpleText())
		{
			return null;
		}

		const text: string | null = this.#getTextUpToAnchor(selection);

		// console.log("text:", text);

		if (!Type.isStringFilled(text))
		{
			return null;
		}

		return this.#matchMention(text, minMatchLength);
	}

	#getTextUpToAnchor(selection: RangeSelection): string | null
	{
		const anchor = selection.anchor;
		if (anchor.type !== 'text')
		{
			return null;
		}

		const anchorNode = anchor.getNode();
		if (!anchorNode.isSimpleText())
		{
			return null;
		}

		const anchorOffset: number = anchor.offset;

		return anchorNode.getTextContent().slice(0, anchorOffset);
	}

	#isSelectionOnEntityBoundary(offset: number): boolean
	{
		if (offset !== 0)
		{
			return false;
		}

		return this.getEditor().getEditorState().read(() => {
			const selection: RangeSelection = $getSelection();
			if ($isRangeSelection(selection))
			{
				const anchor = selection.anchor;
				const anchorNode = anchor.getNode();
				const prevSibling = anchorNode.getPreviousSibling();

				return $isTextNode(prevSibling) && prevSibling.isTextEntity();
			}

			return false;
		});
	}

	#matchMention(text: string, minMatchLength: number): QueryMatch | null
	{
		const match = mentionRegex.exec(text);
		if (match !== null)
		{
			// The strategy ignores leading whitespace but we need to know it's
			// length to add it to the leadOffset
			const maybeLeadingWhitespace = match[1];

			const matchingString = match[3];
			if (matchingString.length >= minMatchLength)
			{
				return {
					leadOffset: match.index + maybeLeadingWhitespace.length,
					matchingString,
					replaceableString: match[2],
				};
			}
		}

		return null;
	}

	/**
	 * Split Lexical TextNode and return a new TextNode only containing matched text.
	 * Common use cases include: removing the node, replacing with a new node.
	 */
	#splitNodeContainingQuery(match: QueryMatch): TextNode | null
	{
		const selection: RangeSelection = $getSelection();
		if (!$isRangeSelection(selection) || !selection.isCollapsed())
		{
			return null;
		}

		const anchor = selection.anchor;
		if (anchor.type !== 'text')
		{
			return null;
		}

		const anchorNode = anchor.getNode();
		if (!anchorNode.isSimpleText())
		{
			return null;
		}

		const selectionOffset = anchor.offset;
		const textContent = anchorNode.getTextContent().slice(0, selectionOffset);
		const characterOffset: number = match.replaceableString.length;
		const queryOffset: number = this.#getFullMatchOffset(textContent, match.matchingString, characterOffset);

		const startOffset: number = selectionOffset - queryOffset;
		if (startOffset < 0)
		{
			return null;
		}

		let newNode = null;
		if (startOffset === 0)
		{
			[newNode] = anchorNode.splitText(selectionOffset);
		}
		else
		{
			[, newNode] = anchorNode.splitText(startOffset, selectionOffset);
		}

		return newNode;
	}

	/**
 	* Walk backwards along user input and forward through entity title to try
 	* and replace more of the user's text with entity.
 	*/
	#getFullMatchOffset(documentText: string, entryText: string, offset: number): number
	{
		let triggerOffset: number = offset;
		for (let i: number = triggerOffset; i <= entryText.length; i++)
		{
			if (documentText.slice(-i) === entryText.slice(0, Math.max(0, i)))
			{
				triggerOffset = i;
			}
		}

		return triggerOffset;
	}

	#openDialog(queryMatch: QueryMatch): void
	{
		if (this.isDestroyed())
		{
			return;
		}

		this.#lastQueryMatch = queryMatch;
		if (this.#dialog === null)
		{
			const dialogOptions = Type.isPlainObject(this.#dialogOptions) ? { ...this.#dialogOptions } : {};
			const userEvents = dialogOptions.events;

			Runtime.loadExtension('ui.entity-selector').then((exports) => {
				if (this.isDestroyed())
				{
					return;
				}

				const { Dialog } = exports;

				const entitySelectorOptions: DialogOptions = {
					multiple: false,
					enableSearch: false,
					clearSearchOnSelect: true,
					hideOnSelect: true,
					hideByEsc: true,
					autoHide: true,
					height: 300,
					width: 400,
					offsetAnimation: false,
					compactView: true,
					...dialogOptions,
					events: {
						onShow: () => {
							this.#lockKeyboardCommands();
							this.#startMentionListening();
							Event.bind(this.getEditor().getScrollerContainer(), 'scroll', this.#onEditorScroll);
						},
						onHide: () => {
							this.#handleHideOrDestroy();
						},
						onDestroy: () => {
							this.#handleHideOrDestroy();
						},
						'Item:onBeforeSelect': (event: BaseEvent) => {
							const selectedItem = event.getData().item;
							event.preventDefault();

							this.getEditor().update((): void => {
								const nodeToReplace: ?TextNode = this.#splitNodeContainingQuery(this.#lastQueryMatch);
								const mentionNode: MentionNode = $createMentionNode(
									selectedItem.getEntityId(),
									selectedItem.getId(),
								);

								mentionNode.append(
									$createTextNode(selectedItem.getTitle()),
								);

								if (nodeToReplace)
								{
									nodeToReplace.replace(mentionNode);
									mentionNode.select();
								}

								this.#hideDialog();
							});
						},
					},
				};

				this.#dialog = new Dialog(entitySelectorOptions);

				this.getEditor().dispatchCommand(HIDE_DIALOG_COMMAND, { sender: 'mention' });

				this.#dialog.subscribeFromOptions(userEvents);
				this.#dialog.show();
				this.#dialog.search(queryMatch.matchingString);
				this.#adjustDialogPosition();
			})
				.catch((error) => {
					console.error('TextEditor: MentionPlugin: cannot load "ui.entity-selector"', error);
				});
		}
		else
		{
			this.getEditor().dispatchCommand(HIDE_DIALOG_COMMAND, { sender: 'mention' });

			this.#dialog.show();
			this.#dialog.search(queryMatch.matchingString);
			this.#adjustDialogPosition();
		}
	}

	isDialogVisible(): boolean
	{
		return this.#dialog !== null && this.#dialog.isRendered() && this.#dialog.getPopup().isShown();
	}

	#adjustDialogPosition(): void
	{
		this.getEditor().update(() => {
			const selectionPosition = $getSelectionPosition(this.getEditor(), $getSelection(), document.body);
			if (selectionPosition === null)
			{
				return;
			}

			const { top, left, bottom } = selectionPosition;
			const scrollerRect: DOMRect = Dom.getPosition(this.getEditor().getScrollerContainer());
			const popupWidth = 400;

			let offsetLeft = 10;
			if (left - offsetLeft < scrollerRect.left)
			{
				// Left boundary
				const overflow = scrollerRect.left - (left - offsetLeft);
				offsetLeft -= overflow + 16;
			}
			else if (scrollerRect.right < (left + popupWidth - offsetLeft))
			{
				// Right boundary
				offsetLeft += (left + popupWidth - offsetLeft) - scrollerRect.right + 16;
			}

			if (bottom < scrollerRect.top || top > scrollerRect.bottom)
			{
				Dom.addClass(this.#dialog.getPopup().getPopupContainer(), 'ui-text-editor-mention-popup__hidden');
			}
			else
			{
				Dom.removeClass(this.#dialog.getPopup().getPopupContainer(), 'ui-text-editor-mention-popup__hidden');

				this.#dialog.show();
				if (this.#lastPosition === null || this.#lastPosition.top !== bottom)
				{
					this.#lastPosition = { left: left - offsetLeft, top: bottom };
				}

				this.#dialog.getPopup().setBindElement(this.#lastPosition);
				this.#dialog.getPopup().adjustPosition({ forceBindPosition: true, forceTop: true });
			}
		});
	}

	#handleEditorScroll(): void
	{
		this.#adjustDialogPosition();
	}

	#handleHideOrDestroy(): void
	{
		this.#lastPosition = null;
		this.#unlockKeyboardCommands();
		this.#stopMentionListening();
		Event.unbind(this.getEditor().getScrollerContainer(), 'scroll', this.#onEditorScroll);
	}

	#hideDialog(): void
	{
		if (this.#dialog !== null)
		{
			this.#dialog.hide();
		}
	}

	#lockKeyboardCommands(): void
	{
		if (this.#removeKeyboardCommandsLock === null)
		{
			this.#removeKeyboardCommandsLock = mergeRegister(
				this.getEditor().registerCommand(KEY_ARROW_DOWN_COMMAND, (): true => true, COMMAND_PRIORITY_LOW),
				this.getEditor().registerCommand(KEY_ARROW_UP_COMMAND, (): true => true, COMMAND_PRIORITY_LOW),
				this.getEditor().registerCommand(KEY_ESCAPE_COMMAND, (): true => true, COMMAND_PRIORITY_LOW),
				this.getEditor().registerCommand(KEY_TAB_COMMAND, (): true => true, COMMAND_PRIORITY_LOW),
				this.getEditor().registerCommand(KEY_ENTER_COMMAND, (): true => true, COMMAND_PRIORITY_LOW),
			);
		}
	}

	#unlockKeyboardCommands(): void
	{
		if (this.#removeKeyboardCommandsLock !== null)
		{
			this.#removeKeyboardCommandsLock();
			this.#removeKeyboardCommandsLock = null;
		}
	}

	destroy(): void
	{
		super.destroy();

		if (this.#timeoutId !== null)
		{
			clearTimeout(this.#timeoutId);
			this.#timeoutId = null;
		}

		if (this.#dialog !== null)
		{
			this.#dialog.destroy();
		}

		this.#unregisterTextContentListener();
		this.#unlockKeyboardCommands();
	}
}
