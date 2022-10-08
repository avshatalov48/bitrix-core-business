import parseTag from '../../src/lib/tag/internal/parse-tag';
import parseText from '../../src/lib/tag/internal/parse-text';
import {Tag, Text} from '../../src/core';

function performanceTest(callback: () => void, count = 10)
{
	const times = Array.from({length: count - 1}, () => {
		const startTime = +new Date();
		callback();
		const endTime = +new Date();
		return endTime - startTime
	});

	const min = Math.min(...times);
	const max = Math.max(...times);
	const iterations = times.length + 1;
	const total = times.reduce((acc, time) => acc + time, 0);
	const avg = total / iterations;

	return {min, max, avg, total, iterations};
}

describe('tag/render', () => {
	describe('tag/render/parseTag', () => {
		it('Div without attributes', () => {
			const result1 = parseTag(`<div>`);
			assert.deepEqual(
				result1,
				{
					type: 'tag',
					name: 'div',
					svg: false,
					attrs: {},
					children: [],
					voidElement: false,
				},
			);
		});

		it('Div with attributes', () => {
			const result1 = parseTag(`<div class="class1 class-2 classThree" data-test="1" disabled>`);
			assert.deepEqual(
				result1,
				{
					type: 'tag',
					name: 'div',
					svg: false,
					attrs: {
						class: 'class1 class-2 classThree',
						'data-test': '1',
						disabled: '',
					},
					children: [],
					voidElement: false,
				},
			);
		});

		it('Div with attributes and multiline formatting', () => {
			const result1 = parseTag(
				`<div 
					class="class1 class-2 classThree" 
					data-test="1"
					disabled
				>`,
			);
			assert.deepEqual(
				result1,
				{
					type: 'tag',
					name: 'div',
					svg: false,
					attrs: {
						class: 'class1 class-2 classThree',
						'data-test': '1',
						disabled: '',
					},
					children: [],
					voidElement: false,
				},
			)
		});

		it('Void elements without attributes', () => {
			const result1 = parseTag(`<br>`);
			assert.deepEqual(
				result1,
				{
					type: 'tag',
					name: 'br',
					svg: false,
					attrs: {},
					children: [],
					voidElement: true,
				},
			);

			const result2 = parseTag(`</br>`);
			assert.deepEqual(
				result2,
				{
					type: 'tag',
					name: 'br',
					svg: false,
					attrs: {},
					children: [],
					voidElement: true,
				},
			);
		});

		it('Void element with attributes', () => {
			const result1 = parseTag(`<hr style="border: none;">`);
			assert.deepEqual(
				result1,
				{
					type: 'tag',
					name: 'hr',
					svg: false,
					attrs: {
						style: 'border: none;',
					},
					children: [],
					voidElement: true,
				},
			)
		});

		it('Html comment', () => {
			const result1 = parseTag(`<!-- Test comment -->`);
			assert.deepEqual(
				result1,
				{
					type: 'comment',
					content: ' Test comment '
				},
			)
		});
	});

	describe('tag/render/parseText', () => {
		it('Text with substitutions', () => {
			const result = parseText(
				`Text1 {{uid1}}text2{{uid2}} text3 {{uid3}}`,
			);

			assert.deepEqual(
				result,
				[
					{type: 'text', content: 'Text1 '},
					{type: 'placeholder', uid: 1},
					{type: 'text', content: 'text2'},
					{type: 'placeholder', uid: 2},
					{type: 'text', content: ' text3 '},
					{type: 'placeholder', uid: 3},
				],
			);
		});

		it('Text with substitutions multiline', () => {
			const result = parseText(
				`Text1 {{uid1}}
				text2{{uid2}}
				text3
				{{uid3}}`,
			);

			assert.deepEqual(
				result,
				[
					{type: 'text', content: 'Text1 '},
					{type: 'placeholder', uid: 1},
					{type: 'text', content: 'text2'},
					{type: 'placeholder', uid: 2},
					{type: 'text', content: 'text3'},
					{type: 'placeholder', uid: 3},
				],
			);
		});

		it('Text only multiline', () => {
			const result = parseText(
				`Test
				text
				only`,
			);

			assert.deepEqual(
				result,
				[
					{type: 'text', content: 'Test'},
					{type: 'text', content: 'text'},
					{type: 'text', content: 'only'},
				],
			);
		});

		it('Substitutions only', () => {
			const result = parseText(
				`{{uid1}}{{uid2}}{{uid3}}`,
			);

			assert.deepEqual(
				result,
				[
					{type: 'placeholder', uid: 1},
					{type: 'placeholder', uid: 2},
					{type: 'placeholder', uid: 3},
				],
			);
		});

		it('Substitutions only multiline', () => {
			const result = parseText(
				`{{uid1}}
				{{uid2}}
				{{uid3}}`,
			);

			assert.deepEqual(
				result,
				[
					{type: 'placeholder', uid: 1},
					{type: 'placeholder', uid: 2},
					{type: 'placeholder', uid: 3},
				],
			);
		});
	});

	describe('render', () => {
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

			assert.ok(element.value === `"  onerror`, 'attr#value');
			assert.ok(element.getAttribute('data-value') === `" onerror`, 'attr#data-value');
			assert.ok(element.hasAttribute('onclick') === false, 'attr#onclick');
			assert.ok(element.hasAttribute('onabort') === false, 'attr#onabort');
			assert.ok(element.hasAttribute('onautocomplete') === true, 'attr#onautocomplete');
		});

		it('Should works with comments', () => {
			const result = Tag.render`
				<div>
					<!--Comment-->
					<!-- Comment2 -->
					<!--<div></div>-->
					<!--<div>
						<span></span>
					</div>-->
				</div>
			`;

			assert.ok(
				result.outerHTML,
				`<div><!--Comment--><!-- Comment2 --><!--<div></div>--><!--<div><span></span></div>--></div>`,
			);
		});

		it('Tag with multiline attribute value', () => {
			const element = Tag.render`
				<div class="class1
					class2
					class3
					class4"
					data-test="11">Test</div>
			`;

			assert.equal(
				element.outerHTML,
				`<div class="class1
class2
class3
class4" data-test="11">Test</div>`
			);
		});

		it('Layout with comment', () => {
			const getElement3 = () => Tag.render`<div class="element3"></div>`;
			const element = Tag.render`
				<div class="container">
					<div class="inner">
						<div class="element1"></div>
						<!--<div class="element2"></div>-->
						${getElement3()}
						<div class="element4"></div>
					</div>
				</div>
			`;

			assert.equal(
				element.outerHTML,
				`<div class="container"><div class="inner"><div class="element1"></div><!--<div class="element2"></div>--><div class="element3"></div><div class="element4"></div></div></div>`,
			);
		});

		it('Link', () => {
			const element = Tag.render`
				<a href="/workgroups/group/1/tasks/?tab=plan">Test link</a>
			`;

			assert.equal(
				element.outerHTML,
				'<a href="/workgroups/group/1/tasks/?tab=plan">Test link</a>',
			);
		});

		it('Placeholder only', () => {
			const childElement = document.createElement('div');
			const element = Tag.render`${childElement}`;
			assert.equal(element.outerHTML, '<div></div>');

			const childElement2 = document.createElement('div');
			const element2 = Tag.render`
				${childElement2}
			`;
			assert.equal(element2.outerHTML, '<div></div>');
		});

		it('Should works with any placeholders value', () => {
			const element1 = Tag.render`
				<input type="text" value="${{v: 1}}"/>
			`;
			assert.equal(
				element1.outerHTML,
				'<input type="text" value="[object Object]">',
			);

			const element2 = Tag.render`
				<input type="text" value="${[1, 2]}"/>
			`;
			assert.equal(
				element2.outerHTML,
				'<input type="text" value="1,2">',
			);

			const element3 = Tag.render`
				<input type="text" value="${1}"/>
			`;
			assert.equal(
				element3.outerHTML,
				'<input type="text" value="1">',
			);

			const element4 = Tag.render`
				<input type="text" value="${'1'}"/>
			`;
			assert.equal(
				element4.outerHTML,
				'<input type="text" value="1">',
			);
		});

		it('Should works with once events', () => {
			const spy = sinon.spy();
			const element = Tag.render`
				<span onclickonce="${spy}"></span>
			`;

			element.click();
			element.click();

			assert.ok(spy.calledOnce);
		});

		it('Should works with template tag', () => {
			const element = Tag.render`
				<div>
					<template id="template">
						<div class="template-content-1">
							<span>Test 1</span>
						</div>
						Any test text 2
						<span>Test 3</span>
					</template>
				</div>
			`;

			assert.ok(element.tagName === 'DIV', 'Root element is not a div');
			assert.ok(element.firstChild.tagName === 'TEMPLATE', 'First child is not a template');
			assert.ok(element.firstChild.content.childNodes[0].tagName === 'DIV');
			assert.ok(element.firstChild.content.childNodes[0].innerHTML === '<span>Test 1</span>');
			assert.ok(element.firstChild.content.childNodes[1].nodeType === 3);
			assert.ok(element.firstChild.content.childNodes[2].tagName === 'SPAN');
			assert.ok(element.firstChild.content.childNodes[2].textContent === 'Test 3');
		});

		it('Should works with svg void elements', () => {
			const element = Tag.render`
				<div>
					000
					<div class="main-file-input-camera-block-image">
						111
						<div class="main-file-input-user-loader-item">
							<div class="main-file-input-loader">
								<svg class="main-file-input-circular" viewBox="25 25 50 50">
									<circle class="main-file-input-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>
								</svg>
							</div>
						</div>
						222
						<div class="main-file-input-error">
							<span>
								test1
							</span>
							<span data-bx-role="tab-camera-error"></span>
						</div>
						333
						<div class="main-file-input-camera-block-image-inner">
							<video autoplay></video>
						</div>
					</div>
					444
					<div class="main-file-input-button-layout" data-bx-role="camera-button">
						<div class="main-file-input-button">
							<span class="main-file-input-button-icon"></span>
						</div>
					</div>
				</div>
			`;

			assert.ok(element.childNodes[0].nodeType === 3);
			assert.ok(element.childNodes[0].textContent === '000');
			assert.ok(element.querySelector('.main-file-input-camera-block-image'));
			assert.ok(element.querySelector('.main-file-input-camera-block-image').childNodes[0].nodeType === 3);
			assert.ok(element.querySelector('.main-file-input-camera-block-image > .main-file-input-user-loader-item'));
			assert.ok(element.querySelector('.main-file-input-camera-block-image > .main-file-input-user-loader-item > .main-file-input-loader'));
			assert.ok(element.querySelector('.main-file-input-camera-block-image > .main-file-input-user-loader-item > .main-file-input-loader > .main-file-input-circular'));
			assert.ok(element.querySelector('.main-file-input-camera-block-image > .main-file-input-user-loader-item > .main-file-input-loader > .main-file-input-circular > .main-file-input-path'));

			assert.ok(element.querySelector('.main-file-input-camera-block-image').childNodes[2].nodeType === 3);
			assert.ok(element.querySelector('.main-file-input-camera-block-image > .main-file-input-error'));
			assert.ok(element.querySelector('.main-file-input-camera-block-image > .main-file-input-error').firstChild.tagName === 'SPAN');
			assert.ok(element.querySelector('.main-file-input-camera-block-image > .main-file-input-error').lastChild.tagName === 'SPAN');

			assert.ok(element.querySelector('.main-file-input-camera-block-image').childNodes[4].nodeType === 3);
			assert.ok(element.querySelector('.main-file-input-camera-block-image > .main-file-input-camera-block-image-inner'));
			assert.ok(element.querySelector('.main-file-input-camera-block-image > .main-file-input-camera-block-image-inner > video'));

			assert.ok(element.childNodes[2].textContent === '444');
			assert.ok(element.querySelector('.main-file-input-button-layout'));
			assert.ok(element.querySelector('.main-file-input-button-layout > .main-file-input-button'));
			assert.ok(element.querySelector('.main-file-input-button-layout > .main-file-input-button > .main-file-input-button-icon'));
		});

		it('Should works with bad characters', () => {
			const element1 = Tag.render`
				<div title="${Text.encode('"><b>xss</b>')}"></div>
			`;
			assert.equal(
				element1.outerHTML,
				`<div title="&quot;><b>xss</b>"></div>`,
			);

			const element2 = Tag.render`
				<div title="${Text.encode(JSON.stringify({test: '2'}))}"></div>
			`;
			assert.equal(
				element2.outerHTML,
				`<div title="{&quot;test&quot;:&quot;2&quot;}"></div>`,
			);
		});

		it('Should works with style tag', () => {
			const element = Tag.render`
				<style>body {padding: 20px;}</style>
			`;

			assert.ok(element instanceof global.window.HTMLStyleElement);
			assert.equal(
				element.outerHTML,
				`<style>body {padding: 20px;}</style>`,
			);
		});

		it('Should works with any attributes formatting (double quotes)', () => {
			const element = Tag.render`
				<div
					data-test="testValue"
					data-test2 = "testValue2"
					data-url="/workgroups/group/1/tasks/?tab=plan"
					data-url-2 = "/workgroups/group/1/tasks/?tab=plan2"
					style="background-image: url('/image.svg'); opacity: 0.35;"
					title="title1"
					title2 = "title2"
					role=
						"alert"
					data-role = 
						"test"
					class="class1
						class2
						class3
					class4
					class5"
					checked
				>Any text</div>
			`;

			assert.ok(element.tagName === 'DIV');
			assert.ok(element.innerHTML === 'Any text');

			assert.ok(element.getAttribute('data-test') === 'testValue');
			assert.ok(element.getAttribute('data-test2') === 'testValue2');
			assert.ok(element.getAttribute('data-url') === '/workgroups/group/1/tasks/?tab=plan');
			assert.ok(element.getAttribute('data-url-2') === '/workgroups/group/1/tasks/?tab=plan2');
			assert.ok(element.getAttribute('title') === 'title1');
			assert.ok(element.getAttribute('title2') === 'title2');
			assert.ok(element.getAttribute('role') === 'alert');
			assert.ok(element.getAttribute('data-role') === 'test');
			assert.ok(element.getAttribute('class') === 'class1\nclass2\nclass3\nclass4\nclass5');
			assert.ok(element.getAttribute('checked') === '');
			assert.ok(element.style.backgroundImage === 'url(/image.svg)');
			assert.ok(element.style.opacity === '0.35');
		});

		it('Should works with any attributes formatting (single quotes)', () => {
			const element = Tag.render`
				<div
					data-test='testValue'
					data-test2 = 'testValue2'
					data-url='/workgroups/group/1/tasks/?tab=plan'
					data-url-2 = '/workgroups/group/1/tasks/?tab=plan2'
					style='background-image: url("/image.svg"); opacity: 0.35;'
					title='title1'
					title2 = 'title2'
					role=
						'alert'
					data-role = 
						'test'
					class='class1
						class2
						class3
					class4
					class5'
					checked
				>Any text</div>
			`;

			assert.ok(element.tagName === 'DIV');
			assert.ok(element.innerHTML === 'Any text');

			assert.ok(element.getAttribute('data-test') === 'testValue');
			assert.ok(element.getAttribute('data-test2') === 'testValue2');
			assert.ok(element.getAttribute('data-url') === '/workgroups/group/1/tasks/?tab=plan');
			assert.ok(element.getAttribute('data-url-2') === '/workgroups/group/1/tasks/?tab=plan2');
			assert.ok(element.getAttribute('title') === 'title1');
			assert.ok(element.getAttribute('title2') === 'title2');
			assert.ok(element.getAttribute('role') === 'alert');
			assert.ok(element.getAttribute('data-role') === 'test');
			assert.ok(element.getAttribute('class') === 'class1\nclass2\nclass3\nclass4\nclass5');
			assert.ok(element.getAttribute('checked') === '');
			assert.ok(element.style.backgroundImage === 'url(/image.svg)');
			assert.ok(element.style.opacity === '0.35');
		});

		it('Should works with any attributes formatting (void element)', () => {
			const element = Tag.render`
				<hr
					data-test='testValue'
					data-test2 = 'testValue2'
					data-url='/workgroups/group/1/tasks/?tab=plan'
					data-url-2 = '/workgroups/group/1/tasks/?tab=plan2'
					title='title1'
					title2 = 'title2'
					role=
						'alert'
					data-role = 
						'test'
					class='class1
						class2
						class3
					class4
					class5'
					checked
				>
			`;

			assert.ok(element.tagName === 'HR');

			assert.ok(element.getAttribute('data-test') === 'testValue');
			assert.ok(element.getAttribute('data-test2') === 'testValue2');
			assert.ok(element.getAttribute('data-url') === '/workgroups/group/1/tasks/?tab=plan');
			assert.ok(element.getAttribute('data-url-2') === '/workgroups/group/1/tasks/?tab=plan2');
			assert.ok(element.getAttribute('title') === 'title1');
			assert.ok(element.getAttribute('title2') === 'title2');
			assert.ok(element.getAttribute('role') === 'alert');
			assert.ok(element.getAttribute('data-role') === 'test');
			assert.ok(element.getAttribute('class') === 'class1\nclass2\nclass3\nclass4\nclass5');
		});

		it('Should works with any attributes formatting (svg void element)', () => {
			const element = Tag.render`
				<svg>
					<path
						d=""
						data-test='testValue'
						data-test2 = 'testValue2'
						data-url='/workgroups/group/1/tasks/?tab=plan'
						data-url-2 = '/workgroups/group/1/tasks/?tab=plan2'
						title='title1'
						title2 = 'title2'
						role=
							'alert'
						data-role = 
							'test'
						class='class1
							class2
							class3
						class4
						class5'
						checked
					/>
				</svg>
			`;

			assert.ok(element.tagName === 'svg');

			const child = element.firstChild;
			assert.ok(child.tagName === 'path');

			assert.ok(child.getAttribute('data-test') === 'testValue');
			assert.ok(child.getAttribute('data-test2') === 'testValue2');
			assert.ok(child.getAttribute('data-url') === '/workgroups/group/1/tasks/?tab=plan');
			assert.ok(child.getAttribute('data-url-2') === '/workgroups/group/1/tasks/?tab=plan2');
			assert.ok(child.getAttribute('title') === 'title1');
			assert.ok(child.getAttribute('title2') === 'title2');
			assert.ok(child.getAttribute('role') === 'alert');
			assert.ok(child.getAttribute('data-role') === 'test');
			assert.ok(child.getAttribute('class') === 'class1\nclass2\nclass3\nclass4\nclass5');
		});

		it('Should works with any allowed attribute names', () => {
			const element = Tag.render`
				<div xml:lang="ru" my:custom dot.name="val2" dot.test data-test_name="val1">Any text</div>
			`;

			assert.ok(element.tagName === 'DIV');
			assert.ok(element.getAttribute('xml:lang') === 'ru');
			assert.ok(element.getAttribute('my:custom') === '');
			assert.ok(element.getAttribute('dot.name') === 'val2');
			assert.ok(element.getAttribute('dot.test') === '');
			assert.ok(element.getAttribute('data-test_name') === 'val1');
		});

		it('Should works with mixed attribute value', () => {
			const class3 = undefined;
			const class4 = 'class4';
			const class5 = 'class5';
			const element = Tag.render`
				<div class="test1 test2 uid1
					${class3} ${class4} ${class5}">
					<span>test</span>
				</div>
			`;

			assert.equal(
				element.outerHTML,
				`<div class="test1 test2 uid1
undefined class4 class5"><span>test</span></div>`
			);
		});

		describe('render svg', () => {
			it('Should render svg element', () => {
				const result = Tag.render`
					<svg class="main-ui-loader-svg" viewBox="25 25 50 50">
						<circle class="main-ui-loader-svg-circle" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10">
					</svg>
				`;

				assert.ok(result instanceof window.SVGSVGElement, 'Result is not a SVGSVGElement');
				assert.ok(result.attributes.class.nodeValue === 'main-ui-loader-svg', 'Invalid class attribute');
				assert.ok(result.attributes.viewBox.nodeValue === '25 25 50 50', 'Invalid viewBox attribute');
				assert.ok(result.children.length === 1, 'Result contains more than one child');
				assert.ok(result.children[0] instanceof window.SVGElement, 'First child is not a SVGElement');
				assert.ok(result.children[0].nodeName === 'circle', 'First child node name is not a circle');
				assert.ok(result.children[0].attributes.class.nodeValue === 'main-ui-loader-svg-circle', 'Invalid class attribute of children');
				assert.ok(result.children[0].attributes.cx.nodeValue === '50', 'Invalid cx attribute');
				assert.ok(result.children[0].attributes.cy.nodeValue === '50', 'Invalid cy attribute');
				assert.ok(result.children[0].attributes.r.nodeValue === '20', 'Invalid r attribute');
				assert.ok(result.children[0].attributes.fill.nodeValue === 'none', 'Invalid fill attribute');
				assert.ok(result.children[0].attributes['stroke-miterlimit'].nodeValue === '10', 'Invalid strokeMiterlimit attribute');
			});
		});

		describe('Performance', () => {
			it('Should be create 2000 simple items in no more than 100 milliseconds (avg)', () => {
				const times = performanceTest(() => {
					Array.from({length: 2000}, (value) => {
						Tag.render` 
							<div class="my-class-${value}"></div>
						`;
					});
				});

				assert.ok(times.avg <= 100);
			});

			it('Should be create 300 big items in no more than 400 milliseconds (avg)', () => {
				const times = performanceTest(() => {
					Array.from({length: 300}, (value) => {
						Tag.render`
							<div class="my-class-${value}">
								<div class="inner">
									<span class="title-${value}" data-value="${value}"></span>
									<span class="descr-${value}" data-value2="${value}"></span>
									<span 
										class="ui-btn"
										onclick="${() => {}}"
										onmousedown="${() => {}}"
										onmouseenter="${() => {}}"
										onmouseleave="${() => {}}"
									>Click Me</span>
									<table>
										<thead>
											<th>
												<td>Col 1</td>
												<td>Col 2</td>
												<td>Col 3</td>
											</th>
										</thead>
										<tbody>
											<tr>
												<td>Data 1</td>
												<td>Data 2</td>
												<td>Data 3</td>
											</tr>
											<tr>
												<td>Data 1</td>
												<td>Data 2</td>
												<td>Data 3</td>
											</tr>
											<tr>
												<td>Data 1</td>
												<td>Data 2</td>
												<td>Data 3</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						`;
					});
				});

				assert.ok(times.avg <= 300);
			});
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
});