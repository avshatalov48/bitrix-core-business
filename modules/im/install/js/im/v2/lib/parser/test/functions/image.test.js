import 'im.v2.test';

import { ParserIcon } from '../../src/functions/icon';
import { ParserImage } from '../../src/functions/image';

describe('ParserImage', () => {
	let getImageBlockStub = null;

	beforeEach(() => {
		getImageBlockStub = sinon.stub(ParserIcon, 'getImageBlock').returns('[Фото]');
	});

	afterEach(() => {
		getImageBlockStub.restore();
	});

	describe('purifyLink', () => {
		it('should purify a valid image link', () => {
			const link = 'https://some.domain.test/media/image.jpg';
			const result = ParserImage.purifyLink(link);
			assert.equal(result, '[Фото]');
		});

		it('should not purify a non-image link', () => {
			const link = 'https://some.domain.test/media/document.pdf';
			const result = ParserImage.purifyLink(link);
			assert.equal(result, link);
		});

		it('should not purify a link with "logout=yes"', () => {
			const link = 'https://some.domain.test/media/image.jpg?logout=yes';
			const result = ParserImage.purifyLink(link);
			assert.equal(result, link);
		});

		it('should not purify a link with "/docs/pub/"', () => {
			const link = 'https://some.domain.test/docs/pub/image.jpg';
			const result = ParserImage.purifyLink(link);
			assert.equal(result, link);
		});

		it('should purify a link with query parameters', () => {
			const link = 'https://some.domain.test/media/image.jpg?param=value&a=1';
			const result = ParserImage.purifyLink(link);
			assert.equal(result, '[Фото]');
		});

		// @see bug http://jabber.bx/view.php?id=191961
		it('should purify a link when filename duplicates in query parameter', () => {
			const link = 'https://some.domain.test/media/giphy.gif?param=giphy.gif&param2=value';

			const purifiedLink = ParserImage.purifyLink(link);

			assert.equal(purifiedLink, '[Фото]');
		});

		it('should handle text with multiple links', () => {
			const text = 'Check this image: https://some.domain.test/media/image.jpg and this one: https://some.domain.test/media/another_image.png';
			const result = ParserImage.purifyLink(text);
			assert.equal(result, 'Check this image: [Фото] and this one: [Фото]');
		});

		it('should handle text with mixed content', () => {
			const text = 'Here is a link: https://some.domain.test/media/image.jpg and some text.';
			const result = ParserImage.purifyLink(text);
			assert.equal(result, 'Here is a link: [Фото] and some text.');
		});

		it('should not purify an image link with some text before link', () => {
			const text = 'texthttps://some.domain.test/media/image.jpg';
			const result = ParserImage.purifyLink(text);
			assert.equal(result, text);
		});

		it('should not purify an image link with text "0" before link', () => {
			const text = '0https://some.domain.test/media/image.jpg';
			const result = ParserImage.purifyLink(text);
			assert.equal(result, text);
		});

		it('should purify an image link with special symbol before link', () => {
			const text = 'some>https://some.domain.test/media/image.jpg';
			const result = ParserImage.purifyLink(text);
			assert.equal(result, 'some>[Фото]');
		});
	});
});
