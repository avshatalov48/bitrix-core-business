export function createDeferredPromise(): { promise: Promise<any>, resolve: () => void, reject: () => void }
{
	let resolve;
	let reject;
	const promise = new Promise((resolveRef, rejectRef) => {
		resolve = resolveRef;
		reject = rejectRef;
	});

	return {
		promise,
		resolve,
		reject,
	};
}
