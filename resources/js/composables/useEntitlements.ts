import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useEntitlements() {
    const page = usePage<any>()

    const entitlements = computed(() => page.props.entitlements || null)
    const can = computed(() => entitlements.value?.can || {})
    const plan = computed(() => entitlements.value?.plan || null)
    const usage = computed(() => entitlements.value?.usage || null)
    const limits = computed(() => entitlements.value?.limits || null)
    const org = computed(() => page.props.auth?.organization || null)

    return { entitlements, can, plan, usage, limits, org }
}
