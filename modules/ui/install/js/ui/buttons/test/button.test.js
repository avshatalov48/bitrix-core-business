import {
	Button,
	ButtonSize,
	ButtonTag,
	ButtonColor,
	ButtonState,
	ButtonIcon,
	ButtonStyle,
	ButtonManager
} from '../src';
import { AddButton, ApplyButton, CancelButton, CloseButton, CreateButton, SaveButton, SendButton, SettingsButton} from '../src'
import loadMessages from './load-messages';
import { Reflection, Tag } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { MenuItem } from 'main.popup';

describe('BX.UI.Button', () => {

	loadMessages();

	describe('Basic usage', () => {
		it('Should render a button into DOM', () => {

			const container = document.createElement('div');
			const caption = 'Hello, World!';

			const button = new Button({ text: caption });
			button.renderTo(container);

			assert.equal(container.innerHTML, `<button class="${button.getBaseClass()}"><span class="ui-btn-text">${caption}</span></button>`);
		});

		it('Should create a large button', () => {

			const caption = 'Large Button';
			const button = new Button({ text: caption, size: ButtonSize.LARGE });

			assert.equal(button.getSize(), ButtonSize.LARGE);

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonSize.LARGE}"><span class="ui-btn-text">${caption}</span></button>`
			);
		});

		it('Should create a colored button', () => {

			const caption = 'Success Button';
			const button = new Button({ text: caption, color: ButtonColor.SUCCESS });

			assert.equal(button.getColor(), ButtonColor.SUCCESS);

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonColor.SUCCESS}"><span class="ui-btn-text">${caption}</span></button>`
			);
		});

		it('Should create a disabled button', () => {

			const caption = 'Disabled Button';
			const button = new Button({ text: caption, state: ButtonState.DISABLED });

			assert.equal(button.getState(), ButtonState.DISABLED);

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonState.DISABLED}"><span class="ui-btn-text">${caption}</span></button>`
			);
		});

		it('Should create a button with an icon', () => {

			const caption = 'Settings';
			const button = new Button({ text: caption, icon: ButtonIcon.SETTING });

			assert.equal(button.getIcon(), ButtonIcon.SETTING);

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonIcon.SETTING}"><span class="ui-btn-text">${caption}</span></button>`
			);
		});

		it('Should use a specified tag', () => {

			const caption = 'Button';

			Object.values(ButtonTag).forEach(tag => {

				const button = new Button({
					text: caption,
					color: ButtonColor.LIGHT_BORDER,
					size: ButtonSize.EXTRA_SMALL,
					tag
				});

				const className = `${button.getBaseClass()} ${ButtonSize.EXTRA_SMALL} ${ButtonColor.LIGHT_BORDER}`;

				const tagResults = {
					[ButtonTag.BUTTON]: () => `<button class="${className}"><span class="ui-btn-text">${caption}</span></button>`,
					[ButtonTag.DIV]: () => `<div class="${className}"><span class="ui-btn-text">${caption}</span></div>`,
					[ButtonTag.SPAN]: () => `<span class="${className}"><span class="ui-btn-text">${caption}</span></span>`,
					[ButtonTag.LINK]: () => `<a class="${className}" href=""><span class="ui-btn-text">${caption}</span></a>`,
					[ButtonTag.INPUT]: () => `<input class="${className}" type="button" value="${caption}">`,
					[ButtonTag.SUBMIT]: () => `<input class="${className}" type="submit" value="${caption}">`
				};

				assert.equal(button.getTag(), tag);

				assert.equal(
					button.render().outerHTML,
					tagResults[tag]()
				);
			});
		});

		it('Should set html properties', () => {

			const caption = 'Button';
			const button = new Button({
				text: caption,
				props: {
					id: 'my-id',
					name: 'my-name'
				}
			});

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}" id="my-id" name="my-name"><span class="ui-btn-text">${caption}</span></button>`
			);

		});

		it('Should set data attributes', () => {
			const caption = 'Button';
			const button = new Button({
				text: caption,
				dataset: {
					id: 'my-id',
					name: 'my-name'
				}
			});

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}" data-id="my-id" data-name="my-name"><span class="ui-btn-text">${caption}</span></button>`
			);

		});

		it('Should bind an "onclick" event', () => {
			const caption = 'Button';
			const onclick = sinon.stub().callsFake((btn, event) => {
				setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
					assert.equal(btn, button);
					assert.equal(event.type, 'click');
				}, 0);
			});

			const button = new Button({
				text: caption,
				onclick
			});

			assert.equal(onclick.callCount, 0);
			button.getContainer().click();
			assert.equal(onclick.callCount, 1);
		});

		it('Should bind DOM events', () => {

			const caption = 'Events';
			const onclick = sinon.stub().callsFake((btn, event) => {
				setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
					assert.equal(btn, button);
					assert.equal(event.type, 'click');
				}, 0);
			});

			const onmouseover = sinon.stub().callsFake((btn, event) => {
				setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
					assert.equal(btn, button);
					assert.equal(event.type, 'mouseover');
				}, 0);
			});

			const button = new Button({
				text: caption,
				events: {
					click: onclick,
					mouseover: onmouseover
				}
			});

			assert.equal(onclick.callCount, 0);
			assert.equal(onmouseover.callCount, 0);

			const event = new window.MouseEvent('mouseover', {
				view: window,
				bubbles: true,
				cancelable: true
			});

			button.getContainer().dispatchEvent(event);

			assert.equal(onclick.callCount, 0);
			assert.equal(onmouseover.callCount, 1);

			button.getContainer().click();

			assert.equal(onclick.callCount, 1);
			assert.equal(onmouseover.callCount, 1);
		});

		it('Should create a Round Button', () => {

			const caption = 'Round Button';
			const button = new Button({ text: caption, round: true });
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonStyle.ROUND}"><span class="ui-btn-text">${caption}</span></button>`
			);
		});

		it('Should create a Dropdown Button', () => {
			const caption = 'Dropdown Button';
			const button = new Button({ text: caption, dropdown: true });
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonStyle.DROPDOWN}"><span class="ui-btn-text">${caption}</span></button>`
			);
		});

		it('Should create a container for a caption', () => {
			const caption = 'text';
			const button = new Button({ text: caption });
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}"><span class="ui-btn-text">${caption}</span></button>`
			);

			button.setText('');

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}"></button>`
			);

			button.setCounter(12);

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}"><span class="ui-btn-counter">12</span></button>`
			);

			button.setText('New Text');

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}">` +
					`<span class="ui-btn-text">New Text</span>` +
					`<span class="ui-btn-counter">12</span>` +
				`</button>`
			);

			button.setText('New Text 2');

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}">` +
					`<span class="ui-btn-text">New Text 2</span>` +
					`<span class="ui-btn-counter">12</span>` +
				`</button>`
			);

			button.setText('');

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}">` +
					`<span class="ui-btn-counter">12</span>` +
				`</button>`
			);

			button.setText('ABC');

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}">` +
					`<span class="ui-btn-text">ABC</span>` +
					`<span class="ui-btn-counter">12</span>` +
				`</button>`
			);

			button.setCounter('');

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}">` +
					`<span class="ui-btn-text">ABC</span>` +
				`</button>`
			);

			button.setText('');

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}">` +
				`</button>`
			);
		});

		it('Shouldn\'t create a container for an empty caption', () => {
			const button = new Button();
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}"></button>`
			);

			button.setCounter(12);

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}"><span class="ui-btn-counter">12</span></button>`
			);

			button.setText('New Text');

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}">` +
					`<span class="ui-btn-text">New Text</span>` +
					`<span class="ui-btn-counter">12</span>` +
				`</button>`
			);

			button.setText('New Text 2');

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}">` +
					`<span class="ui-btn-text">New Text 2</span>` +
					`<span class="ui-btn-counter">12</span>` +
				`</button>`
			);

			button.setText('');

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}">` +
					`<span class="ui-btn-counter">12</span>` +
				`</button>`
			);

			button.setText('ABC');

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}">` +
					`<span class="ui-btn-text">ABC</span>` +
					`<span class="ui-btn-counter">12</span>` +
				`</button>`
			);
		});

		it('Should create a No-Caps Button', () => {
			const caption = 'No-Caps Button';
			const button = new Button({ text: caption, noCaps: true });
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonStyle.NO_CAPS}"><span class="ui-btn-text">${caption}</span></button>`
			);
		});

		it('Should create a menu', () => {
			const caption = 'Menu';

			const itemClick = sinon.stub().callsFake((event, item) => {
				setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
					assert.equal(item, itemOne);
					assert.equal(event.type, 'click');
				}, 0);
			});

			const itemMouseEnter = sinon.stub().callsFake(function(event: BaseEvent) {
				setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
					assert.equal(event.getTarget(), itemOne);
				}, 0);
			});

			const menuOptions = {
				id: 'my-menu',
				items: [
					{
						text: 'Item One',
						onclick: itemClick,
						events: { // EventEmitter events
							onMouseEnter: itemMouseEnter
						}
					},
					{
						text: 'Item Two'
					}
				],
				minWidth: 111
			};

			const button = new Button({ text: caption, noCaps: true, menu: menuOptions });

			assert.equal(button.isDropdown(), true);

			const menu = button.getMenuWindow();
			assert.equal(menu.getId(), 'my-menu');
			assert.equal(menu.getPopupWindow().getMinWidth(), 111);
			assert.equal(menu.getMenuItems().length, 2);

			const itemOne: MenuItem = menu.getMenuItems()[0];
			const itemTwo: MenuItem = menu.getMenuItems()[1];

			assert.equal(itemOne.getText(), 'Item One');
			assert.equal(itemTwo.getText(), 'Item Two');

			assert.equal(menu.getPopupWindow().isShown(), false);
			button.getContainer().click();
			assert.equal(menu.getPopupWindow().isShown(), true);

			assert.equal(itemClick.callCount, 0);
			const clickEvent = new window.MouseEvent('click', { view: window, bubbles: true, cancelable: true });
			itemOne.getLayout().item.dispatchEvent(clickEvent);
			assert.equal(itemClick.callCount, 1);

			assert.equal(itemMouseEnter.callCount, 0);
			const mouseOverEvent = new window.MouseEvent('mouseenter', { view: window, bubbles: true, cancelable: true });
			itemOne.getLayout().item.dispatchEvent(mouseOverEvent);
			assert.equal(itemMouseEnter.callCount, 1);

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonStyle.NO_CAPS} ${ButtonStyle.DROPDOWN}"><span class="ui-btn-text">${caption}</span></button>`
			);
		});

		it('Should create a link button', () => {
			const caption = 'Link';
			const button = new Button({ link: '/path/to/', text: caption });

			assert.equal(
				button.render().outerHTML,
				`<a class="${button.getBaseClass()}" href="/path/to/"><span class="ui-btn-text">${caption}</span></a>`
			);
			assert.equal(button.getLink(), '/path/to/');

			button.setLink('/aaa/?a=4&b=2');
			assert.equal(button.getLink(), '/aaa/?a=4&b=2');

			assert.equal(
				button.render().outerHTML,
				`<a class="${button.getBaseClass()}" href="/aaa/?a=4&amp;b=2"><span class="ui-btn-text">${caption}</span></a>`
			);
		});

		it('Should set a max-width', () => {
			const button = new Button({ maxWidth: 256, text: 'Text' });

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}" style="max-width: 256px;"><span class="ui-btn-text">Text</span></button>`
			);

			button.setMaxWidth(133);

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}" style="max-width: 133px;"><span class="ui-btn-text">Text</span></button>`
			);

			button.setMaxWidth(null);

			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}" style=""><span class="ui-btn-text">Text</span></button>`
			);
		});

		it('Should set a counter', () => {
			const button = new Button({ counter: 256, text: 'Text' });

			assert.equal(button.getCounter(), 256);
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}">` +
					`<span class="ui-btn-text">Text</span>` +
					`<span class="ui-btn-counter">256</span>` +
				`</button>`
			);

			button.setCounter('90+');
			assert.equal(button.getCounter(), '90+');
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}">` +
				`<span class="ui-btn-text">Text</span>` +
				`<span class="ui-btn-counter">90+</span>` +
				`</button>`
			);

			button.setCounter(null);
			assert.equal(button.getCounter(), null);
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()}">` +
				`<span class="ui-btn-text">Text</span>` +
				`</button>`
			);
		});
	});

	describe('Presets', () => {

		it('Should create an Add Button', () => {
			const button = new AddButton();
			assert.equal(button.getText(), 'Add');
			assert.equal(button.getColor(), ButtonColor.SUCCESS);
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonColor.SUCCESS}">` +
					`<span class="ui-btn-text">Add</span>` +
				`</button>`
			);
		});

		it('Should create an Apply Button', () => {
			const button = new ApplyButton();
			assert.equal(button.getText(), 'Apply');
			assert.equal(button.getColor(), ButtonColor.LIGHT_BORDER);
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonColor.LIGHT_BORDER}">` +
					`<span class="ui-btn-text">Apply</span>` +
				`</button>`
			);
		});

		it('Should create a Cancel Button', () => {
			const button = new CancelButton();
			assert.equal(button.getText(), 'Cancel');
			assert.equal(button.getColor(), ButtonColor.LINK);
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonColor.LINK}">` +
					`<span class="ui-btn-text">Cancel</span>` +
				`</button>`
			);
		});

		it('Should create a Cancel Button', () => {
			const button = new CloseButton();
			assert.equal(button.getText(), 'Close');
			assert.equal(button.getColor(), ButtonColor.LINK);
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonColor.LINK}">` +
					`<span class="ui-btn-text">Close</span>` +
				`</button>`
			);
		});

		it('Should create a Create Button', () => {
			const button = new CreateButton();
			assert.equal(button.getText(), 'Create');
			assert.equal(button.getColor(), ButtonColor.SUCCESS);
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonColor.SUCCESS}">` +
					`<span class="ui-btn-text">Create</span>` +
				`</button>`
			);
		});

		it('Should create a Create Button', () => {
			const button = new SaveButton();
			assert.equal(button.getText(), 'Save');
			assert.equal(button.getColor(), ButtonColor.SUCCESS);
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonColor.SUCCESS}">` +
					`<span class="ui-btn-text">Save</span>` +
				`</button>`
			);
		});

		it('Should create a Create Button', () => {
			const button = new SendButton();
			assert.equal(button.getText(), 'Send');
			assert.equal(button.getColor(), ButtonColor.SUCCESS);
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonColor.SUCCESS}">` +
					`<span class="ui-btn-text">Send</span>` +
				`</button>`
			);
		});

		it('Should create a Settings Button', () => {
			const button = new SettingsButton();
			assert.equal(button.getText(), '');
			assert.equal(button.getColor(), ButtonColor.LIGHT_BORDER);
			assert.equal(button.getIcon(), ButtonIcon.SETTING);
			assert.equal(
				button.render().outerHTML,
				`<button class="${button.getBaseClass()} ${ButtonColor.LIGHT_BORDER} ${ButtonIcon.SETTING}"></button>`
			);
		});

	});

	describe('Creation from a DOM node', () => {

		describe('Basic buttons', () => {

			it('Case 1', () => {
				const html = `<button class="ui-btn ui-btn-lg" data-btn-uniqid="my-id">Large</button>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				assert.equal(button.getText(), 'Large');
				assert.equal(button.getId(), 'my-id');
				assert.equal(button.getTag(), ButtonTag.BUTTON);
				assert.equal(button.getSize(), ButtonSize.LARGE);
				assert.equal(
					button.getContainer().outerHTML,
					`<button class="ui-btn ui-btn-lg" data-btn-uniqid="my-id"><span class="ui-btn-text">Large</span></button>`
				);
			});

			it('Case 1-2', () => {
				const html = `<button class="ui-btn ui-btn-lg" data-btn-uniqid="my-id"><span class="ui-btn-text">Large</span></button>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				assert.equal(button.getText(), 'Large');
				assert.equal(button.getId(), 'my-id');
				assert.equal(button.getTag(), ButtonTag.BUTTON);
				assert.equal(button.getSize(), ButtonSize.LARGE);
				assert.equal(button.getContainer().outerHTML, html);
			});

			it('Case 2', () => {
				const html = `<a class="ui-btn ui-btn-xs ui-btn-hover ui-btn-dropdown">link</a>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				assert.equal(button.getText(), 'link');
				assert.equal(button.getTag(), ButtonTag.LINK);
				assert.equal(button.getSize(), ButtonSize.EXTRA_SMALL);
				assert.equal(button.getState(), ButtonState.HOVER);
				assert.equal(button.isDropdown(), true);
				assert.equal(
					button.getContainer().outerHTML,
					`<a class="ui-btn ui-btn-xs ui-btn-hover ui-btn-dropdown"><span class="ui-btn-text">link</span></a>`
				);
			});

			it('Case 2-2', () => {
				const html = `<a class="ui-btn ui-btn-xs ui-btn-hover ui-btn-dropdown"><span class="ui-btn-text">link</span></a>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				assert.equal(button.getText(), 'link');
				assert.equal(button.getTag(), ButtonTag.LINK);
				assert.equal(button.getSize(), ButtonSize.EXTRA_SMALL);
				assert.equal(button.getState(), ButtonState.HOVER);
				assert.equal(button.isDropdown(), true);
				assert.equal(button.getContainer().outerHTML, html);
			});

			it('Case 3', () => {
				const html = `<button class="ui-btn ui-btn-lg ui-btn-danger-dark ui-btn-icon-info ui-btn-hover">&lt;br&gt;</button>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				assert.equal(button.getText(), '<br>');
				assert.equal(button.getTag(), ButtonTag.BUTTON);
				assert.equal(button.getSize(), ButtonSize.LARGE);
				assert.equal(button.getColor(), ButtonColor.DANGER_DARK);
				assert.equal(button.getIcon(), ButtonIcon.INFO);
				assert.equal(button.getState(), ButtonState.HOVER);
				assert.equal(button.isHover(), true);
				assert.equal(
					button.getContainer().outerHTML,
					`<button class="ui-btn ui-btn-lg ui-btn-danger-dark ui-btn-icon-info ui-btn-hover"><span class="ui-btn-text">&lt;br&gt;</span></button>`
				);
			});

			it('Case 3-2', () => {
				const html = `<button class="ui-btn ui-btn-lg ui-btn-danger-dark ui-btn-icon-info ui-btn-hover"><span class="ui-btn-text">&lt;br&gt;</span></button>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				assert.equal(button.getText(), '<br>');
				assert.equal(button.getTag(), ButtonTag.BUTTON);
				assert.equal(button.getSize(), ButtonSize.LARGE);
				assert.equal(button.getColor(), ButtonColor.DANGER_DARK);
				assert.equal(button.getIcon(), ButtonIcon.INFO);
				assert.equal(button.getState(), ButtonState.HOVER);
				assert.equal(button.isHover(), true);
				assert.equal(button.getContainer().outerHTML, html);
			});

			it('Case 4', () => {
				const html = `<input type="submit" class="ui-btn ui-btn-md ui-btn-success-light ui-btn-active ui-btn-round" value="submit">`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				assert.equal(button.getText(), 'submit');
				assert.equal(button.getTag(), ButtonTag.SUBMIT);
				assert.equal(button.getSize(), ButtonSize.MEDIUM);
				assert.equal(button.getColor(), ButtonColor.SUCCESS_LIGHT);
				assert.equal(button.getIcon(), null);
				assert.equal(button.getState(), ButtonState.ACTIVE);
				assert.equal(button.isActive(), true);
				assert.equal(button.isRound(), true);
				assert.equal(button.isDropdown(), false);
				assert.equal(button.isHover(), false);
				assert.equal(button.getContainer().outerHTML, html);
			});

			it('Case 5', () => {
				const html = `<button class="ui-btn ui-btn-disabled ui-btn-dropdown" disabled="true">Disabled</button>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				assert.equal(button.getText(), 'Disabled');
				assert.equal(button.getTag(), ButtonTag.BUTTON);
				assert.equal(button.getSize(), null);
				assert.equal(button.getColor(), null);
				assert.equal(button.getIcon(), null);
				assert.equal(button.getState(), ButtonState.DISABLED);
				assert.equal(button.isDisabled(), true);
				assert.equal(button.isDropdown(), true);
				assert.equal(
					button.getContainer().outerHTML,
					`<button class="ui-btn ui-btn-disabled ui-btn-dropdown" disabled="true"><span class="ui-btn-text">Disabled</span></button>`
				);
			});

			it('Case 5-2', () => {
				const html = `<button class="ui-btn ui-btn-disabled ui-btn-dropdown" disabled="true"><span class="ui-btn-text">Disabled</span></button>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				assert.equal(button.getText(), 'Disabled');
				assert.equal(button.getTag(), ButtonTag.BUTTON);
				assert.equal(button.getSize(), null);
				assert.equal(button.getColor(), null);
				assert.equal(button.getIcon(), null);
				assert.equal(button.getState(), ButtonState.DISABLED);
				assert.equal(button.isDisabled(), true);
				assert.equal(button.isDropdown(), true);
				assert.equal(button.getContainer().outerHTML, html);
			});

			it('Case 6', () => {
				const html = `<button class="ui-btn" lang="ru" data-id="123" disabled="true">Disabled</button>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				assert.equal(button.getText(), 'Disabled');

				assert.equal(JSON.stringify(button.getProps()), `{"lang":"ru","disabled":"true"}`);
				assert.equal(
					button.getContainer().outerHTML,
					`<button class="ui-btn" lang="ru" data-id="123" disabled="true"><span class="ui-btn-text">Disabled</span></button>`
				);
			});

			it('Case 6-2', () => {
				const html = `<button class="ui-btn" lang="ru" data-id="123" disabled="true"><span class="ui-btn-text">Disabled</span></button>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				assert.equal(button.getText(), 'Disabled');

				assert.equal(JSON.stringify(button.getProps()), `{"lang":"ru","disabled":"true"}`);
				assert.equal(button.getContainer().outerHTML, html);
			});

		});

		describe('Counter', () => {
			it('Should a button with a counter', () => {

				const html =
					`<button class="ui-btn ui-btn-lg">Counter<span class="ui-btn-counter">234</span></button>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.equal(button.getText(), 'Counter');
				assert.equal(button.getCounter(), 234);

				assert.equal(
					button.getContainer().outerHTML,
					`<button class="ui-btn ui-btn-lg">` +
						`<span class="ui-btn-text">Counter</span>` +
						`<span class="ui-btn-counter">234</span>` +
					`</button>`
				);

			});

			it('Should a button with a counter 2', () => {

				const html =
					`<button class="ui-btn ui-btn-lg"><span class="ui-btn-counter" id="cnt">1</span></button>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.equal(button.getText(), '');
				assert.equal(button.getCounter(), 1);

				assert.equal(
					button.getContainer().outerHTML,
					`<button class="ui-btn ui-btn-lg">` +
						`<span class="ui-btn-counter" id="cnt">1</span>` +
					`</button>`
				);

			});

			it('Should a button with a counter 3', () => {

				const html =
					`<button class="ui-btn ui-btn-lg">` +
						`<span class="ui-btn-text">Counter2</span>` +
						`<span class="ui-btn-counter">90+</span>` +
					`</button>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.equal(button.getText(), 'Counter2');
				assert.equal(button.getCounter(), '90+');
				assert.equal(
					button.getContainer().outerHTML,
					`<button class="ui-btn ui-btn-lg">` +
						`<span class="ui-btn-text">Counter2</span>` +
						`<span class="ui-btn-counter">90+</span>` +
					`</button>`
				);
			});

			it('Should a button with a counter 4', () => {

				const html =
					`<button class="ui-btn ui-btn-lg" id="my-btn-id">` +
						`<span class="ui-btn-text" id="my-text-id"></span>` +
						`<span class="ui-btn-counter" id="my-counter-id">99+</span>` +
					`</button>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.equal(button.getText(), '');
				assert.equal(button.getCounter(), '99+');
				assert.equal(
					button.getContainer().outerHTML,
					`<button class="ui-btn ui-btn-lg" id="my-btn-id">` +
						`<span class="ui-btn-counter" id="my-counter-id">99+</span>` +
					`</button>`
				);
			});
		});

		describe('Dynamic behavior', () => {

			const Test = Reflection.namespace('BX.Test');

			it('Should bind events', () => {

				let onclickCount = 0;
				Test.onclick = function(btn, event) {
					onclickCount++;
					setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
						assert.equal(btn, button);
						assert.equal(event.type, 'click');
						assert.equal(this, Test.clickContext);
					}, 0);
				};

				let onmouseoverCount = 0;
				Test.onmouseover = function(btn, event) {
					onmouseoverCount++;
					setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
						assert.equal(btn, button);
						assert.equal(event.type, 'mouseover');
						assert.equal(this, Test.overContext);
					}, 0);
				};

				Test.clickContext = { a: 123 };
				Test.overContext = { b: 456 };

				const options = {
					onclick: {
						handler: 'BX.Test.onclick',
						context: 'BX.Test.clickContext'
					},
					events: {
						mouseover: {
							handler: 'BX.Test.onmouseover',
							context: 'BX.Test.overContext'
						}
					}
				};
				const html = `<button class="ui-btn" data-json-options="${Tag.safe`${JSON.stringify(options)}`}">Click</button>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);

				assert.equal(onclickCount, 0);
				button.getContainer().click();
				assert.equal(onclickCount, 1);

				assert.equal(onmouseoverCount, 0);
				const event = new window.MouseEvent('mouseover', { view: window, bubbles: true, cancelable: true });
				button.getContainer().dispatchEvent(event);
				assert.equal(onmouseoverCount, 1);

			});

			it('Should emit events', () => {

				const options = {
					onclick: {
						event: 'BX.Test:Button:onClick'
					},
					events: {
						mouseover: {
							event: 'BX.Test:Button:onMouserOver'
						}
					},
					menu: {
						items: [
							{
								text: 'One',
								onclick: {
									event: 'BX.Test:Button:onItemClick',
								},
							},
							{
								text: 'Two'
							}
						],
					}
				};
				const html = `<button class="ui-btn" data-json-options="${Tag.safe`${JSON.stringify(options)}`}">Click</button>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				button.setId('button-code');

				const buttonClick = sinon.stub().callsFake((event: BaseEvent) => {
					setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
						const { button: btn, event: mouseEvent } = event.getData();
						assert.equal(btn, button);
						assert.equal(mouseEvent.type, 'click');
					}, 0);
				});

				const buttonMouseOver = sinon.stub().callsFake((event: BaseEvent) => {
					setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
						const { button: btn, event: mouseEvent } = event.getData();
						assert.equal(btn, button);
						assert.equal(mouseEvent.type, 'mouseover');
					}, 0);
				});

				const itemClick = sinon.stub().callsFake((event: BaseEvent) => {
					setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
						const { item, event: mouseEvent } = event.getData();
						assert.equal(item, menuItem);
						assert.equal(mouseEvent.type, 'click');
					}, 0);
				});

				EventEmitter.subscribe('BX.Test:Button:onClick', buttonClick);
				EventEmitter.subscribe('BX.Test:Button:onMouserOver', buttonMouseOver);
				EventEmitter.subscribe('BX.Test:Button:onItemClick', itemClick);

				assert.equal(buttonClick.callCount, 0);
				button.getContainer().click();
				assert.equal(buttonClick.callCount, 1);

				assert.equal(buttonMouseOver.callCount, 0);
				const event = new window.MouseEvent('mouseover', { view: window, bubbles: true, cancelable: true });
				button.getContainer().dispatchEvent(event);
				assert.equal(buttonMouseOver.callCount, 1);

				assert.equal(itemClick.callCount, 0);
				const menuItem: MenuItem = button.getMenuWindow().getMenuItems()[0];
				const clickEvent = new window.MouseEvent('click', { view: window, bubbles: true, cancelable: true });
				menuItem.getLayout().item.dispatchEvent(clickEvent);
				assert.equal(itemClick.callCount, 1);

			});

			it('Should eval code', () => {

				Test.onclickCount = 0;
				Test.onmouseoverCount = 0;

				const options = {
					onclick: {
						code:
							`
							const [button, event] = arguments;
							window.BX.Test.onclickCount++;
							setTimeout(() => {
								assert.equal(event.type, 'click');
								assert.equal(button.getId(), 'button-code');
							}, 0);
							
							`
					},
					events: {
						mouseover: {
							code: `
								const [button, event] = arguments;
								window.BX.Test.onmouseoverCount++;
								setTimeout(() => {
									assert.equal(event.type, 'mouseover');
									assert.equal(button.getId(), 'button-code');
								}, 0); 
							`
						}
					},
				};
				const html = `<button class="ui-btn" data-json-options="${Tag.safe`${JSON.stringify(options)}`}">Click</button>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				button.setId('button-code');

				assert.equal(Test.onclickCount, 0);
				button.getContainer().click();
				assert.equal(Test.onclickCount, 1);

				assert.equal(Test.onmouseoverCount, 0);
				const event = new window.MouseEvent('mouseover', { view: window, bubbles: true, cancelable: true });
				button.getContainer().dispatchEvent(event);
				assert.equal(Test.onmouseoverCount, 1);
			});

			it('Should create a menu', () => {
				const options = {
					menu: {
						id: 'my-button-menu',
						items: [
							{
								text: 'One',
								onclick: {
									handler: 'BX.Test.itemClick',
									context: 'BX.Test.itemContext'
								},
								events: {
									onMouseEnter: { // EventEmitter events
										handler: 'BX.Test.itemMouseEnter',
										context: 'BX.Test.itemOverContext'
									}
								}
							},
							{
								text: 'Two'
							}
						],
						maxWidth: 143
					}
				};

				let itemClickCount = 0;
				Test.itemClick = function(event, item) {
					itemClickCount++;
					setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
						assert.equal(item, itemOne);
						assert.equal(event.type, 'click');
						assert.equal(this, Test.itemContext);
					}, 0);
				};

				let itemMouseEnterCount = 0;
				Test.itemMouseEnter = function(event: BaseEvent) {
					itemMouseEnterCount++;
					setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
						assert.equal(event.getTarget(), itemOne);
						assert.equal(this, Test.itemOverContext);
					}, 0);
				};

				Test.itemContext = { a: 123 };
				Test.itemOverContext = { b: 456 };

				const html = `<button class="ui-btn" data-json-options="${Tag.safe`${JSON.stringify(options)}`}">Click</button>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);

				const menu = button.getMenuWindow();
				assert.equal(menu.getId(), 'my-button-menu');
				assert.equal(menu.getPopupWindow().getMaxWidth(), 143);
				assert.equal(menu.getMenuItems().length, 2);

				const itemOne: MenuItem = menu.getMenuItems()[0];
				const itemTwo: MenuItem = menu.getMenuItems()[1];

				assert.equal(itemOne.getText(), 'One');
				assert.equal(itemTwo.getText(), 'Two');

				assert.equal(menu.getPopupWindow().isShown(), false);
				button.getContainer().click();
				assert.equal(menu.getPopupWindow().isShown(), true);

				assert.equal(itemClickCount, 0);
				const clickEvent = new window.MouseEvent('click', { view: window, bubbles: true, cancelable: true });
				itemOne.getLayout().item.dispatchEvent(clickEvent);
				assert.equal(itemClickCount, 1);

				assert.equal(itemMouseEnterCount, 0);
				const mouseOverEvent = new window.MouseEvent('mouseenter', { view: window, bubbles: true, cancelable: true });
				itemOne.getLayout().item.dispatchEvent(mouseOverEvent);
				assert.equal(itemMouseEnterCount, 1);

			});

			it('Should unset a dropdown mode for a button with a menu', () => {

				const options = {
					dropdown: false,
					menu: {
						items: [
							{ text: 'One' },
							{ text: 'Two' },
						]
					}
				};

				const html = `<button class="ui-btn" data-json-options="${Tag.safe`${JSON.stringify(options)}`}"><span class="ui-btn-text">Click</span></button>`;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof Button);
				assert.equal(button.isDropdown(), false);
				assert.equal(button.getMenuWindow().getMenuItems().length, 2);
				assert.equal(button.getContainer().outerHTML, html);
			});

		});
	});

});