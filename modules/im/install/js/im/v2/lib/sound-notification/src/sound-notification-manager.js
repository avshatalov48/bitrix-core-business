import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { UserStatus, SoundType, Settings } from 'im.v2.const';
import { DesktopManager } from 'im.v2.lib.desktop';
import { CallManager } from 'im.v2.lib.call';

import { SoundPlayer } from './classes/sound-player';

export class SoundNotificationManager
{
	store: Store;
	desktopManager: DesktopManager;
	soundPlayer: SoundPlayer;
	callManager: CallManager;

	static instance: SoundNotificationManager | null = null;

	static getInstance(): SoundNotificationManager
	{
		if (!this.instance)
		{
			const store = Core.getStore();
			const desktopManager = DesktopManager.getInstance();
			const callManager = CallManager.getInstance();
			const soundPlayer = new SoundPlayer();

			this.instance = new this(store, desktopManager, callManager, soundPlayer);
		}

		return this.instance;
	}

	constructor(store, desktopManager, callManager, soundPlayer)
	{
		this.store = store;
		this.desktopManager = desktopManager;
		this.soundPlayer = soundPlayer;
		this.callManager = callManager;
	}

	playOnce(type: $Keys<typeof SoundType>)
	{
		if (this.#hasActiveCall() || !this.#canPlayInContext())
		{
			return;
		}

		if (!this.#isSoundEnabled() || this.#isUserDnd())
		{
			return;
		}

		this.soundPlayer.playSingle(type);
	}

	forcePlayOnce(type: $Keys<typeof SoundType>)
	{
		if (!this.#canPlayInContext())
		{
			return;
		}

		if (!this.#isSoundEnabled())
		{
			return;
		}

		this.soundPlayer.playSingle(type);
	}

	playLoop(type: $Keys<typeof SoundType>, timeout: number = 5000, force = false)
	{
		if (this.#hasActiveCall() && !force)
		{
			return;
		}

		if (!this.#canPlayInContext())
		{
			return;
		}

		if (force)
		{
			this.soundPlayer.playLoop(type, timeout);

			return;
		}

		if (this.#isUserDnd() || !this.#isSoundEnabled())
		{
			return;
		}

		this.soundPlayer.playLoop(type, timeout);
	}

	stop(type: $Keys<typeof SoundType>)
	{
		this.soundPlayer.stop(type);
	}

	#canPlayInContext(): boolean
	{
		return DesktopManager.isDesktop() || !this.desktopManager.isDesktopActive();
	}

	#isUserDnd(): boolean
	{
		const status = this.store.getters['application/settings/get'](Settings.user.status);

		return status === UserStatus.dnd;
	}

	#hasActiveCall(): boolean
	{
		return this.callManager.hasCurrentCall();
	}

	#isSoundEnabled(): boolean
	{
		return this.store.getters['application/settings/get'](Settings.notification.enableSound);
	}
}
