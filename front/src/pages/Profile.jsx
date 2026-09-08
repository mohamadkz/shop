import { useAuth } from '../context/AuthContext'
import Card from '../components/ui/Card'

export default function Profile() {
  const { user } = useAuth()

  return (
    <div className="mx-auto max-w-xl space-y-5">
      <h1 className="text-2xl font-bold">پروفایل</h1>
      <Card className="p-6 space-y-3">
        <Row label="نام" value={user?.name} />
        <Row label="ایمیل" value={user?.email} />
        <Row label="موبایل" value={user?.phone} />
        <Row label="تایید موبایل" value={user?.phone_verified ? 'بله' : 'خیر'} />
      </Card>
    </div>
  )
}

function Row({ label, value }) {
  return (
    <div className="flex items-center justify-between border-b border-slate-100 pb-3 last:border-0 last:pb-0">
      <span className="text-sm text-slate-500">{label}</span>
      <span className="font-medium">{value || '—'}</span>
    </div>
  )
}
