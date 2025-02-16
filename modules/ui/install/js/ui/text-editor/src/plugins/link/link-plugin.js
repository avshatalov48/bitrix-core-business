/* eslint-disable no-underscore-dangle */
import { Browser, Event, Loc, Type, Validation } from 'main.core';

import type { BBCodeElementNode } from 'ui.bbcode.model';
import type { BaseEvent } from 'main.core.events';
import type {
	BBCodeConversion,
	BBCodeConversionFn,
	BBCodeExportConversion,
	BBCodeExportOutput,
	BBCodeImportConversion,
} from '../../bbcode';

import { DIALOG_VISIBILITY_COMMAND, HIDE_DIALOG_COMMAND } from '../../commands';
import { UNFORMATTED } from '../../constants';
import { $adjustDialogPosition } from '../../helpers/adjust-dialog-position';
import { getSelectedNode } from '../../helpers/get-selected-node';

import Button from '../../toolbar/button';
import type { SchemeValidationOptions } from '../../types/scheme-validation-options';
import { TextEditorLexicalNode } from '../../types/text-editor-lexical-node';
import BasePlugin from '../base-plugin';
import { LinkEditor } from './link-editor';

import { sanitizeUrl } from '../../helpers/sanitize-url';
import { validateUrl } from '../../helpers/validate-url';

import {
	COMMAND_PRIORITY_LOW,
	COMMAND_PRIORITY_NORMAL,
	PASTE_COMMAND,
	KEY_MODIFIER_COMMAND,
	$isTextNode,
	$isElementNode,
	$getSelection,
	$setSelection,
	$isRangeSelection,
	$insertNodes,
	$isRootOrShadowRoot,
	$createParagraphNode,
	$createTextNode,
	$getNodeByKey,
	createCommand,
	type LexicalNode,
	type RangeSelection,
	type NodeKey,
	type LexicalCommand,
} from 'ui.lexical.core';

import { $wrapNodeInElement, $findMatchingParent, mergeRegister } from 'ui.lexical.utils';

import {
	LinkNode,
	TOGGLE_LINK_COMMAND,
	$toggleLink,
	$createLinkNode,
	$isLinkNode,
	$isAutoLinkNode,
	type LinkAttributes,
	type AutoLinkNode,
} from 'ui.lexical.link';

import { type TextEditor } from '../../text-editor';

export const INSERT_LINK_DIALOG_COMMAND: LexicalCommand<string> = createCommand('INSERT_LINK_DIALOG_COMMAND');

export class LinkPlugin extends BasePlugin
{
	#linkEditor: LinkEditor = null;
	#onEditorScroll: Function = this.#handleEditorScroll.bind(this);
	#lastSelection: RangeSelection = null;

	constructor(editor: TextEditor)
	{
		super(editor);

		this.#registerCommands();
		this.#registerListeners();
		this.#registerComponents();
	}

	static getName(): string
	{
		return 'Link';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode>>
	{
		return [LinkNode];
	}

	importBBCode(): BBCodeImportConversion
	{
		return {
			url: (): BBCodeConversion => ({
				conversion: (node: BBCodeElementNode): BBCodeConversionFn | null => {
					// [url]{url}[/url]
					// [url={url}]{text}[/url]
					let url = node.getValue();
					if (!validateUrl(url))
					{
						url = node.toPlainText();
						if (!validateUrl(url))
						{
							return { node: null };
						}
					}

					return {
						node: $createLinkNode(sanitizeUrl(url), { target: '_blank' }),
					};
				},
				priority: 0,
			}),
		};
	}

	exportBBCode(): BBCodeExportConversion
	{
		return {
			link: (lexicalNode: LinkNode): BBCodeExportOutput => {
				const url = lexicalNode.getURL();
				const children = lexicalNode.getChildren();
				const isSimpleText = (
					children.length === 1
					&& $isTextNode(children[0])
					&& children[0].getFormat() === 0
				);

				const scheme = this.getEditor().getBBCodeScheme();
				if (isSimpleText && children[0].getTextContent() === url)
				{
					return {
						node: scheme.createElement({ name: 'url' }),
					};
				}

				return {
					node: scheme.createElement({ name: 'url', value: url }),
				};
			},
		};
	}

	validateScheme(): SchemeValidationOptions | null
	{
		return {
			nodes: [{
				nodeClass: LinkNode,
			}],
			bbcodeMap: {
				link: 'url',
			},
		};
	}

	#registerListeners(): void
	{
		this.cleanUpRegister(
			this.getEditor().registerEventListener(LinkNode, 'click', (event: Event, nodeKey: NodeKey) => {
				const linkNode: LinkNode = $getNodeByKey(nodeKey);
				if ($isLinkNode(linkNode))
				{
					this.getEditor().dispatchCommand(INSERT_LINK_DIALOG_COMMAND, linkNode);
				}
			}),
		);
	}

	#registerCommands(): void
	{
		this.cleanUpRegister(
			this.#registerToggleLinkCommand(),
			this.#registerInsertLinkCommand(),
			this.#registerKeyModifierCommand(),
			this.#registerPasteCommand(),
		);
	}

	#registerToggleLinkCommand(): () => void
	{
		return this.getEditor().registerCommand(
			TOGGLE_LINK_COMMAND,
			(payload): boolean => {
				if (payload === null)
				{
					$toggleLink(payload);

					return true;
				}

				const selection: RangeSelection = $getSelection();
				if (!$isRangeSelection(selection))
				{
					return false;
				}

				let url = null;
				let originalUrl = null;

				let attributes = {};
				if (Type.isStringFilled(payload))
				{
					url = payload;
				}
				else if (Type.isPlainObject(payload))
				{
					const { target, rel, title } = payload;
					attributes = { rel, target, title };
					url = payload.url;
					originalUrl = payload.originalUrl || null;
				}

				if (Type.isStringFilled(url))
				{
					if (!Type.isStringFilled(attributes.target))
					{
						attributes.target = '_blank';
					}

					if (validateUrl(url))
					{
						if (selection.isCollapsed() && !this.#isLinkSelected(selection))
						{
							this.#insertLink(selection, url, attributes, originalUrl);
						}
						else
						{
							$toggleLink(url, attributes);
						}

						return true;
					}

					return false;
				}

				return false;
			},
			COMMAND_PRIORITY_LOW,
		);
	}

	#registerInsertLinkCommand(): () => void
	{
		return mergeRegister(
			this.getEditor().registerCommand(
				INSERT_LINK_DIALOG_COMMAND,
				(payload): boolean => {
					const selection: RangeSelection = $getSelection();
					if (!$isRangeSelection(selection) || !this.getEditor().isEditable())
					{
						return false;
					}

					this.#lastSelection = selection.clone();
					if (this.#linkEditor !== null)
					{
						this.#linkEditor.destroy();
					}

					let lineNode = null;
					let linkUrl = null;

					if ($isLinkNode(payload))
					{
						lineNode = payload;
						linkUrl = lineNode.getURL();
					}
					else
					{
						const $isUnformatted = $findMatchingParent(
							selection.anchor.getNode(),
							(node: TextEditorLexicalNode) => {
								return (node.__flags & UNFORMATTED) !== 0;
							},
						);

						if ($isUnformatted)
						{
							return false;
						}

						const node = getSelectedNode(selection);
						const linkParent = $findMatchingParent(node, $isLinkNode);

						if (linkParent)
						{
							lineNode = linkParent;
							linkUrl = lineNode.getURL();
							lineNode.select();
						}
						else if ($isLinkNode(node))
						{
							lineNode = node;
							linkUrl = lineNode.getURL();
							lineNode.select();
						}
					}

					this.getEditor().dispatchCommand(HIDE_DIALOG_COMMAND);

					this.#linkEditor = new LinkEditor({
						linkUrl,
						autoLinkMode: $isAutoLinkNode(lineNode),
						// for an embedded popup: document.body -> this.getEditor().getScrollerContainer()
						targetContainer: document.body,
						events: {
							onSave: (event: BaseEvent) => {
								const linkEditor: LinkEditor = event.getTarget();
								let url = linkEditor.getLinkUrl();
								if (!Type.isStringFilled(url))
								{
									linkEditor.hide();

									return;
								}

								const protocol = Validation.isEmail(url) ? 'mailto:' : 'https://';
								const originalUrl = url;
								if (!validateUrl(url))
								{
									url = `${protocol}${url}`;
									linkEditor.setLinkUrl(url);
								}

								if (lineNode === null)
								{
									this.getEditor().update(() => {
										this.#restoreSelection();

										this.getEditor().dispatchCommand(TOGGLE_LINK_COMMAND, { url, originalUrl, rel: null });
										linkEditor.setEditMode(false);

										const currentSelection: RangeSelection = $getSelection();
										if ($isRangeSelection(currentSelection))
										{
											this.#lastSelection = currentSelection.clone();
										}

										if (!$isRangeSelection(currentSelection) || currentSelection.isCollapsed())
										{
											linkEditor.hide();
										}

										this.#convertAutoLinkToLink(currentSelection);
									});
								}
								else
								{
									this.getEditor().update(() => {
										lineNode.setURL(url);
										this.#convertAutoLinkToLink($getSelection());
										linkEditor.setAutoLinkMode(false);
									});

									linkEditor.setEditMode(false);
								}

								this.getEditor().resetHighlightSelection();
							},
							onCancel: (event: BaseEvent) => {
								const linkEditor: LinkEditor = event.getTarget();
								linkEditor.hide();
							},
							onUnlink: (event: BaseEvent) => {
								if (lineNode === null)
								{
									this.getEditor().dispatchCommand(TOGGLE_LINK_COMMAND, null);
								}
								else
								{
									this.getEditor().update(() => {
										const children = lineNode.getChildren();
										for (const child of children)
										{
											// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
											lineNode.insertBefore(child);
										}

										lineNode.remove();
									});
								}

								const linkEditor: LinkEditor = event.getTarget();
								linkEditor.hide();
							},
							onShow: () => {
								if ($adjustDialogPosition(this.#linkEditor.getPopup(), this.getEditor()))
								{
									Event.bind(this.getEditor().getScrollerContainer(), 'scroll', this.#onEditorScroll);
									this.getEditor().highlightSelection();
								}
							},
							onClose: () => {
								this.#handleDialogDestroy();
							},
							onDestroy: () => {
								this.#handleDialogDestroy();
							},
						},
					});

					this.#linkEditor.show();

					return true;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				HIDE_DIALOG_COMMAND,
				(): boolean => {
					if (this.#linkEditor !== null)
					{
						this.#linkEditor.destroy();
					}

					return false;
				},
				COMMAND_PRIORITY_LOW,
			),
			this.getEditor().registerCommand(
				DIALOG_VISIBILITY_COMMAND,
				(): boolean => {
					return this.#linkEditor !== null && this.#linkEditor.isShown();
				},
				COMMAND_PRIORITY_LOW,
			),
		);
	}

	#restoreSelection(): boolean
	{
		const selection = $getSelection();
		if (!$isRangeSelection(selection) && this.#lastSelection !== null)
		{
			$setSelection(this.#lastSelection);
			this.#lastSelection = null;

			return true;
		}

		return false;
	}

	#handleDialogDestroy(): void
	{
		this.#linkEditor = null;
		Event.unbind(this.getEditor().getScrollerContainer(), 'scroll', this.#onEditorScroll);
		this.getEditor().resetHighlightSelection();

		this.getEditor().update(() => {
			if (!this.#restoreSelection())
			{
				this.getEditor().focus();
			}
		});
	}

	#handleEditorScroll(): void
	{
		this.getEditor().update(() => {
			$adjustDialogPosition(this.#linkEditor.getPopup(), this.getEditor());
		});
	}

	#registerKeyModifierCommand(): () => void
	{
		return this.getEditor().registerCommand(
			KEY_MODIFIER_COMMAND,
			(payload) => {
				const event: KeyboardEvent = payload;
				const { code, ctrlKey, metaKey } = event;
				if (code === 'KeyK' && (ctrlKey || metaKey))
				{
					event.preventDefault();
					this.getEditor().dispatchCommand(INSERT_LINK_DIALOG_COMMAND);

					return true;
				}

				return false;
			},
			COMMAND_PRIORITY_NORMAL,
		);
	}

	#registerPasteCommand(): () => void
	{
		return this.getEditor().registerCommand(
			PASTE_COMMAND,
			(event) => {
				const selection: RangeSelection = $getSelection();
				if (
					!$isRangeSelection(selection)
					|| selection.isCollapsed()
					|| !(event instanceof ClipboardEvent)
					|| event.clipboardData === null
				)
				{
					return false;
				}

				const clipboardText = event.clipboardData.getData('text');
				if (!validateUrl(clipboardText))
				{
					return false;
				}

				// If we select nodes that are elements then avoid applying the link.
				if (!selection.getNodes().some((node) => $isElementNode(node)))
				{
					$toggleLink(clipboardText);
					event.preventDefault();

					return true;
				}

				return false;
			},
			COMMAND_PRIORITY_NORMAL,
		);
	}

	#insertLink(selection: RangeSelection, url: string, attributes?: LinkAttributes, originalUrl?: string): void
	{
		const linkUrl = sanitizeUrl(url);
		const linkNode = $createLinkNode(linkUrl, attributes);
		linkNode.append($createTextNode(Type.isStringFilled(originalUrl) ? originalUrl : linkUrl));

		const anchor = selection.anchor;
		if (anchor.type === 'text' && anchor.getNode().isSimpleText())
		{
			const anchorNode = anchor.getNode();
			const selectionOffset = anchor.offset;

			const splitNodes = anchorNode.splitText(selectionOffset);
			if (selectionOffset === 0)
			{
				// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
				splitNodes[0].insertBefore(linkNode);
				linkNode.select();
			}
			else
			{
				splitNodes[0].insertAfter(linkNode);
				linkNode.select();
			}
		}
		else
		{
			$insertNodes([linkNode]);
			if ($isRootOrShadowRoot(linkNode.getParentOrThrow()))
			{
				$wrapNodeInElement(linkNode, $createParagraphNode).selectEnd();
			}
		}
	}

	#isLinkSelected(selection: RangeSelection): boolean
	{
		const node = getSelectedNode(selection);
		const parent = node.getParent();

		return $isLinkNode(parent) || $isLinkNode(node);
	}

	#convertAutoLinkToLink(selection: RangeSelection): boolean
	{
		if ($isRangeSelection(selection))
		{
			const parent: AutoLinkNode = getSelectedNode(selection).getParent();
			if ($isAutoLinkNode(parent))
			{
				const linkNode = $createLinkNode(
					parent.getURL(),
					{
						rel: parent.getRel(),
						target: Type.isStringFilled(parent.getTarget()) ? parent.getTarget() : '_blank',
						title: parent.getTitle(),
					},
				);

				parent.replace(linkNode, true);

				return true;
			}
		}

		return false;
	}

	#registerComponents(): void
	{
		this.getEditor().getComponentRegistry().register('link', (): Button => {
			const button: Button = new Button();
			button.setContent('<span class="ui-icon-set --link-3"></span>');
			button.setTooltip(Loc.getMessage('TEXT_EDITOR_BTN_LINK'));
			button.setBlockType('link');
			button.disableInsideUnformatted();
			button.setTooltip(
				Loc.getMessage('TEXT_EDITOR_BTN_LINK', { '#keystroke#': Browser.isMac() ? '⌘K' : 'Ctrl+K' }),
			);
			button.subscribe('onClick', (): void => {
				if (this.#linkEditor !== null && this.#linkEditor.isShown())
				{
					return;
				}

				this.getEditor().focus(() => {
					this.getEditor().dispatchCommand(INSERT_LINK_DIALOG_COMMAND);
				});
			});

			return button;
		});
	}

	destroy(): void
	{
		super.destroy();

		if (this.#linkEditor !== null)
		{
			this.#linkEditor.destroy();
		}
	}
}
