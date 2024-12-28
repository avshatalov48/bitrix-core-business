export type Timestamp = number;
export type UserId = number;
export type WorkflowId = string;

export class WorkflowResultStatus
{
	static NO_RESULT = 0;
	static BB_CODE_RESULT = 1;
	static USER_RESULT = 2;
	static NO_RIGHTS_RESULT = 3;
}
