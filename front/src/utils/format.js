export function formatPrice(value) {
  const amount = Number(value ?? 0)
  return `${new Intl.NumberFormat('fa-IR').format(amount)} تومان`
}

export function formatDate(value) {
  if (!value) return '—'
  try {
    return new Intl.DateTimeFormat('fa-IR', {
      dateStyle: 'medium',
      timeStyle: 'short',
    }).format(new Date(value))
  } catch {
    return value
  }
}

export function slugify(text) {
  return String(text || '')
    .trim()
    .toLowerCase()
    .replace(/\s+/g, '-')
    .replace(/[^a-z0-9\u0600-\u06FF-]/g, '')
    .replace(/-+/g, '-')
}

export function unwrap(payload) {
  if (payload == null) return payload
  if (Array.isArray(payload)) return payload
  if (payload.data !== undefined && (payload.success === true || payload.success === false || payload.meta || payload.links)) {
    return unwrap(payload.data)
  }
  if (payload.data && typeof payload.data === 'object' && !Array.isArray(payload.data) && payload.data.id && Object.keys(payload).length <= 2) {
    return payload.data
  }
  return payload
}

export function unwrapList(payload) {
  const root = payload?.data !== undefined ? payload : { data: payload }
  const data = Array.isArray(root.data) ? root.data : unwrap(root.data)
  return {
    items: Array.isArray(data) ? data.map((item) => unwrap(item) ?? item) : [],
    meta: root.meta ?? payload?.meta ?? null,
    links: root.links ?? payload?.links ?? null,
    message: payload?.message,
  }
}

export function validationMessage(error) {
  const res = error?.response?.data
  if (res?.errors && typeof res.errors === 'object') {
    const first = Object.values(res.errors).flat()[0]
    if (first) return String(first)
  }
  return res?.message || error?.message || 'خطای غیرمنتظره‌ای رخ داد'
}

export function createIdempotencyKey() {
  if (typeof crypto !== 'undefined' && crypto.randomUUID) return crypto.randomUUID()
  return `chk_${Date.now()}_${Math.random().toString(16).slice(2)}`
}
