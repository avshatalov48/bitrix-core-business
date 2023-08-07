import {addCustomEvent, Type, Tag} from 'main.core';
import {Switcher, SwitcherSize, SwitcherColor} from '../../src/ui.switcher';
import {Switcher as Switcher2} from '../../src/ui.switcher';
import {fireEvent} from '@testing-library/dom';

describe('UI.Switcher', () => {
	describe('Options', () => {
		describe('"attributeName"', () => {
			it('Should use default value is empty', () => {
				const switcher = new Switcher()
				assert(switcher.getAttributeName() === 'data-switcher');
			});
			it('Should use string value', () => {
				const switcher = new Switcher({
					attributeName: 'new-switcher-attribute'
				})
				assert(switcher.getAttributeName() === 'new-switcher-attribute');
			});
			it("Should use default value if option is invalid", () => {
				const obj = {name: 123};
				const switcher = new Switcher({
					attributeName: obj,
				})
				assert(switcher.getAttributeName() === 'data-switcher');
			});
		});
		describe('"inputName"', () => {
			it('Should set default value', () => {
				const switcher = new Switcher()
				assert(switcher.getInputName() === '');
			});
			it('Should set string value', () => {
				const switcher = new Switcher({
					inputName: 'new-switcher-attribute'
				})
				assert(switcher.getInputName() === 'new-switcher-attribute');
			});
			it("Should not set string value", () => {
				const obj = {name: 123};
				const switcher = new Switcher({
					attributeName: obj,
				})
				assert(switcher.getInputName() === '');
			});

			it ('should add input with inputName and type=hidden', () => {
				const inputName = 'new-switcher-attribute';
				const switcher = new Switcher({
					inputName,
				});

				document.body.appendChild(switcher.getNode());

				const input = switcher.getNode().querySelector(`[name="${inputName}"]`);

				assert.ok(input);
				assert.equal(input.getAttribute('type'), 'hidden');
				assert.equal(input.getAttribute('name'), inputName)


				assert.equal(switcher.getInputName(), inputName);
			});
		});
		describe('size', () => {
			it ('small value from SwitcherSize', () => {
				const size = SwitcherSize.small;

				const switcher = new Switcher({
					size,
				});

				assert(switcher.getNode().classList.contains('ui-switcher-size-sm'));
			});

			it ('Should use default value if incorrect', () => {
				const size = 'incorrect-size';

				const switcher = new Switcher({
					size,
					checked: true,
				});

				assert.equal(switcher.getNode().className, 'ui-switcher');
			});
		});
		describe('color', () => {
			const correctColorOption = SwitcherColor.green;

			it('use value from SwitcherColor', () => {
				const switcher = new Switcher({
					color: correctColorOption,
				});

				assert(switcher.getNode().classList.contains('--color-green'));
			});

			it('Should use default value if incorrect', () => {
				const color = 123;

				const switcher = new Switcher({
					color,
					checked: true,
				});

				assert.equal(switcher.getNode().className, 'ui-switcher');
			});
		});
		describe('"checked"', () => {
			it('True value', () => {
				const switcher: Switcher = new Switcher({checked: true});
				assert(switcher.isChecked() === true);
			});

			it('False value', () => {
				const switcher: Switcher = new Switcher({checked: false});
				assert(switcher.isChecked() === false);
			});

			it('Default value', () => {
				const switcher: Switcher = new Switcher();
				assert(switcher.isChecked() === false);
			});

			it('Not boolean type value', () => {
				const switcher: Switcher = new Switcher({
					checked: {},
				});
				assert(switcher.isChecked() === false);
			});
		});
		describe('"id"', () => {
			it('Should use random number as default value', () => {
				const switcher = new Switcher( {});
				assert(Type.isNumber(switcher.id));
			});

			it('Should use string or number value', () => {
				const switcher = new Switcher( {
					id: '123',
				});
				assert.equal(switcher.id, '123');

				const switcher2 = new Switcher({
					id: 123,
				});

				assert.equal(switcher2.id, 123);
			});

		});
		describe('"node"', () => {
			it ('Should work with HTML element without attributes', () => {
				const node = document.createElement('span');

				const switcher = new Switcher({
					node,
				});

				assert(switcher.getNode() === node);
			});
			it ('Should throw an exception if node is not HTMLElement', () => {
				const node = '<span></span>';

				assert.throws(() => {
					new Switcher({
						node,
					})
				});
			});
			it ('Should replace id field from data-attribute with id from options', () => {
				const nodeData = {
					id: 789,
				}
				const node = Tag.render`
					<span class="${Switcher.className}" data-switcher='${JSON.stringify(nodeData)}'></span>
				`
				const switcher = new Switcher({
					node,
					id: 6789,
				});

				assert.equal(switcher.id, 6789);
			});
			it ('Should replace "checked" from data-attribute with checked from options', () => {
				const nodeData = {
					checked: true,
				};

				const node = Tag.render`
					<span class="${Switcher.className}" data-switcher='${JSON.stringify(nodeData)}'></span>
				`;

				const switcher = new Switcher({
					node,
					checked: false,
				});

				assert.equal(switcher.isChecked(), false);
			});
			it ('Should replace "inputName" from data-attribute with "inputName" from options', () => {
				const nodeData = {
					inputName: 'nodeInputName',
				};

				const node = Tag.render`
					<span class="${Switcher.className}" data-switcher='${JSON.stringify(nodeData)}'></span>
				`;

				const switcher = new Switcher({
					node,
					inputName: 'inputName',
				});

				assert.equal(switcher.getInputName(), 'inputName');
			});
			it ('Should replace "color" and "size" from options with "color" and "size" from data-attribute', () => {
				const nodeData = {
					inputName: 'nodeInputName',
					color: SwitcherColor.primary,
					size: SwitcherSize.medium,
					checked: true,
				};

				const node = Tag.render`
					<span class="${Switcher.className}" data-switcher='${JSON.stringify(nodeData)}'></span>
				`;

				const switcher = new Switcher({
					node,
					inputName: 'inputName',
					color: SwitcherColor.primary,
					size: SwitcherSize.medium,
				});

				assert.equal(switcher.getNode().className, 'ui-switcher');
				assert.equal(switcher.getNode().className, 'ui-switcher');
			});
		});
		describe("'handlers'", () => {
			it ('Should "checked" handler work', () => {
				let isCheckedCaught = false;

				const switcher = new Switcher({
					checked: true,
					handlers: {
						checked() {
							isCheckedCaught = true;
						},
					}
				});

				switcher.check(false, true);

				assert.equal(isCheckedCaught, true);


			});

			it ('Should "unchecked" handler work', () => {
				let isEventCaught = false;

				const switcher = new Switcher({
					checked: false,
					handlers: {
						unchecked() {
							isEventCaught = true;
						},
					}
				});

				switcher.check(true, true);

				assert.equal(isEventCaught, true);
			});

			it ('Should "toggle" handler work', () => {
				let isEventCaught = false;

				const switcher = new Switcher({
					checked: false,
					handlers: {
						toggled() {
							isEventCaught = true;
						},
					}
				});

				switcher.check(true, true);

				assert.equal(isEventCaught, true);
			});
		});
	});
	describe('Basic Usage', () => {
		it('Should add switcher to the Switcher.list when created', () => {
			const switcher = new Switcher({
				id: 1,
			});

			const switcher2 = new Switcher({
				id: 2,
			});

			assert.equal(Switcher.list[Switcher.list.length - 2], switcher);
			assert.equal(Switcher.list[Switcher.list.length - 1], switcher2);
		});
		it ('Should method getNode() work fine', () => {
			const node = document.createElement('span');

			const switcher = new Switcher({
				node,
			});

			assert.deepEqual(switcher.getNode(), node);
		});
		it ('Should static getById() return switcher by ID and null if switcher is not exist', () => {
			const switcher = new Switcher({
				id: 99,
			});

			const switcher2 = new Switcher2({
				id: 100,
			});

			assert.deepEqual(Switcher.getById(switcher.id), switcher);
			assert.deepEqual(Switcher.getById(switcher2.id), switcher2);
			assert.deepEqual(Switcher.getById('not_exist_switcher_id'), null);
		});
		it ('Should static initByClassname init switcher by div with switcher data and classname', () => {
			const switcher1Data = JSON.stringify({
				id: "one",
				checked: true,
				handlers: { unchecked : 'Y' }
			});

			const switcher2Data = JSON.stringify({
				id: "two",
				checked: true,
			})

			const switcher = Tag.render`
				<div class="${Switcher.className}" data-switcher='${switcher1Data}'></div>
			`;

			const switcher2 = Tag.render`
				<div class="${Switcher.className}" data-switcher='${switcher2Data}'></div>
			`;

			document.body.appendChild(switcher);
			document.body.appendChild(switcher2);

			Switcher.initByClassname();

			assert.ok(Switcher.getById('one'));
			assert.ok(Switcher.getById('two'));
		});
		it ('Should render to container', () => {
			const switcher = new Switcher();

			const container = document.createElement('div');
			container.setAttribute('id', 'container');
			document.body.appendChild(container);

			switcher.renderTo(container);

			assert.equal(switcher.getNode().parentElement, container);
		});
		it ('Should throw error if target container in renderTo method is not HTMLElement', () => {
			const switcher = new Switcher();

			assert.throws(() => {
				switcher.renderTo(123);
			});
		});
	});
	describe('Toggling', () => {
		const switcherOffClassnameModifier = 'ui-switcher-off';

		it('Should toggle method work fine', () => {
			const switcher = new Switcher({
				checked: false,
			});

			switcher.toggle();

			assert.equal(switcher.isChecked(), true);

			switcher.toggle();

			assert.equal(switcher.isChecked(), false);
		});

		it ('Should check method work fine', () => {
			const switcher = new Switcher({
				checked: false,
			});

			switcher.check(true);

			assert.equal(switcher.isChecked(), true);

			switcher.check(false);

			assert.equal(switcher.isChecked(), false);
		});

		it('Should change checked state from false to true on click', () => {
			const switcher = new Switcher({
				checked: false,
			});
			switcher.renderTo(document.body);

			fireEvent.click(switcher.getNode());

			assert.equal(switcher.isChecked(), true);
		});

		it('Should change checked state from true to false on click', () => {
			const switcher = new Switcher({
				checked: true,
			});
			switcher.renderTo(document.body);

			fireEvent.click(switcher.getNode());

			assert.equal(switcher.isChecked(), false);
		});

		it('Should add class when toggle off on click', () => {
			const switcher = new Switcher({
				checked: true,
			});
			switcher.renderTo(document.body);

			fireEvent.click(switcher.getNode());

			assert.equal(switcher.getNode().classList.contains(switcherOffClassnameModifier), true);
		});

		it('Should remove class when toggle on on click', () => {
			const switcher = new Switcher({
				checked: false,
			});
			switcher.renderTo(document.body);

			fireEvent.click(switcher.getNode());

			assert.equal(switcher.getNode().classList.contains(switcherOffClassnameModifier), false);
		});

		it ('Should add class when toggle off by program', () => {
			const switcher = new Switcher({
				checked: true,
			});

			assert.equal(switcher.getNode().classList.contains(switcherOffClassnameModifier), false);

			switcher.check(false);

			assert.equal(switcher.getNode().classList.contains(switcherOffClassnameModifier), true);
		});

		it ('Should remove class when toggle on by program', () => {
			const switcher = new Switcher({
				checked: false,
			});

			assert.equal(switcher.getNode().classList.contains(switcherOffClassnameModifier), true);

			switcher.check(true);

			assert.equal(switcher.getNode().classList.contains(switcherOffClassnameModifier), false, 'FFFF');
		});

		it ('Should fire checked event when check(true, true)', () => {
			const switcher = new Switcher({
				checked: true,
			});

			let eventCaughtCount = 0;
			addCustomEvent(switcher, switcher.events.unchecked, () => {
				eventCaughtCount += 1;
			});

			switcher.check(true, true);

			assert.equal(eventCaughtCount, 1);
		});

		it ('Should fire unchecked event when check(false, true)', () => {
			const switcher = new Switcher({
				checked: true,
			});

			let eventCaughtCount = 0;
			addCustomEvent(switcher, switcher.events.checked, () => {
				eventCaughtCount++;
			});

			switcher.check(false, true);

			assert.equal(eventCaughtCount, 1);
		});

		it ('Should fire toggle event', () => {
			const switcher = new Switcher({
				checked: false,
			});

			let eventCaughtCount = 0;
			addCustomEvent(switcher, switcher.events.toggled, () => {
				eventCaughtCount++;
			});

			switcher.check(false, true);

			assert.equal(eventCaughtCount, 1)

			switcher.check(true, true);

			assert.equal(eventCaughtCount, 2);
		})
	});
	describe('Loading state', () => {
		it ('Loading field should change after selLoading(boolean)', () => {
			const switcher = new Switcher();

			switcher.setLoading(true);

			assert.equal(switcher.isLoading(), true);

			switcher.setLoading(false);

			assert.equal(switcher.isLoading(), false);
		});

		it ('Should be added svg loader when loading state change from false to true', () => {
			const switcher = new Switcher();

			switcher.setLoading(true);

			assert.ok(switcher.getNode().querySelector('.ui-sidepanel-wrapper-loader-path'));
		});

		it ('Should be removed svg loader when loading state change from true to false', () => {
			const switcher = new Switcher();

			switcher.setLoading(false);

			assert.equal(switcher.getNode().querySelector('.ui-sidepanel-wrapper-loader-path'), null);
		});
	});
});