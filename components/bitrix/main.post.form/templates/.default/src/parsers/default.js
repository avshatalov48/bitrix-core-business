import Editor from '../editor';
export default class Default
{
	id: string = 'SomeParser';
	buttonParams: ?Object = {
		name: 'Some parser name',
		iconClassName: 'some-parser-class',
		disabledForTextarea: false,
		src: '/icon.png',
		toolbarSort: 205,
		compact: false
	};
	editor;
	htmlEditor;

	constructor(editor: Editor, htmlEditor)
	{
		this.editor = editor;
		this.htmlEditor = htmlEditor;
		this.handler = this.handler.bind(this);
	}

	handler()
	{

	}

	parse(text)
	{
		return text;
	}

	unparse(bxTag, oNode)
	{
		return '';
	}

	hasButton()
	{
		return (this.buttonParams !== null);
	}

	getButton()
	{
		if (this.buttonParams === null)
		{
			return null;
		}
		return {
			id: this.id,
			name: this.buttonParams.name,
			iconClassName: this.buttonParams.iconClassName,
			disabledForTextarea: this.buttonParams.disabledForTextarea,
			src: this.buttonParams.src,
			toolbarSort: this.buttonParams.toolbarSort,
			compact: this.buttonParams.compact === true,
			handler: this.handler
		}
	}

	getParser()
	{
		return {
			name: this.id,
			obj: {
				Parse: (parserId, text) => {
					return this.parse(text);
				},
				UnParse: this.unparse.bind(this)
			}
		};
	}
}