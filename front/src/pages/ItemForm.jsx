import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { catalogApi, unwrap, unwrapList } from '../api/client'
import { slugify } from '../utils/format'
import Input from '../components/ui/Input'
import Textarea from '../components/ui/Textarea'
import Button from '../components/ui/Button'
import { useToast } from '../context/ToastContext'
import { DetailSkeleton } from '../components/ui/Skeleton'

const empty = {
  name: '',
  slug: '',
  category_id: '',
  description: '',
  price: '',
  stock: '0',
  status: 'active',
}

export default function ItemForm() {
  const { id } = useParams()
  const isEdit = Boolean(id)
  const toast = useToast()
  const navigate = useNavigate()
  const [form, setForm] = useState(empty)
  const [image, setImage] = useState(null)
  const [categories, setCategories] = useState([])
  const [loading, setLoading] = useState(isEdit)
  const [busy, setBusy] = useState(false)

  function set(key, value) {
    setForm((f) => ({ ...f, [key]: value }))
  }

  useEffect(() => {
    catalogApi.categories()
      .then((payload) => {
        const list = unwrapList(payload).items
        const flat = []
        for (const cat of list) {
          flat.push(cat)
          if (Array.isArray(cat.children)) flat.push(...cat.children)
        }
        setCategories(flat)
      })
      .catch(() => setCategories([]))
  }, [])

  useEffect(() => {
    if (!isEdit) return
    catalogApi
      .item(id)
      .then((payload) => {
        const item = unwrap(payload) ?? payload?.data
        setForm({
          name: item.name || '',
          slug: item.slug || '',
          category_id: item.category_id || '',
          description: item.description || '',
          price: item.price ?? '',
          stock: String(item.stock ?? 0),
          status: item.status || 'active',
        })
      })
      .catch((err) => toast.error(err.message))
      .finally(() => setLoading(false))
  }, [id, isEdit, toast])

  async function submit(e) {
    e.preventDefault()
    setBusy(true)
    try {
      const data = new FormData()
      data.append('name', form.name)
      data.append('slug', form.slug || slugify(form.name))
      data.append('category_id', form.category_id)
      data.append('description', form.description)
      data.append('price', form.price)
      data.append('stock', form.stock)
      data.append('status', form.status)
      if (image) data.append('image', image)
      if (isEdit) data.append('_method', 'PUT')

      const payload = isEdit ? await catalogApi.updateItem(id, data) : await catalogApi.createItem(data)
      toast.success(payload?.message || (isEdit ? 'کالا به‌روزرسانی شد' : 'کالا ایجاد شد'))
      navigate('/admin/items')
    } catch (err) {
      toast.error(err.message)
    } finally {
      setBusy(false)
    }
  }

  if (loading) return <DetailSkeleton />

  return (
    <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4 rounded-3xl border border-slate-200 bg-white p-6">
      <h1 className="text-2xl font-bold">{isEdit ? 'ویرایش کالا' : 'کالای جدید'}</h1>
      <Input label="نام" required value={form.name} onChange={(e) => {
        set('name', e.target.value)
        if (!isEdit) set('slug', slugify(e.target.value))
      }} />
      <Input label="اسلاگ" required value={form.slug} onChange={(e) => set('slug', e.target.value)} />
      <label className="block text-sm font-medium text-slate-700">
        دسته
        <select
          required
          value={form.category_id}
          onChange={(e) => set('category_id', e.target.value)}
          className="mt-1.5 h-11 w-full rounded-xl border border-slate-200 px-3 text-sm"
        >
          <option value="">انتخاب کنید</option>
          {categories.map((cat) => (
            <option key={cat.id} value={cat.id}>{cat.name}</option>
          ))}
        </select>
      </label>
      <Textarea label="توضیحات" value={form.description} onChange={(e) => set('description', e.target.value)} />
      <div className="grid gap-4 sm:grid-cols-2">
        <Input label="قیمت" type="number" min="0" step="0.01" required value={form.price} onChange={(e) => set('price', e.target.value)} />
        <Input label="موجودی" type="number" min="0" required value={form.stock} onChange={(e) => set('stock', e.target.value)} />
      </div>
      <label className="block text-sm font-medium">
        وضعیت
        <select value={form.status} onChange={(e) => set('status', e.target.value)} className="mt-1.5 h-11 w-full rounded-xl border px-3">
          <option value="draft">draft</option>
          <option value="active">active</option>
          <option value="archived">archived</option>
        </select>
      </label>
      <label className="block text-sm font-medium">
        تصویر
        <input type="file" accept="image/jpeg,image/png,image/webp" className="mt-1.5 block w-full text-sm" onChange={(e) => setImage(e.target.files?.[0] || null)} />
      </label>
      <Button type="submit" disabled={busy}>{isEdit ? 'ذخیره تغییرات' : 'ایجاد کالا'}</Button>
    </form>
  )
}
