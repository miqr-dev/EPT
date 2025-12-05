import { onMounted, ref } from 'vue';

/**
 * @fileoverview Composable for managing theme appearance (light/dark/system).
 */

/**
 * Represents the possible appearance settings.
 */
type Appearance = 'light' | 'dark' | 'system';

/**
 * Updates the theme of the application based on the provided appearance value.
 * @param {Appearance} value - The desired appearance ('light', 'dark', or 'system').
 */
export function updateTheme(value: Appearance) {
    if (typeof window === 'undefined') {
        return;
    }

    if (value === 'system') {
        const mediaQueryList = window.matchMedia('(prefers-color-scheme: dark)');
        const systemTheme = mediaQueryList.matches ? 'dark' : 'light';

        document.documentElement.classList.toggle('dark', systemTheme === 'dark');
    } else {
        document.documentElement.classList.toggle('dark', value === 'dark');
    }
}

/**
 * Sets a cookie in the browser.
 * @param {string} name - The name of the cookie.
 * @param {string} value - The value of the cookie.
 * @param {number} [days=365] - The number of days until the cookie expires.
 */
const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

/**
 * Returns a media query list for detecting the user's preferred color scheme.
 * @returns {MediaQueryList | null} The media query list, or null if not in a browser environment.
 */
const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

/**
 * Retrieves the stored appearance setting from local storage.
 * @returns {Appearance | null} The stored appearance, or null if not set or not in a browser.
 */
const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as Appearance | null;
};

/**
 * Handles changes to the system's theme preference.
 */
const handleSystemThemeChange = () => {
    const currentAppearance = getStoredAppearance();

    updateTheme(currentAppearance || 'system');
};

/**
 * Initializes the theme based on stored preferences or system settings.
 */
export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Initialize theme from saved preference or default to system...
    const savedAppearance = getStoredAppearance();
    updateTheme(savedAppearance || 'system');

    // Set up system theme change listener...
    mediaQuery()?.addEventListener('change', handleSystemThemeChange);
}

/**
 * A ref to hold the current appearance value.
 * @type {import('vue').Ref<Appearance>}
 */
const appearance = ref<Appearance>('system');

/**
 * A composable function for managing the application's theme appearance.
 * @returns {{
 *   appearance: import('vue').Ref<Appearance>,
 *   updateAppearance: (value: Appearance) => void
 * }} An object containing the current appearance and a function to update it.
 */
export function useAppearance() {
    onMounted(() => {
        const savedAppearance = localStorage.getItem('appearance') as Appearance | null;

        if (savedAppearance) {
            appearance.value = savedAppearance;
        }
    });

    /**
     * Updates the current appearance setting.
     * @param {Appearance} value - The new appearance value.
     */
    function updateAppearance(value: Appearance) {
        appearance.value = value;

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', value);

        // Store in cookie for SSR...
        setCookie('appearance', value);

        updateTheme(value);
    }

    return {
        appearance,
        updateAppearance,
    };
}
