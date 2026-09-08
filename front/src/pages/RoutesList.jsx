import { useMemo } from 'react'
import routes from '../routes.json'

export default function RoutesList() {
  const groups = useMemo(() => Object.entries(routes), [])

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">نقشه API</h1>
      {groups.map(([name, list]) => (
        <section key={name} className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
          <header className="bg-slate-50 px-4 py-3 font-semibold">{name}</header>
          <ul className="divide-y divide-slate-100 text-sm">
            {list.map((route) => (
              <li key={`${route.method}-${route.path}`} className="flex flex-col gap-1 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <span className="font-mono text-xs text-indigo-700">{route.method}</span>
                <span className="flex-1 font-mono text-slate-700 sm:px-4">{route.path}</span>
                <span className="text-xs text-slate-400">{route.middleware || 'public'}</span>
              </li>
            ))}
          </ul>
        </section>
      ))}
    </div>
  )
}
