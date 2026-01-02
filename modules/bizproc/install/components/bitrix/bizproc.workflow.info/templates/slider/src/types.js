export type TaskField = {
	Id: string,
	Name: string,
	FieldId: string,
	Type: string,
	Required: boolean,
	Multiple: boolean,
};

export type TaskFieldError = {
	fieldId: ?string,
	message: string,
};
