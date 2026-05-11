/** Alinhado com `router/index.ts` — rotas só de convidado ou 403. */
export const AUTH_PUBLIC_PATHS = new Set(['/login', '/forgot-password', '/reset-password'])

/** Rotas com `meta.skipPermissionCheck` (alinhar com definições nas rotas). */
export const ROUTE_PATHS_SKIP_PERMISSION = new Set(['/profile/security'])

/**
 * Ordem de preferência para "início" — primeiro caminho onde o utilizador tem acesso.
 * Não é rota fixa: depende das permissões vindas de `/api/v1/me`.
 */
type HomeRouteCandidate = {
  path: string
  permission: string
}

export function normalizePath(fullPath: string): string {
  const path = fullPath.split('?')[0] ?? ''
  if (path === '' || path === '/') {
    return '/'
  }
  return path.replace(/\/+$/, '') || '/'
}

export const HOME_ROUTE_CANDIDATES: readonly HomeRouteCandidate[] = [
  { path: '/clients', permission: 'entities.read' },
  { path: '/suppliers', permission: 'entities.read' },
  { path: '/contacts', permission: 'contacts.read' },
  { path: '/proposals', permission: 'proposals.read' },
  { path: '/calendar', permission: 'calendar-events.read' },
  { path: '/client-orders', permission: 'client-orders.read' },
  { path: '/supplier-orders', permission: 'supplier-orders.read' },
  { path: '/work-orders', permission: 'work-orders.read' },
  { path: '/supplier-invoices', permission: 'supplier-invoices.read' },
  { path: '/bank-accounts', permission: 'bank-accounts.read' },
  { path: '/customer-accounts', permission: 'customer-accounts.read' },
  { path: '/digital-archive', permission: 'digital-files.read' },
  { path: '/users', permission: 'users.read' },
  { path: '/permissions', permission: 'roles.read' },
  { path: '/settings/countries', permission: 'countries.read' },
  { path: '/settings/contact-functions', permission: 'contact-functions.read' },
  { path: '/settings/calendar-types', permission: 'calendar-types.read' },
  { path: '/settings/calendar-actions', permission: 'calendar-actions.read' },
  { path: '/settings/articles', permission: 'articles.read' },
  { path: '/settings/vat', permission: 'vat.read' },
  { path: '/settings/logs', permission: 'logs.read' },
  { path: '/settings/company', permission: 'company.read' },
  { path: '/company', permission: 'company.read' },
]

/**
 * Indica se o utilizador pode abrir este caminho na app autenticada com base
 * em mapeamento explícito rota -> permissão (sem parser de path).
 */
export function isPathAccessibleForUser(fullPath: string, permissions: readonly string[]): boolean {
  const path = normalizePath(fullPath.split('?')[0] ?? '')

  if (path === '/403' || path.startsWith('/403/')) {
    return false
  }

  if (AUTH_PUBLIC_PATHS.has(path)) {
    return false
  }

  if (ROUTE_PATHS_SKIP_PERMISSION.has(path)) {
    return true
  }

  const candidate = HOME_ROUTE_CANDIDATES.find((item) => normalizePath(item.path) === path)
  return Boolean(candidate && permissions.includes(candidate.permission))
}

/**
 * Primeiro destino da lista {@link HOME_ROUTE_CANDIDATES} acessível com as permissões dadas;
 * caso contrário perfil (sempre permitido para sessão autenticada).
 */
export function resolveHomeByPermission(permissions: readonly string[]): string {
  for (const candidate of HOME_ROUTE_CANDIDATES) {
    if (isPathAccessibleForUser(candidate.path, permissions)) {
      return candidate.path
    }
  }

  return '/profile/security'
}

/**
 * Destino seguro para o botão "Voltar" na página 403: só reutiliza `from` se for acessível;
 * caso contrário o mesmo valor que {@link resolveHomeByPermission} (evita loop 403 ↔ rota negada).
 */
export function resolveSafeBackPath(fromQuery: unknown, permissions: readonly string[]): string {
  const raw = typeof fromQuery === 'string' ? fromQuery.trim() : ''
  if (raw === '' || !raw.startsWith('/') || raw.startsWith('//')) {
    return resolveHomeByPermission(permissions)
  }

  const pathOnly = normalizePath(raw.split('?')[0] ?? '')

  if (isPathAccessibleForUser(pathOnly, permissions)) {
    return pathOnly
  }

  return resolveHomeByPermission(permissions)
}
