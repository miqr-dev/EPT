/**
 * Generates initials from a full name.
 *
 * @param {string} [fullName] - The full name to generate initials from.
 * @returns {string} The generated initials.
 */
export function getInitials(fullName?: string): string {
    if (!fullName) return '';

    const names = fullName.trim().split(' ');

    if (names.length === 0) return '';
    if (names.length === 1) return names[0].charAt(0).toUpperCase();

    return `${names[0].charAt(0)}${names[names.length - 1].charAt(0)}`.toUpperCase();
}

/**
 * A composable function that provides the `getInitials` utility.
 *
 * @returns {{ getInitials: (fullName?: string) => string }} An object containing the `getInitials` function.
 */
export function useInitials() {
    return { getInitials };
}
