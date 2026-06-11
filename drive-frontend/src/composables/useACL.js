import { useAuthStore } from '../stores/auth';

export function useACL() {
  const authStore = useAuthStore();

  const hasRole = (roleName) => {
    if (!authStore.user) return false;
    // Global Developer Override
    if (authStore.user.roles && authStore.user.roles.includes('Developer')) return true;
    if (authStore.user.role && authStore.user.role.toLowerCase() === 'developer') return true;

    if (authStore.user.role && authStore.user.role.toLowerCase() === roleName.toLowerCase()) return true;
    return authStore.user.roles && authStore.user.roles.includes(roleName);
  };

  const hasPermission = (permissionName) => {
    if (!authStore.user) return false;
    // Global Developer Override
    if (authStore.user.roles && authStore.user.roles.includes('Developer')) return true;
    if (authStore.user.role && authStore.user.role.toLowerCase() === 'developer') return true;

    return authStore.user.permissions && authStore.user.permissions.includes(permissionName);
  };

  const hasAnyPermission = (permissions) => {
    if (!authStore.user) return false;
    // Global Developer Override
    if (authStore.user.roles && authStore.user.roles.includes('Developer')) return true;
    if (authStore.user.role && authStore.user.role.toLowerCase() === 'developer') return true;

    if (!authStore.user.permissions) return false;
    return permissions.some(perm => authStore.user.permissions.includes(perm));
  };

  return {
    hasRole,
    hasPermission,
    hasAnyPermission
  };
}
