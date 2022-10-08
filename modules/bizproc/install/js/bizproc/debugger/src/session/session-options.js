import type { DocumentOptions } from './document-options';

export type SessionOptions = {
	Id: string,
	Mode: number,
	StartedBy: number,
	Active: boolean,
	Fixed: boolean,
	Documents: [DocumentOptions],
	ShortDescription: string,
	CategoryId: number,
};