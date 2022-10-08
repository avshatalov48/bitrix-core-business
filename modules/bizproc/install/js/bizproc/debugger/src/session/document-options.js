export type DocumentOptions = {
	Id: number,
	SessionId: string,
	DocumentId: string,
	DocumentCategoryId: number,
	DateExpire: Date | null,
	DocumentSigned?: string,
};