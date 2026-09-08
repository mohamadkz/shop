import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import Input from '../components/ui/Input'
import Button from '../components/ui/Button'
import { useAuth } from '../context/AuthContext'
import { useToast } from '../context/ToastContext'

export default function Register() {
  const { register } = useAuth()
  const toast = useToast()
  const navigate = useNavigate()
  const [form, setForm] = useState({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
  })
  const [busy, setBusy] = useState(false)

  function set(key, value) {
    setForm((f) => ({ ...f, [key]: value }))
  }

  async function submit(e) {
    e.preventDefault()
    setBusy(true)
    try {
      await register(form)
      navigate('/login')
    } catch (err) {
      toast.error(err.message)
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="mx-auto max-w-md">
      <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
        <h1 className="text-2xl font-bold">ثبت‌نام</h1>
        <p className="mt-1 text-sm text-slate-500">رمز باید حداقل ۸ کاراکتر، شامل حروف بزرگ و کوچک، عدد و نماد باشد.</p>
        <form onSubmit={submit} className="mt-6 space-y-4">
          <Input label="نام" required value={form.name} onChange={(e) => set('name', e.target.value)} />
          <Input label="ایمیل" type="email" required value={form.email} onChange={(e) => set('email', e.target.value)} />
          <Input label="موبایل" required placeholder="09xxxxxxxxx" value={form.phone} onChange={(e) => set('phone', e.target.value)} />
          <Input label="رمز عبور" type="password" required value={form.password} onChange={(e) => set('password', e.target.value)} />
          <Input label="تکرار رمز" type="password" required value={form.password_confirmation} onChange={(e) => set('password_confirmation', e.target.value)} />
          <Button type="submit" className="w-full" disabled={busy}>ایجاد حساب</Button>
        </form>
        <Link to="/login" className="mt-5 block text-sm text-indigo-600">قبلاً ثبت‌نام کرده‌اید؟ ورود</Link>
      </div>
    </div>
  )
}
