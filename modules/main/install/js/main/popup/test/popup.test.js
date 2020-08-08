import BX from '../../core/test/old/core/internal/bootstrap';
import Popup from '../src/popup/popup';
import Menu from '../src/menu/menu';
import MenuManager from '../src/menu/menu-manager';
import PopupManager from '../src/popup/popup-manager';
import { BaseEvent } from 'main.core.events';

describe('BX.Main.Popup', () => {
	it('Should support old arguments in the constructor', () => {
		const bindElement = { x: 100, y: 200 };
		const params = {
			padding: 11
		};

		const popup = new Popup('old-popup', bindElement, params);
		assert.equal(popup.isCompatibleMode(), true);
		assert.equal(popup.getId(), 'old-popup');
		assert.equal(popup.getPadding(), params.padding);

		const popup2 = new Popup('old-popup2', bindElement, { compatibleMode: false, padding: 15 });
		assert.equal(popup2.isCompatibleMode(), false);
		assert.equal(popup2.getId(), 'old-popup2');
		assert.equal(popup2.getPadding(), 15);

		const popup3 = PopupManager.create('old-popup3', bindElement, params);
		assert.equal(popup3.isCompatibleMode(), true);
		assert.equal(popup3.getId(), 'old-popup3');
		assert.equal(popup3.getPadding(), params.padding);

		const popup4 = PopupManager.create('old-popup4', bindElement, { compatibleMode: false, padding: 15 });
		assert.equal(popup4.isCompatibleMode(), false);
		assert.equal(popup4.getId(), 'old-popup4');
		assert.equal(popup4.getPadding(), 15);

		const popup5 = new Popup({
			id: 'new-popup',
			bindElement,
			padding: params.padding
		});

		assert.equal(popup5.isCompatibleMode(), false);
		assert.equal(popup5.getId(), 'new-popup');
		assert.equal(popup5.getPadding(), params.padding);

		const popup6 = PopupManager.create({
			id: 'new-popup2',
			bindElement,
			padding: 22
		});

		assert.equal(popup6.isCompatibleMode(), false);
		assert.equal(popup6.getId(), 'new-popup2');
		assert.equal(popup6.getPadding(), 22);

	});

	it('Should emit old and new events', () => {

		const onShowOld = sinon.stub().callsFake(function(arg) {
			assert.equal(arg, popup);
			assert.equal(this, popup);
		});

		const onShowNew = sinon.stub().callsFake(function(event: BaseEvent) {
			assert.equal(event.getTarget(), popup);
		});

		const onCloseOld = sinon.stub().callsFake(function(arg) {
			assert.equal(arg, popup);
			assert.equal(this, popup);
		});

		const onCloseOld2 = sinon.stub().callsFake(function(arg) {
			assert.equal(arg, popup);
			assert.equal(this, popup);
		});

		const onCloseNew = sinon.stub().callsFake(function(event: BaseEvent) {
			assert.equal(event.getTarget(), popup);
		});

		const popup = new Popup('popup1', null, {
			content: 'text',
			events: {
				onPopupShow: onShowOld,
				onShow: onShowNew,
				onPopupClose: onCloseOld,
				onClose: onCloseNew
			}
		});

		BX.addCustomEvent(popup, 'onPopupClose', onCloseOld2);

		popup.show();

		assert.equal(popup.isCompatibleMode(), true);
		assert.equal(onShowNew.callCount, 1);
		assert.equal(onShowOld.callCount, 1);
		assert.equal(onCloseOld.callCount, 0);
		assert.equal(onCloseOld2.callCount, 0);
		assert.equal(onCloseNew.callCount, 0);
		assert.equal(popup.isDestroyed(), false);

		popup.close();
		popup.show();

		assert.equal(onCloseOld.callCount, 1);
		assert.equal(onCloseOld2.callCount, 1);
		assert.equal(onCloseNew.callCount, 1);
		assert.equal(onShowOld.callCount, 2);
		assert.equal(onShowNew.callCount, 2);

		assert.equal(popup.isDestroyed(), false);
		popup.destroy();

		assert.equal(popup.isDestroyed(), true);
	});

	describe('PopupManager', () => {

		it('Should collect all popups', () => {

			Object.values(PopupManager._popups).forEach(popup => {
				popup.destroy();
			});
			assert.equal(PopupManager._popups.length, 0);

			const bindElement = { x: 0, y: 0 };
			const params = {};

			const popup = new Popup('old-popup', bindElement, params);
			const popup1 = PopupManager.create('old-popup3', bindElement, params);
			const popup2 = new Popup({ id: 'new-popup', bindElement });
			const popup3 = PopupManager.create({ id: 'new-popup2', bindElement });

			assert.equal(PopupManager._popups.length, 4);

			const menu = new Menu('old-menu', bindElement, params);
			const menu1 = MenuManager.create('old-menu3', bindElement, params);
			const menu2 = new Menu({ id: 'new-menu', bindElement });
			const menu3 = MenuManager.create({ id: 'new-menu2', bindElement });

			assert.equal(PopupManager._popups.length, 8);

			Object.values(PopupManager._popups).forEach(popup => {
				popup.destroy();
			});
			assert.equal(PopupManager._popups.length, 0);
		});
	});
});