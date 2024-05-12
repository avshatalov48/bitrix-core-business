import { ButtonColor } from 'im.v2.component.elements';

export const Await = Object.freeze({
	inviteCompany: 'inviteCompany',
	inviteEmployeeSes: 'inviteEmployeeSes',
	inviteEmployeeTaxcom: 'inviteEmployeeTaxcom',
	inviteEmployeeGosKey: 'inviteEmployeeGosKey',
	inviteReviewer: 'inviteReviewer',
});

export const Success = Object.freeze({
	doneCompany: 'doneCompany',
	doneEmployee: 'doneEmployee',
	doneEmployeeGosKey: 'doneEmployeeGosKey',
	doneFromAssignee: 'doneFromAssignee',
	doneFromEditor: 'doneFromEditor',
	doneFromReviewer: 'doneFromReviewer',
});

export const Failure = Object.freeze({
	refusedCompany: 'refusedCompany',
	employeeStoppedToCompany: 'employeeStoppedToCompany',
	documentStopped: 'documentStopped',
});

export type SignButtonParams = {
	text: string,
	callback: (...rest: any[]) => void,
	color: $Values<typeof ButtonColor>,
}

export type SignMessageComponentParams = {
	title: (...rest: any[]) => string,
	description: (...rest: any[]) => string,
	button: SignButtonParams | null,
};
