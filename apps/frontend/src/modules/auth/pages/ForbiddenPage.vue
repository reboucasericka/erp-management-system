<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

import { fetchAuthenticatedUser, peekAuthenticatedUser } from '@/modules/auth/services/authService'
import {
  normalizePath,
  resolveHomeByPermission,
  resolveSafeBackPath,
} from '@/router/resolveHomeByPermission'

const route = useRoute()

/** Permissões do `/api/v1/me` (cache ou refresh pontual). */
const permissions = ref<string[]>([])

onMounted(async () => {
  const cached = peekAuthenticatedUser()
  if (cached) {
    permissions.value = cached.permissions ?? []
    return
  }
  const user = await fetchAuthenticatedUser()
  permissions.value = user?.permissions ?? []
})

const safeHomePath = computed(() => resolveHomeByPermission(permissions.value))

const backPath = computed(() => resolveSafeBackPath(route.query.from, permissions.value))

/** Evita dois botões para o mesmo destino ou “voltar” para rota insegura (já tratado em resolveSafeBackPath). */
const showBackLink = computed(() => normalizePath(backPath.value) !== normalizePath(safeHomePath.value))
</script>

<template>
  <div class="forbidden">
    <h1>403 — Acesso negado</h1>
    <p>Não tem permissão para aceder a esta página.</p>
    <div class="actions">
      <RouterLink v-if="showBackLink" class="link" :to="backPath">Voltar à página anterior</RouterLink>
      <RouterLink class="link" :to="safeHomePath">Ir para o início</RouterLink>
    </div>
  </div>
</template>

<style scoped>
.forbidden {
  min-height: 100svh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1.5rem;
  text-align: center;
  background: hsl(var(--background, 210 40% 98%));
  color: hsl(var(--foreground, 222 47% 11%));
}

h1 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

p {
  margin: 0;
  color: hsl(var(--muted-foreground, 215 16% 47%));
}

.actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.link {
  color: hsl(var(--primary, 221 83% 53%));
  text-decoration: underline;
}
</style>
