import { useState } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import Input from '../components/ui/Input'
import Button from '../components/ui/Button'
import { useAuth } from '../context/AuthContext'
import { useToast } from '../context/ToastContext'

export default function Login() {
  const { login } = useAuth()
  const toast = useToast()
  const navigate = useNavigate()
  const location = useLocation()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [busy, setBusy] = useState(false)

  async function submit(e) {
    e.preventDefault()
    setBusy(true)
    try {
      await login(email, password)
      navigate(location.state?.from || '/dashboard')
    } catch (err) {
      toast.error(err.message)
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="mx-auto max-w-md">
      <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
        <h1 className="text-2xl font-bold">ورود</h1>
        <p className="mt-1 text-sm text-slate-500">با ایمیل و رمز عبور وارد شوید</p>
        <form onSubmit={submit} className="mt-6 space-y-4">
          <Input label="ایمیل" type="email" required value={email} onChange={(e) => setEmail(e.target.value)} />
          <Input label="رمز عبور" type="password" required value={password} onChange={(e) => setPassword(e.target.value)} />
          <Button type="submit" className="w-full" disabled={busy}>ورود</Button>
        </form>
        <div className="mt-5 flex flex-col gap-2 text-sm">
          <Link className="text-indigo-600" to="/otp">ورود با کد یک‌بارمصرف</Link>
          <Link className="text-slate-500" to="/register">حساب ندارید؟ ثبت‌نام</Link>
        </div>
      </div>
    </div>
  )
}
