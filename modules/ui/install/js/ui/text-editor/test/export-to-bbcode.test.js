import { $exportToBBCode } from '../src/bbcode/export-to-bbcode';

describe('BBCode Export', (): void => {
	it('should export base formats', (): void => {
		const result = $exportToBBCode('[b]Hello[/b]');

		console.log(result);
	});
});
