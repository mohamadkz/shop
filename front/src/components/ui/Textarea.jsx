export default function Textarea({ label, error, className = '', ...props }) {
  return (
    <label className={`block ${className}`}>
      {label && <span className="mb-1.5 block text-sm font-medium text-slate-700">{label}</span>}
      <textarea
        className={`min-h-[120px] w-full rounded-xl border bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:ring-2 ${
          error ? 'border-rose-300 focus:ring-rose-200' : 'border-slate-200 focus:border-indigo-300 focus:ring-indigo-100'
        }`}
        {...props}
      />
      {error && <span className="mt-1 block text-xs text-rose-600">{error}</span>}
    </label>
  )
}
