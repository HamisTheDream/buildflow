<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage<any>()
const admin = page.props.ownerAuth?.admin
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <header class="border-b bg-white">
      <div class="mx-auto max-w-6xl px-4 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-xl bg-indigo-600"></div>
          <div>
            <div class="text-sm font-semibold text-gray-900">BuildFlow Owner</div>
            <div class="text-xs text-gray-500">ERP / CRM Console</div>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <div class="text-sm text-gray-700" v-if="admin">
            {{ admin.name }}
          </div>

          <form method="post" action="/owner/logout">
            <input type="hidden" name="_token" :value="page.props.csrf_token" />
            <button class="rounded-lg bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200">
              Logout
            </button>
          </form>
        </div>
      </div>

      <nav class="bg-white">
        <div class="mx-auto max-w-6xl px-4 py-3 flex gap-2 text-sm">
          <Link href="/owner/dashboard" class="rounded-lg px-3 py-2 hover:bg-gray-100">Dashboard</Link>
          <Link href="/owner/organizations" class="rounded-lg px-3 py-2 hover:bg-gray-100">Organizations</Link>
          <Link href="/owner/billing/payments" class="rounded-lg px-3 py-2 hover:bg-gray-100">Billing</Link>
          <Link href="/owner/announcements" class="rounded-lg px-3 py-2 hover:bg-gray-100">Announcements</Link>
          <Link href="/owner/support" class="rounded-lg px-3 py-2 hover:bg-gray-100">Support</Link>
          <Link href="/owner/admins" class="rounded-lg px-3 py-2 hover:bg-gray-100">Admins</Link>
        </div>
      </nav>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
      <slot />
    </main>
  </div>
</template>
