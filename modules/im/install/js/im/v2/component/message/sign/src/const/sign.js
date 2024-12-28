import { ButtonColor } from 'im.v2.component.elements';

export const Await = Object.freeze({
	// initiated by company
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
	inviteB2bDocumentSigning: 'inviteB2bDocumentSigning',

	// initiated by employee
	byEmployeeInviteCompany: 'byEmployeeInviteCompany',
	byEmployeeInviteReviewer: 'byEmployeeInviteReviewer',
	byEmployeeInviteEmployee: 'byEmployeeInviteEmployee',
	byEmployeeSignedByEmployee: 'byEmployeeSignedByEmployee',
});

export const Success = Object.freeze({
	// initiated by company
	doneCompany: 'doneCompany',
	doneEmployee: 'doneEmployee',
	doneEmployeeGosKey: 'doneEmployeeGosKey',
	doneFromAssignee: 'doneFromAssignee',
	doneFromEditor: 'doneFromEditor',
	doneFromReviewer: 'doneFromReviewer',

	// initiated by employee
	byEmployeeDoneEmployee: 'byEmployeeDoneEmployee',
	byEmployeeDoneEmployeeM: 'byEmployeeDoneEmployeeM',
	byEmployeeDoneEmployeeF: 'byEmployeeDoneEmployeeF',
	byEmployeeDoneCompany: 'byEmployeeDoneCompany',
	doneB2bDocumentSigning: 'doneB2bDocumentSigning',
});

export const Failure = Object.freeze({
	// initiated by company
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

	// initiated by employee
	byEmployeeStoppedToEmployee: 'byEmployeeStoppedToEmployee',
	byEmployeeStoppedToEmployeeM: 'byEmployeeStoppedToEmployeeM',
	byEmployeeStoppedToEmployeeF: 'byEmployeeStoppedToEmployeeF',
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
