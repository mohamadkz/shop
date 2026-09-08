import { Link } from 'react-router-dom'
import { formatPrice } from '../../utils/format'
import Button from './Button'
import { Heart, ShoppingBag } from 'lucide-react'

export default function ProductCard({ item, onAdd, adding }) {
  const image = item.image || `https://picsum.photos/seed/${item.id}/640/480`

  return (
    <article className="group overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card transition hover:-translate-y-0.5 hover:shadow-lg">
      <Link to={`/products/${item.id}`} className="block overflow-hidden bg-slate-100">
        <img src={image} alt={item.name} className="h-48 w-full object-cover transition duration-500 group-hover:scale-105" />
      </Link>
      <div className="space-y-3 p-4">
        <div className="flex items-start justify-between gap-3">
          <div>
            <p className="text-xs text-indigo-600">{item.category?.name || 'کاتالوگ'}</p>
            <Link to={`/products/${item.id}`} className="mt-1 line-clamp-1 font-semibold text-slate-900">
              {item.name}
            </Link>
          </div>
          <span className={`rounded-full px-2 py-1 text-[11px] ${item.in_stock ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'}`}>
            {item.in_stock ? 'موجود' : 'ناموجود'}
          </span>
        </div>
        <p className="line-clamp-2 text-sm text-slate-500">{item.description || 'توضیحی ثبت نشده است.'}</p>
        <div className="flex items-center justify-between">
          <p className="font-bold text-slate-900">{item.formatted_price ? `${item.formatted_price} تومان` : formatPrice(item.price)}</p>
          <Heart className="h-4 w-4 text-slate-300" />
        </div>
        <Button className="w-full" disabled={!item.in_stock || adding} onClick={() => onAdd?.(item)}>
          <ShoppingBag className="h-4 w-4" />
          افزودن به سبد
        </Button>
      </div>
    </article>
  )
}
