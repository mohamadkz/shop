import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom'
import {
  LayoutDashboard,
  ShoppingBag,
  Package,
  ClipboardList,
  UserRound,
  LogOut,
  Menu,
  X,
  Store,
  Sparkles,
} from 'lucide-react'
import { useState } from 'react'
import { useAuth } from '../../context/AuthContext'
import { useCart } from '../../context/CartContext'

const links = [
  { to: '/', label: 'خانه', icon: Sparkles },
  { to: '/products', label: 'محصولات', icon: Package },
  { to: '/cart', label: 'سبد خرید', icon: ShoppingBag },
  { to: '/orders', label: 'سفارش‌ها', icon: ClipboardList, auth: true },
  { to: '/dashboard', label: 'داشبورد', icon: LayoutDashboard, auth: true },
  { to: '/admin/items', label: 'مدیریت کالا', icon: Store, auth: true },
  { to: '/profile', label: 'پروفایل', icon: UserRound, auth: true },
]

export default function AppLayout() {
  const { user, logout } = useAuth()
  const { count } = useCart()
  const [open, setOpen] = useState(false)
  const navigate = useNavigate()

  async function handleLogout() {
    await logout()
    navigate('/')
  }

  const nav = (
    <nav className="space-y-1">
      {links
        .filter((link) => !link.auth || user)
        .map((link) => {
          const Icon = link.icon
          return (
            <NavLink
              key={link.to}
              to={link.to}
              end={link.to === '/'}
              onClick={() => setOpen(false)}
              className={({ isActive }) =>
                `flex items-center justify-between rounded-xl px-3 py-2.5 text-sm transition ${
                  isActive ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'
                }`
              }
            >
              <span className="flex items-center gap-2">
                <Icon className="h-4 w-4" />
                {link.label}
              </span>
              {link.to === '/cart' && count > 0 && (
                <span className="rounded-full bg-white/20 px-2 py-0.5 text-[11px]">{count}</span>
              )}
            </NavLink>
          )
        })}
    </nav>
  )

  return (
    <div className="min-h-screen bg-slate-50">
      <div className="lg:grid lg:grid-cols-[260px_1fr]">
        <aside className="hidden min-h-screen border-l border-slate-200 bg-white p-5 lg:block">
          <Link to="/" className="mb-8 flex items-center gap-2">
            <span className="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-600 text-white">
              <Store className="h-5 w-5" />
            </span>
            <div>
              <p className="font-extrabold text-slate-900">Lumière</p>
              <p className="text-xs text-slate-500">فروشگاه ماژولار</p>
            </div>
          </Link>
          {nav}
        </aside>

        <div className="flex min-h-screen flex-col">
          <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur">
            <div className="flex items-center justify-between gap-3 px-4 py-3 sm:px-6">
              <div className="flex items-center gap-3">
                <button className="rounded-xl p-2 text-slate-600 hover:bg-slate-100 lg:hidden" onClick={() => setOpen(true)}>
                  <Menu className="h-5 w-5" />
                </button>
                <Link to="/" className="font-bold text-slate-900 lg:hidden">
                  Lumière
                </Link>
              </div>
              <div className="flex items-center gap-2">
                <Link
                  to="/cart"
                  className="relative rounded-xl p-2 text-slate-600 hover:bg-slate-100"
                >
                  <ShoppingBag className="h-5 w-5" />
                  {count > 0 && (
                    <span className="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-indigo-600 px-1 text-[10px] text-white">
                      {count}
                    </span>
                  )}
                </Link>
                {user ? (
                  <>
                    <Link to="/profile" className="hidden rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 sm:block">
                      {user.name}
                    </Link>
                    <button onClick={handleLogout} className="rounded-xl p-2 text-slate-500 hover:bg-slate-100">
                      <LogOut className="h-5 w-5" />
                    </button>
                  </>
                ) : (
                  <Link to="/login" className="rounded-xl bg-slate-900 px-3 py-2 text-sm text-white">
                    ورود
                  </Link>
                )}
              </div>
            </div>
          </header>

          {open && (
            <div className="fixed inset-0 z-50 lg:hidden">
              <button className="absolute inset-0 bg-slate-900/40" onClick={() => setOpen(false)} />
              <aside className="absolute right-0 top-0 h-full w-72 bg-white p-5 shadow-card">
                <div className="mb-6 flex items-center justify-between">
                  <p className="font-bold">منو</p>
                  <button onClick={() => setOpen(false)}>
                    <X className="h-5 w-5" />
                  </button>
                </div>
                {nav}
              </aside>
            </div>
          )}

          <main className="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <Outlet />
          </main>

          <footer className="border-t border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500 sm:px-6">
            Lumière Shop · کاتالوگ، سبد، سفارش و پرداخت روی API نسخه ۱
          </footer>
        </div>
      </div>
    </div>
  )
}
