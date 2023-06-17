this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,main_core_events,main_popup,bizproc_automation,bizproc_localSettings,main_core,ui_entityCatalog,ui_vue3_pinia) {
	'use strict';

	class GroupIcon {}
	GroupIcon.COMMUNICATION = `
		<svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M1.59123 0.576996H10.945C11.4447 0.576996 11.8498 0.982076 11.8498 1.48177V7.73366C11.8498 8.23335 11.4447 8.63843 10.945 8.63843H6.26813L3.79313 11.1131C3.56213 11.3441 3.1672 11.1805 3.1672 10.8538V8.63843H1.59123C1.09154 8.63843 0.686462 8.23335 0.686462 7.73366V1.48177C0.686462 0.982076 1.09154 0.576996 1.59123 0.576996ZM15.2863 4.29766C15.786 4.29766 16.1911 4.70274 16.1911 5.20244V10.8342C16.1911 11.3339 15.786 11.739 15.2863 11.739H13.7103V13.3343C13.7103 13.6609 13.3154 13.8245 13.0844 13.5936L11.2296 11.739H7.1729C6.67321 11.739 6.26813 11.3339 6.26813 10.8342V9.87865H11.7361C12.4856 9.87865 13.0932 9.27103 13.0932 8.5215V4.29766H15.2863Z"/>
		</svg>
	`;
	GroupIcon.INFORMING = `
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M7.94062 15.3333C11.9579 15.3333 15.2146 12.0767 15.2146 8.0594C15.2146 4.04211 11.9579 0.785461 7.94062 0.785461C3.92334 0.785461 0.666687 4.04211 0.666687 8.0594C0.666687 12.0767 3.92334 15.3333 7.94062 15.3333ZM9.0163 7.25348H7.13241V7.25388H6.32419V8.02399H7.13241V11.2923H6.32419V12.1005H7.13241H8.74884H9.0163H9.55705V11.2923H9.0163V7.25348ZM9.07747 4.83716C9.07747 5.46503 8.56849 5.97401 7.94062 5.97401C7.31276 5.97401 6.80377 5.46503 6.80377 4.83716C6.80377 4.2093 7.31276 3.70031 7.94062 3.70031C8.56849 3.70031 9.07747 4.2093 9.07747 4.83716Z" />
		</svg>
	`;
	GroupIcon.EMPLOYEES = `
		<svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path  class="bizproc-creating-robot__svg-icon-blue"  d="M10.6326 9.50203C10.6326 9.50203 11.0806 11.1896 11.3421 12.4571C11.3926 12.7023 11.2486 12.9445 11.0083 13.0147C9.59769 13.4267 8.0006 13.66 6.30852 13.6623H6.25753C4.10343 13.6594 2.10326 13.2821 0.445725 12.6382C0.22605 12.5529 0.103653 12.3208 0.14889 12.0895C0.317093 11.2295 0.511772 10.2623 0.609865 9.87472C0.803459 9.10982 1.88818 8.54142 2.88587 8.10456C3.14668 7.99043 3.30402 7.89929 3.46302 7.80719C3.61876 7.71699 3.77608 7.62586 4.03378 7.5113C4.06286 7.37067 4.07455 7.227 4.06859 7.08353L4.51067 7.03006C4.51067 7.03006 4.56877 7.13753 4.47532 6.50551C4.47532 6.50551 3.97915 6.37317 3.95613 5.36684C3.95613 5.36684 3.5834 5.4933 3.5609 4.88346C3.55633 4.76149 3.52551 4.64429 3.496 4.53208C3.42531 4.26327 3.36212 4.02302 3.68515 3.81404L3.45192 3.18201C3.45192 3.18201 3.20719 0.737309 4.282 0.936222C3.84607 0.233879 7.52356 -0.35056 7.76776 1.80272C7.86388 2.45181 7.86388 3.11148 7.76776 3.76057C7.76776 3.76057 8.31722 3.6964 7.95038 4.75941C7.95038 4.75941 7.74848 5.52351 7.43841 5.35106C7.43841 5.35106 7.48875 6.31809 7.00035 6.48198C7.00035 6.48198 7.03516 6.99691 7.03516 7.03193L7.4435 7.09529C7.4435 7.09529 7.43225 7.52467 7.51258 7.57119C7.8848 7.81592 8.29283 8.00145 8.72208 8.12114C9.98994 8.44838 10.6326 9.01009 10.6326 9.50203Z"/>
			<path class="bizproc-creating-robot__svg-icon-blue" d="M16.2687 9.983C16.2809 10.2247 16.2951 10.5077 16.309 10.7857C16.3202 11.0094 16.1815 11.2139 15.9676 11.2803C14.9025 11.611 13.6824 11.8769 12.3528 12.0616H11.9565C11.9327 11.726 11.6287 10.5637 11.4523 9.8892C11.3824 9.62183 11.3325 9.43108 11.3281 9.39897C11.3053 8.76373 10.7465 8.19641 9.79111 7.80874C9.86336 7.71104 9.92528 7.60612 9.97586 7.49567C10.1098 7.32941 10.2845 7.20048 10.483 7.12137L10.4983 6.62409L9.44944 6.29631C9.44944 6.29631 9.1798 6.17039 9.15302 6.17039C9.18405 6.09388 9.2229 6.02077 9.26896 5.95222C9.28905 5.89875 9.41597 5.49986 9.41597 5.49986C9.26324 5.69604 9.08411 5.87022 8.88365 6.01746C9.06714 5.69336 9.22273 5.3543 9.34876 5.00391C9.4319 4.66665 9.4876 4.32323 9.51531 3.977C9.58716 3.34781 9.69932 2.72386 9.85108 2.10899C9.96002 1.80216 10.152 1.53148 10.4056 1.32697C10.7804 1.06695 11.2179 0.911436 11.673 0.876477H11.7265C12.1824 0.911144 12.6207 1.06666 12.9963 1.32697C13.2502 1.53108 13.4422 1.80165 13.5511 2.10845C13.7027 2.72337 13.8149 3.3473 13.8871 3.97647C13.9194 4.31501 13.9779 4.65056 14.062 4.98012C14.1879 5.33668 14.3408 5.68319 14.5193 6.01666C14.3185 5.8698 14.139 5.69588 13.9859 5.49986C13.9859 5.49986 14.0847 5.86186 14.1045 5.91533C14.1588 5.99613 14.2071 6.08074 14.2491 6.16851C14.2232 6.16851 13.9527 6.29444 13.9527 6.29444L12.9039 6.62222L12.9189 7.11976C13.1175 7.19865 13.2922 7.32761 13.426 7.49406C13.4895 7.65382 13.59 7.79629 13.7192 7.9098C13.9723 7.99781 14.2162 8.1099 14.4478 8.24453C14.7984 8.43933 15.1832 8.56529 15.5813 8.61562C15.9829 8.68166 16.2362 9.32999 16.2362 9.32999C16.2362 9.33633 16.2499 9.60933 16.2686 9.9805L16.2687 9.983Z"/>
		</svg>
	`;
	GroupIcon.PAPERWORK = `
		<svg width="12" height="15" viewBox="0 0 12 15" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M1.41669 0.666687C0.864402 0.666687 0.416687 1.1144 0.416687 1.66669V13.4167C0.416687 13.969 0.864402 14.4167 1.41669 14.4167H10.4375C10.9898 14.4167 11.4375 13.969 11.4375 13.4167V5.31535C11.4375 4.81316 11.2486 4.32935 10.9083 3.96005L8.46753 1.31138C8.08886 0.900458 7.55555 0.666687 6.99676 0.666687H1.41669ZM2.99401 4.31723C2.59418 4.31723 2.27006 4.64135 2.27006 5.04118C2.27006 5.441 2.59418 5.76512 2.99401 5.76512H8.32692C8.72674 5.76512 9.05086 5.441 9.05086 5.04118C9.05086 4.64135 8.72674 4.31723 8.32692 4.31723H2.99401ZM2.99401 7.06723C2.59418 7.06723 2.27006 7.39135 2.27006 7.79118C2.27006 8.191 2.59418 8.51512 2.99401 8.51512H8.32692C8.72674 8.51512 9.05086 8.191 9.05086 7.79118C9.05086 7.39135 8.72674 7.06723 8.32692 7.06723H2.99401ZM2.27006 10.5043C2.27006 10.1045 2.59418 9.78037 2.99401 9.78037H6.76482C7.16465 9.78037 7.48877 10.1045 7.48877 10.5043C7.48877 10.9041 7.16465 11.2283 6.76483 11.2283H2.99401C2.59418 11.2283 2.27006 10.9041 2.27006 10.5043Z"/>
		</svg>
	`;
	GroupIcon.PAYMENT = `
		<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M12.9523 2.41008C11.9065 2.05645 5.13194 0.75 5.13194 0.75L4.60299 1.98149L12.7995 3.44285C13.6433 3.44285 14.3273 4.19526 14.3273 5.1234V4.09064C14.3273 3.16249 13.6913 2.65999 12.9523 2.41008ZM2.80304 3.24492C2.80304 3.24492 11.8548 3.97571 12.9005 4.32934C13.6395 4.57925 14.2755 5.08175 14.2755 6.00989V7.04266C14.2755 6.11451 13.5915 5.3621 12.7477 5.3621H2.30619L2.80304 3.24492ZM0.583313 7.47248C0.583313 6.9202 1.03103 6.47248 1.58331 6.47248H13.2769C13.8291 6.47248 14.2769 6.9202 14.2769 7.47248V13.7702C14.2769 14.3225 13.8291 14.7702 13.2769 14.7702H1.58331C1.03103 14.7702 0.583313 14.3225 0.583313 13.7702V7.47248Z"/>
		</svg>
	`;
	GroupIcon.DELIVERY = `
		<svg width="18" height="13" viewBox="0 0 18 13" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M17.0631 10.1625C17.0631 10.7911 16.5536 11.3007 15.925 11.3007L15.1601 11.3007C15.1642 11.2436 15.1663 11.186 15.1663 11.1279C15.1663 9.81834 14.1047 8.75677 12.7952 8.75677C11.4857 8.75677 10.4241 9.81834 10.4241 11.1279C10.4241 11.186 10.4262 11.2436 10.4303 11.3007H7.11522C7.11933 11.2436 7.12142 11.186 7.12142 11.1279C7.12142 9.81834 6.05985 8.75677 4.75032 8.75677C3.4408 8.75677 2.37923 9.81834 2.37923 11.1279C2.37923 11.186 2.38132 11.2436 2.38543 11.3007L1.88813 11.3007C1.25956 11.3007 0.75 10.7911 0.75 10.1625V7.31723C0.75 7.2853 0.751315 7.25367 0.753894 7.2224L0.75 7.22238V2.10081C0.75 1.26272 1.42941 0.583313 2.2675 0.583313H9.27287C10.111 0.583313 10.7904 1.26272 10.7904 2.10081L10.7904 2.38535H12.7354C13.6263 2.38535 14.4578 2.83205 14.9496 3.57486L16.5272 5.95741C16.6877 6.19983 16.8138 6.46148 16.9033 6.73488C17.0047 6.90528 17.0631 7.10443 17.0631 7.31723V10.1625ZM6.28471 11.1279C6.28471 10.2804 5.59774 9.59348 4.75032 9.59348C3.90291 9.59348 3.21594 10.2804 3.21594 11.1279C3.21594 11.9753 3.90291 12.6622 4.75032 12.6622C5.59774 12.6622 6.28471 11.9753 6.28471 11.1279ZM14.3296 11.1279C14.3296 10.2804 13.6426 9.59348 12.7952 9.59348C11.9478 9.59348 11.2608 10.2804 11.2608 11.1279C11.2608 11.9753 11.9478 12.6622 12.7952 12.6622C13.6426 12.6622 14.3296 11.9753 14.3296 11.1279ZM11.5622 3.52347H12.9067C13.2879 3.52347 13.6438 3.71431 13.8547 4.03184L15.281 6.1791H11.5622V3.52347Z"/>
		</svg>
	`;
	GroupIcon.SALES = `
		<svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M14.477 10.6451C14.7693 10.6451 15.0063 10.8979 15.0063 11.2372V13.0355C15.0063 13.3748 14.7693 13.6498 14.477 13.6498H1.52304C1.23074 13.6498 0.993774 13.3748 0.993774 13.0355V11.2372C0.993774 10.8979 1.23074 10.6451 1.52304 10.6451H14.477ZM8.00057 11.2299C7.56141 11.2299 7.20539 11.6055 7.20539 12.0688C7.20539 12.5322 7.56141 12.9078 8.00057 12.9078C8.43974 12.9078 8.79575 12.5322 8.79575 12.0688C8.79575 11.6055 8.43974 11.2299 8.00057 11.2299ZM7.48783 0.604309C7.78014 0.604309 8.0171 0.854327 8.0171 1.16274V2.30995H12.1024C12.3947 2.30995 12.6317 2.55997 12.6317 2.86838V4.01317C12.6317 4.01317 14.1401 8.20753 14.1401 8.32705V8.80363C14.1401 9.11205 13.9031 9.36206 13.6108 9.36206H2.41678C2.12448 9.36206 1.88752 9.11205 1.88752 8.80363V8.32705C1.88752 8.20753 3.15776 4.04874 3.15776 4.04874V2.86838C3.15776 2.55997 3.39472 2.30995 3.68703 2.30995H4.49416V1.16274C4.49416 0.854327 4.73113 0.604309 5.02343 0.604309L7.48783 0.604309ZM11.971 6.70599H4.11137C4.06909 6.70599 4.03453 6.74086 4.03212 6.78483L4.03198 6.78975V7.68324C4.03198 7.72785 4.06503 7.76432 4.10671 7.76687L11.971 7.76701C12.0133 7.76701 12.0479 7.73214 12.0503 7.68817L12.0504 6.78975C12.0504 6.74349 12.0149 6.70599 11.971 6.70599ZM11.3134 4.95441H4.76732C4.7321 4.95441 4.70332 4.98929 4.70131 5.03326L4.70119 5.03818V5.93167C4.70119 5.97628 4.72872 6.01274 4.76343 6.01529L4.76732 6.01543H11.3134C11.3486 6.01543 11.3774 5.98056 11.3794 5.93659L11.3796 5.03818C11.3796 4.99192 11.3499 4.95441 11.3134 4.95441ZM7.19148 1.50743C7.19148 1.48665 7.17463 1.4698 7.15385 1.4698H5.35742C5.33663 1.4698 5.31978 1.48665 5.31978 1.50743V2.99836C5.31978 3.01914 5.33663 3.03599 5.35742 3.03599H7.15385C7.17463 3.03599 7.19148 3.01914 7.19148 2.99836V1.50743Z" />
		</svg>
	`;
	GroupIcon.ADS = `
		<svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M2.78548 3.14396C2.79104 3.14168 2.79686 3.13987 2.80291 3.13859L14.6093 0.641989C14.9824 0.563099 15.3334 0.847685 15.3334 1.22901V9.70901C15.3334 10.1027 14.9607 10.3897 14.5801 10.2891L10.0882 9.10177V10.4762C10.0882 11.0285 9.64052 11.4762 9.08823 11.4762H4.97031C4.41803 11.4762 3.97032 11.0285 3.97032 10.4762V7.48464L2.79804 7.17478C2.79253 7.17332 2.78722 7.17142 2.78216 7.16912C2.75365 7.19136 2.72079 7.20868 2.68467 7.21962L1.0833 7.70455C0.890654 7.76288 0.69635 7.61871 0.69635 7.41742V2.89303C0.69635 2.69175 0.890654 2.54757 1.0833 2.60591L2.68467 3.09084C2.72219 3.1022 2.7562 3.12046 2.78548 3.14396ZM8.88285 8.78316L5.1757 7.80326V10.0991C5.1757 10.2648 5.31001 10.3991 5.4757 10.3991H8.58285C8.74853 10.3991 8.88285 10.2648 8.88285 10.0991V8.78316Z"/>
		</svg>
	`;
	GroupIcon.ELEMENT_CONTROL = `
		<svg width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M0.915689 0.698242H15.0846C15.4503 0.698242 15.7467 0.981381 15.7467 1.33065C15.7467 1.40145 15.7343 1.47174 15.7099 1.5386L15.2818 2.71309C15.1891 2.96724 14.9383 3.13755 14.6565 3.13755H1.34033C1.05798 3.13755 0.806687 2.96651 0.714515 2.71158L0.289868 1.5371C0.170503 1.20696 0.353927 0.8469 0.699558 0.732885C0.769087 0.70995 0.842131 0.698242 0.915689 0.698242ZM3.24939 5.20731H12.7509C13.1166 5.20731 13.413 5.49045 13.413 5.83972C13.413 5.91555 13.3987 5.99075 13.3709 6.06175L12.9098 7.23624C12.8129 7.48308 12.5659 7.64662 12.2899 7.64662H3.68591C3.40564 7.64662 3.15574 7.47806 3.06197 7.22578L2.62545 6.0513C2.50312 5.72215 2.68329 5.3606 3.02788 5.24375C3.099 5.21963 3.17392 5.20731 3.24939 5.20731ZM6.19927 9.73052H9.80104C10.1667 9.73052 10.4631 10.0137 10.4631 10.3629C10.4631 10.4245 10.4537 10.4857 10.4352 10.5446L10.0665 11.7191C9.98248 11.9866 9.7247 12.1698 9.43229 12.1698H6.60798C6.32279 12.1698 6.06965 11.9954 5.97969 11.7369L5.57098 10.5624C5.45564 10.231 5.64343 9.87298 5.99043 9.76281C6.05778 9.74142 6.12829 9.73052 6.19927 9.73052Z"/>
		</svg>
	`;
	GroupIcon.CLIENT_DATA = `
		<svg width="16" height="13" viewBox="0 0 16 13" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M14.1417 2.31749C14.7999 2.31749 15.3334 2.85186 15.3334 3.51103V11.2231C15.3334 11.8823 14.7999 12.4167 14.1417 12.4167H1.85841C1.20028 12.4167 0.666748 11.8823 0.666748 11.2231V3.51103C0.666748 2.85186 1.20028 2.31749 1.85841 2.31749H14.1417ZM5.01297 4.4398C4.31453 4.32938 4.47357 5.68653 4.47357 5.68653L4.62513 6.0374C4.35463 6.1869 4.50088 6.36522 4.53729 6.56914L4.54439 6.63108C4.55901 6.96963 4.80122 6.89943 4.80122 6.89943C4.81618 7.45808 5.1386 7.53155 5.1386 7.53155L5.16431 7.69656C5.18574 7.86093 5.16157 7.82275 5.16157 7.82275L4.8743 7.85243C4.87817 7.93208 4.87058 8.01183 4.85168 8.0899C4.51324 8.21844 4.44121 8.29383 4.10573 8.41925L3.8048 8.5365C3.25423 8.76144 2.73317 9.04264 2.62673 9.40194L2.13065 11.1374V11.1374H9.71988C9.72022 10.6957 9.13976 9.19504 9.13976 9.19504C9.13976 8.92195 8.72215 8.61012 7.89826 8.42845C7.61932 8.36201 7.35417 8.25902 7.1123 8.12315C7.0601 8.09733 7.0674 7.85896 7.0674 7.85896L6.80205 7.82379L6.77943 7.51849V7.51849C7.09681 7.4275 7.0641 6.89067 7.0641 6.89067C7.20514 6.95768 7.31174 6.76993 7.36301 6.65125L7.39679 6.56221C7.63517 5.97209 7.27812 6.00772 7.27812 6.00772C7.34058 5.64738 7.34058 5.28117 7.27812 4.92083C7.11943 3.72546 4.7297 4.0499 5.01297 4.4398ZM13.0417 6.83182H10.2917C10.0667 6.83182 9.87961 6.99421 9.8408 7.20836L9.83341 7.29087V7.95816C9.83341 8.18351 9.99555 8.37094 10.2094 8.40981L10.2917 8.41721H13.0417C13.2668 8.41721 13.4539 8.25482 13.4927 8.04067L13.5001 7.95816V7.29087C13.5001 7.03734 13.2949 6.83182 13.0417 6.83182ZM13.0417 4.1537H10.2917C10.0667 4.1537 9.87961 4.31609 9.8408 4.53024L9.83341 4.61276V5.28004C9.83341 5.5054 9.99555 5.69283 10.2094 5.7317L10.2917 5.73909H13.0417C13.2668 5.73909 13.4539 5.5767 13.4927 5.36255L13.5001 5.28004V4.61276C13.5001 4.35923 13.2949 4.1537 13.0417 4.1537ZM13.2686 0.5C13.8316 0.5 14.3035 0.891092 14.4281 1.41684H1.57207C1.69668 0.891092 2.16852 0.5 2.73155 0.5H13.2686Z" />
		</svg>
	`;
	GroupIcon.GOODS = `
		<svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M4.23787 0.58961L4.67123 2.46161L15.5258 2.44151C15.7066 2.44228 15.8768 2.52618 15.9876 2.66781C16.0992 2.81022 16.1392 2.99495 16.0977 3.17045L14.9939 9.29919C14.9307 9.56399 14.6944 9.75103 14.4227 9.75257H5.67583C5.40411 9.75104 5.16781 9.56399 5.10469 9.29919L3.19343 1.31855H1.49839C1.1719 1.31855 0.907227 1.05388 0.907227 0.727389C0.907227 0.400901 1.1719 0.13623 1.49839 0.13623H3.66597C3.93845 0.137767 4.17399 0.32481 4.23787 0.58961ZM8.88512 11.9202C8.88512 12.7469 8.21469 13.4173 7.38721 13.4173C6.56051 13.4173 5.89006 12.7469 5.89006 11.9202C5.89006 11.0927 6.56049 10.4222 7.38721 10.4222C8.21467 10.4222 8.88512 11.0927 8.88512 11.9202ZM14.1663 11.9202C14.1663 12.7469 13.4959 13.4173 12.6684 13.4173C11.8417 13.4173 11.1713 12.7469 11.1713 11.9202C11.1713 11.0927 11.8417 10.4222 12.6684 10.4222C13.4959 10.4222 14.1663 11.0927 14.1663 11.9202Z"/>
		</svg>
	`;
	GroupIcon.TASK = `
		<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M9.2613 11.521V9.06562H11.5278V2.45493H2.46174V9.06562H4.72826V11.521H9.2613ZM1.70626 0.566162H12.2834C12.9092 0.566162 13.4166 1.07354 13.4166 1.69942V12.2765C13.4166 12.9024 12.9092 13.4098 12.2834 13.4098H1.70626C1.08038 13.4098 0.572998 12.9024 0.572998 12.2765V1.69942C0.572998 1.07354 1.08038 0.566162 1.70626 0.566162ZM5.22457 5.44907L6.28228 6.54455L8.92655 3.8625L9.68206 4.99576L6.28228 8.39555L4.39351 6.50678L5.22457 5.44907Z"/>
		</svg>
	`;
	GroupIcon.STORAGE = `
		<svg width="17" height="12" viewBox="0 0 17 12" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M15.5167 6C15.9218 6 16.2501 6.32832 16.2501 6.73333V10.4C16.2501 11.0075 15.7576 11.5 15.1501 11.5H1.76675C1.15923 11.5 0.666748 11.0075 0.666748 10.4V6.73333C0.666748 6.32832 0.995073 6 1.40008 6H15.5167ZM11.1167 7.83333H5.80008C5.5267 7.83333 5.2999 8.03279 5.25728 8.29412L5.25008 8.38333V9.11667C5.25008 9.39005 5.44954 9.61684 5.71087 9.65947L5.80008 9.66667H11.1167C11.3901 9.66667 11.6169 9.46721 11.6595 9.20588L11.6667 9.11667V8.38333C11.6667 8.07958 11.4205 7.83333 11.1167 7.83333ZM3.41675 7.83333C2.91049 7.83333 2.50008 8.24374 2.50008 8.75C2.50008 9.25626 2.91049 9.66667 3.41675 9.66667C3.92301 9.66667 4.33341 9.25626 4.33341 8.75C4.33341 8.24374 3.92301 7.83333 3.41675 7.83333ZM13.1471 0.5C13.4892 0.5 13.8028 0.690431 13.9605 0.993929L15.7917 4.51735H1.21675L2.96024 1.00875C3.11511 0.697081 3.43312 0.5 3.78114 0.5H13.1471Z" />
		</svg>
	`;
	GroupIcon.AUTOMATION = `
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M8.97334 0.666626L10.7051 2.40481L9.27589 3.84155C11.0294 4.38805 12.3014 6.02865 12.3014 7.96851C12.3014 8.15827 12.2884 8.34478 12.2645 8.52803L12.8208 10.1903L15.1957 9.38574C15.2857 8.92706 15.3334 8.45321 15.3334 7.96743C15.3334 4.23082 12.5638 1.14482 8.97334 0.666626V0.666626ZM11.166 10.8919C10.3809 11.7485 9.25312 12.2874 7.99954 12.2874C7.05285 12.2874 6.17773 11.9795 5.46744 11.459L3.65864 11.4666L3.66948 13.9107C4.88186 14.8052 6.38051 15.3333 7.99954 15.3333C10.7539 15.3333 13.1548 13.8076 14.4084 11.5522L11.6941 12.4685L11.166 10.8919ZM4.04361 9.66658C3.8213 9.1461 3.69659 8.5714 3.69659 7.96743C3.69659 6.0178 4.98379 4.36962 6.75138 3.83287L8.17305 2.40481L6.52474 0.752288C3.1815 1.4365 0.666748 4.40757 0.666748 7.96743C0.666748 9.46055 1.10919 10.8496 1.87044 12.0098L1.8596 9.67525L4.04361 9.66658V9.66658Z" />
		</svg>
	`;
	GroupIcon.ANDROID = `
		<svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M12.5312 4.18202L13.3243 1.51824C13.3934 1.28664 13.3351 1.03534 13.1714 0.859007C13.0077 0.68267 12.7634 0.608084 12.5305 0.663346C12.2977 0.718608 12.1117 0.895322 12.0426 1.12692L11.3632 3.41359C9.25115 2.29852 6.73295 2.30118 4.62319 3.4207L3.94049 1.12692C3.83366 0.768899 3.46013 0.566266 3.10618 0.674327C2.75224 0.782388 2.55191 1.16022 2.65874 1.51824L3.45523 4.19251C1.66659 5.63864 0.627862 7.83115 0.634061 10.1473C0.634061 14.3273 3.93045 13.3016 7.99989 13.3016C12.0693 13.3016 15.3657 14.3273 15.3657 10.1473C15.3729 7.82494 14.3285 5.62706 12.5312 4.18202ZM7.99328 9.57927C5.08844 9.57927 2.73245 9.88394 2.73245 8.63854C2.73245 7.39315 5.08844 6.382 7.99328 6.382C10.8981 6.382 13.2575 7.39213 13.2575 8.63854C13.2575 9.88495 10.9015 9.57927 7.99328 9.57927ZM5.58709 7.31495C5.17236 7.39158 4.88832 7.7816 4.93893 8.20497C4.98954 8.62833 5.35729 8.93863 5.77813 8.91306C6.19896 8.88749 6.52734 8.53488 6.52748 8.10843C6.48049 7.62873 6.06206 7.27566 5.58709 7.31495ZM9.7501 8.20708C9.80166 8.63038 10.1699 8.94002 10.5908 8.91391C11.0116 8.8878 11.3397 8.53497 11.3398 8.10847C11.2928 7.62735 10.8722 7.27376 10.396 7.315C9.98151 7.393 9.69855 7.78379 9.7501 8.20708Z" />
		</svg>
	`;
	GroupIcon.COMMERCE = `
		<svg width="15" height="17" viewBox="0 0 15 17" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" fill-rule="evenodd" clip-rule="evenodd" d="M7.10547 1.54478C5.89891 1.54478 5.41758 2.42589 5.41758 2.8329V2.88466H8.79335V2.8329C8.79335 2.42589 8.31202 1.54478 7.10547 1.54478ZM3.99609 5.63149V4.46409H3.57865C2.95967 4.46409 2.44504 4.94065 2.39757 5.55781L1.87044 12.4105C1.81752 13.0985 2.3615 13.6859 3.05153 13.6859H6.70043L7.17648 15.2653H3.05153C1.44146 15.2653 0.172173 13.8946 0.295659 12.2893L0.822785 5.43668C0.933558 3.99663 2.13436 2.88466 3.57865 2.88466H3.99609V2.8329C3.99609 1.64082 5.11384 0.123291 7.10547 0.123291C9.09709 0.123291 10.2148 1.64082 10.2148 2.8329V2.88466H10.6065C12.0508 2.88466 13.2516 3.99664 13.3624 5.43668L13.7062 9.90643L12.0737 9.27686L11.7876 5.55782C11.7402 4.94066 11.2255 4.46409 10.6065 4.46409H10.2148V5.61755C10.5605 5.85185 10.7877 6.24787 10.7877 6.69694C10.7877 7.41659 10.2043 7.99998 9.48468 7.99998C8.76504 7.99998 8.18165 7.41659 8.18165 6.69694C8.18165 6.23126 8.42594 5.82263 8.79335 5.59222V4.46409H5.41758V5.57983C5.79619 5.8078 6.04944 6.2228 6.04944 6.69694C6.04944 7.41659 5.46605 7.99998 4.74641 7.99998C4.02676 7.99998 3.44337 7.41659 3.44337 6.69694C3.44337 6.25665 3.66174 5.86737 3.99609 5.63149ZM9.37277 15.8087L7.63611 10.0469C7.61028 9.96117 7.61146 9.86957 7.63946 9.78576C7.71106 9.57151 7.93599 9.46219 8.14185 9.54157L13.7392 11.7001C13.8352 11.7372 13.9147 11.8114 13.961 11.9073C14.0601 12.1129 13.9831 12.3604 13.789 12.4602L12.7522 12.9934C12.7125 13.0137 12.6766 13.0411 12.6459 13.0742C12.4956 13.2365 12.5017 13.4984 12.6595 13.6593L14.0727 15.0996L14.0802 15.1075C14.234 15.2727 14.2334 15.5346 14.0789 15.6924L13.3295 16.4585L13.3219 16.466C13.1634 16.6194 12.9136 16.6066 12.764 16.4373L11.4483 14.9481C11.437 14.9354 11.425 14.9233 11.4123 14.9121C11.2441 14.7632 10.9953 14.7831 10.8567 14.9564L10.0534 15.9609C10.0051 16.0213 9.94085 16.0655 9.86844 16.0881C9.66073 16.1529 9.43881 16.0278 9.37277 15.8087Z" />
		</svg>
	`;
	GroupIcon.PARTNER = `
		<svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path class="bizproc-creating-robot__svg-icon-blue" d="M12.6869 13.5858C12.9532 13.485 13.1018 13.2043 13.0469 12.9248L12.7469 11.3972C12.7469 10.8375 12.0017 10.1983 10.5341 9.8258C10.0369 9.68968 9.56423 9.47859 9.13302 9.20008C9.03872 9.14718 9.05305 8.65847 9.05305 8.65847L8.58038 8.58785C8.58038 8.54818 8.53997 7.96213 8.53997 7.96213C9.1055 7.77559 9.04732 6.67522 9.04732 6.67522C9.40647 6.87076 9.64037 5.99997 9.64037 5.99997C10.0652 4.79016 9.42883 4.86331 9.42883 4.86331C9.54015 4.12475 9.54015 3.37413 9.42883 2.63556C9.14592 0.185548 4.8865 0.850665 5.39155 1.65083C4.14669 1.42575 4.43075 4.20607 4.43075 4.20607L4.70076 4.92605C4.3265 5.16434 4.4 5.4378 4.4821 5.74327C4.51632 5.87062 4.55204 6.00353 4.55744 6.14178C4.58352 6.83559 5.01606 6.69182 5.01606 6.69182C5.04271 7.83692 5.61799 7.98604 5.61799 7.98604C5.72606 8.70518 5.6587 8.58279 5.6587 8.58279L5.14676 8.64356C5.15369 8.8071 5.14012 8.97086 5.10635 9.13114C4.80891 9.26127 4.62681 9.36482 4.44651 9.46733C4.26194 9.57229 4.07925 9.67616 3.77664 9.80639C2.62092 10.3035 1.36488 10.9501 1.14159 11.8206C1.06552 12.1172 0.991278 12.5327 0.926446 12.9518C0.884754 13.2213 1.03401 13.4842 1.28895 13.581C2.88255 14.1861 4.69417 14.5455 6.62018 14.5869H7.36842C9.28929 14.5456 11.0964 14.188 12.6869 13.5858Z" />
		</svg>
	`;

	var _customData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("customData");
	var _selected = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selected");
	var _disabled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disabled");
	var _compare = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("compare");
	class Group {
	  constructor() {
	    Object.defineProperty(this, _customData, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _selected, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _disabled, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _compare, {
	      writable: true,
	      value: null
	    });
	    if (this.constructor === Group) {
	      throw new Error('Object of Abstract Class cannot be created');
	    }
	  }
	  getId() {
	    throw new Error("Abstract Method has no implementation");
	  }
	  getName() {
	    throw new Error("Abstract Method has no implementation");
	  }
	  getIcon() {
	    return '';
	  }
	  getTags() {
	    return [];
	  }
	  getAdviceTitle() {
	    return '';
	  }
	  getAdviceAvatar() {
	    return '';
	  }
	  setCustomData(customData = {}) {
	    babelHelpers.classPrivateFieldLooseBase(this, _customData)[_customData] = customData;
	    return this;
	  }
	  getCustomData() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _customData)[_customData];
	  }
	  setSelected(selected = false) {
	    babelHelpers.classPrivateFieldLooseBase(this, _selected)[_selected] = selected;
	    return this;
	  }
	  getSelected() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selected)[_selected];
	  }
	  setDisabled(disabled = false) {
	    babelHelpers.classPrivateFieldLooseBase(this, _disabled)[_disabled] = disabled;
	    return this;
	  }
	  getDisabled() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _disabled)[_disabled];
	  }
	  setCompare(compare) {
	    babelHelpers.classPrivateFieldLooseBase(this, _compare)[_compare] = compare;
	    return this;
	  }
	  getCompare() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _compare)[_compare];
	  }
	  getData() {
	    const data = {
	      id: this.getId(),
	      name: this.getName(),
	      icon: this.getIcon(),
	      tags: this.getTags(),
	      adviceTitle: this.getAdviceTitle(),
	      adviceAvatar: this.getAdviceAvatar(),
	      customData: this.getCustomData(),
	      selected: this.getSelected(),
	      disabled: this.getDisabled()
	    };
	    if (main_core.Type.isFunction(this.getCompare())) {
	      data.compare = this.getCompare();
	    }
	    return data;
	  }
	}

	class ClientCommunication extends Group {
	  getId() {
	    return 'clientCommunication';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_CLIENT_COMMUNICATION');
	  }
	  getIcon() {
	    return GroupIcon.COMMUNICATION;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_CLIENT_COMMUNICATION');
	  }
	}

	class InformingEmployee extends Group {
	  getId() {
	    return 'informingEmployee';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_INFORMING_EMPLOYEE');
	  }
	  getIcon() {
	    return GroupIcon.INFORMING;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_INFORMING_EMPLOYEE');
	  }
	}

	class EmployeeControl extends Group {
	  getId() {
	    return 'employeeControl';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_EMPLOYEE_CONTROL');
	  }
	  getIcon() {
	    return GroupIcon.EMPLOYEES;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_EMPLOYEE_CONTROL_1');
	  }
	}

	class Paperwork extends Group {
	  getId() {
	    return 'paperwork';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_PAPERWORK');
	  }
	  getIcon() {
	    return GroupIcon.PAPERWORK;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_PAPERWORK');
	  }
	}

	class Payment extends Group {
	  getId() {
	    return 'payment';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_PAYMENT');
	  }
	  getIcon() {
	    return GroupIcon.PAYMENT;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_PAYMENT');
	  }
	}

	class Delivery extends Group {
	  getId() {
	    return 'delivery';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DELIVERY');
	  }
	  getIcon() {
	    return GroupIcon.DELIVERY;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_DELIVERY');
	  }
	}

	class RepeatSales extends Group {
	  getId() {
	    return 'repeatSales';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_REPEAT_SALES');
	  }
	  getIcon() {
	    return GroupIcon.SALES;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_REPEAT_SALES');
	  }
	}

	class Ads extends Group {
	  getId() {
	    return 'ads';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_ADS');
	  }
	  getIcon() {
	    return GroupIcon.ADS;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_ADS');
	  }
	}

	class ElementControl extends Group {
	  getId() {
	    return 'elementControl';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_ELEMENT_CONTROL');
	  }
	  getIcon() {
	    return GroupIcon.ELEMENT_CONTROL;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_ELEMENT_CONTROL');
	  }
	}

	class ClientData extends Group {
	  getId() {
	    return 'clientData';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_CLIENT_DATA');
	  }
	  getIcon() {
	    return GroupIcon.CLIENT_DATA;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_CLIENT_DATA');
	  }
	}

	class Goods extends Group {
	  getId() {
	    return 'goods';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_GOODS');
	  }
	  getIcon() {
	    return GroupIcon.GOODS;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_GOODS');
	  }
	}

	class TaskManagement extends Group {
	  getId() {
	    return 'taskManagement';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_TASK_MANAGEMENT');
	  }
	  getIcon() {
	    return GroupIcon.TASK;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_TASK_MANAGEMENT');
	  }
	}

	class ModificationData extends Group {
	  getId() {
	    return 'modificationData';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_MODIFICATION_DATA');
	  }
	  getIcon() {
	    return GroupIcon.STORAGE;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_MODIFICATION_DATA');
	  }
	}

	class DigitalWorkplace extends Group {
	  getId() {
	    return 'digitalWorkplace';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DIGITAL_WORKPLACE');
	  }
	  getIcon() {
	    return GroupIcon.AUTOMATION;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_DIGITAL_WORKPLACE');
	  }
	}

	class OtherGroup extends Group {
	  getId() {
	    return 'other';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_OTHER');
	  }
	  getIcon() {
	    return GroupIcon.ANDROID;
	  }
	  getAdviceTitle() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_OTHER');
	  }
	}

	class EmployeeCategory extends Group {
	  getId() {
	    return 'employee_category';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_EMPLOYEE_CATEGORY');
	  }
	  getIcon() {
	    return GroupIcon.EMPLOYEES;
	  }
	}

	class ClientCategory extends Group {
	  getId() {
	    return 'client_category';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_CLIENT_CATEGORY');
	  }
	  getIcon() {
	    return GroupIcon.COMMUNICATION;
	  }
	}

	class AdsCategory extends Group {
	  getId() {
	    return 'ads_category';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_ADS_CATEGORY');
	  }
	  getIcon() {
	    return GroupIcon.ADS;
	  }
	}

	class OtherCategory extends Group {
	  getId() {
	    return 'other_category';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_OTHER_CATEGORY');
	  }
	  getIcon() {
	    return GroupIcon.ANDROID;
	  }
	}

	class TriggerCategory extends Group {
	  getId() {
	    return 'trigger_category';
	  }
	  getName() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_TRIGGER_CATEGORY');
	  }
	  getIcon() {
	    return GroupIcon.AUTOMATION;
	  }
	}

	let instance = null;
	var _clientCommunicationGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clientCommunicationGroup");
	var _informingEmployeeGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("informingEmployeeGroup");
	var _employeeControlGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("employeeControlGroup");
	var _paperworkGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("paperworkGroup");
	var _paymentGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("paymentGroup");
	var _deliveryGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deliveryGroup");
	var _repeatSalesGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("repeatSalesGroup");
	var _adsGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adsGroup");
	var _elementControlGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("elementControlGroup");
	var _clientDataGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clientDataGroup");
	var _goodsGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("goodsGroup");
	var _taskManagementGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("taskManagementGroup");
	var _modificationDataGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("modificationDataGroup");
	var _digitalWorkplaceGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("digitalWorkplaceGroup");
	var _otherGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("otherGroup");
	var _employeeCategory = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("employeeCategory");
	var _clientCategory = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clientCategory");
	var _adsCategory = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adsCategory");
	var _otherCategory = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("otherCategory");
	var _triggerCategory = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("triggerCategory");
	class Manager {
	  constructor() {
	    Object.defineProperty(this, _clientCommunicationGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _informingEmployeeGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _employeeControlGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _paperworkGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _paymentGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _deliveryGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _repeatSalesGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _adsGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _elementControlGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _clientDataGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _goodsGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _taskManagementGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _modificationDataGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _digitalWorkplaceGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _otherGroup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _employeeCategory, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _clientCategory, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _adsCategory, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _otherCategory, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _triggerCategory, {
	      writable: true,
	      value: void 0
	    });
	  }
	  static get Instance() {
	    if (instance === null) {
	      instance = new Manager();
	    }
	    return instance;
	  }
	  getAutomationGroupsData() {
	    return [this.clientCommunicationGroup.getData(), this.informingEmployeeGroup.getData(), this.employeeControlGroup.getData(), this.paperworkGroup.getData(), this.paymentGroup.getData(), this.deliveryGroup.getData(), this.repeatSalesGroup.getData(), this.adsGroup.getData(), this.elementControlGroup.getData(), this.clientDataGroup.getData(), this.goodsGroup.getData(), this.taskManagementGroup.getData(), this.modificationDataGroup.getData(), this.digitalWorkplaceGroup.getData(), this.otherGroup.getData()];
	  }
	  getAutomationCategoriesData() {
	    return [this.employeeCategory.getData(), this.clientCategory.getData(), this.adsCategory.getData(), this.otherCategory.getData(), this.triggerCategory.getData()];
	  }
	  get clientCommunicationGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _clientCommunicationGroup)[_clientCommunicationGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _clientCommunicationGroup)[_clientCommunicationGroup] = new ClientCommunication();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _clientCommunicationGroup)[_clientCommunicationGroup];
	  }
	  get informingEmployeeGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _informingEmployeeGroup)[_informingEmployeeGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _informingEmployeeGroup)[_informingEmployeeGroup] = new InformingEmployee();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _informingEmployeeGroup)[_informingEmployeeGroup];
	  }
	  get employeeControlGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _employeeControlGroup)[_employeeControlGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _employeeControlGroup)[_employeeControlGroup] = new EmployeeControl();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _employeeControlGroup)[_employeeControlGroup];
	  }
	  get paperworkGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _paperworkGroup)[_paperworkGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _paperworkGroup)[_paperworkGroup] = new Paperwork();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _paperworkGroup)[_paperworkGroup];
	  }
	  get paymentGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _paymentGroup)[_paymentGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _paymentGroup)[_paymentGroup] = new Payment();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _paymentGroup)[_paymentGroup];
	  }
	  get deliveryGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _deliveryGroup)[_deliveryGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _deliveryGroup)[_deliveryGroup] = new Delivery();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _deliveryGroup)[_deliveryGroup];
	  }
	  get repeatSalesGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _repeatSalesGroup)[_repeatSalesGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _repeatSalesGroup)[_repeatSalesGroup] = new RepeatSales();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _repeatSalesGroup)[_repeatSalesGroup];
	  }
	  get adsGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _adsGroup)[_adsGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _adsGroup)[_adsGroup] = new Ads();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _adsGroup)[_adsGroup];
	  }
	  get elementControlGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _elementControlGroup)[_elementControlGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _elementControlGroup)[_elementControlGroup] = new ElementControl();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _elementControlGroup)[_elementControlGroup];
	  }
	  get clientDataGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _clientDataGroup)[_clientDataGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _clientDataGroup)[_clientDataGroup] = new ClientData();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _clientDataGroup)[_clientDataGroup];
	  }
	  get goodsGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _goodsGroup)[_goodsGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _goodsGroup)[_goodsGroup] = new Goods();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _goodsGroup)[_goodsGroup];
	  }
	  get taskManagementGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _taskManagementGroup)[_taskManagementGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _taskManagementGroup)[_taskManagementGroup] = new TaskManagement();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _taskManagementGroup)[_taskManagementGroup];
	  }
	  get modificationDataGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _modificationDataGroup)[_modificationDataGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _modificationDataGroup)[_modificationDataGroup] = new ModificationData();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _modificationDataGroup)[_modificationDataGroup];
	  }
	  get digitalWorkplaceGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _digitalWorkplaceGroup)[_digitalWorkplaceGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _digitalWorkplaceGroup)[_digitalWorkplaceGroup] = new DigitalWorkplace();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _digitalWorkplaceGroup)[_digitalWorkplaceGroup];
	  }
	  get otherGroup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _otherGroup)[_otherGroup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _otherGroup)[_otherGroup] = new OtherGroup();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _otherGroup)[_otherGroup];
	  }
	  get employeeCategory() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _employeeCategory)[_employeeCategory]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _employeeCategory)[_employeeCategory] = new EmployeeCategory();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _employeeCategory)[_employeeCategory];
	  }
	  get clientCategory() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _clientCategory)[_clientCategory]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _clientCategory)[_clientCategory] = new ClientCategory();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _clientCategory)[_clientCategory];
	  }
	  get adsCategory() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _adsCategory)[_adsCategory]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _adsCategory)[_adsCategory] = new AdsCategory();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _adsCategory)[_adsCategory];
	  }
	  get otherCategory() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _otherCategory)[_otherCategory]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _otherCategory)[_otherCategory] = new OtherCategory();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _otherCategory)[_otherCategory];
	  }
	  get triggerCategory() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _triggerCategory)[_triggerCategory]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _triggerCategory)[_triggerCategory] = new TriggerCategory();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _triggerCategory)[_triggerCategory];
	  }
	}

	var _applied = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applied");
	class Filter {
	  constructor() {
	    Object.defineProperty(this, _applied, {
	      writable: true,
	      value: false
	    });
	    if (this.constructor === Filter) {
	      throw new Error('Object of Abstract Class cannot be created');
	    }
	  }
	  getId() {
	    throw new Error("Abstract Method has no implementation");
	  }
	  getText() {
	    throw new Error("Abstract Method has no implementation");
	  }
	  getAction() {
	    throw new Error("Abstract Method has no implementation");
	  }
	  setApplied(applied = false) {
	    babelHelpers.classPrivateFieldLooseBase(this, _applied)[_applied] = applied;
	    return this;
	  }
	  getApplied() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _applied)[_applied];
	  }
	  getData() {
	    return {
	      id: this.getId(),
	      text: this.getText(),
	      action: this.getAction(),
	      applied: this.getApplied()
	    };
	  }
	}

	class B24Robots extends Filter {
	  getId() {
	    return 'bitrix24_robots';
	  }
	  getText() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TITLEBAR_FILTER_BITRIX_24_ROBOTS');
	  }
	  getAction() {
	    return item => {
	      return item.customData.type === 'robot' && item.customData.owner === 'bitrix24';
	    };
	  }
	}

	class B24Triggers extends Filter {
	  getId() {
	    return 'bitrix24_triggers';
	  }
	  getText() {
	    return main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TITLEBAR_FILTER_BITRIX_24_TRIGGERS');
	  }
	  getAction() {
	    return item => {
	      return item.customData.type === 'trigger' && item.customData.owner === 'bitrix24';
	    };
	  }
	}

	class RecentGroup extends Group {
	  getId() {
	    return 'recent';
	  }
	  getName() {
	    return '';
	  }
	  getData() {
	    return {
	      selected: this.getSelected(),
	      compare: this.getCompare()
	    };
	  }
	}

	const EmptyGroupStub = {
	  name: 'bizproc-robot-selector-empty-group-stub',
	  components: {
	    EmptyContent: ui_entityCatalog.Stubs.EmptyContent
	  },
	  props: {
	    group: {
	      type: ui_entityCatalog.GroupData,
	      required: true
	    }
	  },
	  computed: {
	    isRecentGroup() {
	      var _this$currentGroup;
	      return ((_this$currentGroup = this.currentGroup) == null ? void 0 : _this$currentGroup.id) === 'recent';
	    },
	    ...ui_vue3_pinia.mapState(ui_entityCatalog.States.useGlobalState, ['currentGroup'])
	  },
	  template: `
		<EmptyContent>
			<div v-if="isRecentGroup">
				<b>{{$Bitrix.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_EMPTY_RECENT_GROUP_STUB_TITLE')}}</b><br/>
				{{$Bitrix.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_EMPTY_RECENT_GROUP_STUB_TEXT')}}
			</div>
			<div v-else>
				{{$Bitrix.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_EMPTY_GROUP_STUB_TITLE')}}
			</div>
		</EmptyContent>
	`
	};

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4;
	var _context = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("context");
	var _stageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stageId");
	var _catalog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("catalog");
	var _cache = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");
	var _showNewGroups = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showNewGroups");
	var _getRecentEntitiesIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRecentEntitiesIds");
	var _getRecentEntities = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRecentEntities");
	var _getCatalog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCatalog");
	var _getDefaultRobotGroups = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDefaultRobotGroups");
	var _getItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getItems");
	var _getAssociatedTriggers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAssociatedTriggers");
	var _getGroupIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getGroupIds");
	var _getRobotItemData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRobotItemData");
	var _getRecentRobotIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRecentRobotIds");
	var _getTriggerItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTriggerItems");
	var _getRecentTriggersIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRecentTriggersIds");
	var _addToRecentGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addToRecentGroup");
	var _getSlots = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSlots");
	var _getGroupsHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getGroupsHeader");
	var _getItemsHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getItemsHeader");
	var _getItemsStub = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getItemsStub");
	var _getSearchNotFoundStub = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSearchNotFoundStub");
	var _getGroupsFooter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getGroupsFooter");
	var _getTitleBar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTitleBar");
	var _getTitleBarStageBlock = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTitleBarStageBlock");
	var _onTitleBarStageBlockClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onTitleBarStageBlockClick");
	var _onStageIdChanged = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onStageIdChanged");
	var _createTitleBarStageBlockColor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createTitleBarStageBlockColor");
	var _getFilterOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFilterOptions");
	var _getPopupOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupOptions");
	var _sortItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sortItems");
	var _oldSortItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("oldSortItems");
	var _getColor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getColor");
	class RobotSelector extends main_core_events.EventEmitter {
	  constructor(props) {
	    super();
	    // TODO - fix namespace
	    Object.defineProperty(this, _oldSortItems, {
	      value: _oldSortItems2
	    });
	    Object.defineProperty(this, _sortItems, {
	      value: _sortItems2
	    });
	    Object.defineProperty(this, _getPopupOptions, {
	      value: _getPopupOptions2
	    });
	    Object.defineProperty(this, _getFilterOptions, {
	      value: _getFilterOptions2
	    });
	    Object.defineProperty(this, _createTitleBarStageBlockColor, {
	      value: _createTitleBarStageBlockColor2
	    });
	    Object.defineProperty(this, _onStageIdChanged, {
	      value: _onStageIdChanged2
	    });
	    Object.defineProperty(this, _onTitleBarStageBlockClick, {
	      value: _onTitleBarStageBlockClick2
	    });
	    Object.defineProperty(this, _getTitleBarStageBlock, {
	      value: _getTitleBarStageBlock2
	    });
	    Object.defineProperty(this, _getTitleBar, {
	      value: _getTitleBar2
	    });
	    Object.defineProperty(this, _getGroupsFooter, {
	      value: _getGroupsFooter2
	    });
	    Object.defineProperty(this, _getSearchNotFoundStub, {
	      value: _getSearchNotFoundStub2
	    });
	    Object.defineProperty(this, _getItemsStub, {
	      value: _getItemsStub2
	    });
	    Object.defineProperty(this, _getItemsHeader, {
	      value: _getItemsHeader2
	    });
	    Object.defineProperty(this, _getGroupsHeader, {
	      value: _getGroupsHeader2
	    });
	    Object.defineProperty(this, _getSlots, {
	      value: _getSlots2
	    });
	    Object.defineProperty(this, _addToRecentGroup, {
	      value: _addToRecentGroup2
	    });
	    Object.defineProperty(this, _getRecentTriggersIds, {
	      value: _getRecentTriggersIds2
	    });
	    Object.defineProperty(this, _getTriggerItems, {
	      value: _getTriggerItems2
	    });
	    Object.defineProperty(this, _getRecentRobotIds, {
	      value: _getRecentRobotIds2
	    });
	    Object.defineProperty(this, _getRobotItemData, {
	      value: _getRobotItemData2
	    });
	    Object.defineProperty(this, _getGroupIds, {
	      value: _getGroupIds2
	    });
	    Object.defineProperty(this, _getAssociatedTriggers, {
	      value: _getAssociatedTriggers2
	    });
	    Object.defineProperty(this, _getItems, {
	      value: _getItems2
	    });
	    Object.defineProperty(this, _getDefaultRobotGroups, {
	      value: _getDefaultRobotGroups2
	    });
	    Object.defineProperty(this, _getCatalog, {
	      value: _getCatalog2
	    });
	    Object.defineProperty(this, _getRecentEntities, {
	      value: _getRecentEntities2
	    });
	    Object.defineProperty(this, _getRecentEntitiesIds, {
	      value: _getRecentEntitiesIds2
	    });
	    Object.defineProperty(this, _context, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stageId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _catalog, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _cache, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _showNewGroups, {
	      writable: true,
	      value: false
	    });
	    this.setEventNamespace('BX.Bizproc.Automation.RobotSelector');
	    babelHelpers.classPrivateFieldLooseBase(this, _context)[_context] = props.context;
	    babelHelpers.classPrivateFieldLooseBase(this, _stageId)[_stageId] = props.stageId;
	    babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache] = new bizproc_localSettings.Settings('robot-selector');
	    this.recentGroupIdsSort = new Map(babelHelpers.classPrivateFieldLooseBase(this, _getRecentEntitiesIds)[_getRecentEntitiesIds]().map((id, index) => [id, index]));
	    babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].set('recentAutomationEntities', new Map());
	    babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].subsribeValueChanges('recentAutomationEntities', event => {
	      const {
	        value: newRecentGroupIds
	      } = event.getData();
	      this.recentGroupIdsSort = new Map(newRecentGroupIds.map((item, index) => [item.id, index]));
	    });
	    this.subscribeFromOptions(props.events);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].document.getRawType()[0] === 'crm') {
	      babelHelpers.classPrivateFieldLooseBase(this, _showNewGroups)[_showNewGroups] = true;
	    }
	  }
	  setStageId(stageId) {
	    if (main_core.Type.isStringFilled(stageId)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _stageId)[_stageId] = stageId;
	      babelHelpers.classPrivateFieldLooseBase(this, _onStageIdChanged)[_onStageIdChanged]();
	    }
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getCatalog)[_getCatalog]().show();
	    this.emit('onAfterShow');
	  }
	  close() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getCatalog)[_getCatalog]().close();
	  }
	  isShown() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getCatalog)[_getCatalog]().isShown();
	  }
	}
	function _getRecentEntitiesIds2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getRecentEntities)[_getRecentEntities]().map(item => item.id);
	}
	function _getRecentEntities2() {
	  var _recentEntitiesByDocu;
	  const recentEntitiesByDocumentType = babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('recentAutomationEntities', {});
	  return (_recentEntitiesByDocu = recentEntitiesByDocumentType[babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].document.getRawType()[2]]) != null ? _recentEntitiesByDocu : [];
	}
	function _getCatalog2() {
	  if (main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _catalog)[_catalog])) {
	    babelHelpers.classPrivateFieldLooseBase(this, _catalog)[_catalog] = new ui_entityCatalog.EntityCatalog({
	      groups: babelHelpers.classPrivateFieldLooseBase(this, _getDefaultRobotGroups)[_getDefaultRobotGroups](),
	      items: babelHelpers.classPrivateFieldLooseBase(this, _getItems)[_getItems](),
	      recentGroupData: new RecentGroup().setSelected(babelHelpers.classPrivateFieldLooseBase(this, _getRecentEntities)[_getRecentEntities]().length > 0).setCompare((lhsItem, rhsItem) => this.recentGroupIdsSort.get(lhsItem.id) - this.recentGroupIdsSort.get(rhsItem.id)).getData(),
	      canDeselectGroups: false,
	      customTitleBar: babelHelpers.classPrivateFieldLooseBase(this, _getTitleBar)[_getTitleBar](),
	      slots: babelHelpers.classPrivateFieldLooseBase(this, _getSlots)[_getSlots](),
	      showEmptyGroups: false,
	      showRecentGroup: true,
	      showSearch: true,
	      filterOptions: babelHelpers.classPrivateFieldLooseBase(this, _getFilterOptions)[_getFilterOptions](),
	      popupOptions: babelHelpers.classPrivateFieldLooseBase(this, _getPopupOptions)[_getPopupOptions](),
	      customComponents: {
	        EmptyGroupStub
	      }
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _catalog)[_catalog];
	}
	function _getDefaultRobotGroups2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _showNewGroups)[_showNewGroups] ? [Manager.Instance.getAutomationGroupsData()] : [Manager.Instance.getAutomationCategoriesData()];
	}
	function _getItems2() {
	  const getButtonHandler = robotData => {
	    return event => {
	      if (robotData.LOCKED) {
	        if (top.BX.UI && top.BX.UI.InfoHelper && robotData.LOCKED.INFO_CODE) {
	          top.BX.UI.InfoHelper.show(robotData.LOCKED.INFO_CODE);
	        }
	        return;
	      }
	      if (!event.getData().eventData.groupIds.includes(this.constructor.RECENT_GROUP_ID)) {
	        event.getData().eventData.groupIds.push(this.constructor.RECENT_GROUP_ID);
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _addToRecentGroup)[_addToRecentGroup]({
	        entity: 'robot',
	        id: event.getData().eventData.id
	      });
	      const originalEvent = event.getData().originalEvent;
	      this.emit('robotSelected', {
	        robotData,
	        originalEvent,
	        item: event.getData().eventData,
	        stageId: babelHelpers.classPrivateFieldLooseBase(this, _stageId)[_stageId]
	      });
	    };
	  };
	  const availableRobots = babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].availableRobots;
	  const recentRobotIds = babelHelpers.classPrivateFieldLooseBase(this, _getRecentRobotIds)[_getRecentRobotIds]();
	  const triggers = babelHelpers.classPrivateFieldLooseBase(this, _getTriggerItems)[_getTriggerItems]();
	  let items = [];
	  const restRobots = [];
	  let restTriggers = [];
	  for (const robot of availableRobots) {
	    const settings = main_core.Type.isPlainObject(robot['ROBOT_SETTINGS']) ? robot['ROBOT_SETTINGS'] : {};
	    if (robot['EXCLUDED']) {
	      if (main_core.Type.isPlainObject(settings['ASSOCIATED_TRIGGERS'])) {
	        items = items.concat(babelHelpers.classPrivateFieldLooseBase(this, _getAssociatedTriggers)[_getAssociatedTriggers](settings, triggers));
	      }
	      continue;
	    }
	    const isRestRobot = robot['CATEGORY'] && robot['CATEGORY']['ID'] && robot['CATEGORY']['ID'] === 'rest';
	    const robotItem = babelHelpers.classPrivateFieldLooseBase(this, _getRobotItemData)[_getRobotItemData](robot);
	    robotItem.button = {
	      action: getButtonHandler(robot),
	      locked: !!robot.LOCKED
	    };
	    const isRecentRobot = recentRobotIds.includes(robotItem.id);
	    if (isRecentRobot && !robotItem.groupIds.includes(this.constructor.RECENT_GROUP_ID)) {
	      robotItem.groupIds.push(this.constructor.RECENT_GROUP_ID);
	    } else if (!isRecentRobot) {
	      robotItem.groupIds = robotItem.groupIds.filter(id => id !== RobotSelector.RECENT_GROUP_ID);
	    }
	    if (isRestRobot) {
	      restRobots.push(robotItem);
	      if (main_core.Type.isPlainObject(settings['ASSOCIATED_TRIGGERS'])) {
	        restTriggers = restTriggers.concat(babelHelpers.classPrivateFieldLooseBase(this, _getAssociatedTriggers)[_getAssociatedTriggers](settings, triggers));
	      }
	    } else {
	      const useGroupKeys = babelHelpers.classPrivateFieldLooseBase(this, _showNewGroups)[_showNewGroups] && settings['TITLE_GROUP'];
	      const useCategoryKeys = !babelHelpers.classPrivateFieldLooseBase(this, _showNewGroups)[_showNewGroups] && settings['TITLE_CATEGORY'];
	      if (useGroupKeys || useCategoryKeys) {
	        const titleGroupKey = useGroupKeys ? 'TITLE_GROUP' : 'TITLE_CATEGORY';
	        const descriptionGroupKey = useGroupKeys ? 'DESCRIPTION_GROUP' : 'DESCRIPTION_CATEGORY';
	        const groupIdsByTitle = {};
	        Object.entries(settings[titleGroupKey]).forEach(([key, value]) => {
	          if (!main_core.Type.isArray(groupIdsByTitle[value])) {
	            groupIdsByTitle[value] = [];
	          }
	          groupIdsByTitle[value].push(key);
	        });
	        for (const groupTitle in groupIdsByTitle) {
	          const firstGroupId = groupIdsByTitle[groupTitle][0];
	          const groupIds = groupIdsByTitle[groupTitle];
	          const item = main_core.Runtime.clone(robotItem);
	          item.id = robot['CLASS'] + '@' + firstGroupId;
	          item.title = groupTitle;
	          item.groupIds = babelHelpers.classPrivateFieldLooseBase(this, _getGroupIds)[_getGroupIds]({
	            'GROUP': groupIds,
	            'CATEGORY': groupIds
	          }, 'robot');
	          item.description = settings[descriptionGroupKey] ? settings[descriptionGroupKey][firstGroupId] : robot['DESCRIPTION'];
	          item.customData.contextGroup = firstGroupId;
	          if (recentRobotIds.includes(item.id)) {
	            if (!item.groupIds.includes(this.constructor.RECENT_GROUP_ID)) {
	              item.groupIds.push(this.constructor.RECENT_GROUP_ID);
	            }
	          }
	          items.push(item);
	        }
	      } else {
	        items.push(robotItem);
	      }
	      if (main_core.Type.isPlainObject(settings['ASSOCIATED_TRIGGERS'])) {
	        items = items.concat(babelHelpers.classPrivateFieldLooseBase(this, _getAssociatedTriggers)[_getAssociatedTriggers](settings, triggers));
	      }
	    }
	  }
	  for (const key in triggers) {
	    if (triggers[key].customData.owner === 'rest') {
	      restTriggers.push(triggers[key]);
	      continue;
	    }
	    items.push(triggers[key]);
	  }
	  items = items.concat(restRobots, restTriggers);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _showNewGroups)[_showNewGroups]) {
	    items.sort(babelHelpers.classPrivateFieldLooseBase(this, _sortItems)[_sortItems].bind(this));
	  } else {
	    items.sort(babelHelpers.classPrivateFieldLooseBase(this, _oldSortItems)[_oldSortItems].bind(this));
	  }
	  return items;
	}
	function _getAssociatedTriggers2(settings, triggers) {
	  const associatedTriggers = [];
	  if (main_core.Type.isPlainObject(settings['ASSOCIATED_TRIGGERS'])) {
	    for (const code in settings['ASSOCIATED_TRIGGERS']) {
	      const trigger = triggers[code];
	      if (trigger) {
	        trigger.customData.sort = settings['SORT'] + settings['ASSOCIATED_TRIGGERS'][code];
	        associatedTriggers.push(main_core.Runtime.clone(trigger));
	        delete triggers[code];
	      }
	    }
	  }
	  return associatedTriggers;
	}
	function _getGroupIds2(settings, type) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _showNewGroups)[_showNewGroups]) {
	    return main_core.Type.isArrayFilled(settings['GROUP']) ? settings['GROUP'] : [RobotSelector.DEFAULT_GROUP_NAME];
	  }
	  if (type === 'robot') {
	    const categories = main_core.Type.isArray(settings['CATEGORY']) ? settings['CATEGORY'] : [settings['CATEGORY']];
	    return categories.map(category => category + '_category');
	  }
	  return ['trigger_category'];
	}
	function _getRobotItemData2(robot) {
	  var _settings$TITLE, _settings$SORT;
	  const settings = main_core.Type.isPlainObject(robot['ROBOT_SETTINGS']) ? robot['ROBOT_SETTINGS'] : {};
	  const title = (_settings$TITLE = settings['TITLE']) != null ? _settings$TITLE : robot['NAME'];
	  const isRestRobot = robot['CATEGORY'] && robot['CATEGORY']['ID'] && robot['CATEGORY']['ID'] === 'rest';
	  return {
	    id: robot['CLASS'],
	    title: title,
	    groupIds: babelHelpers.classPrivateFieldLooseBase(this, _getGroupIds)[_getGroupIds](settings, 'robot'),
	    description: robot['DESCRIPTION'],
	    tags: [main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TAGS_ROBOTS'), main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TAGS_ROBOT')],
	    customData: {
	      robotData: main_core.Runtime.clone(robot),
	      contextGroup: null,
	      type: 'robot',
	      owner: isRestRobot ? 'rest' : 'bitrix24',
	      sort: (_settings$SORT = settings['SORT']) != null ? _settings$SORT : null
	    }
	  };
	}
	function _getRecentRobotIds2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getRecentEntities)[_getRecentEntities]().filter(item => item.entity === 'robot').map(item => item.id);
	}
	function _getTriggerItems2() {
	  const getButtonHandler = triggerData => {
	    return event => {
	      if (triggerData.LOCKED) {
	        if (top.BX.UI && top.BX.UI.InfoHelper && triggerData.LOCKED.INFO_CODE) {
	          top.BX.UI.InfoHelper.show(triggerData.LOCKED.INFO_CODE);
	        }
	        return;
	      }
	      if (!event.getData().eventData.groupIds.includes(this.constructor.RECENT_GROUP_ID)) {
	        event.getData().eventData.groupIds.push(this.constructor.RECENT_GROUP_ID);
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _addToRecentGroup)[_addToRecentGroup]({
	        entity: 'trigger',
	        id: triggerData.CODE
	      });
	      const originalEvent = event.getData().originalEvent;
	      this.emit('triggerSelected', {
	        triggerData,
	        originalEvent,
	        item: event.getData().eventData,
	        stageId: babelHelpers.classPrivateFieldLooseBase(this, _stageId)[_stageId]
	      });
	    };
	  };
	  const availableTriggers = babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].availableTriggers;
	  const recentTriggerIds = babelHelpers.classPrivateFieldLooseBase(this, _getRecentTriggersIds)[_getRecentTriggersIds]();
	  const triggerItems = {};
	  for (const key in availableTriggers) {
	    const trigger = availableTriggers[key];
	    const isRecentTrigger = recentTriggerIds.includes(trigger.CODE);
	    let groupIds = babelHelpers.classPrivateFieldLooseBase(this, _getGroupIds)[_getGroupIds](trigger, 'trigger');
	    if (isRecentTrigger && !groupIds.includes(this.constructor.RECENT_GROUP_ID)) {
	      groupIds.push(RobotSelector.RECENT_GROUP_ID);
	    } else if (!isRecentTrigger) {
	      groupIds = groupIds.filter(id => id !== this.constructor.RECENT_GROUP_ID);
	    }
	    if (trigger.CODE !== 'APP') {
	      triggerItems[trigger.CODE] = {
	        groupIds,
	        id: trigger.CODE,
	        title: trigger.NAME,
	        subtitle: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_ITEM_SUBTITLE_TRIGGER'),
	        description: trigger['DESCRIPTION'],
	        tags: [main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TAGS_TRIGGERS'), main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TAGS_TRIGGER')],
	        customData: {
	          triggerData: {
	            CODE: trigger.CODE
	          },
	          type: 'trigger',
	          owner: 'bitrix24',
	          sort: null
	        },
	        button: {
	          action: getButtonHandler(trigger),
	          locked: !!trigger.LOCKED
	        }
	      };
	      continue;
	    }
	    for (const i in trigger['APP_LIST']) {
	      const item = trigger['APP_LIST'][i];
	      const id = item['CODE'] + '@' + item['APP_ID'] + '@' + i;
	      const itemName = '[' + item['APP_NAME'] + '] ' + item['NAME'];
	      const groupIds = babelHelpers.classPrivateFieldLooseBase(this, _getGroupIds)[_getGroupIds](trigger, 'trigger');
	      if (groupIds.includes(id)) {
	        groupIds.push(RobotSelector.RECENT_GROUP_ID);
	      }
	      triggerItems[id] = {
	        id,
	        title: itemName,
	        subtitle: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_ITEM_SUBTITLE_TRIGGER'),
	        groupIds,
	        description: trigger['DESCRIPTION'],
	        tags: [main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TAGS_TRIGGERS'), main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TAGS_TRIGGER')],
	        customData: {
	          triggerData: {
	            NAME: itemName,
	            CODE: trigger.CODE,
	            APPLY_RULES: {
	              APP_ID: item['APP_ID'],
	              CODE: item['CODE']
	            }
	          },
	          type: 'trigger',
	          owner: 'rest',
	          sort: null
	        },
	        button: {
	          action: getButtonHandler(item)
	        }
	      };
	    }
	  }
	  return triggerItems;
	}
	function _getRecentTriggersIds2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getRecentEntities)[_getRecentEntities]().filter(item => item.entity === 'trigger').map(item => item.id);
	}
	function _addToRecentGroup2(newItem) {
	  let recentGroupItems = babelHelpers.classPrivateFieldLooseBase(this, _getRecentEntities)[_getRecentEntities]().filter(item => item.id !== newItem.id);
	  if (recentGroupItems.length >= RobotSelector.MAX_SIZE_OF_RECENT_GROUP) {
	    recentGroupItems = recentGroupItems.slice(0, RobotSelector.MAX_SIZE_OF_RECENT_GROUP - 1);
	  }
	  recentGroupItems = [newItem, ...recentGroupItems];
	  const entitiesByDocType = babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].remember('recentAutomationEntities', {});
	  entitiesByDocType[babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].document.getRawType()[2]] = recentGroupItems;
	  babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].set('recentAutomationEntities', entitiesByDocType);
	  babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].set('recentAutomationEntities', recentGroupItems);
	  babelHelpers.classPrivateFieldLooseBase(this, _getCatalog)[_getCatalog]().setItems(babelHelpers.classPrivateFieldLooseBase(this, _getItems)[_getItems]());
	}
	function _getSlots2() {
	  return {
	    [ui_entityCatalog.EntityCatalog.SLOT_GROUP_LIST_HEADER]: babelHelpers.classPrivateFieldLooseBase(this, _getGroupsHeader)[_getGroupsHeader](),
	    [ui_entityCatalog.EntityCatalog.SLOT_MAIN_CONTENT_HEADER]: babelHelpers.classPrivateFieldLooseBase(this, _getItemsHeader)[_getItemsHeader](),
	    [ui_entityCatalog.EntityCatalog.SLOT_MAIN_CONTENT_WELCOME_STUB]: babelHelpers.classPrivateFieldLooseBase(this, _getItemsStub)[_getItemsStub](),
	    [ui_entityCatalog.EntityCatalog.SLOT_GROUP_LIST_FOOTER]: babelHelpers.classPrivateFieldLooseBase(this, _getGroupsFooter)[_getGroupsFooter](),
	    [ui_entityCatalog.EntityCatalog.SLOT_MAIN_CONTENT_SEARCH_NOT_FOUND]: babelHelpers.classPrivateFieldLooseBase(this, _getSearchNotFoundStub)[_getSearchNotFoundStub](),
	    [ui_entityCatalog.EntityCatalog.SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB]: `<EmptyGroupStub/>`,
	    [ui_entityCatalog.EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB_TITLE]: main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_EMPTY_GROUP_STUB_TITLE')
	  };
	}
	function _getGroupsHeader2() {
	  return `
			<div class="bizproc-creating-robot__head">
				<div class="bizproc-creating-robot__head_title">
					<div class="bizproc-creating-robot__head_name">${main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUPS_HEADER_TITLE')}</div>
					<Hint text="${main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUPS_HEADER_TITLE_HINT')}"/>
				</div>
			</div>
		`;
	}
	function _getItemsHeader2() {
	  const helpFeedbackParams = {
	    id: String(Math.random()),
	    portalUri: 'https://bitrix24.team',
	    forms: [{
	      zones: ['ru'],
	      id: 1922,
	      lang: 'ru',
	      sec: 'frsxzd'
	    }, {
	      zones: ['kz'],
	      id: 1923,
	      lang: 'ru',
	      sec: 'skbmjc'
	    }, {
	      zones: ['by'],
	      id: 1931,
	      lang: 'ru',
	      sec: 'om1f4c'
	    }, {
	      zones: ['en'],
	      id: 1937,
	      lang: 'en',
	      sec: 'yu3ljc'
	    }, {
	      zones: ['la', 'co', 'mx'],
	      id: 1947,
	      lang: 'es',
	      sec: 'wuezi9'
	    }, {
	      zones: ['br'],
	      id: 1948,
	      lang: 'br',
	      sec: 'j5gglp'
	    }, {
	      zones: ['de'],
	      id: 1946,
	      lang: 'de',
	      sec: '6tpoy4'
	    }]
	  };
	  return `
			<div class="bizproc-creating-robot__head_title">
				<div class="bizproc-creating-robot__head_name">${main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_ITEMS_HEADER_TITLE_1')}</div>
				<Hint text="${main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_ITEMS_HEADER_TITLE_HINT_1')}"/>
				<a class="bizproc-creating-robot__help-link" v-feedback="${main_core.Text.encode(JSON.stringify(helpFeedbackParams))}" href="javascipt:none">
					${main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_HELP_SET_UP_AUTOMATION')}
				</a>
			</div>
		`;
	}
	function _getItemsStub2() {
	  return `
			<div class="bizproc-creating-robot__content --help-block --select-grouping">
				<div class="bizproc-creating-robot__empty-content">
					<div class="bizproc-creating-robot__empty-content_icon">
						<img src="/bitrix/js/bizproc/automation/robot-selector/images/bizproc-creating-robot--select-grouping.svg" alt="">
					</div>
					<div class="bizproc-creating-robot__empty-content_text">
						${main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_ITEM_LIST_STUB_TITLE')}
					</div>
				</div>
			</div>
		`;
	}
	function _getSearchNotFoundStub2() {
	  var _Text$encode, _Text$encode2;
	  const title = (_Text$encode = main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_SEARCH_NOT_FOUND_TITLE'))) != null ? _Text$encode : '';
	  let msg = (_Text$encode2 = main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_SEARCH_NOT_FOUND'))) != null ? _Text$encode2 : '';
	  const feedbackParams = {
	    id: Math.random() + '',
	    portalUri: 'https://product-feedback.bitrix24.com/',
	    forms: [{
	      zones: ['by', 'kz', 'ru'],
	      id: 438,
	      lang: 'ru',
	      sec: 'odyyl1'
	    }, {
	      zones: ['com.br'],
	      id: 436,
	      lang: 'br',
	      sec: '8fb4et'
	    }, {
	      zones: ['la', 'co', 'mx'],
	      id: 434,
	      lang: 'es',
	      sec: 'ze9mqq'
	    }, {
	      zones: ['de'],
	      id: 432,
	      lang: 'de',
	      sec: 'm8isto'
	    }, {
	      zones: ['en', 'eu', 'in', 'uk'],
	      id: 430,
	      lang: 'en',
	      sec: 'etg2n4'
	    }]
	  };
	  msg = msg.replace('#A1#', `<a v-feedback="${main_core.Text.encode(JSON.stringify(feedbackParams))}" href="#feedback">`).replace('#A2#', '</a>');
	  return `<b>${title}</b><br/>${msg}`;
	}
	function _getGroupsFooter2() {
	  const url = '/marketplace/category/%category%/'.replace('%category%', babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].get('marketplaceRobotCategory'));
	  return `
			<a class="bizproc-creating-robot__menu-market" href="${url}">
				<div class='bizproc-creating-robot__menu_item-market'>
					<span class="bizproc-creating-robot__menu_item-icon-market">
						${GroupIcon.COMMERCE}
					</span>
					<span class="bizproc-creating-robot__menu_item-text-market">${main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_MARKETPLACE')}</span>
				</div>
			</a>
		`;
	}
	function _getTitleBar2() {
	  var _babelHelpers$classPr;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].document.statusList.length <= 1) {
	    return main_core.Tag.render(_t || (_t = _`
				<div>
					${0}
				</div>
			`), main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_POPUP_TITLE_1'));
	  }
	  return main_core.Tag.render(_t2 || (_t2 = _`
			<div>
				${0}
			</div>
			<div class="bizproc-creating-robot__titlebar_subtitle">
				${0}
			</div>
			${0}
		`), main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_POPUP_TITLE_1'), main_core.Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TITLEBAR_SUBTITLE'), (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _getTitleBarStageBlock)[_getTitleBarStageBlock]()) != null ? _babelHelpers$classPr : '');
	}
	function _getTitleBarStageBlock2() {
	  const statusList = babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].document.statusList;
	  for (const key in statusList) {
	    if (String(statusList[key]['STATUS_ID']) === String(babelHelpers.classPrivateFieldLooseBase(this, _stageId)[_stageId])) {
	      var _statusList$key$NAME;
	      const currentStageColor = babelHelpers.classPrivateFieldLooseBase(this.constructor, _getColor)[_getColor](statusList[key]['COLOR']);
	      const currentStageName = (_statusList$key$NAME = statusList[key]['NAME']) != null ? _statusList$key$NAME : statusList[key]['TITLE'];
	      const stageBlock = main_core.Tag.render(_t3 || (_t3 = _`
					<div class="bizproc-creating-robot__stage-block" data-role="bp-robot-selector-stage-block">
						<div class="bizproc-creating-robot__stage-block_title">
							${0}
							<div class="bizproc-creating-robot__stage-block_title-block">
								<span
									class="bizproc-creating-robot__stage-block_title-text"
									data-role="bp-robot-selector-stage-block-title"
								>
									${0}
								</span>
							</div>
						</div>
						<div class="bizproc-creating-robot__stage-block_bg">
							<span class="bizproc-creating-robot__stage-block_bg-arrow"></span>
						</div>
					</div>
				`), babelHelpers.classPrivateFieldLooseBase(this, _createTitleBarStageBlockColor)[_createTitleBarStageBlockColor](currentStageColor, {
	        width: 13,
	        height: 12
	      }), main_core.Text.encode(currentStageName));
	      main_core.Event.bind(stageBlock, 'click', babelHelpers.classPrivateFieldLooseBase(this, _onTitleBarStageBlockClick)[_onTitleBarStageBlockClick].bind(this));
	      return stageBlock;
	    }
	  }
	  return null;
	}
	function _onTitleBarStageBlockClick2(event) {
	  const statusList = babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].document.statusList;
	  const stageMenuItems = [];
	  for (const key in statusList) {
	    var _statusList$key$NAME2;
	    stageMenuItems.push({
	      html: `
					<div class="bizproc-creating-robot__stage-block_title-menu">
						${babelHelpers.classPrivateFieldLooseBase(this, _createTitleBarStageBlockColor)[_createTitleBarStageBlockColor](statusList[key]['COLOR'], {
        width: 14,
        height: 12
      })}
						<span class="bizproc-creating-robot__stage-block_title-text-menu">
							${main_core.Text.encode((_statusList$key$NAME2 = statusList[key]['NAME']) != null ? _statusList$key$NAME2 : statusList[key]['TITLE'])}
						</span>
						${String(statusList[key]['STATUS_ID']) === String(babelHelpers.classPrivateFieldLooseBase(this, _stageId)[_stageId]) ? `<div class="bizproc-creating-robot__stage-block_selected"></div>` : ''}
					</div>
				`,
	      onclick: (event, item) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _stageId)[_stageId] = statusList[key]['STATUS_ID'];
	        babelHelpers.classPrivateFieldLooseBase(this, _onStageIdChanged)[_onStageIdChanged]();
	        item.getMenuWindow().close();
	      }
	    });
	  }
	  main_popup.MenuManager.create({
	    id: 'bizproc-automation-robot-selector-menu-stages',
	    bindElement: event.target,
	    items: stageMenuItems,
	    minWidth: 228,
	    autoHide: true,
	    contentColor: 'white',
	    draggable: false,
	    cacheable: false
	  }).show();
	}
	function _onStageIdChanged2() {
	  const status = babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].document.statusList.find(status => {
	    return String(status.STATUS_ID) === String(babelHelpers.classPrivateFieldLooseBase(this, _stageId)[_stageId]);
	  });
	  const stageBlock = babelHelpers.classPrivateFieldLooseBase(this, _getCatalog)[_getCatalog]().getPopup().getTitleContainer().querySelector('[data-role="bp-robot-selector-stage-block"]');
	  if (!stageBlock) {
	    return;
	  }
	  const stageColorBlock = stageBlock.querySelector('[data-role="bp-robot-selector-stage-block-color-block"]');
	  if (stageColorBlock) {
	    main_core.Dom.replace(stageColorBlock, main_core.Tag.render(_t4 || (_t4 = _`${0}`), babelHelpers.classPrivateFieldLooseBase(this, _createTitleBarStageBlockColor)[_createTitleBarStageBlockColor](status['COLOR'])));
	  }
	  const stageBlockTitle = stageBlock.querySelector('[data-role="bp-robot-selector-stage-block-title"]');
	  if (stageBlockTitle) {
	    var _status$NAME;
	    stageBlockTitle.innerText = (_status$NAME = status['NAME']) != null ? _status$NAME : status['TITLE'];
	  }
	}
	function _createTitleBarStageBlockColor2(color, size = {
	  width: 13,
	  height: 12
	}) {
	  color = babelHelpers.classPrivateFieldLooseBase(this.constructor, _getColor)[_getColor](color);
	  return `
			<svg 
				class="bizproc-creating-robot__stage-block_color"
				width="${size.width}"
				height="${size.height}"
				viewBox="0 0 13 12"
				fill="none"
				xmlns="http://www.w3.org/2000/svg"
				data-role="bp-robot-selector-stage-block-color-block"
			>
				<path 
					fill="${color}"
					d="M0 2.25C0 1.00736 1.02835 0 2.29689 0H8.68575C9.25708 0 9.80141 0.20818 10.2184 0.574156C10.465 0.790543 10.6254 1.08387 10.7737 1.37649L12.6727 5.12357C12.7468 5.26988 12.8412 5.40624 12.9071 5.55648C13.031 5.83933 13.031 6.16066 12.9071 6.44352C12.8412 6.59376 12.7468 6.73012 12.6727 6.87643L10.7737 10.6235C10.6254 10.9161 10.465 11.2095 10.2184 11.4258C9.80141 11.7918 9.25708 12 8.68575 12L2.29689 12C1.02835 12 0 10.9926 0 9.75V2.25Z"
				/>
			</svg>
		`;
	}
	function _getFilterOptions2() {
	  return {
	    filterItems: [new B24Robots().getData(), new B24Triggers().getData()],
	    multiple: false
	  };
	}
	function _getPopupOptions2() {
	  return {
	    overlay: {
	      backgroundColor: 'transparent'
	    },
	    events: {
	      onPopupAfterClose: () => {
	        this.emit('onAfterClose');
	      }
	    },
	    zIndexOptions: {
	      alwaysOnTop: false
	    }
	  };
	}
	function _sortItems2(item1, item2) {
	  const sortItem1 = item1.customData.sort;
	  const sortItem2 = item2.customData.sort;
	  return sortItem1 && sortItem2 ? sortItem1 - sortItem2 : main_core.Text.toNumber(sortItem2) - main_core.Text.toNumber(sortItem1);
	}
	function _oldSortItems2(item1, item2) {
	  var _item1$customData$rob, _item2$customData$rob;
	  const sortItem1 = (_item1$customData$rob = item1.customData.robotData) == null ? void 0 : _item1$customData$rob.SORT;
	  const sortItem2 = (_item2$customData$rob = item2.customData.robotData) == null ? void 0 : _item2$customData$rob.SORT;
	  return sortItem1 && sortItem2 ? sortItem1 - sortItem2 : main_core.Text.toNumber(sortItem2) - main_core.Text.toNumber(sortItem1);
	}
	function _getColor2(color) {
	  if (main_core.Type.isStringFilled(color)) {
	    return color.startsWith('#') ? color : '#' + color;
	  }
	  return '#ACF2FA';
	}
	Object.defineProperty(RobotSelector, _getColor, {
	  value: _getColor2
	});
	RobotSelector.RECENT_GROUP_ID = 'recent';
	RobotSelector.DEFAULT_GROUP_NAME = 'other';
	RobotSelector.MAX_SIZE_OF_RECENT_GROUP = 10;

	exports.RobotSelector = RobotSelector;

}((this.BX.Bizproc.Automation = this.BX.Bizproc.Automation || {}),BX.Event,BX.Main,BX.Bizproc.Automation,BX.Bizproc.LocalSettings,BX,BX.UI,BX.Vue3.Pinia));
//# sourceMappingURL=robot-selector.bundle.js.map
