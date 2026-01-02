import 'im.v2.test';
import { Utils } from 'im.v2.lib.utils';

import { Textarea } from '../src/textarea';

describe('Textarea', () => {
	describe('applyWrapping', () => {
		it('should wrap selected text with given tags', () => {
			const textarea = createMockTextarea('Hello world!', 6, 11); // selects "world"
			const leftTag = '[b]';
			const rightTag = '[/b]';

			const result = Textarea.applyWrapping(textarea, leftTag, rightTag);

			const expectedValue = 'Hello [b]world[/b]!';
			assert.strictEqual(result, expectedValue, 'The returned string is incorrect');
			assert.strictEqual(textarea.value, expectedValue, 'Textarea value was not updated correctly');
			assert.strictEqual(textarea.selectionStart, 6, 'Selection start is incorrect');
			// old end (11) + left tag (3) + right tag (4) = 18
			assert.strictEqual(textarea.selectionEnd, 18, 'Selection end is incorrect');
		});

		it('should wrap the entire text if all of it is selected', () => {
			const textarea = createMockTextarea('Select all', 0, 10);
			const leftTag = '[i]';
			const rightTag = '[/i]';

			const result = Textarea.applyWrapping(textarea, leftTag, rightTag);

			const expectedValue = '[i]Select all[/i]';
			assert.strictEqual(result, expectedValue, 'The returned string is incorrect');
			assert.strictEqual(textarea.value, expectedValue, 'Textarea value was not updated correctly');
			assert.strictEqual(textarea.selectionStart, 0, 'Selection start is incorrect');
			// old end (10) + left tag (3) + right tag (4) = 17
			assert.strictEqual(textarea.selectionEnd, 17, 'Selection end is incorrect');
		});
	});
	describe('handlePasteUrl', () => {
		let checkUrlStub;

		beforeEach(() => {
			checkUrlStub = sinon.stub(Utils.text, 'checkUrl');
		});

		afterEach(() => {
			checkUrlStub.restore();
		});

		it('should wrap selected text with URL tags when a valid URL is pasted', () => {
			const textarea = createMockTextarea('Click this link', 6, 10); // selects "this"
			const pastedUrl = 'https://example.com';
			checkUrlStub.returns(true);
			const event = createMockClipboardEvent(pastedUrl);

			const result = Textarea.handlePasteUrl(textarea, event);

			const expectedValue = `Click [URL=${pastedUrl}]this[/URL] link`;
			assert.strictEqual(result, expectedValue, 'The final string is incorrect');
		});

		it('should do nothing if pasted text is not a URL', () => {
			const initialText = 'Click this link';
			const textarea = createMockTextarea(initialText, 6, 10);
			const pastedText = 'not a valid url';
			checkUrlStub.returns(false);
			const event = createMockClipboardEvent(pastedText);

			const result = Textarea.handlePasteUrl(textarea, event);

			assert.strictEqual(result, initialText, 'The returned string should be the original value');
			assert.strictEqual(textarea.value, initialText, 'Textarea value should not have changed');
		});

		it('should do nothing if no text is selected', () => {
			const initialValue = 'Click this link';
			const textarea = createMockTextarea(initialValue, 10, 10); // No selection
			const pastedUrl = 'https://example.com';
			checkUrlStub.returns(true);
			const event = createMockClipboardEvent(pastedUrl);

			const result = Textarea.handlePasteUrl(textarea, event);

			assert.strictEqual(result, initialValue, 'The returned string should be the original value');
			assert.strictEqual(textarea.value, initialValue, 'Textarea value should not have changed');
		});

		it('should do nothing if clipboard data is empty', () => {
			const initialValue = 'Click this link';
			const textarea = createMockTextarea(initialValue, 6, 10);
			const event = createMockClipboardEvent('');

			const result = Textarea.handlePasteUrl(textarea, event);

			assert.strictEqual(result, initialValue, 'The returned string should be the original value');
			assert.strictEqual(textarea.value, initialValue, 'Textarea value should not have changed');
		});
	});
});

const createMockTextarea = (value: string, selectionStart: number, selectionEnd: number) => ({
	value,
	selectionStart,
	selectionEnd,
});

const createMockClipboardEvent = (pastedText: string) => ({
	clipboardData: {
		getData: () => pastedText,
	},
	preventDefault: () => {},
});
