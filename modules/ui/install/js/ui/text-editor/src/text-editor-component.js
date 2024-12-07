import { Type } from 'main.core';
import { TextEditor } from './text-editor';
import { type BitrixVueComponentProps } from 'ui.vue3';

export const TextEditorComponent: BitrixVueComponentProps = {
	name: 'TextEditorComponent',
	props: {
		editorOptions: {
			type: Object,
		},
		editorInstance: {
			type: TextEditor,
			default: null,
		},
		events: {
			type: Object,
			default: {},
		},
		editable: {
			type: Boolean,
			default: null,
		},
	},
	setup()
	{
		return {
			editorClass: TextEditor,
		};
	},
	provide(): Object<string, any> {
		return {
			editor: this.editor,
		};
	},
	beforeCreate()
	{
		if (this.editorInstance === null)
		{
			this.hasOwnEditor = true;

			const EditorClass = this.editorClass;
			this.editor = new EditorClass(this.editorOptions);
		}
		else
		{
			this.hasOwnEditor = false;
			this.editor = this.editorInstance;
		}

		if (Type.isPlainObject(this.events))
		{
			for (const [eventName, fn] of Object.entries(this.events))
			{
				this.editor.subscribe(eventName, fn);
			}
		}
	},
	computed:
	{
		headerContainer(): string
		{
			return this.editor.getHeaderContainer();
		},
		footerContainer(): string
		{
			return this.editor.getFooterContainer();
		},
	},
	watch:
	{
		editable(value: boolean): void
		{
			this.editor.setEditable(value);
		},
	},
	mounted(): void
	{
		this.editor.renderTo(this.$refs.container, true);
	},
	unmounted(): void
	{
		if (this.hasOwnEditor)
		{
			this.editor.destroy();
			this.editor = null;
		}
	},
	template: `
		<div ref="container"></div>
		<Teleport :to="headerContainer">
			<slot name="header"></slot>
		</Teleport>
		<Teleport :to="footerContainer">
			<slot name="footer"></slot>
		</Teleport>
	`,
};
