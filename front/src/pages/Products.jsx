import { useEffect, useMemo, useState } from 'react'
import { catalogApi, unwrap, unwrapList } from '../api/client'
import { useAuth } from '../context/AuthContext'
import { useCart } from '../context/CartContext'
import { useToast } from '../context/ToastContext'
import Input from '../components/ui/Input'
import ProductCard from '../components/ui/ProductCard'
import { ProductSkeleton } from '../components/ui/Skeleton'
import Button from '../components/ui/Button'

export default function Products() {
  const { user } = useAuth()
  const { addItem } = useCart()
  const toast = useToast()
  const [q, setQ] = useState('')
  const [categoryId, setCategoryId] = useState('')
  const [page, setPage] = useState(1)
  const [items, setItems] = useState([])
  const [meta, setMeta] = useState(null)
  const [categories, setCategories] = useState([])
  const [loading, setLoading] = useState(true)
  const [adding, setAdding] = useState(null)

  useEffect(() => {
    catalogApi.categories()
      .then((payload) => {
        const list = unwrapList(payload).items
        setCategories(list.length ? list : unwrap(payload) || [])
      })
      .catch(() => setCategories([]))
  }, [])

  useEffect(() => {
    let cancelled = false
    setLoading(true)
    const params = { page, per_page: 12 }
    if (q.trim().length >= 2) params.q = q.trim()
    if (categoryId) params.category_id = categoryId

    catalogApi
      .items(params)
      .then((payload) => {
        if (cancelled) return
        const { items: list, meta: nextMeta } = unwrapList(payload)
        setItems(list)
        setMeta(nextMeta)
      })
      .catch((err) => {
        if (!cancelled) {
          setItems([])
          toast.error(err.message)
        }
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [q, categoryId, page, toast])

  const flatCategories = useMemo(() => {
    const out = []
    for (const cat of categories) {
      out.push(cat)
      if (Array.isArray(cat.children)) out.push(...cat.children)
    }
    return out
  }, [categories])

  async function handleAdd(item) {
    if (!user) {
      toast.info('برای افزودن به سبد وارد شوید')
      return
    }
    setAdding(item.id)
    try {
      await addItem(item, 1)
    } catch (err) {
      toast.error(err.message)
    } finally {
      setAdding(null)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">محصولات</h1>
          <p className="text-sm text-slate-500">جستجو و فیلتر بر اساس دسته</p>
        </div>
        <div className="flex w-full flex-col gap-3 sm:max-w-xl sm:flex-row">
          <Input
            placeholder="جستجو (حداقل ۲ حرف)"
            value={q}
            onChange={(e) => {
              setPage(1)
              setQ(e.target.value)
            }}
          />
          <select
            value={categoryId}
            onChange={(e) => {
              setPage(1)
              setCategoryId(e.target.value)
            }}
            className="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm"
          >
            <option value="">همه دسته‌ها</option>
            {flatCategories.map((cat) => (
              <option key={cat.id} value={cat.id}>
                {cat.name}
              </option>
            ))}
          </select>
        </div>
      </div>

      {loading ? (
        <ProductSkeleton />
      ) : items.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
          کالایی پیدا نشد.
        </div>
      ) : (
        <div className="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
          {items.map((item) => (
            <ProductCard key={item.id} item={item} adding={adding === item.id} onAdd={handleAdd} />
          ))}
        </div>
      )}

      {meta?.last_page > 1 && (
        <div className="flex items-center justify-center gap-2">
          <Button variant="secondary" disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
            قبلی
          </Button>
          <span className="text-sm text-slate-500">
            صفحه {meta.current_page} از {meta.last_page}
          </span>
          <Button variant="secondary" disabled={page >= meta.last_page} onClick={() => setPage((p) => p + 1)}>
            بعدی
          </Button>
        </div>
      )}
    </div>
  )
}
