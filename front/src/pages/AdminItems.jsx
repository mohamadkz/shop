import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { catalogApi, unwrapList } from '../api/client'
import { formatPrice } from '../utils/format'
import Button from '../components/ui/Button'
import Modal from '../components/ui/Modal'
import { TableSkeleton } from '../components/ui/Skeleton'
import { useToast } from '../context/ToastContext'
import { Pencil, Plus, Trash2 } from 'lucide-react'

export default function AdminItems() {
  const toast = useToast()
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)
  const [deleting, setDeleting] = useState(null)

  async function load() {
    setLoading(true)
    try {
      const payload = await catalogApi.items({ per_page: 50 })
      setItems(unwrapList(payload).items)
    } catch (err) {
      toast.error(err.message)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    load()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  async function confirmDelete() {
    try {
      const payload = await catalogApi.deleteItem(deleting.id)
      toast.success(payload?.message || 'کالا حذف شد')
      setDeleting(null)
      await load()
    } catch (err) {
      toast.error(err.message)
    }
  }

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">مدیریت کالا</h1>
          <p className="text-sm text-slate-500">نیاز به توانایی Sanctum با نام items:write</p>
        </div>
        <Link to="/admin/items/new">
          <Button>
            <Plus className="h-4 w-4" />
            کالای جدید
          </Button>
        </Link>
      </div>

      {loading ? (
        <TableSkeleton />
      ) : (
        <div className="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
          <table className="w-full min-w-[640px] text-right text-sm">
            <thead className="bg-slate-50 text-slate-500">
              <tr>
                <th className="px-4 py-3 font-medium">نام</th>
                <th className="px-4 py-3 font-medium">قیمت</th>
                <th className="px-4 py-3 font-medium">موجودی</th>
                <th className="px-4 py-3 font-medium">وضعیت</th>
                <th className="px-4 py-3 font-medium"></th>
              </tr>
            </thead>
            <tbody>
              {items.map((item) => (
                <tr key={item.id} className="border-t border-slate-100">
                  <td className="px-4 py-3 font-medium">{item.name}</td>
                  <td className="px-4 py-3">{formatPrice(item.price)}</td>
                  <td className="px-4 py-3">{item.stock}</td>
                  <td className="px-4 py-3">{item.status}</td>
                  <td className="px-4 py-3">
                    <div className="flex justify-end gap-2">
                      <Link to={`/admin/items/${item.id}/edit`} className="rounded-lg p-2 hover:bg-slate-100">
                        <Pencil className="h-4 w-4" />
                      </Link>
                      <button className="rounded-lg p-2 text-rose-600 hover:bg-rose-50" onClick={() => setDeleting(item)}>
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <Modal
        open={Boolean(deleting)}
        title="حذف کالا"
        onClose={() => setDeleting(null)}
        footer={
          <>
            <Button variant="secondary" onClick={() => setDeleting(null)}>انصراف</Button>
            <Button variant="danger" onClick={confirmDelete}>حذف</Button>
          </>
        }
      >
        <p className="text-sm text-slate-600">کالای «{deleting?.name}» حذف شود؟</p>
      </Modal>
    </div>
  )
}
