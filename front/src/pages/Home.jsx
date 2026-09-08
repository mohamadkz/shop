import { Link } from 'react-router-dom'
import { ArrowLeft, Sparkles } from 'lucide-react'
import Button from '../components/ui/Button'
import { useAuth } from '../context/AuthContext'

export default function Home() {
  const { user } = useAuth()

  return (
    <div className="space-y-10">
      <section className="overflow-hidden rounded-3xl bg-gradient-to-bl from-slate-900 via-indigo-900 to-indigo-700 p-8 text-white sm:p-12">
        <p className="mb-3 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs">
          <Sparkles className="h-3.5 w-3.5" />
          فروشگاه ماژولار
        </p>
        <h1 className="max-w-2xl text-3xl font-extrabold leading-tight sm:text-5xl">
          تجربه‌ای مدرن برای خرید کالا، مدیریت سبد و پیگیری سفارش
        </h1>
        <p className="mt-4 max-w-xl text-sm text-indigo-100 sm:text-base">
          کاتالوگ عمومی، ورود با ایمیل یا OTP، سبد خرید، تسویه و پرداخت؛ همه روی قرارداد API بک‌اند شما.
        </p>
        <div className="mt-8 flex flex-wrap gap-3">
          <Link to="/products">
            <Button size="lg">
              مشاهده محصولات
              <ArrowLeft className="h-4 w-4" />
            </Button>
          </Link>
          {!user && (
            <Link to="/login">
              <Button size="lg" variant="secondary">
                ورود به حساب
              </Button>
            </Link>
          )}
        </div>
      </section>

      <section className="grid gap-4 sm:grid-cols-3">
        {[
          { title: 'کاتالوگ زنده', text: 'جستجو، فیلتر دسته و جزئیات کالا با نظرات و علاقه‌مندی.' },
          { title: 'سبد هوشمند', text: 'تغییر تعداد، کد تخفیف و تسویه با کلید تکرارناپذیر.' },
          { title: 'سفارش و پرداخت', text: 'تاریخچه سفارش و شروع درگاه پرداخت از روی UUID سفارش.' },
        ].map((card) => (
          <div key={card.title} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-card">
            <h3 className="font-semibold text-slate-900">{card.title}</h3>
            <p className="mt-2 text-sm text-slate-500">{card.text}</p>
          </div>
        ))}
      </section>
    </div>
  )
}
