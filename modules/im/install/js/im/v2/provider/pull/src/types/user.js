import type { RawUser } from './common';

export type UserInviteParams = {
	userId: number,
	invited: boolean,
	user: RawUser,
	date: string,
};
