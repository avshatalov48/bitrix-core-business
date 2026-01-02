import 'im.v2.test';

import { ParserDisk } from '../../src/functions/disk';

describe('ParserDisk', () => {
	describe('decode', () => {
		it('should replace [disk=id] with text', () => {
			const text = 'Hello, [disk=123]!';
			const expected = 'Hello, [File]!';
			const actual = ParserDisk.decode(text);

			assert.equal(actual, expected);
		});
		it('should handle multiple [disk=id] tags', () => {
			const text = '[disk=1] and [disk=2]';
			const expected = '[File] and [File]';
			const actual = ParserDisk.decode(text);

			assert.equal(actual, expected);
		});
		it('should not replace anything if there are no disk tags', () => {
			const text = 'Hello, world!';
			const actual = ParserDisk.decode(text);

			assert.equal(actual, text);
		});
	});
	describe('purify', () => {
		it('should be an alias for decode', () => {
			const text = 'Purify me, [disk=456]!';
			const expected = 'Purify me, [File]!';
			const actual = ParserDisk.decode(text);

			assert.equal(actual, expected);
		});
	});
});
