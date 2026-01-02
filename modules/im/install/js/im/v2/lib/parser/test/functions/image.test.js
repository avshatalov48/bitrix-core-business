import 'im.v2.test';
import { Dom, Tag } from 'main.core';

import { ParserIcon } from '../../src/functions/icon';
import { ParserImage, ImageBbCodeSizes } from '../../src/functions/image';
import { Parser } from '../../src/parser';
import { FileUtil } from '../../../utils/src/file';
import * as CoreProxy from '../../src/utils/core-proxy';

describe('ParserImage', () => {
	let getImageBlockStub = null;
	let utilsStub = null;
	let coreStub = null;

	beforeEach(() => {
		getImageBlockStub = sinon.stub(ParserIcon, 'getImageBlock').returns('[Photo]');
		utilsStub = sinon.stub(CoreProxy, 'getUtils').returns({
			text: { checkUrl: () => true },
			file: { getViewerDataForImageSrc: () => imageBbCodeConfig },
		});
		coreStub = sinon.stub(CoreProxy, 'getCore').returns({
			store: { getters: { 'chats/get': () => 1 } },
		});
	});

	afterEach(() => {
		getImageBlockStub.restore();
		utilsStub.restore();
		coreStub.restore();
	});

	describe('purifyLink', () => {
		it('should purify a valid image link', () => {
			const link = 'https://some.domain.test/media/image.jpg';
			const result = ParserImage.purifyLink(link);
			assert.equal(result, '[Photo]');
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
			assert.equal(result, '[Photo]');
		});

		// @see bug http://jabber.bx/view.php?id=191961
		it('should purify a link when filename duplicates in query parameter', () => {
			const link = 'https://some.domain.test/media/giphy.gif?param=giphy.gif&param2=value';

			const purifiedLink = ParserImage.purifyLink(link);

			assert.equal(purifiedLink, '[Photo]');
		});

		it('should handle text with multiple links', () => {
			const text = 'Check this image: https://some.domain.test/media/image.jpg and this one: https://some.domain.test/media/another_image.png';
			const result = ParserImage.purifyLink(text);
			assert.equal(result, 'Check this image: [Photo] and this one: [Photo]');
		});

		it('should handle text with mixed content', () => {
			const text = 'Here is a link: https://some.domain.test/media/image.jpg and some text.';
			const result = ParserImage.purifyLink(text);
			assert.equal(result, 'Here is a link: [Photo] and some text.');
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
			assert.equal(result, 'some>[Photo]');
		});
	});

	describe('purifyImageBbCode', () => {
		const IMAGE_PLACEHOLDER = '[Photo]';

		it('should purify valid [img] tag', () => {
			const text = `[img size=${IMAGE_SIZE_SMALL}]https://some.domain/image.jpg[/img]`;
			const result = ParserImage.purifyImageBbCode(text);
			assert.equal(result, IMAGE_PLACEHOLDER);
		});

		it('should not purify tag with invalid size', () => {
			const text = '[img size=huge]https://some.domain/image.jpg[/img]';
			const result = ParserImage.purifyImageBbCode(text);
			assert.equal(result, text);
		});

		it('should purify valid [img] tag with any content inside', () => {
			const text = `[img size=${IMAGE_SIZE_SMALL}]<img />[/img]`;
			const result = ParserImage.purifyImageBbCode(text);
			assert.equal(result, IMAGE_PLACEHOLDER);
		});

		it('should purify multiple image BBCodes', () => {
			const text = `[img size=${IMAGE_SIZE_SMALL}]${IMAGE_SRC}[/img] and [img size=${IMAGE_SIZE_SMALL}]${IMAGE_SRC}[/img]`;
			const result = ParserImage.purifyImageBbCode(text);
			assert.equal(result, `${IMAGE_PLACEHOLDER} and ${IMAGE_PLACEHOLDER}`);
		});

		it('should not purify incorrect BBCode tag', () => {
			const text = `[img size=${IMAGE_SIZE_SMALL}]${IMAGE_SRC}`;
			const result = ParserImage.purifyImageBbCode(text);
			assert.equal(result, text);
		});
	});

	describe('decodeLink', () => {
		it('should convert a valid image from [url]', () => {
			const text = `[url]${IMAGE_SRC}[/url]`;
			const expectedResult = getImageHTMLFromUrl(IMAGE_SRC);

			const actualResult = Parser.decode({ text });

			assert.equal(actualResult, expectedResult);
		});

		it('should convert a valid image from both [url] and [img]', () => {
			const text = `[url]${IMAGE_SRC}[/url] [img size=${IMAGE_SIZE_SMALL}][url]${IMAGE_SRC}[/url] [/img]`;
			const expectedResult = `${getImageHTMLFromUrl(IMAGE_SRC)} ${getImageHTMLFromBbCode(IMAGE_SIZE_SMALL, IMAGE_SRC)}`;

			const actualResult = Parser.decode({ text });

			assert.equal(actualResult, expectedResult);
		});

		it('should not convert an image from [url] with non-image extension', () => {
			const text = '[url]https://example.com/file.txt[/url]';
			const expectedResult = '<a href="https://example.com/file.txt" target="_blank">https://example.com/file.txt</a>';

			const actualResult = Parser.decode({ text });

			assert.equal(actualResult, expectedResult);
		});
	});

	describe('decodeImageBbCode', () => {
		it('should return empty string for empty input', () => {
			assert.equal(ParserImage.decodeImageBbCode(''), '');
			assert.equal(ParserImage.decodeImageBbCode(null), '');
		});

		it('should convert valid BB code with space after link', () => {
			const text = `[img size=${IMAGE_SIZE_SMALL}][url]${IMAGE_SRC}[/url] [/img]`;
			const expectedResult = getImageHTMLFromBbCode(IMAGE_SIZE_SMALL, IMAGE_SRC);

			const actualResult = Parser.decode({ text });

			assert.equal(actualResult, expectedResult);
		});

		it('should convert valid BB code if space after link is missing', () => {
			const text = `[img size=${IMAGE_SIZE_SMALL}][url]${IMAGE_SRC}[/url][/img]`;
			const expectedResult = getImageHTMLFromBbCode(IMAGE_SIZE_SMALL, IMAGE_SRC);

			const actualResult = Parser.decode({ text });

			assert.equal(actualResult, expectedResult);
		});

		it('should convert valid BB code if url BB code is missing', () => {
			const text = `[img size=${IMAGE_SIZE_SMALL}]${IMAGE_SRC} [/img]`;
			const expectedResult = getImageHTMLFromBbCode(IMAGE_SIZE_SMALL, IMAGE_SRC);

			const actualResult = Parser.decode({ text });

			assert.equal(actualResult, expectedResult);
		});

		it('should ignore non-image file types', () => {
			const text = `[img size=${IMAGE_SIZE_SMALL}]https://example.com/file.txt [/img]`;

			const result = Parser.decode({ text });

			assert.equal(result, text);
		});

		it('should ignore invalid size', () => {
			const text = `[img size=giant][url]${IMAGE_SRC}[/url] [/img]`;
			const expectedResult = `[img size=giant]${IMAGE_SRC} [/img]`;

			const actualResult = Parser.decode({ text });

			assert.equal(actualResult, expectedResult);
		});

		it('should ignore [img] tag without size attribute', () => {
			const text = `[img][url]${IMAGE_SRC}[/url] [/img]`;
			const expectedResult = `[img]${IMAGE_SRC} [/img]`;

			const actualResult = Parser.decode({ text });

			assert.equal(actualResult, expectedResult);
		});

		it('should ignore [img] tag with non-URL content', () => {
			const text = `[img size=${IMAGE_SIZE_SMALL}][url]not-a-link[/url] [/img]`;
			const expectedResult = `[img size=${IMAGE_SIZE_SMALL}]not-a-link [/img]`;

			const actualResult = Parser.decode({ text });

			assert.equal(actualResult, expectedResult);
		});

		it('should preserve text before and after the [img] tag', () => {
			const text = `Hello![img size=${IMAGE_SIZE_SMALL}][url]${IMAGE_SRC}[/url] [/img]Bye!`;
			const messageHTML = getImageHTMLFromBbCode(IMAGE_SIZE_SMALL, IMAGE_SRC);
			const expectedResult = `Hello!${messageHTML}Bye!`;

			const actualResult = Parser.decode({ text });

			assert.equal(actualResult, expectedResult);
		});

		it('should create viewer data attributes with correct values', () => {
			const text = `[img size=${IMAGE_SIZE_SMALL}][url]${IMAGE_SRC}[/url] [/img]`;
			const expectedAttrs = [
				'data-actions',
				'data-src',
				'data-title',
				'data-viewer-group-by',
				'data-viewer-type',
			];

			const resultHtml = Parser.decode({ text, contextDialogId: '1' });

			const container = document.createElement('div');
			container.innerHTML = resultHtml;
			const img = container.querySelector('img');

			assert.ok(img, 'Image element should exist');
			for (const attr of expectedAttrs)
			{
				assert.ok(img.hasAttribute(attr), `Attribute ${attr} should be present`);
			}
		});
	});
});

const getImageHTMLFromBbCode = (size: string, url: string): string => {
	const messageNode = Tag.render`
		<a class="bx-im-message-image bx-im-message-image--${size}">
			<img
				class="bx-im-message-image-source"
				src="${url}"
			>
		</a>
	`;

	Dom.attr(messageNode.firstChild, imageBbCodeConfig);

	return messageNode.outerHTML;
};

const getImageHTMLFromUrl = (url: string): string => {
	const messageNode = Tag.render`
		<a href="${url}" target="_blank">
			<span class="bx-im-message-image">
				<img class="bx-im-message-image-source" src="${url}">
			</span>
		</a>
	`;

	return messageNode.outerHTML;
};

const IMAGE_SRC = 'https://example.com/image.jpg';
const IMAGE_SIZE_SMALL = ImageBbCodeSizes.small;
const imageBbCodeConfig = FileUtil.getViewerDataForImageSrc({ src: IMAGE_SRC, viewerGroupBy: '1' });
