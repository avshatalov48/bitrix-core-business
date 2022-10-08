import {Tag, Dom, Text} from '../../src/core';
import message from '../../src/lib/loc/message';

describe('core/tag', () => {
	describe('safe', () => {
		it('Should be exported as function', () => {
			assert(typeof Tag.safe === 'function');
		});

		it('Should escape all substitutions', () => {
			let html = `<div class="name">World</div>`;
			let source = Tag.safe`Yo ${html}`;
			let result = 'Yo &lt;div class=&quot;name&quot;&gt;World&lt;/div&gt;';

			assert(source === result);
		});
	});

	describe('unsafe', () => {
		it('Should be exported as function', () => {
			assert(typeof Tag.unsafe === 'function');
		});

		it('Should unescape all substitution', () => {

		});
	});

	describe('style', () => {
		it('Should be exported as function', () => {
			assert(typeof Tag.style === 'function');
		});

		it('Should styled passed element with passed properties', () => {
			let element = document.createElement('div');
			let result = 'display: block; background: url(bitrix/images/main/test.png); color: rgb(0, 0, 0); font-size: 16px; line-height: 21px; content: \'\';';

			Tag.style(element)`
				display: ${'block'};
				background: url(bitrix/images/main/test.png);
				color: #000;
				font-size: 16px;
				line-height: 21px;
				content: '';
			`;

			assert(element.getAttribute('style') === result);
		});
	});

	describe('message', () => {
		it('Should be exported as function', () => {
			assert(typeof Tag.message === 'function');
		});

		it('Should resolves messages', () => {
			message({
				'TEST_1': 'TEST_MESSAGE_1',
				'TEST_2': 'TEST_MESSAGE_2',
				'TEST_3': 'TEST_MESSAGE_3'
			});

			let renderedResult = 'TEST_MESSAGE_1 test TEST_MESSAGE_2 TEST_MESSAGE_3';
			let result = Tag.message`${'TEST_1'} test ${'TEST_2'} ${'TEST_3'}`;

			assert(result === renderedResult);
		});
	});

	describe('attrs', () => {
		let element;

		beforeEach(() => {
			element = document.createElement('div');
		});

		it('Should be exported as function', () => {
			assert(typeof Tag.attrs === 'function');
		});

		it('Should set passed attributes', () => {
			Tag.attrs(element)`
				data-test: value;
				attrtest: true;
			`;

			assert(element.getAttribute('data-test') === 'value');
			assert(element.getAttribute('attrtest') === 'true');
		});

		it('Should set attrs without substitutions', () => {
			Tag.attrs(element)`
				data-test1: value;
				data-test2: 2;
				data-test3: true;
				data-test4: false;
			`;

			assert.ok(Dom.attr(element, 'data-test1') === 'value');
			assert.ok(Dom.attr(element, 'data-test2') === 2);
			assert.ok(Dom.attr(element, 'data-test3') === true);
			assert.ok(Dom.attr(element, 'data-test4') === false);
		});

		it('Should set string value', () => {
			Tag.attrs(element)`
				data-test1: ${'test1'};
				data-test2: ${'test2'};
				data-test3: ${'test3'};
				data-test4: ${'test4'};
			`;

			assert.ok(Dom.attr(element, 'data-test1') === 'test1');
			assert.ok(Dom.attr(element, 'data-test2') === 'test2');
			assert.ok(Dom.attr(element, 'data-test3') === 'test3');
			assert.ok(Dom.attr(element, 'data-test4') === 'test4');
		});

		it('Should set number value', () => {
			Tag.attrs(element)`
				data-test1: ${1};
				data-test2: ${1.2};
				data-test3: ${.99};
			`;

			assert.ok(Dom.attr(element, 'data-test1') === 1);
			assert.ok(Dom.attr(element, 'data-test2') === 1.2);
			assert.ok(Dom.attr(element, 'data-test3') === .99);
		});

		it('Should set boolean value', () => {
			Tag.attrs(element)`
				data-test1: ${true};
				data-test2: ${false};
			`;

			assert.ok(Dom.attr(element, 'data-test1') === true);
			assert.ok(Dom.attr(element, 'data-test2') === false);
		});

		it('Should set plain object value', () => {
			const value1 = {test1: 1};
			const value2 = {test2: [1, 2, 3]};

			Tag.attrs(element)`
				data-test1: ${value1};
				data-test2: ${value2};
			`;

			assert.deepEqual(Dom.attr(element, 'data-test1'), value1);
			assert.deepEqual(Dom.attr(element, 'data-test2'), value2);
		});

		it('Should set array value', () => {
			const value1 = [1, 2, 3];
			const value2 = [true, false, null];

			Tag.attrs(element)`
				data-test1: ${value1};
				data-test2: ${value2};
			`;

			assert.deepEqual(Dom.attr(element, 'data-test1'), value1);
			assert.deepEqual(Dom.attr(element, 'data-test2'), value2);
		});
	});
});