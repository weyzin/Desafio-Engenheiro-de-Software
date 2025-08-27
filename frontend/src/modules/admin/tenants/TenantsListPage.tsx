import React from "react"
import { useQuery } from "@tanstack/react-query"
import { Link, useSearchParams } from "react-router-dom"
import { listTenants, type Tenant } from "./api"
import type { ListResponse } from "../../vehicles/types"
import { Pagination } from "../../../ui/Pagination"
import { Skeleton } from "../../../ui/Skeleton"
import { useToast } from "../../../ui/Toast"

export default function TenantsListPage() {
  const [sp, setSp] = useSearchParams()
  const { push } = useToast()

  const page = sp.get("page") ? Number(sp.get("page")) : undefined
  const q = sp.get("q") || undefined

  const { data, isLoading, isError } = useQuery<ListResponse<Tenant>>({
    queryKey: ["admin-tenants", { page, q }],
    queryFn: () => listTenants({ page, q }),
    // manter dados anteriores enquanto busca nova página
    placeholderData: (prev) => prev,
  })

  React.useEffect(() => {
    if (isError) push({ kind: "error", message: "Falha ao carregar tenants." })
  }, [isError, push])

  function onNavigate(url: string) {
    const u = new URL(url)
    const next = new URLSearchParams(sp)
    const p = u.searchParams.get("page")
    if (p) next.set("page", p)
    setSp(next, { replace: true })
  }

  return (
    <section className="grid gap-3">
      <header className="flex items-end justify-between">
        <div>
          <h1 className="text-xl font-semibold">Tenants</h1>
          <p className="text-sm text-gray-600">Gerencie espaços multi-tenant.</p>
        </div>
        <Link to="/admin/tenants/new" className="rounded bg-blue-600 text-white px-4 py-2">
          Novo tenant
        </Link>
      </header>

      <div className="bg-white rounded-2xl border p-3">
        <input
          placeholder="Buscar por nome/slug…"
          className="rounded border px-3 py-2 w-full max-w-md mb-3"
          value={q ?? ""}
          onChange={(e) => {
            const s = new URLSearchParams(sp)
            const v = e.target.value
            v ? s.set("q", v) : s.delete("q")
            s.delete("page")
            setSp(s, { replace: true })
          }}
        />

        {isLoading ? (
          <div className="grid gap-2">
            <Skeleton className="h-8" />
            <Skeleton className="h-8" />
            <Skeleton className="h-8" />
          </div>
        ) : !data || data.data.length === 0 ? (
          <div className="p-4 text-sm text-gray-600">Nenhum tenant encontrado.</div>
        ) : (
          <table className="min-w-full text-sm">
            <thead>
              <tr className="bg-gray-50 text-left">
                <th className="px-3 py-2">ID</th>
                <th className="px-3 py-2">Nome</th>
                <th className="px-3 py-2">Slug</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {data.data.map((t) => (
                <tr key={t.id} className="border-t">
                  <td className="px-3 py-2">{t.id}</td>
                  <td className="px-3 py-2">{t.name}</td>
                  <td className="px-3 py-2">{t.slug}</td>
                  <td className="px-3 py-2 text-right">
                    <Link to={`/admin/tenants/${t.id}`} className="px-2 py-1 rounded border">
                      Editar
                    </Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {data?.meta && (
        <Pagination
          meta={{ links: data.meta.links, from: data.meta.from, to: data.meta.to, total: data.meta.total }}
          onNavigate={onNavigate}
        />
      )}
    </section>
  )
}
