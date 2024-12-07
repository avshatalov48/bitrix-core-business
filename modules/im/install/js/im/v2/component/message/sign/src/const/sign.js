import { ButtonColor } from 'im.v2.component.elements';

export const Await = Object.freeze({
	inviteCompany: 'inviteCompany',
	inviteCompanyWithInitiator: 'inviteCompanyWithInitiator',
	inviteEmployeeSes: 'inviteEmployeeSes',
	inviteEmployeeSesWithInitiator: 'inviteEmployeeSesWithInitiator',
	inviteEmployeeGosKey: 'inviteEmployeeGosKey',
	inviteEmployeeGosKeyV2: 'inviteEmployeeGosKeyV2',
	inviteEmployeeGosKeyWithInitiator: 'inviteEmployeeGosKeyWithInitiator',
	inviteReviewer: 'inviteReviewer',
	inviteReviewerWithInitiator: 'inviteReviewerWithInitiator',
	inviteEditor: 'inviteEditor',
	inviteEditorWithInitiator: 'inviteEditorWithInitiator',
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
	refusedCompanyV2: 'refusedCompanyV2',
	refusedCompanyV2M: 'refusedCompanyV2M',
	refusedCompanyV2F: 'refusedCompanyV2F',
	employeeStoppedToCompanyV2: 'employeeStoppedToCompanyV2',
	employeeStoppedToCompanyV2M: 'employeeStoppedToCompanyV2M',
	employeeStoppedToCompanyV2F: 'employeeStoppedToCompanyV2F',
	documentStoppedToInitiator: 'documentStoppedToInitiator',
	documentStoppedToInitiatorM: 'documentStoppedToInitiatorM',
	documentStoppedToInitiatorF: 'documentStoppedToInitiatorF',
	documentStoppedToAssignee: 'documentStoppedToAssignee',
	documentStoppedToAssigneeM: 'documentStoppedToAssigneeM',
	documentStoppedToAssigneeF: 'documentStoppedToAssigneeF',
	documentStoppedToReviewer: 'documentStoppedToReviewer',
	documentStoppedToReviewerM: 'documentStoppedToReviewerM',
	documentStoppedToReviewerF: 'documentStoppedToReviewerF',
	documentStoppedToEditor: 'documentStoppedToEditor',
	documentStoppedToEditorM: 'documentStoppedToEditorM',
	documentStoppedToEditorF: 'documentStoppedToEditorF',
	refusedCompany: 'refusedCompany',
	employeeStoppedToCompany: 'employeeStoppedToCompany',
	documentStopped: 'documentStopped',
	documentCancelled: 'documentCancelled',
	stoppedToEmployee: 'stoppedToEmployee',
	stoppedToEmployeeM: 'stoppedToEmployeeM',
	stoppedToEmployeeF: 'stoppedToEmployeeF',
	signingError: 'signingError',
	repeatSigning: 'repeatSigning',
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
