export interface ICacheStorage
{
	size: number;
	get(key: string): any;
	set(key: string, value: any): void;
	has(key: string): boolean;
	delete(key: string): void;
	keys(): Array<string>;
	values(): Array<any>;
}