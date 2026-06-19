import { usePage } from '@inertiajs/react';

/**
 * Permission-driven authorization for the UI.
 *
 * The backend shares a flat list of the current user's permission names
 * (HandleInertiaRequests). Components gate on permissions — never on role
 * names — so the rules stay in one place (the seeder) and the React side
 * just asks "can I?".
 *
 * @example
 *   const { can } = usePermissions();
 *   {can('quotations.approve') && <ApproveButton />}
 */
export function usePermissions() {
    const { auth } = usePage().props;
    const permissions = auth?.permissions ?? [];
    const roles = auth?.roles ?? [];

    /** True if the user has at least one of the given permission(s). */
    const can = (permission: string | string[]): boolean => {
        const wanted = Array.isArray(permission) ? permission : [permission];

        return wanted.some((p) => permissions.includes(p));
    };

    /** True only if the user has every one of the given permissions. */
    const canAll = (wanted: string[]): boolean =>
        wanted.every((p) => permissions.includes(p));

    /** Escape hatch for the rare role-specific branch (prefer can()). */
    const hasRole = (role: string): boolean => roles.includes(role);

    return { can, canAll, hasRole, permissions, roles };
}
