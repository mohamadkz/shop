import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { orderApi, unwrapList } from '../api/client'
import { formatDate, formatPrice } from '../utils/format'
import { TableSkeleton } from '../components/ui/Skeleton'
import { useToast } from '../context/ToastContext'

export default function Orders() {
  const toast = useToast()
  const [orders, setOrders] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    orderApi
      .list({ per_page: 20 })
      .then((payload) => setOrders(unwrapList(payload).items))
      .catch((err) => toast.error(err.message))
      .finally(() => setLoading(false))
  }, [toast])

  if (loading) return <TableSkeleton />

  return (
    <div className="space-y-5">
      <h1 className="text-2xl font-bold">سفارش‌ها</h1>
      {orders.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
          سفارشی ندارید.
        </div>
      ) : (
        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
          {orders.map((order) => (
            <Link
              key={order.id}
              to={`/orders/${order.id}`}
              className="flex flex-col gap-2 border-b border-slate-100 px-4 py-4 last:border-0 hover:bg-slate-50 sm:flex-row sm:items-center sm:justify-between"
            >
              <div>
                <p className="font-semibold">{order.order_number}</p>
                <p className="text-xs text-slate-500">{formatDate(order.created_at)}</p>
              </div>
              <div className="flex items-center gap-4 text-sm">
                <span className="rounded-full bg-indigo-50 px-3 py-1 text-indigo-700">{order.status_label || order.status}</span>
                <span className="font-medium">{formatPrice(order.total_price)}</span>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  )
}
