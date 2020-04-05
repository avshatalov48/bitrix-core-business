import BX from '../../core/test/old/core/internal/bootstrap';
import Menu from '../src/menu/menu';
import MenuManager from '../src/menu/menu-manager';
import PopupManager from '../src/popup/popup-manager';
import Popup from '../src/popup/popup';

describe('BX.Main.Menu', () => {

	describe('Compatible modes', () => {
		it('Should support old arguments in the constructor', () => {

			const bindElement = { x: 10, y: 10 };
			const menuItems = [];

			const options = {
				maxWidth: 17
			};

			const menu = new Menu('old-menu', bindElement, menuItems, options);
			assert.equal(menu.getId(), 'old-menu');
			assert.equal(menu.getPopupWindow().getMaxWidth(), options.maxWidth);
			assert.equal(menu.getPopupWindow().isCompatibleMode(), true);

			const menu2 = new Menu('old-menu2', bindElement, menuItems, { compatibleMode: false, maxWidth: 15 });
			assert.equal(menu2.getId(), 'old-menu2');
			assert.equal(menu2.getPopupWindow().getMaxWidth(), 15);
			assert.equal(menu2.getPopupWindow().isCompatibleMode(), false);

			const menu3 = new Menu('old-menu3', bindElement, menuItems);
			assert.equal(menu3.getId(), 'old-menu3');
			assert.equal(menu3.getPopupWindow().isCompatibleMode(), true);

			const menu4 = MenuManager.create('old-menu4', bindElement, menuItems, options);
			assert.equal(menu4.getId(), 'old-menu4');
			assert.equal(menu4.getPopupWindow().getMaxWidth(), options.maxWidth);
			assert.equal(menu4.getPopupWindow().isCompatibleMode(), true);

			const menu5 = MenuManager.create('old-menu5', bindElement, menuItems, {
				compatibleMode: false,
				maxWidth: 25
			});
			assert.equal(menu5.getId(), 'old-menu5');
			assert.equal(menu5.getPopupWindow().getMaxWidth(), 25);
			assert.equal(menu5.getPopupWindow().isCompatibleMode(), false);

			const menu6 = MenuManager.create('old-menu6', bindElement, menuItems);
			assert.equal(menu6.getId(), 'old-menu6');
			assert.equal(menu6.getPopupWindow().isCompatibleMode(), true);

			const menu7 = new Menu({
				id: 'new-menu',
				bindElement,
				items: menuItems,
				maxWidth: 45
			});

			assert.equal(menu7.getId(), 'new-menu');
			assert.equal(menu7.getPopupWindow().getMaxWidth(), 45);
			assert.equal(menu7.getPopupWindow().isCompatibleMode(), false);

			const menu8 = MenuManager.create({
				id: 'new-menu2',
				bindElement,
				items: menuItems,
				maxWidth: 46
			});

			assert.equal(menu8.getId(), 'new-menu2');
			assert.equal(menu8.getPopupWindow().getMaxWidth(), 46);
			assert.equal(menu8.getPopupWindow().isCompatibleMode(), false);

		});

		it('Should emit old and new events', () => {

			const onPopupClose = sinon.stub();
			const onClose = sinon.stub();
			const onPopupShow = sinon.stub();
			const onShow = sinon.stub();
			const onAfterPopupShow = sinon.stub();
			const onAfterShow = sinon.stub();
			const onPopupDestroy = sinon.stub();
			const onDestroy = sinon.stub();
			const onPopupFirstShow = sinon.stub();
			const onFirstShow = sinon.stub();
			const onPopupAfterClose = sinon.stub();
			const onAfterClose = sinon.stub();

			const menu = MenuManager.create(
				'simple-menu-2',
				null,
				[
					{ text: 'one' },
					{ delimiter: true, text: 'section' },
					{ text: 'three' },
					{ text: 'four' },
					{ text: 'five' },
					{ delimiter: true, text: 'Longlonglonglonglonglonglong Text' },
					{ text: 'six' },
					{ text: 'sevent' },
					{ text: 'eight' }
				],
				{
					autoHide: true,
					animation: false,
					//cacheable: false,

					events: {
						onPopupClose,
						onClose,
						onPopupShow,
						onShow,
						onAfterPopupShow,
						onAfterShow,
						onPopupDestroy,
						onDestroy,
						onPopupFirstShow,
						onFirstShow,
						onPopupAfterClose,
						onAfterClose,
					}
				}
			);

			menu.show();
			menu.show();

			assert.equal(onPopupShow.callCount, 1);
			assert.equal(onShow.callCount, 1);
			assert.equal(onAfterPopupShow.callCount, 1);
			assert.equal(onAfterShow.callCount, 1);
			assert.equal(onPopupFirstShow.callCount, 1);
			assert.equal(onFirstShow.callCount, 1);
			assert.equal(onPopupClose.callCount, 0);
			assert.equal(onClose.callCount, 0);
			assert.equal(onPopupAfterClose.callCount, 0);
			assert.equal(onAfterClose.callCount, 0);
			assert.equal(onPopupDestroy.callCount, 0);
			assert.equal(onDestroy.callCount, 0);

			menu.close();
			menu.close();
			menu.show();
			menu.close();

			assert.equal(onPopupShow.callCount, 2);
			assert.equal(onShow.callCount, 2);
			assert.equal(onAfterPopupShow.callCount, 2);
			assert.equal(onAfterShow.callCount, 2);
			assert.equal(onPopupFirstShow.callCount, 1);
			assert.equal(onFirstShow.callCount, 1);
			assert.equal(onPopupClose.callCount, 2);
			assert.equal(onClose.callCount, 2);
			assert.equal(onPopupAfterClose.callCount, 2);
			assert.equal(onAfterClose.callCount, 2);
			assert.equal(onPopupDestroy.callCount, 0);
			assert.equal(onDestroy.callCount, 0);

			menu.destroy();
			menu.destroy();

			assert.equal(onPopupShow.callCount, 2);
			assert.equal(onShow.callCount, 2);
			assert.equal(onAfterPopupShow.callCount, 2);
			assert.equal(onAfterShow.callCount, 2);
			assert.equal(onPopupFirstShow.callCount, 1);
			assert.equal(onFirstShow.callCount, 1);
			assert.equal(onPopupClose.callCount, 2);
			assert.equal(onClose.callCount, 2);
			assert.equal(onPopupAfterClose.callCount, 2);
			assert.equal(onAfterClose.callCount, 2);
			assert.equal(onPopupDestroy.callCount, 1);
			assert.equal(onDestroy.callCount, 1);

			assert.equal(menu.getPopupWindow().isDestroyed(), true);
		});
	});

	describe('MenuManager', () => {

		it('Should collect menus', () => {

			Object.values(MenuManager.Data).forEach(menu => {
				menu.destroy();
			});
			assert.equal(Object.keys(MenuManager.Data).length, 0);

			const bindElement = { x: 0, y: 0 };
			const items = [];
			const params = {
				cacheable: false, //destroy after close()
				animation: false //destroy immediately
			};

			const menu = MenuManager.create('old-menu', bindElement, items, params);
			const menu2 = MenuManager.create({ id: 'new-menu', bindElement, items });

			assert.equal(Object.keys(MenuManager.Data).length, 2);

			const menu3 = MenuManager.create('old-menu2', bindElement, items, params);
			const menu4 = MenuManager.create({ id: 'new-menu2', bindElement, items });

			const menu5 = MenuManager.create('old-menu', bindElement, items, params);
			const menu6 = MenuManager.create({ id: 'new-menu', bindElement, items });

			assert.equal(menu, menu5);
			assert.equal(menu2, menu6);

			assert.equal(Object.keys(MenuManager.Data).length, 4);

			menu.show();
			menu.close();
			menu2.show();
			menu2.close();
			menu3.show();
			menu3.close();
			menu4.show();
			menu4.close();
			menu5.show();
			menu5.close();
			menu6.show();
			menu6.close();

			assert.equal(menu.getPopupWindow().isDestroyed(), true);
			assert.equal(menu3.getPopupWindow().isDestroyed(), true);

			assert.equal(Object.keys(MenuManager.Data).length, 2);
		});
	});
});