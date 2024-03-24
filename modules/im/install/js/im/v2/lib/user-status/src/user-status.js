import { Type } from 'main.core';

import { Core } from 'im.v2.application.core';
import { Utils } from 'im.v2.lib.utils';
import { RecentService } from 'im.v2.provider.service';

import type { ImModelUser } from 'im.v2.model';

type UserId = number;

const DAY = 1000 * 60 * 60 * 24;

export class UserStatusManager
{
	static #instance: UserStatusManager;

	#absentList: Set<UserId> = new Set();
	#absentCheckInterval: number | null = null;
	#birthdayLoadInterval: number | null = null;

	static getInstance(): UserStatusManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	onUserUpdate(user: ImModelUser): void
	{
		this.#startBirthdayLoadInterval();

		if (user.birthday && Utils.user.isBirthdayToday(user.birthday))
		{
			this.#setUserBirthdayFlag(user.id, true);
			setTimeout(() => {
				this.#setUserBirthdayFlag(user.id, false);
			}, Utils.date.getTimeToNextMidnight());
		}

		if (Type.isDate(user.absent))
		{
			this.#setUserAbsentFlag(user.id, true);
			this.#startAbsentCheckInterval(user.id);
		}
		else if (user.absent === false && this.#absentList.has(user.id))
		{
			this.#setUserAbsentFlag(user.id, false);
			this.#stopAbsentCheckInterval(user.id);
		}
	}

	clear(): void
	{
		this.#absentList = new Set();
		clearTimeout(this.#absentCheckInterval);
		this.#absentCheckInterval = null;
		clearTimeout(this.#birthdayLoadInterval);
		this.#birthdayLoadInterval = null;
	}

	#setUserBirthdayFlag(userId: number, flag: boolean): void
	{
		Core.getStore().dispatch('users/update', {
			id: userId,
			fields: { isBirthday: flag },
		});
	}

	#setUserAbsentFlag(userId: number, flag: boolean): void
	{
		Core.getStore().dispatch('users/update', {
			id: userId,
			fields: { isAbsent: flag },
		});
	}

	#startAbsentCheckInterval(userId: number): void
	{
		this.#absentList.add(userId);
		if (this.#absentCheckInterval)
		{
			return;
		}

		this.#absentCheckInterval = setTimeout(() => {
			this.#checkAbsentList();
			setInterval(() => {
				this.#checkAbsentList();
			}, DAY);
		}, Utils.date.getTimeToNextMidnight());
	}

	#stopAbsentCheckInterval(userId: number): void
	{
		this.#absentList.delete(userId);
	}

	#checkAbsentList()
	{
		for (const userId of this.#absentList)
		{
			const user: ImModelUser = Core.getStore().getters['users/get'](userId);
			if (!user || !Type.isDate(user.absent))
			{
				this.#stopAbsentCheckInterval(userId);

				return;
			}

			const absentEnd: Date = user.absent.getTime();
			if (absentEnd <= Date.now())
			{
				this.#setUserAbsentFlag(user.id, false);
				this.#stopAbsentCheckInterval(user.id);
			}
		}
	}

	#startBirthdayLoadInterval()
	{
		if (this.#birthdayLoadInterval)
		{
			return;
		}

		this.#birthdayLoadInterval = setTimeout(() => {
			RecentService.getInstance().loadFirstPage();
			setInterval(() => {
				RecentService.getInstance().loadFirstPage();
			}, DAY);
		}, Utils.date.getTimeToNextMidnight());
	}
}
