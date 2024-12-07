import { Core } from 'ui.lexical';
console.log("Core", Core);
// import { $importFromBBCode } from '../src/bbcode/import-from-bbcode';
// import TextEditor from '../src/text-editor';

describe('BBCode Export', () => {
	const editor = new TextEditor();
	it('should export base formats', () => {
		editor.update(() => {
			const result = $importFromBBCode(
				"Test text [b]bold[/b], [i]italic[/i], [u]underline[/u],\n[s]strike[/s]\n" // +
				// "[img width=20 height=20]/path/to/image.png[/img]\n" +
				// "[url=https://bitrix24.com]Bitrix24[/url]\n" +
				// "[code]aaaa\n\t\tbbbb\n\t\tcccc[/code]\n"
			);

			console.log(JSON.stringify(result, null, 4));
		})

	});
});
