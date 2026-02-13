export function safeString(v: any, fallback = '—') {
    if (v === null || v === undefined) return fallback
    const s = String(v).trim()
    return s.length ? s : fallback
}

export function formatEnum(v: any) {
    const s = safeString(v, '')
    if (!s) return '—'
    return s
        .replace(/_/g, ' ')
        .replace(/-/g, ' ')
        .replace(/\b\w/g, (m) => m.toUpperCase())
}

export function formatDate(v: any) {
    const s = safeString(v, '')
    if (!s) return '—'
    const d = new Date(s)
    if (Number.isNaN(d.getTime())) return '—'
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

export function formatDateTime(v: any) {
    const s = safeString(v, '')
    if (!s) return '—'
    const d = new Date(s)
    if (Number.isNaN(d.getTime())) return '—'
    return d.toLocaleString(undefined, {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    })
}

export function formatMoneyKobo(kobo: number | string, currency = 'NGN') {
    const n = typeof kobo === 'string' ? Number(kobo) : kobo
    if (!Number.isFinite(n as number)) return '—'
    const value = (n as number) / 100
    return new Intl.NumberFormat(undefined, { style: 'currency', currency }).format(value)
}

export function formatNumber(v: any) {
    const n = Number(v)
    if (!Number.isFinite(n)) return '—'
    return new Intl.NumberFormat().format(n)
}

export function truncate(v: any, len = 80) {
    const s = safeString(v, '')
    if (!s) return '—'
    return s.length > len ? s.slice(0, len - 1) + '…' : s
}

export function formatCurrency(v: number | string, currency = 'NGN') {
    const n = typeof v === 'string' ? Number(v) : v
    if (!Number.isFinite(n as number)) return '—'
    return new Intl.NumberFormat(undefined, { style: 'currency', currency }).format(n as number)
}

export function timeAgo(date: string | Date) {
    const d = new Date(date)
    if (Number.isNaN(d.getTime())) return '—'
    const seconds = Math.floor((new Date().getTime() - d.getTime()) / 1000)

    let interval = seconds / 31536000
    if (interval > 1) return Math.floor(interval) + " yr ago"
    interval = seconds / 2592000
    if (interval > 1) return Math.floor(interval) + " mo ago"
    interval = seconds / 86400
    if (interval > 1) return Math.floor(interval) + " days ago"
    interval = seconds / 3600
    if (interval > 1) return Math.floor(interval) + " hr ago"
    interval = seconds / 60
    if (interval > 1) return Math.floor(interval) + " min ago"
    return "Just now"
}
