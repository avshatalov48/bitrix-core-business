import { Event } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { PopupWindowManager } from 'main.popup';
import { SidePanel } from 'main.sidepanel';

import { Utils } from 'im.v2.lib.utils';
import { CallManager } from 'im.v2.lib.call';
import { Core } from 'im.v2.application.core';
import { EventType, Layout } from 'im.v2.const';
import { LayoutManager } from 'im.v2.lib.layout';
import { MessengerSlider } from 'im.v2.lib.slider';
import { DesktopManager } from 'im.v2.lib.desktop';
import { DesktopApi } from 'im.v2.lib.desktop-api';

const MESSENGER_CONTAINER_SELECTOR = '.bx-im-messenger__container';

export const EscEventAction = Object.freeze({
	handled: 'handled',
	ignored: 'ignored',
});

export class EscManager
{
	#messengerContainer: HTMLElement | null;
	#wasKeyDownHandled: boolean = false;

	static #instance: EscManager;

	constructor()
	{
		this.keyUpEventHandler = this.#onKeyUp.bind(this);
		this.keyDownEventHandler = this.#onKeyDown.bind(this);
	}

	static getInstance(): EscManager
	{
		EscManager.#instance = EscManager.#instance ?? new EscManager();

		return EscManager.#instance;
	}

	register()
	{
		this.#messengerContainer = this.#getMessengerContainer();
		Event.bind(document, 'keyup', this.keyUpEventHandler);
		Event.bind(document, 'keydown', this.keyDownEventHandler);
	}

	unregister()
	{
		this.#messengerContainer = this.#getMessengerContainer();
		Event.unbind(document, 'keyup', this.keyUpEventHandler);
		Event.unbind(document, 'keydown', this.keyDownEventHandler);
	}

	async handleEsc(): Promise
	{
		if (this.#wasKeyDownHandled)
		{
			this.#wasKeyDownHandled = false;

			return;
		}

		if (this.#shouldIgnoreEscape())
		{
			return;
		}

		if (await this.#isHandledBySubscriber())
		{
			return;
		}

		if (this.#handleActiveInput())
		{
			return;
		}

		if (this.#handleChannelComments())
		{
			return;
		}

		if (this.#handleLayoutClear())
		{
			return;
		}

		if (this.#handleMessengerSliderClose())
		{
			return;
		}

		this.#handleDesktopAction();
	}

	#shouldIgnoreEscape(): boolean
	{
		const hasVisibleCall = CallManager.getInstance().hasVisibleCall();
		// popups have their own escape handling
		const isAnyPopupShown = PopupWindowManager.isAnyPopupShown();

		return hasVisibleCall || isAnyPopupShown || this.#isExternalSliderOpened();
	}

	async #isHandledBySubscriber(): Promise<boolean>
	{
		const eventResult = await EventEmitter.emitAsync(EventType.key.onBeforeEscape);

		return eventResult.includes(EscEventAction.handled);
	}

	#handleActiveInput(): boolean
	{
		const { activeElement } = document;
		if (!activeElement)
		{
			return false;
		}

		const isMessengerFocused = this.#messengerContainer.contains(activeElement);
		const isInputFocused = activeElement.matches('input');

		if (isMessengerFocused && isInputFocused)
		{
			activeElement.blur();

			return true;
		}

		return false;
	}

	#handleLayoutClear(): boolean
	{
		const layoutManager = LayoutManager.getInstance();
		const currentLayout = layoutManager.getLayout();
		const isChatLayout = layoutManager.isChatLayout(currentLayout.name);

		const isChatLayoutEmptyState = isChatLayout && currentLayout.entityId === '';
		if (isChatLayoutEmptyState)
		{
			return false;
		}

		if (isChatLayout)
		{
			layoutManager.clearCurrentLayoutEntityId();
		}
		else
		{
			this.#switchToChatLayout();
		}

		return true;
	}

	#handleMessengerSliderClose(): boolean
	{
		const slider = MessengerSlider.getInstance();
		if (slider.getCurrent() && slider.isFocused())
		{
			slider.getCurrent().close();

			return true;
		}

		return false;
	}

	#handleDesktopAction(): boolean
	{
		if (!DesktopManager.isDesktop())
		{
			return false;
		}

		DesktopApi.hideWindow();

		return true;
	}

	#onKeyUp(event: KeyboardEvent)
	{
		if (!Utils.key.isCombination(event, 'Escape'))
		{
			return;
		}

		void this.handleEsc();
	}

	#onKeyDown()
	{
		// Viewer has its own ESC keydown handler, so we need to check if it is opened
		this.#wasKeyDownHandled = BX.UI.Viewer.Instance.isOpen();
	}

	#getMessengerContainer(): HTMLElement
	{
		return document.querySelector(MESSENGER_CONTAINER_SELECTOR);
	}

	#switchToChatLayout()
	{
		void LayoutManager.getInstance().setLayout({
			name: Layout.chat,
			entityId: '',
		});
	}

	#isExternalSliderOpened(): boolean
	{
		const isEmbeddedMode = LayoutManager.getInstance().isEmbeddedMode();
		if (isEmbeddedMode)
		{
			return SidePanel.Instance.getOpenSlidersCount() > 0;
		}

		return !MessengerSlider.getInstance().isFocused();
	}

	#handleChannelComments(): boolean
	{
		const areCommentsOpened = Core.getStore().getters['messages/comments/areOpened'];
		if (areCommentsOpened)
		{
			EventEmitter.emit(EventType.dialog.closeComments);
		}

		return areCommentsOpened;
	}
}
