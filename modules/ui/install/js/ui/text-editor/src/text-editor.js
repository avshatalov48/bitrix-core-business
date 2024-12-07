/* eslint-disable @bitrix24/bitrix24-rules/no-native-dom-methods */
import { Tag, Dom, Type, Cache, Event, Browser, Text } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { SettingsCollection } from 'main.core.collections';
import { DefaultBBCodeScheme, type BBCodeScheme } from 'ui.bbcode.model';
import { createDOMRange, createRectsFromDOMRange } from 'ui.lexical.selection';
import { HIDE_DIALOG_COMMAND } from './commands';
import { NewLineMode } from './constants';
import { createHashCode } from './helpers/create-hash-code';
import { $isRootEmpty } from './helpers/is-root-empty';

import { defaultTheme } from './themes/default-theme';
import PluginCollection from './plugins/plugin-collection';
import ComponentRegistry from './component-registry';
import SchemeValidation from './scheme-validation';
import BasePlugin from './plugins/base-plugin';

import { RichTextPlugin } from './plugins/rich-text';
import { ParagraphPlugin } from './plugins/paragraph';
import { ClipboardPlugin } from './plugins/clipboard';
import { BoldPlugin } from './plugins/bold';
import { ItalicPlugin } from './plugins/italic';
import { StrikethroughPlugin } from './plugins/strikethrough';
import { UnderlinePlugin } from './plugins/underline';
import { ClearFormatPlugin } from './plugins/clear-format';
import { MentionPlugin } from './plugins/mention';
import { CodePlugin } from './plugins/code';
import { QuotePlugin } from './plugins/quote';
import { LinkPlugin } from './plugins/link';
import { AutoLinkPlugin } from './plugins/auto-link';
import { TabIndentPlugin } from './plugins/tab-indent';
import { ListPlugin } from './plugins/list';
import { ImagePlugin } from './plugins/image';
import { VideoPlugin } from './plugins/video';
import { SmileyPlugin } from './plugins/smiley';
import { SpoilerPlugin } from './plugins/spoiler';
import { TablePlugin } from './plugins/table';
import { HashtagPlugin } from './plugins/hashtag';
import { CopilotPlugin } from './plugins/copilot';
import { HistoryPlugin } from './plugins/history';
import { BlockToolbarPlugin } from './plugins/block-toolbar';
import { FloatingToolbarPlugin } from './plugins/floating-toolbar';
import { ToolbarPlugin } from './plugins/toolbar';
import { PlaceholderPlugin } from './plugins/placeholder';
import { FilePlugin } from './plugins/file';

import {
	$importFromBBCode,
	$exportToBBCode,
	type BBCodeImportMap,
	type BBCodeExportConversion,
	type BBCodeImportConversion,
	type BBCodeExportMap,
} from './bbcode';

import {
	createEditor,
	$getRoot,
	$createParagraphNode,
	$getSelection,
	$isRangeSelection,
	$getNearestNodeFromDOMNode,
	$setSelection,
	FOCUS_COMMAND,
	BLUR_COMMAND,
	KEY_ENTER_COMMAND,
	COMMAND_PRIORITY_LOW,
	COMMAND_PRIORITY_CRITICAL,
	CLEAR_HISTORY_COMMAND,
	type LexicalEditor,
	type NodeKey,
	type RootNode,
	type LexicalNode,
	type RangeSelection,
	type EditorThemeClasses,
	type EditorState,
} from 'ui.lexical.core';

import { $findMatchingParent, mergeRegister } from 'ui.lexical.utils';

import type DecoratorComponent from './decorator-component';
import type { PluginConstructor } from './plugins/base-plugin';
import type { ClearOptions } from './types/clear-options';
import type { InitialEditorStateType } from './types/initial-editor-state-type';
import type { SetTextOptions } from './types/set-text-options';
import type { TextEditorOptions } from './types/text-editor-options';
import type { DecoratorOptions } from './types/decorator-options';
import type { NewLineModeType } from './types/new-line-mode-type';

const CollapsingState = {
	COLLAPSED: 'collapsed',
	COLLAPSING: 'collapsing',
	EXPANDED: 'expanded',
	EXPANDING: 'expanding',
};

import './css/layout.css';

/**
 * @memberof BX.UI.TextEditor
 */
export class TextEditor extends EventEmitter
{
	#lexicalEditor: LexicalEditor = null;
	#componentRegistry: ComponentRegistry = new ComponentRegistry();
	#refs: typeof(Cache.MemoryCache) = new Cache.MemoryCache();
	#options: SettingsCollection = null;
	#plugins: PluginCollection = null;
	#newLineMode: NewLineModeType = NewLineMode.MIXED;
	#bbcodeScheme: BBCodeScheme = null;
	#schemeValidation: SchemeValidation = null;
	#bbcodeImportMap: BBCodeImportMap;
	#bbcodeExportMap: BBCodeExportMap;
	#themeClasses: EditorThemeClasses = {};

	#decoratorNodes: Set<NodeKey> = new Set();
	#decoratorComponents: Map<string, DecoratorComponent> = new Map();
	#removeListeners: Function = null;

	#highlightContainer = Tag.render`<div class="ui-text-editor-selection-highlighting"></div>`;
	#autoFocus: boolean = false;
	#minHeight: Number | null = null;
	#maxHeight: Number | null = null;

	#collapsingMode: boolean = false;
	#collapsingState: string = CollapsingState.EXPANDED;
	#collapsingTransitionEnd: Function = this.#handleCollapsingTransition.bind(this);
	#paragraphHeight: number = null;

	#resizeObserver: ResizeObserver = null;
	#destroying: boolean = false;
	#rendered: boolean = false;
	#prevEmptyStatus: boolean = true;

	constructor(editorOptions: TextEditorOptions)
	{
		super();
		this.setEventNamespace('BX.UI.TextEditor.Editor');

		const defaultOptions: TextEditorOptions = this.constructor.getDefaultOptions();
		const options: TextEditorOptions = Type.isPlainObject(editorOptions) ? editorOptions : {};
		this.#options = new SettingsCollection({ ...defaultOptions, ...options });

		const builtinPlugins = [...this.constructor.getBuiltinPlugins()];
		const plugins: Array<string | PluginConstructor> = this.#options.get('plugins', builtinPlugins);
		const extraPlugins: Array<PluginConstructor> = this.#options.get('extraPlugins', []);
		const pluginsToRemove: Array<PluginConstructor> = this.#options.get('removePlugins', []);

		const newLineMode = this.#options.get('newLineMode');
		if ([NewLineMode.LINE_BREAK, NewLineMode.PARAGRAPH].includes(newLineMode))
		{
			this.#newLineMode = newLineMode;
		}

		this.#themeClasses = defaultTheme;

		this.#plugins = new PluginCollection(builtinPlugins, [...plugins, ...extraPlugins], pluginsToRemove);
		const constructors = this.#plugins.getConstructors();
		const nodes = constructors.map((pluginConstructor: PluginConstructor) => {
			return pluginConstructor.getNodes(this);
		});

		this.#lexicalEditor = createEditor({
			// uses when you copy-paste from one to another editor
			namespace: Type.isStringFilled(options.namespace) ? options.namespace : this.#createNamespace(constructors),
			nodes: nodes.flat(),
			onError: (error: Error) => {
				console.error(error);
			},
			theme: this.#themeClasses,
			editable: this.#options.get('editable') !== false,
		});

		this.setMinHeight(options.minHeight);
		this.setMaxHeight(options.maxHeight);
		this.setAutoFocus(options.autoFocus);
		this.setVisualOptions(options.visualOptions);

		this.#removeListeners = mergeRegister(
			this.#registerCommands(),
			this.#initDecorateNodes(nodes.flat()),
		);

		this.#plugins.init(this);

		this.#bbcodeImportMap = this.#initBBCodeImportMap();
		this.#bbcodeExportMap = this.#initBBCodeExportMap();
		this.#bbcodeScheme = this.#initBBCodeScheme();
		this.#schemeValidation = new SchemeValidation(this);

		this.subscribeFromOptions(options.events);
	}

	static getBuiltinPlugins(): Class<BasePlugin>[]
	{
		return [
			RichTextPlugin,
			ParagraphPlugin,
			ClipboardPlugin,
			BoldPlugin,
			UnderlinePlugin,
			ItalicPlugin,
			StrikethroughPlugin,
			ClearFormatPlugin,
			TabIndentPlugin,
			CodePlugin,
			QuotePlugin,
			ListPlugin,
			MentionPlugin,
			LinkPlugin,
			AutoLinkPlugin,
			ImagePlugin,
			VideoPlugin,
			SmileyPlugin,
			SpoilerPlugin,
			TablePlugin,
			HashtagPlugin,
			CopilotPlugin,
			HistoryPlugin,
			BlockToolbarPlugin,
			FloatingToolbarPlugin,
			ToolbarPlugin,
			PlaceholderPlugin,
			FilePlugin,
		];
	}

	static getDefaultOptions(): TextEditorOptions
	{
		return {};
	}

	getComponentRegistry(): ComponentRegistry
	{
		return this.#componentRegistry;
	}

	getOptions(): SettingsCollection
	{
		return this.#options;
	}

	getOption(path: string, defaultValue: any = null): any
	{
		return this.#options.get(path, defaultValue);
	}

	getThemeClasses(): EditorThemeClasses
	{
		return this.#themeClasses;
	}

	getThemeClass(tagName: string): string
	{
		const className = this.#themeClasses[tagName];
		if (className !== undefined)
		{
			return className;
		}

		return '';
	}

	getNewLineMode(): NewLineModeType
	{
		return this.#newLineMode;
	}

	#initEditorState(initialEditorState?: InitialEditorStateType, options?: SetTextOptions): void
	{
		if (Type.isNil(initialEditorState))
		{
			this.#lexicalEditor.update(() => {
				const root = $getRoot();
				if (root.isEmpty())
				{
					const paragraph = $createParagraphNode();
					root.append(paragraph);
				}
			}, options);
		}
		else if (Type.isPlainObject(initialEditorState) || Type.isStringFilled(initialEditorState))
		{
			const parsedEditorState: EditorState = this.#lexicalEditor.parseEditorState(initialEditorState);
			this.#lexicalEditor.setEditorState(parsedEditorState);
		}
		else if (Type.isFunction(initialEditorState))
		{
			this.#lexicalEditor.update(() => {
				const root = $getRoot();
				if (root.isEmpty())
				{
					initialEditorState(this.#lexicalEditor);
				}
			}, options);
		}
	}

	#initDecorateNodes(editorNodes: Class<LexicalNode>[]): () => void
	{
		const removeListeners = [];
		editorNodes.forEach((nodeClass) => {
			if (nodeClass.useDecoratorComponent)
			{
				const removeListener = this.registerMutationListener(
					nodeClass,
					(nodes, payload) => {
						for (const [key, val] of nodes)
						{
							if (val === 'destroyed')
							{
								const component: DecoratorComponent = this.#decoratorComponents.get(key);
								if (component)
								{
									component.destroy();
								}

								this.#decoratorComponents.delete(key);
							}
							else
							{
								this.#decoratorNodes.add(key);
							}
						}
					},
				);

				removeListeners.push(removeListener);
			}
		});

		const removeListener = this.#lexicalEditor.registerDecoratorListener(
			(decorators: Record<NodeKey, DecoratorOptions>) => {
				this.#decoratorNodes.forEach((nodeKey) => {
					const decorator = decorators[nodeKey];
					const {
						componentClass: DecoratorClass,
						options: decoratorOptions,
					} = decorator;

					const component = this.#decoratorComponents.get(nodeKey);
					const htmlElement = this.#lexicalEditor.getElementByKey(nodeKey);
					if (htmlElement?.innerHTML && component)
					{
						component.update(decoratorOptions);
					}
					else if (htmlElement)
					{
						this.#decoratorComponents.set(
							nodeKey,
							new DecoratorClass({
								textEditor: this,
								target: htmlElement,
								nodeKey,
								options: decoratorOptions,
							}),
						);
					}
				});

				this.#decoratorNodes.clear();
			},
		);

		removeListeners.push(removeListener);

		return mergeRegister(...removeListeners);
	}

	#registerCommands(): () => void
	{
		return mergeRegister(
			this.registerCommand(
				FOCUS_COMMAND,
				(): boolean => {
					if (
						this.isCollapsingModeEnabled()
						&& this.#collapsingState === CollapsingState.COLLAPSED
						&& this.isEmpty(false)
					)
					{
						this.expand();

						return true;
					}

					this.emit('onFocus');

					return false;
				},
				COMMAND_PRIORITY_CRITICAL,
			),

			this.registerCommand(
				BLUR_COMMAND,
				(event): boolean => {
					if (
						this.isCollapsingModeEnabled()
						&& (
							this.#collapsingState === CollapsingState.COLLAPSING
							|| this.#collapsingState === CollapsingState.EXPANDING
						)
					)
					{
						return true;
					}

					this.emit('onBlur');

					return false;
				},
				COMMAND_PRIORITY_CRITICAL,
			),

			this.registerUpdateListener(
				({ dirtyElements, dirtyLeaves, prevEditorState, tags }) => {
					const isComposing = this.isComposing();
					const hasContentChanges = dirtyLeaves.size > 0 || dirtyElements.size > 0;
					if (isComposing || !hasContentChanges)
					{
						return;
					}

					const isInitialChange: boolean = prevEditorState.isEmpty();
					if (this.#options.get('collapsingMode') === true)
					{
						if (isInitialChange)
						{
							this.#initCollapsingMode();
						}
						else if (this.isCollapsed() && !this.isEmpty())
						{
							this.expand(false);
						}
					}

					if (!isInitialChange && tags.has('history-merge'))
					{
						return;
					}

					this.emit('onChange', { isInitialChange, tags });

					const isEmpty = this.isEmpty();
					if (this.#prevEmptyStatus !== isEmpty)
					{
						this.#prevEmptyStatus = isEmpty;
						this.emit('onEmptyContentToggle', { isEmpty, isInitialChange });
					}
				},
			),

			this.registerCommand(
				KEY_ENTER_COMMAND,
				(event: KeyboardEvent) => {
					const { code, ctrlKey, metaKey } = event;
					if ((Browser.isMac() && metaKey) || ctrlKey)
					{
						this.emit('onMetaEnter');

						return true;
					}

					if (code === 'Escape')
					{
						this.emit('onEscape');

						return true;
					}

					return false;
				},
				COMMAND_PRIORITY_LOW,
			),

			this.registerEditableListener((isEditable: boolean): boolean => {
				this.getEditableContainer().contentEditable = isEditable;
				if (isEditable)
				{
					Dom.removeClass(this.getRootContainer(), '--read-only');
					Dom.addClass(this.getRootContainer(), '--editable');
				}
				else
				{
					Dom.removeClass(this.getRootContainer(), '--editable');
					Dom.addClass(this.getRootContainer(), '--read-only');
				}

				this.emit('onEditable', { isEditable });
			}),
		);
	}

	#createNamespace(plugins: PluginConstructor[]): string
	{
		const hashCode = createHashCode(
			plugins
				.map((node) => node.getName())
				.sort()
				.join('-'),
		);

		return String(hashCode);
	}

	getBBCodeScheme(): BBCodeScheme
	{
		return this.#bbcodeScheme;
	}

	getSchemeValidation(): SchemeValidation
	{
		return this.#schemeValidation;
	}

	#initBBCodeImportMap(): BBCodeImportMap
	{
		const importMap: BBCodeImportMap = new Map();
		for (const [, plugin] of this.#plugins)
		{
			const map: BBCodeImportConversion = plugin.importBBCode();
			if (map !== null)
			{
				Object.keys(map).forEach((key: string): void => {
					let currentValue = importMap.get(key);
					if (currentValue === undefined)
					{
						currentValue = [];
						importMap.set(key, currentValue);
					}

					currentValue.push(map[key]);
				});
			}
		}

		return importMap;
	}

	#initBBCodeExportMap(): BBCodeImportMap
	{
		const exportMap: BBCodeExportMap = new Map();
		for (const [, plugin] of this.#plugins)
		{
			const map: BBCodeExportConversion | null = plugin.exportBBCode();
			if (map !== null)
			{
				Object.keys(map).forEach((nodeType: string): void => {
					if (Type.isFunction(map[nodeType]))
					{
						exportMap.set(nodeType, map[nodeType]);
					}
				});
			}
		}

		return exportMap;
	}

	#initBBCodeScheme(): BBCodeScheme
	{
		const filePlugin: FilePlugin = this.getPlugin('File');
		const fileTag = filePlugin?.isEnabled() ? filePlugin.getMode() : 'none';

		return new DefaultBBCodeScheme({ fileTag });
	}

	setText(text: string, options?: SetTextOptions): void
	{
		if (Type.isString(text))
		{
			const updateOptions = {
				discrete: Type.isPlainObject(options) && options.discrete === true,
			};

			this.#lexicalEditor.update((): void => {
				const lexicalNodes: Array<LexicalNode> = $importFromBBCode(text, this);
				const root: RootNode = $getRoot();
				root.clear();
				root.append(...lexicalNodes);
				$setSelection(null);
			}, updateOptions);
		}
	}

	clear(options?: ClearOptions): void
	{
		const updateOptions = {
			discrete: Type.isPlainObject(options) && options.discrete === true,
		};

		this.#lexicalEditor.update((): void => {
			const root: RootNode = $getRoot();
			const paragraph = $createParagraphNode();
			root.clear();
			root.append(paragraph);

			// const selection = $getSelection();
			// if (selection !== null)
			// {
			// 	paragraph.select();
			// }

			$setSelection(null);
		}, updateOptions);
	}

	clearHistory(): void
	{
		this.dispatchCommand(CLEAR_HISTORY_COMMAND);
	}

	getText(): string
	{
		return this.#lexicalEditor.getEditorState().read(() => {
			const bbCodeAst = $exportToBBCode($getRoot(), this);

			// console.log("bbCodeAst", bbCodeAst);

			return bbCodeAst.toString();
		});
	}

	isEmpty(trim: boolean = true): boolean
	{
		return this.#lexicalEditor.getEditorState().read(() => {
			return $isRootEmpty(trim);
		});
	}

	setAutoFocus(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.#autoFocus = flag;
		}
	}

	hasAutoFocus(): boolean
	{
		return this.#autoFocus;
	}

	setMinHeight(minHeight: number | null): void
	{
		if ((Type.isNumber(minHeight) && minHeight > 0) || minHeight === null)
		{
			const changed = this.#minHeight !== minHeight;
			this.#minHeight = minHeight;

			if (changed)
			{
				Dom.style(
					this.getScrollerContainer(),
					'--ui-text-editor-min-height',
					minHeight > 0 ? `${minHeight}px` : null,
				);
			}
		}
	}

	getMinHeight(): number | null
	{
		return this.#minHeight;
	}

	setMaxHeight(maxHeight: number | null): void
	{
		if ((Type.isNumber(maxHeight) && maxHeight > 0) || maxHeight === null)
		{
			const changed = this.#maxHeight !== maxHeight;
			this.#maxHeight = maxHeight;

			if (changed)
			{
				Dom.style(
					this.getScrollerContainer(),
					'--ui-text-editor-max-height',
					maxHeight > 0 ? `${maxHeight}px` : null,
				);
			}
		}
	}

	getMaxHeight(): number | null
	{
		return this.#maxHeight;
	}

	setVisualOptions(options: TextEditorOptions['visualOption']): void
	{
		if (!Type.isPlainObject(options))
		{
			return;
		}

		for (const [option, value] of Object.entries(options))
		{
			const name = Text.toKebabCase(option);

			Dom.style(
				this.getRootContainer(),
				`--ui-text-editor-${name}`,
				value,
			);
		}
	}

	#initCollapsingMode()
	{
		this.#collapsingMode = true;
		if (this.isEmpty())
		{
			this.#collapse('hide', false, true);
		}
		else
		{
			this.expand(false);
		}
	}

	isCollapsingModeEnabled(): boolean
	{
		return this.#collapsingMode;
	}

	isCollapsed(): boolean
	{
		return this.#collapsingState === CollapsingState.COLLAPSED;
	}

	#collapse(mode: 'show' | 'hide' | 'toggle' = 'hide', animate: boolean = true, initialState: boolean = false): void
	{
		if (!this.isCollapsingModeEnabled())
		{
			return;
		}

		const collapsed = (
			this.#collapsingState === CollapsingState.COLLAPSED || this.#collapsingState === CollapsingState.COLLAPSING
		);

		const expanded = (
			this.#collapsingState === CollapsingState.EXPANDED || this.#collapsingState === CollapsingState.EXPANDING
		);

		if ((mode === 'hide' && collapsed) || (mode === 'show' && expanded))
		{
			return;
		}

		if (animate === false)
		{
			if (collapsed)
			{
				this.#collapsingState = CollapsingState.EXPANDED;
				Dom.removeClass(this.getRootContainer(), '--collapsed');
				this.emit('onCollapsingToggle', { isOpen: true });
				this.focus();
			}
			else
			{
				this.#collapsingState = CollapsingState.COLLAPSED;
				Dom.addClass(this.getRootContainer(), '--collapsed');
				this.emit('onCollapsingToggle', { isOpen: false });
				this.clear();
				this.clearHistory();
				if (!initialState)
				{
					this.blur();
				}
			}

			return;
		}

		Event.unbind(this.getRootContainer(), 'transitionend', this.#collapsingTransitionEnd);

		if (collapsed)
		{
			this.#collapsingState = CollapsingState.EXPANDING;
			this.blur(); // to avoid a root container scrolling because of a browser focus

			const currentHeight = this.getRootContainer().offsetHeight;
			Dom.removeClass(this.getRootContainer(), ['--collapsed', '--collapsing']);
			Dom.style(this.getRootContainer(), { height: `${currentHeight}px`, overflow: 'hidden' });
			Dom.style(this.getInnerContainer(), { opacity: 0 });

			requestAnimationFrame(() => {
				Dom.addClass(this.getRootContainer(), '--expanding');
				Dom.style(this.getRootContainer(), { height: `${this.getRootContainer().scrollHeight}px` });
				Dom.style(this.getInnerContainer(), { opacity: 1 });

				this.emit('onCollapsingToggle', { isOpen: true });
			});
		}
		else
		{
			this.#collapsingState = CollapsingState.COLLAPSING;

			const currentHeight = this.getRootContainer().offsetHeight;

			Dom.removeClass(this.getRootContainer(), ['--expanding']);
			Dom.style(this.getRootContainer(), { height: `${currentHeight}px`, overflow: 'hidden' });
			Dom.style(this.getInnerContainer(), { opacity: 1 });

			this.blur();

			const paragraphHeight = this.getParagraphHeight();

			requestAnimationFrame(() => {
				Dom.addClass(this.getRootContainer(), '--collapsing');
				Dom.style(this.getRootContainer(), { height: `${paragraphHeight}px` });
				Dom.style(this.getInnerContainer(), { opacity: 0 });

				this.emit('onCollapsingToggle', { isOpen: false });
			});
		}

		Event.bind(this.getRootContainer(), 'transitionend', this.#collapsingTransitionEnd);
	}

	collapse(animate: boolean = true): void
	{
		this.#collapse('hide', animate);
	}

	expand(animate: boolean = true): void
	{
		this.#collapse('show', animate);
	}

	toggle(animate: boolean = true): void
	{
		this.#collapse('toggle', animate);
	}

	getParagraphHeight(): number
	{
		if (this.#paragraphHeight !== null)
		{
			return this.#paragraphHeight;
		}

		const className = this.getThemeClasses().paragraph || '';
		const paragraph = Tag.render`<p class="${className}"><br /></p>`;

		Dom.style(paragraph, {
			position: 'absolute',
			transform: 'translateY(-1000px)',
		});

		Dom.append(paragraph, this.getScrollerContainer());

		this.#paragraphHeight = (
			paragraph.offsetHeight
			+ Text.toNumber(Dom.style(paragraph, 'margin-top'))
			+ Text.toNumber(Dom.style(paragraph, 'margin-bottom'))
		);

		Dom.remove(paragraph);

		return this.#paragraphHeight;
	}

	#handleCollapsingTransition(): void
	{
		Event.unbind(this.getRootContainer(), 'transitionend', this.#collapsingTransitionEnd);

		Dom.style(this.getRootContainer(), { height: null, overflow: null });
		Dom.style(this.getInnerContainer(), { opacity: null });
		Dom.removeClass(this.getRootContainer(), ['--expanding', '--collapsing']);

		if (this.#collapsingState === CollapsingState.COLLAPSING)
		{
			Dom.addClass(this.getRootContainer(), '--collapsed');
			this.#collapsingState = CollapsingState.COLLAPSED;
			this.clear();
			this.clearHistory();
			this.blur();
		}
		else
		{
			this.focus();
			this.#collapsingState = CollapsingState.EXPANDED;
		}
	}

	getLexicalEditor(): LexicalEditor
	{
		return this.#lexicalEditor;
	}

	setRootElement(contentEditableElement: null | HTMLElement)
	{
		if (Type.isElementNode(contentEditableElement) || contentEditableElement === null)
		{
			this.#lexicalEditor.setRootElement(contentEditableElement);
		}
	}

	getBBCodeExportMap(): BBCodeExportMap
	{
		return this.#bbcodeExportMap;
	}

	getBBCodeImportMap(): BBCodeImportMap
	{
		return this.#bbcodeImportMap;
	}

	getEditorState(): EditorState
	{
		return this.#lexicalEditor.getEditorState();
	}

	getPlugins(): PluginCollection
	{
		return this.#plugins;
	}

	getPlugin(key: PluginConstructor | string): BasePlugin | null
	{
		return this.#plugins.get(key);
	}

	getElementByKey(key: NodeKey): HTMLElement | null
	{
		return this.#lexicalEditor.getElementByKey(key);
	}

	setEditorState(editorState: EditorState, options?: Object): void
	{
		this.#lexicalEditor.setEditorState(editorState, options);
	}

	setEditable(editable: boolean): void
	{
		if (Type.isBoolean(editable))
		{
			this.dispatchCommand(HIDE_DIALOG_COMMAND);
			if (!editable)
			{
				this.blur();
			}

			this.#lexicalEditor.setEditable(editable);
		}
	}

	isEditable(): boolean
	{
		return this.#lexicalEditor.isEditable();
	}

	registerUpdateListener(listener): () => void
	{
		return this.#lexicalEditor.registerUpdateListener(listener);
	}

	registerEditableListener(listener): () => void
	{
		return this.#lexicalEditor.registerEditableListener(listener);
	}

	registerCommand(command, listener, priority): () => void
	{
		return this.#lexicalEditor.registerCommand(command, listener, priority);
	}

	dispatchCommand(type, payload): boolean
	{
		return this.#lexicalEditor.dispatchCommand(type, payload);
	}

	registerMutationListener(klass, listener): () => void
	{
		return this.#lexicalEditor.registerMutationListener(klass, listener);
	}

	registerNodeTransform(klass, listener): () => void
	{
		return this.#lexicalEditor.registerNodeTransform(klass, listener);
	}

	registerTextContentListener(listener): () => void
	{
		return this.#lexicalEditor.registerTextContentListener(listener);
	}

	registerDecoratorListener(listener): () => void
	{
		return this.#lexicalEditor.registerDecoratorListener(listener);
	}

	registerRootListener(listener): () => void
	{
		return this.#lexicalEditor.registerRootListener(listener);
	}

	registerEventListener(
		nodeType: Class<LexicalNode>,
		eventType: string,
		eventListener: (event: Event, nodeKey: NodeKey) => void,
	): () => void
	{
		const isCaptured = ['mouseenter', 'mouseleave'].includes(eventType);
		const handleEvent = (event: Event) => {
			this.update(() => {
				const nearestNode = $getNearestNodeFromDOMNode(event.target);
				if (nearestNode !== null)
				{
					const targetNode = (
						isCaptured
							? (nearestNode instanceof nodeType ? nearestNode : null)
							: $findMatchingParent(nearestNode, (node) => node instanceof nodeType)
					);

					if (targetNode !== null)
					{
						eventListener(event, targetNode.getKey());
					}
				}
			});
		};

		return this.registerRootListener((rootElement, prevRootElement): void => {
			if (rootElement)
			{
				Event.bind(rootElement, eventType, handleEvent, isCaptured);
			}

			if (prevRootElement)
			{
				Event.unbind(prevRootElement, eventType, handleEvent, isCaptured);
			}
		});
	}

	update(updateFn: () => void, options?: Object): void
	{
		this.#lexicalEditor.update(updateFn, options);
	}

	focus(
		callbackFn?: () => void,
		options?: { defaultSelection?: 'rootStart' | 'rootEnd' },
	): void
	{
		if (!document.hasFocus())
		{
			window.focus();
		}

		this.#lexicalEditor.focus(
			Type.isFunction(callbackFn) ? callbackFn : null,
			Type.isPlainObject(options) ? options : { defaultSelection: 'rootStart' },
		);
	}

	hasFocus(): boolean
	{
		return this.getRootElement().contains(document.activeElement);
	}

	blur(): void
	{
		this.#lexicalEditor.blur();
	}

	isComposing(): boolean
	{
		return this.#lexicalEditor.isComposing();
	}

	getRootElement(): null | HTMLElement
	{
		return this.#lexicalEditor.getRootElement();
	}

	hasNodes(nodes: Array): boolean
	{
		return this.#lexicalEditor.hasNodes(nodes);
	}

	getRootContainer(): HTMLElement
	{
		return this.#refs.remember('root', () => {
			const classes = [
				this.isEditable() ? '--editable' : '--read-only',
			];

			return Tag.render`
				<div class="ui-text-editor ${classes.join(' ')}">
					${this.getInnerContainer()}
				</div>
			`;
		});
	}

	getInnerContainer(): HTMLElement
	{
		return this.#refs.remember('inner', () => {
			return Tag.render`
				<div class="ui-text-editor-inner">
					${this.getHeaderContainer()}
					${this.getToolbarContainer()}
					${this.getScrollerContainer()}
					${this.getFooterContainer()}
				</div>
			`;
		});
	}

	getToolbarContainer(): HTMLElement
	{
		return this.#refs.remember('toolbar', () => {
			return Tag.render`
				<div class="ui-text-editor-toolbar" tabindex="-1"></div>
			`;
		});
	}

	getScrollerContainer(): HTMLElement
	{
		return this.#refs.remember('scroller', () => {
			return Tag.render`
				<div class="ui-text-editor-scroller">
					${this.getEditableContainer()}
				</div>
			`;
		});
	}

	getEditableContainer(): HTMLElement
	{
		return this.#refs.remember('editable', () => {
			return Tag.render`
				<div 
					class="ui-text-editor-editable" 
					contenteditable="${this.isEditable() ? 'true' : 'false'}" 
					spellcheck="true"
				></div>
			`;
		});
	}

	getFooterContainer(): HTMLElement
	{
		return this.#refs.remember('footer', () => {
			return Tag.render`
				<div class="ui-text-editor-slot ui-text-editor-footer" tabindex="-1"></div>
			`;
		});
	}

	getHeaderContainer(): HTMLElement
	{
		return this.#refs.remember('header', () => {
			return Tag.render`
				<div class="ui-text-editor-slot ui-text-editor-header" tabindex="-1"></div>
			`;
		});
	}

	renderTo(container: HTMLElement, replaceNode: boolean = false): void
	{
		if (!Type.isElementNode(container))
		{
			return;
		}

		if (!this.isRendered())
		{
			if (Type.isStringFilled(this.#options.get('content')))
			{
				this.setText(this.#options.get('content'));
			}
			else
			{
				this.#initEditorState(this.#options.get('editorState'));
			}
		}

		if (replaceNode)
		{
			Dom.replace(container, this.getRootContainer());
		}
		else
		{
			Dom.append(this.getRootContainer(), container);
		}

		this.#lexicalEditor.setRootElement(this.getEditableContainer());

		if (this.hasAutoFocus())
		{
			this.focus(null, { defaultSelection: 'rootStart' });
		}

		if (!this.#rendered)
		{
			this.#resizeObserver = new ResizeObserver(() => {
				this.emit('onResize');
				this.dispatchCommand(HIDE_DIALOG_COMMAND, { context: 'resize' });
			});

			this.#resizeObserver.observe(this.getScrollerContainer());
		}

		this.#rendered = true;
	}

	isRendered(): boolean
	{
		return this.#rendered;
	}

	highlightSelection(): void
	{
		this.getEditorState().read(() => {
			const selection: RangeSelection = $getSelection();
			if (!$isRangeSelection(selection) || selection.isCollapsed())
			{
				return;
			}

			const anchor = selection.anchor;
			const focus = selection.focus;
			const range = createDOMRange(
				this.#lexicalEditor,
				anchor.getNode(),
				anchor.offset,
				focus.getNode(),
				focus.offset,
			);

			if (range !== null)
			{
				const scrollerContainer = this.getScrollerContainer();
				const scrollerRect = scrollerContainer.getBoundingClientRect();
				const selectionRects = createRectsFromDOMRange(this.#lexicalEditor, range);
				const selectionRectsLength = selectionRects.length;

				this.#highlightContainer.innerHTML = '';

				for (let i = 0; i < selectionRectsLength; i++)
				{
					const selectionRect = selectionRects[i];
					const elem = Tag.render`<span class="ui-text-editor-selection-part"></span>`;
					const top = selectionRect.top - scrollerRect.top + scrollerContainer.scrollTop;
					const left = selectionRect.left - scrollerRect.left + scrollerContainer.scrollLeft;

					Dom.style(elem, {
						top: `${top}px`,
						left: `${left}px`,
						height: `${selectionRect.height}px`,
						width: `${selectionRect.width}px`,
					});

					Dom.append(elem, this.#highlightContainer);
				}

				Dom.append(this.#highlightContainer, this.getScrollerContainer());
			}
		});
	}

	resetHighlightSelection(): void
	{
		Dom.remove(this.#highlightContainer);
	}

	destroy(): void
	{
		if (this.#destroying)
		{
			return;
		}

		this.#destroying = true;
		this.emit('onDestroy');

		for (const [, plugin] of this.#plugins)
		{
			plugin.destroy();
		}

		this.#removeListeners();
		if (this.isRendered())
		{
			this.#resizeObserver.disconnect();
			this.setRootElement(null);
			Dom.remove(this.getRootContainer());
		}

		this.#resizeObserver = null;
		this.#plugins = null;
		this.#lexicalEditor = null;
		this.$refs = null;
		this.#schemeValidation = null;
		this.#bbcodeImportMap = null;
		this.#bbcodeExportMap = null;
		this.#decoratorNodes = null;
		this.#decoratorComponents = null;

		Object.setPrototypeOf(this, null);
	}
}
