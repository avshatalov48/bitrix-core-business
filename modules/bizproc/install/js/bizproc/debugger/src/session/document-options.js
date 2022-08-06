export type DocumentOptions = {
	Id: number,
	SessionId: string,
	DocumentId: string,
	DateExpire: Date | null,
	DocumentSigned?: string,
};