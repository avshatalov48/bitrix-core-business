export interface Transformer<T, R>
{
	transform(source: T): R;
}
