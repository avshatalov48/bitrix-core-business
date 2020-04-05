import Dom from '../../src/lib/dom';

describe('Dom', () => {
	describe('#addClass', () => {
		describe('HTMLElement', () => {
			it('Should add class for HTMLElement if passed single string name', () => {
				const element = document.createElement('div');

				Dom.addClass(element, 'test');
				assert(element.className === 'test');
			});

			it('Should add class for HTMLElement if passed multiple string name', () => {
				const element = document.createElement('div');

				Dom.addClass(element, 'test1 test2 test3');
				assert(element.className === 'test1 test2 test3');
			});

			it('Should add unique classname', () => {
				const element = document.createElement('div');

				Dom.addClass(element, 'test1 test2 test3');
				assert(element.className === 'test1 test2 test3');

				Dom.addClass(element, 'test1');
				assert(element.className === 'test1 test2 test3');

				Dom.addClass(element, 'test1 test2');
				assert(element.className === 'test1 test2 test3');
			});

			it('Should add array of names', () => {
				const element = document.createElement('div');

				Dom.addClass(element, ['test1', 'test2', 'test3']);
				assert(element.className === 'test1 test2 test3');
			});
		});

		describe('SVG', () => {
			it('Should add class for SVG if passed single string name', () => {
				const element = document.createElementNS('http://www.w3.org/2000/svg', 'div');

				Dom.addClass(element, 'test');
				assert(element.getAttribute('class') === 'test');
			});

			it('Should add class for SVG if passed multiple string name', () => {
				const element = document.createElementNS('http://www.w3.org/2000/svg', 'div');

				Dom.addClass(element, 'test1 test2 test3');
				assert(element.getAttribute('class') === 'test1 test2 test3');
			});

			it('Should add unique classname', () => {
				const element = document.createElementNS('http://www.w3.org/2000/svg', 'div');

				Dom.addClass(element, 'test1 test2 test3');
				assert(element.getAttribute('class') === 'test1 test2 test3');

				Dom.addClass(element, 'test1');
				assert(element.getAttribute('class') === 'test1 test2 test3');

				Dom.addClass(element, 'test1 test2');
				assert(element.getAttribute('class') === 'test1 test2 test3');
			});

			it('Should add array of names', () => {
				const element = document.createElementNS('http://www.w3.org/2000/svg', 'div');

				Dom.addClass(element, ['test1', 'test2', 'test3']);
				assert(element.getAttribute('class') === 'test1 test2 test3');
			});
		});

		describe('Memory leak detection', () => {
			it('Should not leak if passed single string class name', () => {
				let element = document.createElement('div');
				let className = 'test-class';
				Dom.addClass(element, className);

				let isElementCollected = false;
				global.weak(element, () => {
					isElementCollected = true;
				});

				element = null;

				global.gc();

				assert.ok(isElementCollected, 'Memory leak detected! "Element" is not collected');
			});

			it('Should not leak if passed array of names', () => {
				let element = document.createElement('div');
				let className = ['test-class', 'test-class2', 'test-class3'];
				Dom.addClass(element, className);

				let isElementCollected = false;
				global.weak(element, () => {
					isElementCollected = true;
				});

				let isClassNameCollected = false;
				global.weak(className, () => {
					isClassNameCollected = true;
				});

				element = null;
				className = null;

				global.gc();

				assert.ok(isElementCollected, 'Memory leak detected! "Element" is not collected');
				assert.ok(isClassNameCollected, 'Memory leak detected! "className" is not collected');
			});
		});
	});

	describe('#hasClass', () => {
		describe('HTML', () => {
			it('Should return true if element className includes passed name', () => {
				const element = document.createElement('div');
				element.className = 'test1 test2 test3';

				assert(Dom.hasClass(element, 'test1'));
				assert(Dom.hasClass(element, 'test2'));
				assert(Dom.hasClass(element, 'test3'));
			});

			it('Should return true if element className includes passed multiple name string', () => {
				const element = document.createElement('div');
				element.className = 'test1 test2 test3';

				assert(Dom.hasClass(element, 'test1 test2'));
				assert(Dom.hasClass(element, 'test1 test3'));
				assert(Dom.hasClass(element, 'test3 test1'));
			});

			it('Should return true if element className includes all names from passed names array', () => {
				const element = document.createElement('div');
				element.className = 'test1 test2 test3';

				assert(Dom.hasClass(element, ['test1', 'test2']));
				assert(Dom.hasClass(element, ['test1', 'test3']));
				assert(Dom.hasClass(element, ['test3', 'test1']));
			});

			it('Should return false if element className not includes all names from passed string', () => {
				const element = document.createElement('div');
				element.className = 'test1 test2 test3';

				assert(!Dom.hasClass(element, 'test1 test2 test33'));
				assert(!Dom.hasClass(element, 'test1 test3 te'));
				assert(!Dom.hasClass(element, 'test3 test222 test1'));
			});
		});

		describe('SVG', () => {
			it('Should return true if element className includes passed name', () => {
				const element = document.createElementNS('http://www.w3.org/2000/svg', 'div');
				element.setAttribute('class', 'test1 test2 test3');

				assert(Dom.hasClass(element, 'test1'));
				assert(Dom.hasClass(element, 'test2'));
				assert(Dom.hasClass(element, 'test3'));
			});

			it('Should return true if element className includes passed multiple name string', () => {
				const element = document.createElementNS('http://www.w3.org/2000/svg', 'div');
				element.setAttribute('class', 'test1 test2 test3');

				assert(Dom.hasClass(element, 'test1 test2'));
				assert(Dom.hasClass(element, 'test1 test3'));
				assert(Dom.hasClass(element, 'test3 test1'));
			});

			it('Should return true if element className includes all names from passed names array', () => {
				const element = document.createElementNS('http://www.w3.org/2000/svg', 'div');
				element.setAttribute('class', 'test1 test2 test3');

				assert(Dom.hasClass(element, ['test1', 'test2']));
				assert(Dom.hasClass(element, ['test1', 'test3']));
				assert(Dom.hasClass(element, ['test3', 'test1']));
			});

			it('Should return false if element className not includes all names from passed string', () => {
				const element = document.createElementNS('http://www.w3.org/2000/svg', 'div');
				element.setAttribute('class', 'test1 test2 test3');

				assert(!Dom.hasClass(element, 'test1 test2 test33'));
				assert(!Dom.hasClass(element, 'test1 test3 te'));
				assert(!Dom.hasClass(element, 'test3 test222 test1'));
			});
		});

		describe('Memory leak detection', () => {
			it('Should not leak if passed single string class name', () => {
				let element = document.createElement('div');
				let className = 'test-class';
				Dom.hasClass(element, className);

				let isElementCollected = false;
				global.weak(element, () => {
					isElementCollected = true;
				});

				element = null;

				global.gc();

				assert.ok(isElementCollected, 'Memory leak detected! "Element" is not collected');
			});

			it('Should not leak if passed multiple string class name', () => {
				let element = document.createElement('div');
				let className = 'test-class test-class2 test-class3';
				Dom.hasClass(element, className);

				let isElementCollected = false;
				global.weak(element, () => {
					isElementCollected = true;
				});

				element = null;

				global.gc();

				assert.ok(isElementCollected, 'Memory leak detected! "Element" is not collected');
			});

			it('Should not leak if passed array of names', () => {
				let element = document.createElement('div');
				let className = ['test-class', 'test-class2', 'test-class3'];
				Dom.hasClass(element, className);

				let isElementCollected = false;
				global.weak(element, () => {
					isElementCollected = true;
				});

				let isClassNameCollected = false;
				global.weak(className, () => {
					isClassNameCollected = true;
				});

				element = null;
				className = null;

				global.gc();

				assert.ok(isElementCollected, 'Memory leak detected! "Element" is not collected');
				assert.ok(isClassNameCollected, 'Memory leak detected! "className" is not collected');
			});
		});
	});

	describe('#removeClass', () => {
		describe('HTML', () => {
			it('Should remove passed class name', () => {
				const element = document.createElement('div');
				element.className = 'test1 test2 test3';

				Dom.removeClass(element, 'test1');
				assert(element.className === 'test2 test3');

				Dom.removeClass(element, 'test3');
				assert(element.className === 'test2');

				Dom.removeClass(element, 'test2');
				assert(element.className === '');

				Dom.removeClass(element, 'test222');
				assert(element.className === '');
			});

			it('Should remove all names from string', () => {
				const element = document.createElement('div');
				element.className = 'test1 test2 test3';

				Dom.removeClass(element, 'test1 test3');
				assert(element.className === 'test2');
			});

			it('Should remove all names from names array', () => {
				const element = document.createElement('div');
				element.className = 'test1 test2 test3';

				Dom.removeClass(element, ['test2', 'test3']);
				assert(element.className === 'test1');

				Dom.removeClass(element, ['test1']);
				assert(element.className === '');
			});
		});

		describe('SVG', () => {
			it('Should remove passed name', () => {
				const element = document.createElementNS('http://www.w3.org/2000/svg', 'div');
				element.setAttribute('class', 'test1 test2 test3');

				Dom.removeClass(element, 'test1');
				assert(element.getAttribute('class') === 'test2 test3');

				Dom.removeClass(element, 'test3');
				assert(element.getAttribute('class') === 'test2');

				Dom.removeClass(element, 'test2');
				assert(element.getAttribute('class') === '');
			});

			it('Should remove all names from passed string', () => {
				const element = document.createElementNS('http://www.w3.org/2000/svg', 'div');
				element.setAttribute('class', 'test1 test2 test3');

				Dom.removeClass(element, 'test1 test2');
				assert(element.getAttribute('class') === 'test3');
			});

			it('Should remove all names from names array', () => {
				const element = document.createElementNS('http://www.w3.org/2000/svg', 'div');
				element.setAttribute('class', 'test1 test2 test3');

				Dom.removeClass(element, ['test2', 'test3']);
				assert(element.getAttribute('class') === 'test1');

				Dom.removeClass(element, ['test1']);
				assert(element.getAttribute('class') === '');
			});
		});

		describe('Memory leak detection', () => {
			it('Should not leak if passed single string class name', () => {
				let element = document.createElement('div');
				let className = 'test-class';
				Dom.removeClass(element, className);

				let isElementCollected = false;
				global.weak(element, () => {
					isElementCollected = true;
				});

				element = null;

				global.gc();

				assert.ok(isElementCollected, 'Memory leak detected! "Element" is not collected');
			});

			it('Should not leak if passed multiple string class name', () => {
				let element = document.createElement('div');
				let className = 'test-class test-class2 test-class3';
				Dom.removeClass(element, className);

				let isElementCollected = false;
				global.weak(element, () => {
					isElementCollected = true;
				});

				element = null;

				global.gc();

				assert.ok(isElementCollected, 'Memory leak detected! "Element" is not collected');
			});

			it('Should not leak if passed array of names', () => {
				let element = document.createElement('div');
				let className = ['test-class', 'test-class2', 'test-class3'];
				Dom.removeClass(element, className);

				let isElementCollected = false;
				global.weak(element, () => {
					isElementCollected = true;
				});

				let isClassNameCollected = false;
				global.weak(className, () => {
					isClassNameCollected = true;
				});

				element = null;
				className = null;

				global.gc();

				assert.ok(isElementCollected, 'Memory leak detected! "Element" is not collected');
				assert.ok(isClassNameCollected, 'Memory leak detected! "className" is not collected');
			});
		});
	});

	describe('#create', () => {
		it('Should create div', () => {
			const element = Dom.create('div');
			const result = document.createElement('div');

			assert.deepEqual(element, result);
		});

		it('Should create div with class', () => {
			const element = Dom.create('div', {
				props: {
					className: 'test'
				}
			});
			const result = document.createElement('div');
			result.classList.add('test');

			assert.deepEqual(element, result);
		});

		it('Should create div with classes', () => {
			const element = Dom.create('div', {
				props: {
					className: 'test test2'
				}
			});
			const result = document.createElement('div');
			result.classList.add('test');
			result.classList.add('test2');

			assert.deepEqual(element, result);
		});

		it('Should create div with children as string', () => {
			const element = Dom.create('div', {
				children: 'test string'
			});
			const result = document.createElement('div');
			result.innerHTML = 'test string';

			assert.deepEqual(element, result);
		});

		it('Should create div with children array', () => {
			const element = Dom.create('div', {
				children: [
					'test string'
				]
			});
			const result = document.createElement('div');
			result.innerHTML = 'test string';

			assert.deepEqual(element, result);
		});

		it('Should create from object options', () => {
			const element = Dom.create({
				tag: 'div',
			});

			const result = document.createElement('div');

			assert.deepEqual(element, result);
		});

		it('Should create from object with tag uppercase', () => {
			const element = Dom.create({
				tag: 'DIV',
			});

			const result = document.createElement('div');

			assert.deepEqual(element, result);
		});

		describe('Memory leak detection', () => {
			it('Should not leak if create element without params', () => {
				let element = Dom.create('div');

				let isElementCollected = false;
				global.weak(element, () => {
					isElementCollected = true;
				});

				element = null;

				global.gc();

				assert.ok(isElementCollected, 'Memory leak detected! "Element" is not collected');
			});

			it('Should not leak if create element with params', () => {
				let params = {
					props: {
						className: 'test-class',
					},
				};
				let element = Dom.create('div', params);

				let isParamsCollected = false;
				global.weak(params, () => {
					isParamsCollected = true;
				});

				let isElementCollected = false;
				global.weak(element, () => {
					isElementCollected = true;
				});

				params = null;
				element = null;

				global.gc();

				assert.ok(isParamsCollected, 'Memory leak detected! "params" is not collected');
				assert.ok(isElementCollected, 'Memory leak detected! "element" is not collected');
			});

			it('Should not leak if create element with params object only', () => {
				let params = {
					tag: 'div',
					props: {
						className: 'test',
					}
				};
				let element = Dom.create(params);

				let isParamsCollected = false;
				global.weak(params, () => {
					isParamsCollected = true;
				});

				let isElementCollected = false;
				global.weak(element, () => {
					isElementCollected = true;
				});

				params = null;
				element = null;

				global.gc();

				assert.ok(isParamsCollected, 'Memory leak detected! "params" is not collected');
				assert.ok(isElementCollected, 'Memory leak detected! "element" is not collected');
			});

		});
	});

	describe('#style', () => {
		it('Should return computed property value', () => {
			const element = document.createElement('div');

			assert.ok(Dom.style(element, 'display') === 'block');

			element.style.setProperty('width', '10px');
			assert.ok(Dom.style(element, 'width') === '10px');

			element.style.setProperty('height', '10px');
			assert.ok(Dom.style(element, 'height') === '10px');
		});

		it('Should set multiple props values from object', () => {
			const element = document.createElement('div');
			const props = {
				display: 'inline',
				width: '11px',
				position: 'fixed',
			};

			Dom.style(element, props);

			assert.ok(Dom.style(element, 'display') === props.display);
			assert.ok(Dom.style(element, 'width') === props.width);
			assert.ok(Dom.style(element, 'position') === props.position);
		});

		describe('compatibility tests', () => {
			it('Should return element if passed value', () => {
				const element = document.createElement('div');

				assert.ok(Dom.style(element, 'display', 'none') === element);
				assert.ok(Dom.style(element, 'display', '') === element);
				assert.ok(Dom.style(element, 'width', '100px') === element);
			});
		});

		describe('Memory leaks detection', () => {
			it('Should not retain element param', () => {
				let element = document.createElement('div');

				Dom.style(element, 'padding', '10px');

				let isElementCollected = false;
				global.weak(element, () => {
					isElementCollected = true;
				});

				element = null;

				global.gc(false);

				assert.ok(isElementCollected, 'Memory leak detected! "element" is not collected');
			});

			it('Should not retain styles param', () => {
				let element = document.createElement('div');
				let styles = {padding: '10px', margin: '10px'};

				Dom.style(element, styles);

				let isStylesCollected = false;
				global.weak(styles, () => {
					isStylesCollected = true;
				});

				styles = null;

				global.gc();

				assert.ok(isStylesCollected, 'Memory leak detected! "styles" is not collected');
			});
		});
	});

	describe('#attr', () => {
		let element;

		beforeEach(() => {
			element = document.createElement('div');
		});

		it('Should set/get string value', () => {
			const attr = 'data-test';
			const value = 'myValue';

			Dom.attr(element, attr, value);
			assert.ok(Dom.attr(element, attr) === value);
		});

		it('Should set/get string value with special chars', () => {
			const attr = 'data-test';
			const value = `<div class="test"></div>`;

			Dom.attr(element, attr, value);

			// Should be encoded and not equal the source value
			assert.ok(element.getAttribute(attr) !== value);

			// Should decoded and equal the source value
			assert.ok(Dom.attr(element, attr) === value);
		});

		it('Should set/get boolean value', () => {
			const attr = 'data-test';
			const value1 = true;
			const value2 = false;

			// set/get true
			Dom.attr(element, attr, value1);
			assert.ok(Dom.attr(element, attr) === value1);

			// set/get false
			Dom.attr(element, attr, value2);
			assert.ok(Dom.attr(element, attr) === value2);
		});

		it('Should set/get number (integer) value', () => {
			const attr = 'data-test';
			const value = 990;

			Dom.attr(element, attr, value);
			assert.ok(Dom.attr(element, attr) === value);
		});

		it('Should set/get number (float) value', () => {
			const attr = 'data-test';
			const value = 1.999;

			Dom.attr(element, attr, value);
			assert.ok(Dom.attr(element, attr) === value);
		});

		it('Should set/get number (float with leading decimal point) value', () => {
			const attr = 'data-test';
			const value = .999;

			Dom.attr(element, attr, value);
			assert.ok(Dom.attr(element, attr) === value);
		});

		it('Should set/get array value', () => {
			const attr = 'data-test';
			const value = [1, 2, 3];

			Dom.attr(element, attr, value);

			// Should be encoded and not equal the source value
			assert.ok(element.getAttribute(attr) !== value);

			// Should decoded and equal the source value
			assert.deepEqual(Dom.attr(element, attr), value);
		});

		it('Should set/get plain object value', () => {
			const attr = 'data-test';
			const value = {test: 1, test2: 2};

			Dom.attr(element, attr, value);

			// Should be encoded and not equal the source value
			assert.ok(element.getAttribute(attr) !== value);

			// Should decoded and equal the source value
			assert.deepEqual(Dom.attr(element, attr), value);
		});

		it('Should not parse boolean into string', () => {
			const attr = 'data-test';
			const value1 = 'test true';
			const value2 = 'test false';
			const value3 = 'false test';
			const value4 = 'false test';

			Dom.attr(element, attr, value1);
			assert.ok(Dom.attr(element, attr) === value1);

			Dom.attr(element, attr, value2);
			assert.ok(Dom.attr(element, attr) === value2);

			Dom.attr(element, attr, value3);
			assert.ok(Dom.attr(element, attr) === value3);

			Dom.attr(element, attr, value4);
			assert.ok(Dom.attr(element, attr) === value4);
		});

		it('Should not parse number into string', () => {
			const attr = 'data-test';
			const value1 = 'test 1';
			const value2 = 'test.2';
			const value3 = '.3test';
			const value4 = '0.4.test';

			Dom.attr(element, attr, value1);
			assert.ok(Dom.attr(element, attr) === value1);

			Dom.attr(element, attr, value2);
			assert.ok(Dom.attr(element, attr) === value2);

			Dom.attr(element, attr, value3);
			assert.ok(Dom.attr(element, attr) === value3);

			Dom.attr(element, attr, value4);
			assert.ok(Dom.attr(element, attr) === value4);
		});
	});
});