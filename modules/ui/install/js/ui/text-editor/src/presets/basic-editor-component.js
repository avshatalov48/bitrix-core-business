import { TextEditorComponent } from '../text-editor-component';
import { BasicEditor } from './basic-editor';
import { type BitrixVueComponentProps } from 'ui.vue3';

export const BasicEditorComponent: BitrixVueComponentProps = {
	name: 'BasicEditorComponent',
	extends: TextEditorComponent,
	setup(): Object
	{
		return {
			editorClass: BasicEditor,
		};
	},
};
