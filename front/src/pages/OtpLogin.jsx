import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import Input from '../components/ui/Input'
import Button from '../components/ui/Button'
import { useAuth } from '../context/AuthContext'
import { useToast } from '../context/ToastContext'

export default function OtpLogin() {
  const { sendOtp, verifyOtp } = useAuth()
  const toast = useToast()
  const navigate = useNavigate()
  const [phone, setPhone] = useState('')
  const [code, setCode] = useState('')
  const [sent, setSent] = useState(false)
  const [busy, setBusy] = useState(false)

  async function send(e) {
    e.preventDefault()
    setBusy(true)
    try {
      await sendOtp(phone)
      setSent(true)
    } catch (err) {
      toast.error(err.message)
    } finally {
      setBusy(false)
    }
  }

  async function verify(e) {
    e.preventDefault()
    setBusy(true)
    try {
      await verifyOtp(phone, code)
      navigate('/dashboard')
    } catch (err) {
      toast.error(err.message)
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="mx-auto max-w-md">
      <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-card sm:p-8">
        <h1 className="text-2xl font-bold">ورود با OTP</h1>
        <p className="mt-1 text-sm text-slate-500">شماره باید با ۰۹ شروع شود و ۱۱ رقم باشد.</p>
        {!sent ? (
          <form onSubmit={send} className="mt-6 space-y-4">
            <Input label="موبایل" required value={phone} onChange={(e) => setPhone(e.target.value)} placeholder="09123456789" />
            <Button type="submit" className="w-full" disabled={busy}>ارسال کد</Button>
          </form>
        ) : (
          <form onSubmit={verify} className="mt-6 space-y-4">
            <Input label="کد ۶ رقمی" required value={code} onChange={(e) => setCode(e.target.value)} maxLength={6} />
            <Button type="submit" className="w-full" disabled={busy}>تایید و ورود</Button>
            <Button variant="ghost" className="w-full" onClick={() => setSent(false)}>تغییر شماره</Button>
          </form>
        )}
        <Link to="/login" className="mt-5 block text-sm text-indigo-600">ورود با ایمیل</Link>
      </div>
    </div>
  )
}
