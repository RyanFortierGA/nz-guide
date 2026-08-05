/** Inline travel-mode icons — always sized so they can't blow up cards/pins. */
const attrs = 'width="12" height="12" viewBox="0 0 24 24" aria-hidden="true" focusable="false" style="width:12px;height:12px;flex-shrink:0;display:block"';

export const ICONS = {
    plane: `<svg ${attrs} fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22 11 13 2 9 22 2Z"/></svg>`,
    car: `<svg ${attrs} fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 13l2-6h14l2 6"/><rect x="2" y="13" width="20" height="5" rx="1"/><circle cx="7" cy="18.5" r="1.5"/><circle cx="17" cy="18.5" r="1.5"/></svg>`,
    ferry: `<svg ${attrs} fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15h16l-2 5H6l-2-5Z"/><path d="M7 15V7h7l2.5 8"/><path d="M2 20.5c1.4.9 2.8.9 4.2 0 1.4-.9 2.8-.9 4.2 0 1.4.9 2.8.9 4.2 0 1.4-.9 2.8-.9 4.2 0"/></svg>`,
    walk: `<svg ${attrs} fill="currentColor" stroke="none"><circle cx="13.5" cy="4" r="2"/><path d="M14.6 7.2c-.6-.3-1.3-.4-2-.3l-3 .5c-.8.1-1.4.7-1.6 1.5l-1 4 1.9.5 1-3.8.9-.2-1.5 5.6-3.6 4.9 1.6 1.2 3.7-5 1-1.6.7 2.7-.9 4.4 2 .4.9-4.6c.1-.5 0-1-.2-1.5l-1.1-2.3.4-1.6 1 1.4c.3.4.7.7 1.2.8l2.6.7.5-1.9-2.4-.6-1.7-2.4c-.3-.4-.7-.7-1.1-.9Z"/></svg>`,
};
