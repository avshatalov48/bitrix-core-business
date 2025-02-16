import { wrapTextInParagraph } from '../src/helpers/wrap-text-in-paragraph';

describe('Wrap text in paragraph', () => {
	it('should wrap a simple text', () => {
		assert.equal(wrapTextInParagraph('\n\ntext'), '<p></p><p>text</p>');
		assert.equal(wrapTextInParagraph('\r\n\r\ntext'), '<p></p><p>text</p>');
		assert.equal(wrapTextInParagraph(''), '<p></p>');
		assert.equal(wrapTextInParagraph('\n\n'), '<p></p><p></p>');
		assert.equal(wrapTextInParagraph('\n\n\n\n'), '<p></p><p></p><p></p>');
		assert.equal(wrapTextInParagraph('\r\n\r\n'), '<p></p><p></p>');
		assert.equal(wrapTextInParagraph('\r\n\r\n\r\n\r\n'), '<p></p><p></p><p></p>');
		assert.equal(wrapTextInParagraph('text'), '<p>text</p>');
		assert.equal(wrapTextInParagraph('text\n'), '<p>text<br></p>');
		assert.equal(wrapTextInParagraph('text\nabc'), '<p>text<br>abc</p>');
		assert.equal(wrapTextInParagraph('text\nabc\ndefg'), '<p>text<br>abc<br>defg</p>');
		assert.equal(wrapTextInParagraph('text\r\nabc\r\ndefg'), '<p>text<br>abc<br>defg</p>');
		assert.equal(wrapTextInParagraph('text\n\nabc'), '<p>text</p><p>abc</p>');
		assert.equal(wrapTextInParagraph('text\r\n'), '<p>text<br></p>');
		assert.equal(wrapTextInParagraph('text\r\nabc'), '<p>text<br>abc</p>');
		assert.equal(wrapTextInParagraph('text\r\nabc\r\ndefg'), '<p>text<br>abc<br>defg</p>');
		assert.equal(wrapTextInParagraph('text\r\n\r\nabc'), '<p>text</p><p>abc</p>');
		assert.equal(wrapTextInParagraph('text\n\nabc\n\ndefg'), '<p>text</p><p>abc</p><p>defg</p>');
		assert.equal(wrapTextInParagraph('text\r\n\r\nabc\r\n\r\ndefg'), '<p>text</p><p>abc</p><p>defg</p>');
		assert.equal(wrapTextInParagraph('\n\n\ntext'), '<p></p><p><br>text</p>');
		assert.equal(wrapTextInParagraph('\n\n\n\ntext'), '<p></p><p></p><p>text</p>');
		assert.equal(wrapTextInParagraph('\n\ntext\n\n'), '<p></p><p>text</p><p></p>');
		assert.equal(wrapTextInParagraph('\n\n\ntext\n\n\n'), '<p></p><p><br>text</p><p><br></p>');
		assert.equal(wrapTextInParagraph('\n\n\n\ntext\n\n\n\n'), '<p></p><p></p><p>text</p><p></p><p></p>');
		assert.equal(wrapTextInParagraph('\ntext'), '<p><br>text</p>');
		assert.equal(wrapTextInParagraph('\ntext\n'), '<p><br>text<br></p>');
		assert.equal(wrapTextInParagraph('\r\n\r\n\r\ntext'), '<p></p><p><br>text</p>');
		assert.equal(wrapTextInParagraph('\r\n\r\n\r\n\r\ntext'), '<p></p><p></p><p>text</p>');
		assert.equal(wrapTextInParagraph('\r\n\r\ntext\r\n\r\n'), '<p></p><p>text</p><p></p>');
		assert.equal(wrapTextInParagraph('\r\n\r\n\r\ntext\r\n\r\n\r\n'), '<p></p><p><br>text</p><p><br></p>');
		assert.equal(wrapTextInParagraph('\r\n\r\n\r\n\r\ntext\r\n\r\n\r\n\r\n'), '<p></p><p></p><p>text</p><p></p><p></p>');
		assert.equal(wrapTextInParagraph('\r\ntext'), '<p><br>text</p>');
		assert.equal(wrapTextInParagraph('\r\ntext\r\n'), '<p><br>text<br></p>');

		assert.equal(wrapTextInParagraph('\none\ntwo\n\nthree\n\n\nfour'), '<p><br>one<br>two</p><p>three</p><p><br>four</p>');
	});
});
