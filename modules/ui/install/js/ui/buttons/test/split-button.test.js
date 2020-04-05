import {
	ButtonColor,
	ButtonIcon,
	ButtonManager,
	ButtonSize,
	ButtonTag,
	SplitButton,
	SplitButtonState,
	SplitSubButton,
	SplitSubButtonType
} from '../src';
import {
	AddSplitButton,
	ApplySplitButton,
	CancelSplitButton,
	CloseSplitButton,
	CreateSplitButton,
	SaveSplitButton,
	SendSplitButton
} from '../src';

import loadMessages from './load-messages';
import { Tag, Dom, Reflection } from 'main.core';
import { MenuItem } from 'main.popup';
import { BaseEvent, EventEmitter } from 'main.core.events';

describe('BX.UI.SplitButton', () => {

	loadMessages();

	describe('Basic usage', () => {
		it('Should render a button into DOM', () => {

			const container = document.createElement('div');
			const caption = 'Hello, Split Button!';

			const button = new SplitButton({ text: caption });
			button.renderTo(container);

			assert.equal(
				container.innerHTML,
				`<div class="ui-btn-split">` +
					`<button class="ui-btn-main">` +
						`<span class="ui-btn-text">${caption}</span>` +
					`</button>` +
					`<button class="ui-btn-menu"></button>` +
				`</div>`
			);
		});

		it('Should create a large button', () => {
			const caption = 'Large Button';
			const button = new SplitButton({
				size: ButtonSize.LARGE,
				text: caption
			});

			assert.equal(button.getSize(), ButtonSize.LARGE);
			assert.equal(button.getText(), caption);

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()} ${ButtonSize.LARGE}">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">${caption}</span></button><button class="ui-btn-menu"></button></div>`
			);
		});

		it('Should create a colored button', () => {
			const caption = 'Colored Button';
			const button = new SplitButton({
				color: ButtonColor.DANGER,
				text: caption
			});

			assert.equal(button.getColor(), ButtonColor.DANGER);
			assert.equal(button.getText(), caption);

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()} ${ButtonColor.DANGER}">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">${caption}</span></button><button class="ui-btn-menu"></button></div>`
			);
		});

		it('Should create a disabled button', () => {
			const caption = 'Disabled Button';
			const button = new SplitButton({
				state: SplitButtonState.DISABLED,
				text: caption
			});

			assert.equal(button.getState(), SplitButtonState.DISABLED);
			assert.equal(button.getText(), caption);

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()} ${SplitButtonState.DISABLED}">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">${caption}</span></button><button class="ui-btn-menu"></button></div>`
			);
		});

		it('Should create a button with an icon', () => {
			const caption = 'Icon Button';
			const button = new SplitButton({
				icon: ButtonIcon.BACK,
				text: caption
			});

			assert.equal(button.getIcon(), ButtonIcon.BACK);
			assert.equal(button.getText(), caption);

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()} ${ButtonIcon.BACK}">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">${caption}</span></button><button class="ui-btn-menu"></button></div>`
			);

		});

		it('Should set html properties and data attributes', () => {

			const caption = 'Advanced Button';
			const button = new SplitButton({
				id: 'my-split',
				text: caption,
				props: {
					lang: 'en'
				},
				dataset: {
					id: 'my-id'
				},
				mainButton: {
					props: {
						lang: 'br'
					},
					dataset: {
						id: 'my-main-id'
					}
				},
				menuButton: {
					props: {
						lang: 'ua'
					},
					dataset: {
						id: 'my-menu-id'
					}
				}
			});

			assert.equal(button.getText(), caption);

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}" lang="en" data-id="my-id">` +
				`<button class="ui-btn-main" lang="br" data-id="my-main-id"><span class="ui-btn-text">${caption}</span></button>` +
				`<button class="ui-btn-menu" lang="ua" data-id="my-menu-id"></button></div>`
			);

		});

		it('Should create a "hardcore" button', () => {

			const buttonClick = sinon.stub().callsFake((splitButton: SplitButton, event: MouseEvent) => {

				setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
					assert.equal(button, splitButton);
					assert.equal(event.type, 'click');
				}, 0);

				splitButton.setActive(!splitButton.isActive());
			});

			const mainButtonClick = sinon.stub().callsFake((splitSubButton: SplitSubButton, event: MouseEvent) => {

				setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
					assert.equal(mainButton, splitSubButton);
					assert.equal(event.type, 'click');
				}, 0);

				splitSubButton.setActive(!splitSubButton.isActive());
			});

			const menuButtonClick = sinon.stub().callsFake((splitSubButton: SplitSubButton, event: MouseEvent) => {

				setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
					assert.equal(menuButton, splitSubButton);
					assert.equal(event.type, 'click');
				}, 0);

				splitSubButton.setActive(!splitSubButton.isActive());
				event.preventDefault();
			});

			const buttonMouseEnter = sinon.stub().callsFake((splitButton: SplitButton, event: MouseEvent) => {

				setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
					assert.equal(button, splitButton);
					assert.ok([mouseEnterEvent, mainButtonMouseEnterEvent, menuButtonMouseEnterEvent].includes(event));
					assert.equal(event.type, 'mouseenter');
				}, 0);

				splitButton.setActive(!splitButton.isActive());
			});

			const buttonMouseLeave = sinon.stub().callsFake((splitButton: SplitButton, event: MouseEvent) => {

				setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
					assert.equal(button, splitButton);
					assert.equal(event, mouseLeaveEvent);
					assert.equal(event.type, 'mouseleave');
				}, 0);

				splitButton.setActive(!splitButton.isActive());
			});

			const mainButtonMouseEnter = sinon.stub().callsFake((splitSubButton: SplitSubButton, event: MouseEvent) => {

				setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
					assert.equal(mainButton, splitSubButton);
					assert.equal(event.type, 'mouseenter');
				}, 0);

				splitSubButton.setActive(!splitSubButton.isActive());
			});

			const menuButtonMouseEnter = sinon.stub().callsFake((splitSubButton: SplitSubButton, event: MouseEvent) => {

				setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
					assert.equal(menuButton, splitSubButton);
					assert.equal(event.type, 'mouseenter');
				}, 0);

				splitSubButton.setActive(!splitSubButton.isActive());
				event.preventDefault();
			});

			const closeItemClick = sinon.stub().callsFake((event: MouseEvent, item: MenuItem) => {

				setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
					assert.equal(item, closeItem);
					assert.equal(event, itemClickEvent);
					assert.equal(event.type, 'click');
				}, 0);

				item.getMenuWindow().close();

			});

			const button = new SplitButton({
				id: 'my-split-button',
				text: 'Hello, Split Button!',
				noCaps: true,
				className: 'ddddd-dddd',
				size: ButtonSize.MEDIUM,
				color: ButtonColor.SECONDARY,
				state: SplitButtonState.MAIN_ACTIVE,
				props: {
					id: 'xxx'
				},
				onclick: buttonClick,
				events: {
					mouseenter: buttonMouseEnter,
					mouseleave: buttonMouseLeave
				},
				menu: {
					items: [
						{ text: 'Link', href: '/path/to/page' },
						{ text: 'Edit', disabled: true },
						{
							text: 'Close',
							onclick: closeItemClick
						}
					],
					minHeight: 5,
					animation: null
				},
				menuTarget: SplitSubButtonType.MENU,
				mainButton: {
					dataset: {
						mainBtn: 'bbb'
					},
					props: {
						lang: 'ru'
					},
					onclick: mainButtonClick,
					events: {
						mouseenter: mainButtonMouseEnter
					}
				},
				menuButton: {
					tag: ButtonTag.LINK,
					onclick: menuButtonClick,
					dataset: {
						menuBtn: 'aaa'
					},
					props: {
						href: '/path/'
					},
					events: {
						mouseenter: menuButtonMouseEnter
					}
				}
			});

			assert.equal(button.getId(), 'my-split-button');
			assert.equal(button.getText(), 'Hello, Split Button!');
			assert.equal(button.isNoCaps(), true);
			assert.equal(Dom.hasClass(button.getContainer(), 'ddddd-dddd'), true);
			assert.equal(button.getSize(), ButtonSize.MEDIUM);
			assert.equal(button.getColor(), ButtonColor.SECONDARY);
			assert.equal(button.getState(), SplitButtonState.MAIN_ACTIVE);
			assert.equal(button.getProps().id, 'xxx');
			assert.equal(button.getMenuTarget(), SplitSubButtonType.MENU);

			assert.equal(buttonClick.callCount, 0);
			assert.equal(mainButtonClick.callCount, 0);
			assert.equal(menuButtonClick.callCount, 0);
			assert.equal(buttonMouseEnter.callCount, 0);
			assert.equal(buttonMouseLeave.callCount, 0);
			assert.equal(mainButtonMouseEnter.callCount, 0);
			assert.equal(menuButtonMouseEnter.callCount, 0);

			assert.equal(button.getMenuWindow().getPopupWindow().isShown(), false);
			button.getMenuButton().getContainer().click();
			assert.equal(button.getMenuWindow().getPopupWindow().isShown(), true);
			assert.equal(button.getMenuWindow().getMenuItems().length, 3);
			assert.equal(button.getMenuWindow().getPopupWindow().getMinHeight(), 5);

			const closeItem = button.getMenuWindow().getMenuItems()[2];
			assert.equal(closeItem.getText(), 'Close');

			assert.equal(buttonClick.callCount, 1);
			assert.equal(mainButtonClick.callCount, 0);
			assert.equal(menuButtonClick.callCount, 1);
			assert.equal(buttonMouseEnter.callCount, 0);
			assert.equal(buttonMouseLeave.callCount, 0);
			assert.equal(mainButtonMouseEnter.callCount, 0);
			assert.equal(menuButtonMouseEnter.callCount, 0);

			const mouseEnterEvent = new window.MouseEvent('mouseenter', { view: window, });
			const mouseLeaveEvent = new window.MouseEvent('mouseleave', { view: window, });
			button.getContainer().dispatchEvent(mouseEnterEvent);

			assert.equal(buttonClick.callCount, 1);
			assert.equal(mainButtonClick.callCount, 0);
			assert.equal(menuButtonClick.callCount, 1);
			assert.equal(buttonMouseEnter.callCount, 1);
			assert.equal(buttonMouseLeave.callCount, 0);
			assert.equal(mainButtonMouseEnter.callCount, 0);
			assert.equal(menuButtonMouseEnter.callCount, 0);

			button.getContainer().dispatchEvent(mouseLeaveEvent);

			assert.equal(buttonClick.callCount, 1);
			assert.equal(mainButtonClick.callCount, 0);
			assert.equal(menuButtonClick.callCount, 1);
			assert.equal(buttonMouseEnter.callCount, 1);
			assert.equal(buttonMouseLeave.callCount, 1);
			assert.equal(mainButtonMouseEnter.callCount, 0);
			assert.equal(menuButtonMouseEnter.callCount, 0);

			assert.equal(closeItemClick.callCount, 0);
			const itemClickEvent = new window.MouseEvent('click', { view: window, });
			closeItem.getLayout().item.dispatchEvent(itemClickEvent);
			assert.equal(button.getMenuWindow().getPopupWindow().isShown(), false);
			assert.equal(closeItemClick.callCount, 1);

			const mainButton = button.getMainButton();
			const menuButton = button.getMenuButton();

			assert.equal(mainButton.getTag(), ButtonTag.BUTTON);
			assert.equal(menuButton.getTag(), ButtonTag.LINK);

			button.getMainButton().getContainer().click();

			assert.equal(buttonClick.callCount, 2);
			assert.equal(mainButtonClick.callCount, 1);
			assert.equal(menuButtonClick.callCount, 1);
			assert.equal(buttonMouseEnter.callCount, 1);
			assert.equal(buttonMouseLeave.callCount, 1);
			assert.equal(mainButtonMouseEnter.callCount, 0);
			assert.equal(menuButtonMouseEnter.callCount, 0);

			assert.equal(mainButton.getDataSet().mainBtn, 'bbb');
			assert.equal(menuButton.getDataSet().menuBtn, 'aaa');
			assert.equal(JSON.stringify(mainButton.getProps()), `{"lang":"ru"}`);
			assert.equal(menuButton.getProps().href, '/path/');

			const mainButtonMouseEnterEvent = new window.MouseEvent('mouseenter', { view: window, bubbles: true });
			const menuButtonMouseEnterEvent = new window.MouseEvent('mouseenter', { view: window, bubbles: true });

			button.getMainButton().getContainer().dispatchEvent(mainButtonMouseEnterEvent);

			assert.equal(buttonClick.callCount, 2);
			assert.equal(mainButtonClick.callCount, 1);
			assert.equal(menuButtonClick.callCount, 1);
			assert.equal(buttonMouseEnter.callCount, 2);
			assert.equal(buttonMouseLeave.callCount, 1);
			assert.equal(mainButtonMouseEnter.callCount, 1);
			assert.equal(menuButtonMouseEnter.callCount, 0);

			button.getMenuButton().getContainer().dispatchEvent(menuButtonMouseEnterEvent);

			assert.equal(buttonClick.callCount, 2);
			assert.equal(mainButtonClick.callCount, 1);
			assert.equal(menuButtonClick.callCount, 1);
			assert.equal(buttonMouseEnter.callCount, 3);
			assert.equal(buttonMouseLeave.callCount, 1);
			assert.equal(mainButtonMouseEnter.callCount, 1);
			assert.equal(menuButtonMouseEnter.callCount, 1);
		});

		it('Should create a link button', () => {
			const caption = 'Link';
			const button = new SplitButton({ link: '/split/button', text: caption });

			assert.equal(button.getText(), caption);

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<a class="ui-btn-main" href="/split/button"><span class="ui-btn-text">${caption}</span></a>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setLink('/split2/');


			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<a class="ui-btn-main" href="/split2/"><span class="ui-btn-text">${caption}</span></a>` +
				`<button class="ui-btn-menu"></button></div>`
			);

		});

		it('Should set a max-width', () => {
			const caption = 'Link';
			const button = new SplitButton({ maxWidth: 155, text: caption });

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}" style="max-width: 155px;">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">${caption}</span></button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setMaxWidth(199);

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}" style="max-width: 199px;">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">${caption}</span></button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setMaxWidth(null);

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}" style="">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">${caption}</span></button>` +
				`<button class="ui-btn-menu"></button></div>`
			);
		});

		it('Should set a counter', () => {
			const caption = 'Link';
			const button = new SplitButton({ counter: 155, text: caption });

			assert.equal(button.getCounter(), 155);
			assert.equal(
				button.render().outerHTML,
				`<div class="${button.getBaseClass()}">` +
					`<button class="ui-btn-main">` +
						`<span class="ui-btn-text">Link</span>` +
						`<span class="ui-btn-counter">155</span>` +
					`</button>` +
					`<button class="ui-btn-menu"></button>` +
				`</div>`
			);

			button.setCounter('88+');
			button.setText('New Link');
			assert.equal(button.getCounter(), '88+');
			assert.equal(
				button.render().outerHTML,
				`<div class="${button.getBaseClass()}">` +
					`<button class="ui-btn-main">` +
						`<span class="ui-btn-text">New Link</span>` +
						`<span class="ui-btn-counter">88+</span>` +
					`</button>` +
					`<button class="ui-btn-menu"></button>` +
				`</div>`
			);

			button.setCounter(null);
			button.setText('');

			assert.equal(button.getCounter(), null);
			assert.equal(
				button.render().outerHTML,
				`<div class="${button.getBaseClass()}">` +
					`<button class="ui-btn-main">` +
					`</button>` +
					`<button class="ui-btn-menu"></button>` +
				`</div>`
			);
		});

		it('Should create a container for a caption', () => {

			const caption = 'Link';
			const button = new SplitButton({ text: caption });

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">${caption}</span></button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setText('');

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main"></button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setCounter(123);

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main"><span class="ui-btn-counter">123</span></button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setText('New Text');

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main">` +
					`<span class="ui-btn-text">New Text</span>` +
					`<span class="ui-btn-counter">123</span>` +
				`</button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setText('New Text 2');

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main">` +
					`<span class="ui-btn-text">New Text 2</span>` +
					`<span class="ui-btn-counter">123</span>` +
				`</button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setText('');

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main">` +
					`<span class="ui-btn-counter">123</span>` +
				`</button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setText('ABC');

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main">` +
					`<span class="ui-btn-text">ABC</span>` +
					`<span class="ui-btn-counter">123</span>` +
				`</button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setCounter('');

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main">` +
					`<span class="ui-btn-text">ABC</span>` +
				`</button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setText('');

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main">` +
				`</button>` +
				`<button class="ui-btn-menu"></button></div>`
			);
		});

		it('Shouldn\'t create a container for an empty caption', () => {
			const button = new SplitButton();

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main"></button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setCounter(123);

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main"><span class="ui-btn-counter">123</span></button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setText('New Text');

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main">` +
					`<span class="ui-btn-text">New Text</span>` +
					`<span class="ui-btn-counter">123</span>` +
				`</button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setText('New Text 2');

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main">` +
					`<span class="ui-btn-text">New Text 2</span>` +
					`<span class="ui-btn-counter">123</span>` +
				`</button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setText('');

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main">` +
					`<span class="ui-btn-counter">123</span>` +
				`</button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setText('ABC');

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main">` +
					`<span class="ui-btn-text">ABC</span>` +
					`<span class="ui-btn-counter">123</span>` +
				`</button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setCounter('');

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main">` +
					`<span class="ui-btn-text">ABC</span>` +
				`</button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

			button.setText('');

			assert.equal(
				button.getContainer().outerHTML,
				`<div class="${button.getBaseClass()}">` +
				`<button class="ui-btn-main">` +
				`</button>` +
				`<button class="ui-btn-menu"></button></div>`
			);

		});
	});

	describe('Presets', () => {

		it('Should create an Add Button', () => {
			const button = new AddSplitButton();
			assert.equal(button.getText(), 'Add');
			assert.equal(button.getColor(), ButtonColor.SUCCESS);
			assert.equal(
				button.render().outerHTML,
				`<div class="${button.getBaseClass()} ${ButtonColor.SUCCESS}">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">Add</span></button><button class="ui-btn-menu"></button></div>`
			);
		});
		it('Should create an Apply Button', () => {
			const button = new ApplySplitButton();
			assert.equal(button.getText(), 'Apply');
			assert.equal(button.getColor(), ButtonColor.LIGHT_BORDER);
			assert.equal(
				button.render().outerHTML,
				`<div class="${button.getBaseClass()} ${ButtonColor.LIGHT_BORDER}">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">Apply</span></button><button class="ui-btn-menu"></button></div>`
			);
		});
		it('Should create a Cancel Button', () => {
			const button = new CancelSplitButton();
			assert.equal(button.getText(), 'Cancel');
			assert.equal(button.getColor(), ButtonColor.LINK);
			assert.equal(
				button.render().outerHTML,
				`<div class="${button.getBaseClass()} ${ButtonColor.LINK}">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">Cancel</span></button><button class="ui-btn-menu"></button></div>`
			);
		});
		it('Should create a Close Button', () => {
			const button = new CloseSplitButton();
			assert.equal(button.getText(), 'Close');
			assert.equal(button.getColor(), ButtonColor.LINK);
			assert.equal(
				button.render().outerHTML,
				`<div class="${button.getBaseClass()} ${ButtonColor.LINK}">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">Close</span></button><button class="ui-btn-menu"></button></div>`
			);
		});
		it('Should create a Create Button', () => {
			const button = new CreateSplitButton();
			assert.equal(button.getText(), 'Create');
			assert.equal(button.getColor(), ButtonColor.SUCCESS);
			assert.equal(
				button.render().outerHTML,
				`<div class="${button.getBaseClass()} ${ButtonColor.SUCCESS}">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">Create</span></button><button class="ui-btn-menu"></button></div>`
			);
		});
		it('Should create a Save Button', () => {
			const button = new SaveSplitButton();
			assert.equal(button.getText(), 'Save');
			assert.equal(button.getColor(), ButtonColor.SUCCESS);
			assert.equal(
				button.render().outerHTML,
				`<div class="${button.getBaseClass()} ${ButtonColor.SUCCESS}">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">Save</span></button><button class="ui-btn-menu"></button></div>`
			);
		});
		it('Should create a Send Button', () => {
			const button = new SendSplitButton();
			assert.equal(button.getText(), 'Send');
			assert.equal(button.getColor(), ButtonColor.SUCCESS);
			assert.equal(
				button.render().outerHTML,
				`<div class="${button.getBaseClass()} ${ButtonColor.SUCCESS}">` +
				`<button class="ui-btn-main"><span class="ui-btn-text">Send</span></button><button class="ui-btn-menu"></button></div>`
			);
		});

	});

	describe('Creation from a DOM node', () => {

		describe('Should create basic buttons', () => {

			it('Case 1', () => {
				const html =
					`<div class="ui-btn-split ui-btn-lg">` +
					`<button class="ui-btn-main" lang="ru">Split</button>` +
					`<button class="ui-btn-menu"></button></div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);
				assert.equal(button.getText(), 'Split');
				assert.equal(button.getTag(), ButtonTag.DIV);
				assert.equal(button.getSize(), ButtonSize.LARGE);

				assert.equal(button.getMainButton().isMainButton(), true);
				assert.equal(button.getMenuButton().isMenuButton(), true);
				assert.equal(button.getMainButton().isMenuButton(), false);
				assert.equal(button.getMenuButton().isMainButton(), false);

				assert.equal(button.getMainButton().getText(), 'Split');
				assert.equal(button.getMenuButton().getText(), '');

				assert.equal(
					button.getMainButton().getContainer().outerHTML,
					`<button class="ui-btn-main" lang="ru"><span class="ui-btn-text">Split</span></button>`
				);

				assert.equal(
					button.getMenuButton().getContainer().outerHTML,
					`<button class="ui-btn-menu"></button>`
				);

				assert.equal(
					button.getContainer().outerHTML,
					`<div class="ui-btn-split ui-btn-lg">` +
					`<button class="ui-btn-main" lang="ru"><span class="ui-btn-text">Split</span></button>` +
					`<button class="ui-btn-menu"></button></div>`
				);
			});

			it('Case 1-2', () => {
				const html =
					`<div class="ui-btn-split ui-btn-lg">` +
					`<button class="ui-btn-main" lang="ru"><span class="ui-btn-text">Split</span></button>` +
					`<button class="ui-btn-menu"></button></div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);
				assert.equal(button.getText(), 'Split');
				assert.equal(button.getTag(), ButtonTag.DIV);
				assert.equal(button.getSize(), ButtonSize.LARGE);

				assert.equal(button.getMainButton().isMainButton(), true);
				assert.equal(button.getMenuButton().isMenuButton(), true);
				assert.equal(button.getMainButton().isMenuButton(), false);
				assert.equal(button.getMenuButton().isMainButton(), false);

				assert.equal(button.getMainButton().getText(), 'Split');
				assert.equal(button.getMenuButton().getText(), '');

				assert.equal(
					button.getMainButton().getContainer().outerHTML,
					`<button class="ui-btn-main" lang="ru"><span class="ui-btn-text">Split</span></button>`
				);

				assert.equal(
					button.getMenuButton().getContainer().outerHTML,
					`<button class="ui-btn-menu"></button>`
				);

				assert.equal(button.getContainer().outerHTML, html);
			});

			it('Case 2', () => {
				const html =
					`<div class="ui-btn-split ui-btn-md ui-btn-success-dark ui-btn-icon-info ui-btn-menu-active ui-btn-no-caps">` +
					`<a class="ui-btn-main">my split button</a><a class="ui-btn-menu"></a></div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);

				assert.equal(button.getText(), 'my split button');
				assert.equal(button.getMainButton().getText(), 'my split button');
				assert.equal(button.getMenuButton().getText(), '');

				assert.equal(button.getTag(), ButtonTag.DIV);
				assert.equal(button.getMainButton().getTag(), ButtonTag.LINK);
				assert.equal(button.getMenuButton().getTag(), ButtonTag.LINK);

				assert.equal(button.getSize(), ButtonSize.MEDIUM);
				assert.equal(button.getIcon(), ButtonIcon.INFO);
				assert.equal(button.getColor(), ButtonColor.SUCCESS_DARK);
				assert.equal(button.getState(), SplitButtonState.MENU_ACTIVE);
				assert.equal(button.isNoCaps(), true);
				assert.equal(button.isDropdown(), true);
				assert.equal(button.isRound(), false);

				assert.equal(button.isActive(), false);
				assert.equal(button.getMainButton().isActive(), false);
				assert.equal(button.getMenuButton().isActive(), true);

				assert.equal(
					button.getContainer().outerHTML,
					`<div class="ui-btn-split ui-btn-md ui-btn-success-dark ui-btn-icon-info ui-btn-menu-active ui-btn-no-caps">` +
					`<a class="ui-btn-main"><span class="ui-btn-text">my split button</span></a><a class="ui-btn-menu"></a></div>`
				);
			});

			it('Case 2-2', () => {
				const html =
					`<div class="ui-btn-split ui-btn-md ui-btn-success-dark ui-btn-icon-info ui-btn-menu-active ui-btn-no-caps">` +
					`<a class="ui-btn-main"><span class="ui-btn-text">my split button</span></a><a class="ui-btn-menu"></a></div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);

				assert.equal(button.getText(), 'my split button');
				assert.equal(button.getMainButton().getText(), 'my split button');
				assert.equal(button.getMenuButton().getText(), '');

				assert.equal(button.getTag(), ButtonTag.DIV);
				assert.equal(button.getMainButton().getTag(), ButtonTag.LINK);
				assert.equal(button.getMenuButton().getTag(), ButtonTag.LINK);

				assert.equal(button.getSize(), ButtonSize.MEDIUM);
				assert.equal(button.getIcon(), ButtonIcon.INFO);
				assert.equal(button.getColor(), ButtonColor.SUCCESS_DARK);
				assert.equal(button.getState(), SplitButtonState.MENU_ACTIVE);
				assert.equal(button.isNoCaps(), true);
				assert.equal(button.isDropdown(), true);
				assert.equal(button.isRound(), false);

				assert.equal(button.isActive(), false);
				assert.equal(button.getMainButton().isActive(), false);
				assert.equal(button.getMenuButton().isActive(), true);

				assert.equal(button.getContainer().outerHTML, html);
			});

			it('Case 3', () => {
				const html = `<div class="ui-btn-split ui-btn-disabled ui-btn-no-caps">` +
					`<button class="ui-btn-main" disabled="true">Disabled</button>` +
					`<button class="ui-btn-menu" disabled="true"></button></div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);

				assert.equal(button.getText(), 'Disabled');
				assert.equal(button.getMainButton().getText(), 'Disabled');
				assert.equal(button.getMenuButton().getText(), '');

				assert.equal(button.getMainButton().getTag(), ButtonTag.BUTTON);
				assert.equal(button.getMenuButton().getTag(), ButtonTag.BUTTON);

				assert.equal(button.getSize(), null);
				assert.equal(button.getIcon(), null);
				assert.equal(button.getColor(), null);
				assert.equal(button.getState(), SplitButtonState.DISABLED);
				assert.equal(button.isNoCaps(), true);
				assert.equal(button.isDropdown(), true);

				assert.equal(button.isDisabled(), true);
				assert.equal(button.getMainButton().isDisabled(), true);
				assert.equal(button.getMenuButton().isDisabled(), true);

				assert.equal(
					button.getContainer().outerHTML,
					`<div class="ui-btn-split ui-btn-disabled ui-btn-no-caps">` +
					`<button class="ui-btn-main" disabled="true"><span class="ui-btn-text">Disabled</span></button>` +
					`<button class="ui-btn-menu" disabled="true"></button></div>`
				);
			});

			it('Case 3-2', () => {
				const html = `<div class="ui-btn-split ui-btn-disabled ui-btn-no-caps">` +
					`<button class="ui-btn-main" disabled="true"><span class="ui-btn-text">Disabled</span></button>` +
					`<button class="ui-btn-menu" disabled="true"></button></div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);

				assert.equal(button.getText(), 'Disabled');
				assert.equal(button.getMainButton().getText(), 'Disabled');
				assert.equal(button.getMenuButton().getText(), '');

				assert.equal(button.getMainButton().getTag(), ButtonTag.BUTTON);
				assert.equal(button.getMenuButton().getTag(), ButtonTag.BUTTON);

				assert.equal(button.getSize(), null);
				assert.equal(button.getIcon(), null);
				assert.equal(button.getColor(), null);
				assert.equal(button.getState(), SplitButtonState.DISABLED);
				assert.equal(button.isNoCaps(), true);
				assert.equal(button.isDropdown(), true);

				assert.equal(button.isDisabled(), true);
				assert.equal(button.getMainButton().isDisabled(), true);
				assert.equal(button.getMenuButton().isDisabled(), true);

				assert.equal(button.getContainer().outerHTML, html);
			});

			it('Case 4', () => {
				const html =
					`<div class="ui-btn-split ui-btn-main-disabled ui-btn-no-caps">` +
					`<button class="ui-btn-main" disabled="true">Disabled Main</button>` +
					`<button class="ui-btn-menu"></button></div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);

				assert.equal(button.getText(), 'Disabled Main');
				assert.equal(button.getMainButton().getText(), 'Disabled Main');

				assert.equal(button.getState(), SplitButtonState.MAIN_DISABLED);
				assert.equal(button.isNoCaps(), true);
				assert.equal(button.isDropdown(), true);

				assert.equal(button.isDisabled(), false);
				assert.equal(button.getMainButton().isDisabled(), true);
				assert.equal(button.getMenuButton().isDisabled(), false);

				assert.equal(
					button.getContainer().outerHTML,
					`<div class="ui-btn-split ui-btn-main-disabled ui-btn-no-caps">` +
					`<button class="ui-btn-main" disabled="true"><span class="ui-btn-text">Disabled Main</span></button>` +
					`<button class="ui-btn-menu"></button></div>`
				);
			});

			it('Case 4-2', () => {
				const html =
					`<div class="ui-btn-split ui-btn-main-disabled ui-btn-no-caps">` +
					`<button class="ui-btn-main" disabled="true"><span class="ui-btn-text">Disabled Main</span></button>` +
					`<button class="ui-btn-menu"></button></div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);

				assert.equal(button.getText(), 'Disabled Main');
				assert.equal(button.getMainButton().getText(), 'Disabled Main');

				assert.equal(button.getState(), SplitButtonState.MAIN_DISABLED);
				assert.equal(button.isNoCaps(), true);
				assert.equal(button.isDropdown(), true);

				assert.equal(button.isDisabled(), false);
				assert.equal(button.getMainButton().isDisabled(), true);
				assert.equal(button.getMenuButton().isDisabled(), false);

				assert.equal(button.getContainer().outerHTML, html);
			});

			it('Case 5', () => {
				const html =
					`<div class="ui-btn-split ui-btn-menu-disabled ui-btn-no-caps">` +
					`<button class="ui-btn-main">Disabled Menu</button>` +
					`<button class="ui-btn-menu" disabled="true"></button></div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);

				assert.equal(button.getText(), 'Disabled Menu');
				assert.equal(button.getMainButton().getText(), 'Disabled Menu');

				assert.equal(button.getState(), SplitButtonState.MENU_DISABLED);
				assert.equal(button.isNoCaps(), true);
				assert.equal(button.isDropdown(), true);

				assert.equal(button.isDisabled(), false);
				assert.equal(button.getMainButton().isDisabled(), false);
				assert.equal(button.getMenuButton().isDisabled(), true);

				assert.equal(
					button.getContainer().outerHTML,
					`<div class="ui-btn-split ui-btn-menu-disabled ui-btn-no-caps">` +
					`<button class="ui-btn-main"><span class="ui-btn-text">Disabled Menu</span></button>` +
					`<button class="ui-btn-menu" disabled="true"></button></div>`
				);
			});

			it('Case 5-2', () => {
				const html =
					`<div class="ui-btn-split ui-btn-menu-disabled ui-btn-no-caps">` +
					`<button class="ui-btn-main"><span class="ui-btn-text">Disabled Menu</span></button>` +
					`<button class="ui-btn-menu" disabled="true"></button></div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);

				assert.equal(button.getText(), 'Disabled Menu');
				assert.equal(button.getMainButton().getText(), 'Disabled Menu');

				assert.equal(button.getState(), SplitButtonState.MENU_DISABLED);
				assert.equal(button.isNoCaps(), true);
				assert.equal(button.isDropdown(), true);

				assert.equal(button.isDisabled(), false);
				assert.equal(button.getMainButton().isDisabled(), false);
				assert.equal(button.getMenuButton().isDisabled(), true);

				assert.equal(button.getContainer().outerHTML, html);
			});

			it('Case 6', () => {
				const html =
					`<div class="ui-btn-split ui-btn-xs ui-btn-link ui-btn-icon-cloud ui-btn-wait ui-btn-no-caps" lang="ru" data-id="123">` +
					`<button class="ui-btn-main" lang="ru" data-id="1" aria-flowto="1">Split</button>` +
					`<button class="ui-btn-menu" lang="en" data-id="2" aria-flowto="2"></button></div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);
				assert.equal(button.getText(), 'Split');
				assert.equal(button.getTag(), ButtonTag.DIV);
				assert.equal(button.getSize(), ButtonSize.EXTRA_SMALL);
				assert.equal(button.getIcon(), ButtonIcon.CLOUD);
				assert.equal(button.getState(), SplitButtonState.WAITING);
				assert.equal(button.isWaiting(), true);
				assert.equal(button.isNoCaps(), true);

				assert.equal(JSON.stringify(button.getProps()), `{"lang":"ru"}`);
				assert.equal(JSON.stringify(button.getMainButton().getProps()), `{"lang":"ru","aria-flowto":"1"}`);
				assert.equal(JSON.stringify(button.getMenuButton().getProps()), `{"lang":"en","aria-flowto":"2"}`);

				assert.equal(
					button.getMainButton().getContainer().outerHTML,
					`<button class="ui-btn-main" lang="ru" data-id="1" aria-flowto="1"><span class="ui-btn-text">Split</span></button>`
				);

				assert.equal(
					button.getMenuButton().getContainer().outerHTML,
					`<button class="ui-btn-menu" lang="en" data-id="2" aria-flowto="2"></button>`
				);

				assert.equal(
					button.getContainer().outerHTML,
					`<div class="ui-btn-split ui-btn-xs ui-btn-link ui-btn-icon-cloud ui-btn-wait ui-btn-no-caps" lang="ru" data-id="123">` +
					`<button class="ui-btn-main" lang="ru" data-id="1" aria-flowto="1"><span class="ui-btn-text">Split</span></button>` +
					`<button class="ui-btn-menu" lang="en" data-id="2" aria-flowto="2"></button></div>`
				);
			});

			it('Case 6-2', () => {
				const html =
					`<div class="ui-btn-split ui-btn-xs ui-btn-link ui-btn-icon-cloud ui-btn-wait ui-btn-no-caps" lang="ru" data-id="123">` +
					`<button class="ui-btn-main" lang="ru" data-id="1" aria-flowto="1"><span class="ui-btn-text">Split</span></button>` +
					`<button class="ui-btn-menu" lang="en" data-id="2" aria-flowto="2"></button></div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);
				assert.equal(button.getText(), 'Split');
				assert.equal(button.getTag(), ButtonTag.DIV);
				assert.equal(button.getSize(), ButtonSize.EXTRA_SMALL);
				assert.equal(button.getIcon(), ButtonIcon.CLOUD);
				assert.equal(button.getState(), SplitButtonState.WAITING);
				assert.equal(button.isWaiting(), true);
				assert.equal(button.isNoCaps(), true);

				assert.equal(JSON.stringify(button.getProps()), `{"lang":"ru"}`);
				assert.equal(JSON.stringify(button.getMainButton().getProps()), `{"lang":"ru","aria-flowto":"1"}`);
				assert.equal(JSON.stringify(button.getMenuButton().getProps()), `{"lang":"en","aria-flowto":"2"}`);

				assert.equal(
					button.getMainButton().getContainer().outerHTML,
					`<button class="ui-btn-main" lang="ru" data-id="1" aria-flowto="1"><span class="ui-btn-text">Split</span></button>`
				);

				assert.equal(
					button.getMenuButton().getContainer().outerHTML,
					`<button class="ui-btn-menu" lang="en" data-id="2" aria-flowto="2"></button>`
				);

				assert.equal(button.getContainer().outerHTML, html);
			});
		});

		describe('Counter', () => {
			it('Should a button with a counter', () => {

				const html =
					`<div class="ui-btn-split ui-btn-xs" lang="ru" data-id="123">` +
						`<button class="ui-btn-main" lang="ru">` +
							`<span class="ui-btn-text">Split Counter</span>` +
							`<span class="ui-btn-counter">345</span>` +
						`</button>` +
						`<button class="ui-btn-menu" lang="en"></button>` +
					`</div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.equal(button.getText(), 'Split Counter');
				assert.equal(button.getCounter(), 345);
				assert.equal(
					button.getContainer().outerHTML,
					`<div class="ui-btn-split ui-btn-xs" lang="ru" data-id="123">` +
						`<button class="ui-btn-main" lang="ru">` +
							`<span class="ui-btn-text">Split Counter</span>` +
							`<span class="ui-btn-counter">345</span>` +
						`</button>` +
						`<button class="ui-btn-menu" lang="en"></button>` +
					`</div>`
				);

				button.setText('New Split Counter');
				button.setCounter(22);

				assert.equal(button.getText(), 'New Split Counter');
				assert.equal(button.getCounter(), 22);
				assert.equal(
					button.getContainer().outerHTML,
					`<div class="ui-btn-split ui-btn-xs" lang="ru" data-id="123">` +
						`<button class="ui-btn-main" lang="ru">` +
							`<span class="ui-btn-text">New Split Counter</span>` +
							`<span class="ui-btn-counter">22</span>` +
						`</button>` +
						`<button class="ui-btn-menu" lang="en"></button>` +
					`</div>`
				);
			});

			it('Should a button with a counter 2', () => {

				const html =
					`<div class="ui-btn-split ui-btn-xs" lang="ru" data-id="123">` +
						`<button class="ui-btn-main" lang="ru">` +
							`Split Counter 2` +
							`<i class="ui-btn-counter" lang="ru" data-id="123">999+</i>` +
						`</button>` +
						`<button class="ui-btn-menu" lang="en"></button>` +
					`</div>`
				;

				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.equal(button.getText(), 'Split Counter 2');
				assert.equal(button.getCounter(), '999+');
				assert.equal(
					button.getContainer().outerHTML,
					`<div class="ui-btn-split ui-btn-xs" lang="ru" data-id="123">` +
						`<button class="ui-btn-main" lang="ru">` +
							`<span class="ui-btn-text">Split Counter 2</span>` +
							`<i class="ui-btn-counter" lang="ru" data-id="123">999+</i>` +
						`</button>` +
						`<button class="ui-btn-menu" lang="en"></button>` +
					`</div>`
				);

				button.setText('New Split Counter 2');
				button.setCounter('abc');

				assert.equal(button.getText(), 'New Split Counter 2');
				assert.equal(button.getCounter(), 'abc');
				assert.equal(
					button.getContainer().outerHTML,
					`<div class="ui-btn-split ui-btn-xs" lang="ru" data-id="123">` +
						`<button class="ui-btn-main" lang="ru">` +
							`<span class="ui-btn-text">New Split Counter 2</span>` +
							`<i class="ui-btn-counter" lang="ru" data-id="123">abc</i>` +
						`</button>` +
						`<button class="ui-btn-menu" lang="en"></button>` +
					`</div>`
				);
			});

		});

		describe('Dynamic behavior', () => {

			const Test = Reflection.namespace('BX.Test');

			it('Should bind events', () => {

				let buttonClickCount = 0;
				Test.buttonClickContext = {};
				Test.buttonClick = function(btn, event) {
					buttonClickCount++;
					setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
						assert.equal(btn, button);
						assert.equal(event.type, 'click');
						assert.equal(this, Test.buttonClickContext);
					}, 0);
				};

				let buttonOverCount = 0;
				Test.buttonOverContext = {};
				Test.buttonOver = function(btn, event) {
					buttonOverCount++;
					setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
						assert.equal(btn, button);
						assert.equal(event.type, 'mouseover');
						assert.equal(this, Test.buttonOverContext);
					}, 0);
				};

				let mainButtonClickCount = 0;
				Test.mainButtonClickContext = {};
				Test.mainButtonClick = function(btn, event) {
					mainButtonClickCount++;
					setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
						assert.equal(btn, mainButton);
						assert.equal(event.type, 'click');
						assert.equal(this, Test.mainButtonClickContext);
					}, 0);
				};

				let mainButtonOverCount = 0;
				Test.mainButtonOverContext = {};
				Test.mainButtonOver = function(btn, event) {
					mainButtonOverCount++;
					setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
						assert.equal(btn, mainButton);
						assert.equal(event.type, 'mouseover');
						assert.equal(this, Test.mainButtonOverContext);
					}, 0);
				};

				const onMenuButtonClick = sinon.stub().callsFake((event: BaseEvent) => {
					setTimeout(() => { // a workaround for the jsdom try-catch (asserts don't work)
						const { button: btn, event: mouseEvent } = event.getData();
						assert.equal(btn, menuButton);
						assert.equal(mouseEvent.type, 'click');
					}, 0);
				});

				Test.menuButtonOverCount = 0;

				EventEmitter.subscribe('BX.Test.onMenuButtonClick', onMenuButtonClick);

				const options = {
					onclick: {
						handler: 'BX.Test.buttonClick',
						context: 'BX.Test.buttonClickContext'
					},
					events: {
						mouseover: {
							handler: 'BX.Test.buttonOver',
							context: 'BX.Test.buttonOverContext'
						}
					},
					mainButton: {
						onclick: {
							handler: 'BX.Test.mainButtonClick',
							context: 'BX.Test.mainButtonClickContext'
						},
						events: {
							mouseover: {
								handler: 'BX.Test.mainButtonOver',
								context: 'BX.Test.mainButtonOverContext'
							}
						},
					},
					menuButton: {
						onclick: {
							event: 'BX.Test.onMenuButtonClick',
						},
						events: {
							mouseover: {
								code: `
									window.BX.Test.menuButtonOverCount++;
								`
							}
						},
					}
				};

				const html =
					`<div class="ui-btn-split" data-json-options="${Tag.safe`${JSON.stringify(options)}`}">` +
						`<button class="ui-btn-main">Split Button</button>` +
						`<button class="ui-btn-menu"></button>` +
					`</div>`
				;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);

				const mainButton = button.getMainButton();
				const menuButton = button.getMenuButton();

				assert.equal(buttonClickCount, 0);
				assert.equal(buttonOverCount, 0);
				assert.equal(mainButtonClickCount, 0);
				assert.equal(mainButtonOverCount, 0);
				assert.equal(onMenuButtonClick.callCount, 0);
				assert.equal(Test.menuButtonOverCount, 0);

				const buttonClickEvent = new window.MouseEvent('click', { view: window, });
				button.getContainer().dispatchEvent(buttonClickEvent);

				assert.equal(buttonClickCount, 1);
				assert.equal(buttonOverCount, 0);
				assert.equal(mainButtonClickCount, 0);
				assert.equal(mainButtonOverCount, 0);
				assert.equal(onMenuButtonClick.callCount, 0);
				assert.equal(Test.menuButtonOverCount, 0);

				const buttonOverEvent = new window.MouseEvent('mouseover', { view: window, });
				button.getContainer().dispatchEvent(buttonOverEvent);

				assert.equal(buttonClickCount, 1);
				assert.equal(buttonOverCount, 1);
				assert.equal(mainButtonClickCount, 0);
				assert.equal(mainButtonOverCount, 0);
				assert.equal(onMenuButtonClick.callCount, 0);
				assert.equal(Test.menuButtonOverCount, 0);

				mainButton.getContainer().click();

				assert.equal(buttonClickCount, 2);
				assert.equal(buttonOverCount, 1);
				assert.equal(mainButtonClickCount, 1);
				assert.equal(mainButtonOverCount, 0);
				assert.equal(onMenuButtonClick.callCount, 0);
				assert.equal(Test.menuButtonOverCount, 0);

				const mainButtonOverEvent = new window.MouseEvent('mouseover', { view: window,  bubbles: true });
				mainButton.getContainer().dispatchEvent(mainButtonOverEvent);

				assert.equal(buttonClickCount, 2);
				assert.equal(buttonOverCount, 2);
				assert.equal(mainButtonClickCount, 1);
				assert.equal(mainButtonOverCount, 1);
				assert.equal(onMenuButtonClick.callCount, 0);
				assert.equal(Test.menuButtonOverCount, 0);

				menuButton.getContainer().click();

				assert.equal(buttonClickCount, 3);
				assert.equal(buttonOverCount, 2);
				assert.equal(mainButtonClickCount, 1);
				assert.equal(mainButtonOverCount, 1);
				assert.equal(onMenuButtonClick.callCount, 1);
				assert.equal(Test.menuButtonOverCount, 0);

				const menuButtonOverEvent = new window.MouseEvent('mouseover', { view: window,  bubbles: true });
				menuButton.getContainer().dispatchEvent(menuButtonOverEvent);

				assert.equal(buttonClickCount, 3);
				assert.equal(buttonOverCount, 3);
				assert.equal(mainButtonClickCount, 1);
				assert.equal(mainButtonOverCount, 1);
				assert.equal(onMenuButtonClick.callCount, 1);
				assert.equal(Test.menuButtonOverCount, 1);

			});

			it('Should change a menu target', () => {

				const options = {
					menuTarget: SplitSubButtonType.MENU,
					menu: {
						items: [
							{ text: 'First Item' },
							{ text: 'Second Item' },
						]
					}
				};

				const html =
					`<div class="ui-btn-split" data-json-options="${Tag.safe`${JSON.stringify(options)}`}">` +
					`<button class="ui-btn-main">Split Button</button>` +
					`<button class="ui-btn-menu"></button>` +
					`</div>`
				;
				const button = ButtonManager.createFromNode(Tag.render`${html}`);
				assert.ok(button instanceof SplitButton);

				assert.equal(button.getMenuBindElement(), button.getMenuButton().getContainer());

			});
		});
	});
});