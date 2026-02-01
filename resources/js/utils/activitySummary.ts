import { formatEnum, truncate } from './format'

export function activitySummary(a: any) {
    const entity = formatEnum(a.entity_type)
    const after = a.after || {}
    const before = a.before || {}

    const title =
        after.title || before.title ||
        after.name || before.name ||
        (a.entity_id ? `${entity} #${a.entity_id}` : entity)

    if (a.action === 'status_changed') {
        const from = before.status ? formatEnum(before.status) : '—'
        const to = after.status ? formatEnum(after.status) : '—'
        return `${entity}: Status changed ${from} → ${to}`
    }

    return `${entity}: ${formatEnum(a.action)} — ${truncate(String(title), 80)}`
}
