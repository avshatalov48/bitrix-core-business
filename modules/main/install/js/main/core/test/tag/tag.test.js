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

	describe('render', () => {
		it('Should be exported as function', () => {
			assert(typeof Tag.render === 'function');
		});

		it('Should render single element template', () => {
			let result = Tag.render`
				<div class="name"></div>
			`;

			assert(result.className === 'name');
		});

		it('Should render multiple elements template', () => {
			let result = Tag.render`
				<div class="name"></div>
				<div class="name"></div>
				<div class="name"></div>
			`;

			assert(Array.isArray(result) && result.length === 3);
		});

		it('Should render head entry', () => {
			let result = Tag.render`
				<script></script>
			`;

			assert(result.tagName === 'SCRIPT');
		});

		it('Should render multiple head entries', () => {
			let result = Tag.render`
				<script></script>
				<meta charset="utf-8">
				<title>test</title>
			`;

			assert(Array.isArray(result) && result.length === 3);
			assert(result[0].tagName === 'SCRIPT');
			assert(result[1].tagName === 'META');
			assert(result[2].tagName === 'TITLE');
		});

		it('Should support include elements', () => {
			const childElement = document.createElement('div');
			const element = Tag.render`
				<div>
					${childElement}
				</div>
			`;

			assert(element.children[0] === childElement);
		});

		it('Should support include array of elements', () => {
			const childElement1 = document.createElement('div');
			const childElement2 = document.createElement('div');

			const elements = [
				childElement1,
				childElement2
			];

			const element = Tag.render`
				<div>
					${elements}
				</div>
			`;

			assert(element.children.length === 2);
			assert(element.children[0] === elements[0]);
			assert(element.children[1] === elements[1]);
		});

		it('Should add event listener from attribute', () => {
			const spy = sinon.spy();
			const element = Tag.render`
				<div onclick="${spy}"></div>
			`;

			element.click();

			assert.ok(spy.calledOnce);
			assert.ok(!element.outerHTML.includes('onclick'));
		});

		it('Should add event listeners from multiline declaration', () => {
			const spy = sinon.spy();
			const element = Tag.render`
				<div 
					onclick="${spy}"
				></div> 
			`;

			element.click();

			assert.ok(spy.calledOnce);
			assert.ok(!element.outerHTML.includes('onclick'));
		});

		it('Should not matches attribute value', () => {
			const onclick = () => null;
			const onabort = () => null;

			const element = Tag.render`
				<input 
					type="text"
					class="test"
					value="${Text.encode(`"  onerror`)}"
					data-value="${Text.encode(`" onerror`)}"
					onclick="${onclick}"
					onabort="${onabort}"
					onautocomplete=""
					>
			`;

			assert.ok(element.value === `"  onerror`);
			assert.ok(element.getAttribute('data-value') === `" onerror`);
			assert.ok(element.hasAttribute('onclick') === false);
			assert.ok(element.hasAttribute('onabort') === false);
			assert.ok(element.hasAttribute('onautocomplete') === true);
		});

		describe('bug: 0118220', () => {
			it('Should works with string contains doctype (not document)', () => {
				const text = 'http://test.com/?doctype=1';
				const element = Tag.render`
					<div>${text}</div>
				`;

				assert.ok(element.innerHTML === text);
			});
		});

		it('Should works with string contains doctype (document)', () => {
			const title = 'Test title';
			const content = 'Test content';
			const element = Tag.render`
					<!doctype>
					<html>
						<head>
							<title>${title}</title>
						</head>
						<body>${content}</body>
					</html>
				`;

			assert.ok(element.nodeType === Node.DOCUMENT_NODE);
			assert.ok(element.title === title);
			assert.ok(element.body.innerHTML.trim() === content);
		});

		describe('Memory leak detection', () => {
			it('Should not retain result element', () => {
				let element = Tag.render`<div></div>`;

				let isElementCollected = false;
				global.weak(element, () => {
					isElementCollected = true;
				});

				element = null;

				global.gc();

				assert.ok(isElementCollected, 'Memory leak detected! "element" is not collected');
			});
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