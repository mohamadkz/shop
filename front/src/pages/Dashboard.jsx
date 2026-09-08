import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { useCart } from '../context/CartContext'
import { orderApi, unwrapList } from '../api/client'
import { formatPrice } from '../utils/format'
import Card from '../components/ui/Card'
import { ClipboardList, ShoppingBag, UserRound } from 'lucide-react'

export default function Dashboard() {
  const { user } = useAuth()
  const { count, cart } = useCart()
  const [orderCount, setOrderCount] = useState(0)

  useEffect(() => {
    orderApi.list({ per_page: 1 }).then((payload) => {
      const { meta, items } = unwrapList(payload)
      setOrderCount(meta?.total ?? items.length)
    }).catch(() => setOrderCount(0))
  }, [])

  const stats = [
    { label: 'کالای سبد', value: count, icon: ShoppingBag, to: '/cart' },
    { label: 'مبلغ سبد', value: formatPrice(cart.total), icon: ClipboardList, to: '/cart' },
    { label: 'سفارش‌ها', value: orderCount, icon: UserRound, to: '/orders' },
  ]

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">سلام {user?.name}</h1>
        <p className="text-sm text-slate-500">خلاصه حساب و خریدهای شما</p>
      </div>
      <div className="grid gap-4 sm:grid-cols-3">
        {stats.map((stat) => {
          const Icon = stat.icon
          return (
            <Link key={stat.label} to={stat.to}>
              <Card className="p-5 transition hover:-translate-y-0.5">
                <Icon className="h-5 w-5 text-indigo-600" />
                <p className="mt-4 text-sm text-slate-500">{stat.label}</p>
                <p className="mt-1 text-xl font-bold">{stat.value}</p>
              </Card>
            </Link>
          )
        })}
      </div>
    </div>
  )
}
