import '../../../../../../../../../main/install/js/main/core/test/old/core/internal/bootstrap';
global.BX = window.BX;
import {BaseField} from '../../src/basefield';

describe('landing.ui.field.basefield', () => {
	describe('BaseField', () => {
		it('Should be a function', () => {
			assert(typeof BaseField === 'function');
		});

		describe('Static', () => {
			describe('BaseField.createLayout()', () => {
				it('Should return HTMLElement', () => {
					const result = BaseField.createLayout();
					assert.ok(result.nodeType === Node.ELEMENT_NODE);
				});
			});

			describe('BaseField.createHeader()', () => {
				it('Should return HTMLElement', () => {
					const result = BaseField.createHeader();
					assert.ok(result.nodeType === Node.ELEMENT_NODE);
				});
			});

			describe('BaseField.createDescription()', () => {
				it('Should create HTMLElement with passed text', () => {
					const text = 'Test text';
					const result = BaseField.createDescription(text);
					assert.ok(result.nodeType === Node.ELEMENT_NODE);
					assert.ok(result.innerHTML.includes(text));
				});
			});

			describe('BaseField.currentField', () => {
				it('Should be defined with null value', () => {
					assert.ok(BaseField.currentField === null);
				});
			});
		});

		describe('Create without options', () => {
			let field;

			it('Should does not throws if options is not object', () => {
				assert.doesNotThrow(() => {
					field = new BaseField();
				});
			});

			it('this.data should be an object', () => {
				assert.deepEqual(field.data, {});
			});

			it('this.id should be an string', () => {
				assert.ok(typeof field.id === 'string');
			});

			it('this.selector should be an string', () => {
				assert.ok(typeof field.selector === 'string');
			});

			it('this.content should be an string', () => {
				assert.ok(typeof field.content === 'string');
			});

			it('this.title should be an string', () => {
				assert.ok(typeof field.title === 'string');
			});

			it('this.placeholder should be an string', () => {
				assert.ok(typeof field.placeholder === 'string');
			});

			it('this.className should be an string', () => {
				assert.ok(typeof field.className === 'string');
			});

			it('this.descriptionText should be an string', () => {
				assert.ok(typeof field.descriptionText === 'string');
			});

			it('this.description should be a null', () => {
				assert.ok(field.description === null);
			});

			it('this.attribute should be an string', () => {
				assert.ok(typeof field.attribute === 'string');
			});

			it('this.hidden should be a false', () => {
				assert.ok(field.hidden === false);
			});

			it('this.property should be an string', () => {
				assert.ok(typeof field.property === 'string');
			});

			it('this.style should be an string', () => {
				assert.ok(typeof field.style === 'string');
			});

			it('this.onValueChangeHandler should be an function', () => {
				assert.ok(typeof field.onValueChangeHandler === 'function');
			});

			it('this.layout should be an HTMLElement', () => {
				assert.ok(typeof field.layout === 'object');
				assert.ok(field.layout.nodeType === Node.ELEMENT_NODE);
			});

			it('this.header should be an HTMLElement', () => {
				assert.ok(typeof field.header === 'object');
				assert.ok(field.header.nodeType === Node.ELEMENT_NODE);
			});

			it('this.input should be an HTMLElement', () => {
				assert.ok(typeof field.input === 'object');
				assert.ok(field.input.nodeType === Node.ELEMENT_NODE);
			});
		});

		describe('Create with options', () => {
			let field;
			let options = {
				id: 'test_id',
				selector: '0@.test-selector',
				content: 'test_content',
				title: 'test_title',
				placeholder: 'test_placeholder',
				className: 'testClassName',
				description: 'testDescriptionText',
				attribute: 'testSttribute',
				hidden: true,
				property: 'testProperty',
				style: 'testStyle',
				onValueChange: function() {},
			};

			sinon.spy(BaseField.prototype, 'init');

			it('Should does not throws if options is object', () => {
				assert.doesNotThrow(() => {
					field = new BaseField(options);
				});
			});

			it('field.data should not options', () => {
				assert.ok(field.data !== options);
			});

			it('field.data should deep equal options', () => {
				assert.deepEqual(field.data, options);
			});

			it('field.id should equal options.id', () => {
				assert.equal(field.id, options.id);
			});

			it('field.selector should equal options.selector', () => {
				assert.equal(field.selector, options.selector);
			});

			it('field.content should equal options.content', () => {
				assert.equal(field.content, options.content);
			});

			it('field.title should equal options.title', () => {
				assert.equal(field.title, options.title);
			});

			it('field.placeholder should equal options.placeholder', () => {
				assert.equal(field.placeholder, options.placeholder);
			});

			it('field.className should equal options.className', () => {
				assert.equal(field.className, options.className);
			});

			it('field.descriptionText should equal options.description', () => {
				assert.equal(field.descriptionText, options.description);
			});

			it('field.attribute should equal options.attribute', () => {
				assert.equal(field.attribute, options.attribute);
			});

			it('field.hidden should equal options.hidden', () => {
				assert.equal(field.hidden, options.hidden);
			});

			it('field.property should equal options.property', () => {
				assert.equal(field.property, options.property);
			});

			it('field.style should equal options.style', () => {
				assert.equal(field.style, options.style);
			});

			it('field.onValueChangeHandler should equal options.onValueChange', () => {
				assert.equal(field.onValueChangeHandler, options.onValueChange);
			});

			it('field.layout should contains data-selector attribute with value equal options.selector', () => {
				assert.equal(field.layout.dataset.selector, options.selector);
			});

			it('field.input should contains data-placeholder attribute with value equal options.placeholder', () => {
				assert.equal(field.input.dataset.placeholder, options.placeholder);
			});

			it('field.layout.classList should includes options.className', () => {
				assert.ok(field.layout.classList.contains(options.className));
			});

			it('field.layout should includes field.description', () => {
				assert.ok(field.layout.querySelector('.landing-ui-field-description'));
			});

			it('field.layout should contains options.description text', () => {
				const descriptionNode = field.layout.querySelector('.landing-ui-field-description');
				assert.ok(descriptionNode.innerHTML.includes(options.description));
			});

			it('field.init() should be called once', () => {
				assert.ok(field.init.callCount === 2);
				field.init.restore();
			});

			it('field.createInput() should create input node with options.content', () => {
				const input = field.createInput();
				assert.ok(input.innerHTML.includes(options.content));
			});

			it('field.setValue() should set empty string value', () => {
				const value = '';
				field.setValue(value);
				assert.deepEqual(field.input.innerHTML, value);
			});

			it('field.setValue() should set not empty string value', () => {
				const value = 'test111';
				field.setValue(value);
				assert.deepEqual(field.input.innerHTML, value);
			});

			it('field.setValue() should set number value', () => {
				const value = 11;
				field.setValue(value);
				assert.deepEqual(field.input.innerHTML, value);
			});

			it('field.setValue() should set html string value', () => {
				const value = `<div class="test"></div>`;
				field.setValue(value);
				assert.deepEqual(field.input.innerHTML, value);
			});

			it('field.setValue() should throw if passed null', () => {
				assert.throws(() => {
					field.setValue(null);
				});
			});

			it('field.setValue() should does not throw if passed undefined', () => {
				assert.doesNotThrow(() => {
					field.setValue();
				});
			});

			it('field.disable() should disable field', () => {
				field.disable();

				assert.ok(field.layout.getAttribute('disabled') === 'true');
				assert.ok(field.layout.classList.contains('landing-ui-disable'));
			});

			it('field.enable() should enable field', () => {
				field.enable();

				assert.ok(field.layout.getAttribute('disabled') === 'false');
				assert.ok(field.layout.classList.contains('landing-ui-disable') === false);
			});

			it('field.clone() should clones this field', () => {
				const cloned = field.clone();
				assert.deepEqual(cloned.options, field.options);
			});
		});
	});
});