<?

namespace Bitrix\Sender\Posting\ThreadStrategy;

use Bitrix\Main\ORM\Query\Result;

interface IThreadStrategy
{
	public const TEN = 'Ten';
	public const SINGLE = 'Single';

	function getRecipients(int $limit): Result;

	function fillThreads(): void;

	function lockThread(): void;
	function hasUnprocessedThreads(): bool;

	function getThreadId(): ?int;
	function setPostingId(int $postingId): IThreadStrategy;

	function updateStatus(string $status): bool;

	function lastThreadId(): int;
}
