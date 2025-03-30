export interface MessageCodec
{
	extractMessages(): void,

	encodeMessageBatch(): void,
}
