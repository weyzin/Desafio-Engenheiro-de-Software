import React from "react"
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query"
import { useToast } from "../../ui/Toast"
import {
  listTenants,
  createTenant,
  updateTenant,
  deleteTenant,
  type Tenant,
  type ListResp,
} from "../../lib/adminApi"

export default function TenantsTab() {
  const qc = useQueryClient()
  const { push } = useToast()

  const [q, setQ] = React.useState("")
  const [page, setPage] = React.useState(1)
  const [editing, setEditing] = React.useState<Tenant | null>(null)
  const [form, setForm] = React.useState<{ name: string; slug: string }>({ name: "", slug: "" })

  const { data, isLoading, isError } = useQuery<ListResp<Tenant>>({
    queryKey: ["tenants", { q, page }],
    queryFn: () => listTenants({ q: q || undefined, page, per_page: 20 }),
    // v5: substitui keepPreviousData
    placeholderData: (prev) => prev,
  })

  React.useEffect(() => {
    if (isError) push({ kind: "error", message: "Falha ao carregar tenants." })
  }, [isError, push])

  const resetForm = () => setForm({ name: "", slug: "" })

  const mutCreate = useMutation({
    mutationFn: createTenant,
    onSuccess: () => {
      push({ kind: "success", message: "Tenant criado" })
      qc.invalidateQueries({ queryKey: ["tenants"] })
      resetForm()
      setEditing(null)
    },
  })

  const mutUpdate = useMutation({
    mutationFn: ({ id, payload }: { id: string; payload: { name: string; slug: string } }) =>
      updateTenant(id, payload),
    onSuccess: () => {
      push({ kind: "success", message: "Tenant atualizado" })
      qc.invalidateQueries({ queryKey: ["tenants"] })
      resetForm()
      setEditing(null)
    },
  })

  const mutDelete = useMutation({
    mutationFn: deleteTenant,
    onSuccess: () => {
      push({ kind: "success", message: "Tenant excluído" })
      qc.invalidateQueries({ queryKey: ["tenants"] })
    },
    onError: (e: any) => {
      const msg =
        e?.code === "TENANT_NOT_EMPTY"
          ? "Tenant possui vínculos e não pode ser excluído."
          : e?.message || "Erro ao excluir."
      push({ kind: "error", message: msg })
    },
  })

  const rows = data?.data ?? []

  return (
    <section className="grid gap-4">
      <header className="flex items-end justify-between gap-4">
        <div className="flex-1">
          <h2 className="text-lg font-semibold">Tenants</h2>
          <p className="text-sm text-gray-600">Gerencie organizações (apenas superuser).</p>
        </div>

        {/* formulário compacto de create/update */}
        <form
          className="flex gap-2"
          onSubmit={(e) => {
            e.preventDefault()
            if (editing) mutUpdate.mutate({ id: editing.id, payload: form })
            else mutCreate.mutate(form)
          }}
        >
          <input
            className="rounded border px-3 py-2"
            placeholder="Nome"
            value={form.name}
            onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
            required
          />
          <input
            className="rounded border px-3 py-2"
            placeholder="slug"
            value={form.slug}
            onChange={(e) => setForm((f) => ({ ...f, slug: e.target.value }))}
            required
          />
          <button className="px-3 py-2 rounded bg-blue-600 text-white">
            {editing ? "Salvar" : "Criar"}
          </button>
          {editing && (
            <button
              type="button"
              className="px-3 py-2 rounded border"
              onClick={() => {
                setEditing(null)
                resetForm()
              }}
            >
              Cancelar
            </button>
          )}
        </form>
      </header>

      {/* busca + paginação simples */}
      <div className="flex items-center gap-2">
        <input
          className="rounded border px-3 py-2"
          placeholder="Buscar por nome/slug…"
          value={q}
          onChange={(e) => {
            setQ(e.target.value)
            setPage(1)
          }}
        />
        {data?.meta && data.meta.last_page > 1 && (
          <div className="ml-auto flex items-center gap-2">
            <button
              disabled={page <= 1}
              onClick={() => setPage((p) => p - 1)}
              className="border rounded px-3 py-2 disabled:opacity-50"
            >
              Anterior
            </button>
            <span className="text-sm text-gray-600">
              Página {page} de {data.meta.last_page}
            </span>
            <button
              disabled={page >= data.meta.last_page}
              onClick={() => setPage((p) => p + 1)}
              className="border rounded px-3 py-2 disabled:opacity-50"
            >
              Próxima
            </button>
          </div>
        )}
      </div>

      <div className="bg-white rounded-2xl border overflow-x-auto">
        {isLoading ? (
          <div className="p-4 text-sm text-gray-600">Carregando…</div>
        ) : rows.length === 0 ? (
          <div className="p-4 text-sm text-gray-600">Nenhum tenant encontrado.</div>
        ) : (
          <table className="min-w-full text-sm">
            <thead>
              <tr className="text-left bg-gray-50">
                <th className="px-4 py-3">ID</th>
                <th className="px-4 py-3">Nome</th>
                <th className="px-4 py-3">Slug</th>
                <th className="px-4 py-3" />
              </tr>
            </thead>
            <tbody>
              {rows.map((t) => (
                <tr key={t.id} className="border-t">
                  <td className="px-4 py-3">{t.id}</td>
                  <td className="px-4 py-3">{t.name}</td>
                  <td className="px-4 py-3">{t.slug}</td>
                  <td className="px-4 py-3 text-right">
                    <div className="flex gap-2 justify-end">
                      <button
                        className="px-2 py-1 rounded border hover:bg-gray-50"
                        onClick={() => {
                          setEditing(t)
                          setForm({ name: t.name, slug: t.slug })
                        }}
                      >
                        Editar
                      </button>
                      <button
                        className="px-2 py-1 rounded border hover:bg-red-50"
                        onClick={() => mutDelete.mutate(t.id)}
                      >
                        Excluir
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </section>
  )
}
