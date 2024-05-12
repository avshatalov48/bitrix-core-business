import { Type } from 'main.core';

type Draft = {
	text: string,
};

type PreparedDraft = {
	text: string,
	date: Date | null,
};

type Invitation = Boolean | {
	originatorId: number,
	canResend: boolean,
};

type PreparedInvitation = {
	isActive: boolean,
	originator: number,
	canResend: boolean,
};

export const prepareDraft = (draft: Draft): PreparedDraft => {
	if (!draft.text || draft.text === '')
	{
		return {
			text: '',
			date: null,
		};
	}

	return {
		text: draft.text,
		date: new Date(),
	};
};

export const prepareInvitation = (invited: Invitation): PreparedInvitation => {
	if (Type.isPlainObject(invited))
	{
		return {
			isActive: true,
			originator: invited.originatorId,
			canResend: invited.canResend,
		};
	}

	return {
		isActive: false,
		originator: 0,
		canResend: false,
	};
};
