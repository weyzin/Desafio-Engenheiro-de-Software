import React from "react"
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query"
import { useToast } from "../../ui/Toast"
import {
  listUsers,
  createUser,
  updateUser,
  deleteUser,
  listTenants,
  isValidation,
  type User,
  type UserInput,
  type Tenant,
  type ListResp,
} from "../../lib/adminApi"

export default function UsersTab() {
  const qc = useQueryClient()
  const { push } = useToast()

  const [q, setQ] = React.useState("")
  const [page, setPage] = React.useState(1)
  const per_page = 20

  // Tenants para o <select>
  const { data: tenantsResp } = useQuery<ListResp<Tenant>>({
    queryKey: ["tenants", { per_page: 999 }],
    queryFn: () => listTenants({ per_page: 999 }),
    placeholderData: (prev) => prev,
  })
  const tenants = tenantsResp?.data ?? []

  // Lista de usuários
  const { data, isLoading, isError } = useQuery<ListResp<User>>({
    queryKey: ["users", { q, page, per_page }],
    queryFn: () => listUsers({ q: q || undefined, page, per_page }),
    placeholderData: (prev) => prev,
  })

  React.useEffect(() => {
    if (isError) push({ kind: "error", message: "Falha ao carregar usuários." })
  }, [isError, push])

  const [editing, setEditing] = React.useState<User | null>(null)
  const [form, setForm] = React.useState<UserInput>({
    tenant_id: null,
    name: "",
    email: "",
    password: "",
    role: "agent",
  })
  const resetForm = () =>
    setForm({ tenant_id: null, name: "", email: "", password: "", role: "agent" })

  const onEdit = (u: User) => {
    setEditing(u)
    setForm({
      tenant_id: u.role === "superuser" ? null : (u.tenant_id as any),
      name: u.name,
      email: u.email,
      role: u.role as any,
      password: "",
    })
  }
  const onNew = () => {
    setEditing(null)
    resetForm()
  }

  const mutCreate = useMutation({
    mutationFn: createUser,
    onSuccess: () => {
      push({ kind: "success", message: "Usuário criado" })
      qc.invalidateQueries({ queryKey: ["users"] })
      resetForm()
    },
    onError: (e: any) => {
      push({
        kind: "error",
        message: isValidation(e) ? "Campos inválidos" : e?.message || "Erro ao criar usuário",
      })
    },
  })

  const mutUpdate = useMutation({
    mutationFn: (p: { id: number; input: UserInput }) => updateUser(p.id, p.input),
    onSuccess: () => {
      push({ kind: "success", message: "Usuário atualizado" })
      qc.invalidateQueries({ queryKey: ["users"] })
      setEditing(null)
    },
    onError: (e: any) => {
      push({
        kind: "error",
        message: isValidation(e) ? "Campos inválidos" : e?.message || "Erro ao atualizar usuário",
      })
    },
  })

  const mutDelete = useMutation({
    mutationFn: (id: number) => deleteUser(id),
    onSuccess: () => {
      push({ kind: "success", message: "Usuário excluído" })
      qc.invalidateQueries({ queryKey: ["users"] })
    },
    onError: (e: any) =>
      push({ kind: "error", message: e?.message || "Erro ao excluir usuário" }),
  })

  function submit(ev: React.FormEvent) {
    ev.preventDefault()
    const payload: UserInput = {
      ...form,
      tenant_id: form.role === "superuser" ? null : form.tenant_id,
    }
    if (editing) mutUpdate.mutate({ id: editing.id as number, input: payload })
    else mutCreate.mutate(payload)
  }

  const rows = data?.data ?? []

  return (
    <div className="grid gap-4">
      {/* Ações */}
      <div className="flex items-center justify-between gap-3">
        <div className="flex gap-2">
          <input
            className="border rounded px-3 py-2"
            placeholder="Buscar por nome/email…"
            value={q}
            onChange={(e) => {
              setQ(e.target.value)
              setPage(1)
            }}
          />
        </div>
        <button className="rounded bg-blue-600 text-white px-3 py-2" onClick={onNew}>
          Novo usuário
        </button>
      </div>

      {/* Form */}
      <form onSubmit={submit} className="grid gap-3 bg-white border rounded-2xl p-4">
        <div className="grid md:grid-cols-2 gap-3">
          <input
            className="border rounded px-3 py-2"
            placeholder="Nome"
            value={form.name}
            onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
            required
          />
          <input
            className="border rounded px-3 py-2"
            placeholder="Email"
            type="email"
            value={form.email}
            onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))}
            required
          />
          <select
            className="border rounded px-3 py-2"
            value={form.role}
            onChange={(e) => setForm((f) => ({ ...f, role: e.target.value as any }))}
          >
            <option value="agent">agent</option>
            <option value="owner">owner</option>
            <option value="superuser">superuser</option>
          </select>

          {/* Tenant obrigatório exceto superuser */}
          <select
            className="border rounded px-3 py-2"
            value={form.tenant_id ?? ""}
            onChange={(e) => setForm((f) => ({ ...f, tenant_id: e.target.value || null }))}
            disabled={form.role === "superuser"}
          >
            <option value="">(sem tenant)</option>
            {tenants.map((t) => (
              <option key={t.id} value={t.id}>
                {t.name} ({t.slug})
              </option>
            ))}
          </select>

          {/* Senha: opcional no update; obrigatória no create */}
          <input
            className="border rounded px-3 py-2"
            placeholder={editing ? "Senha (deixar vazio p/ manter)" : "Senha"}
            type="password"
            value={form.password ?? ""}
            onChange={(e) => setForm((f) => ({ ...f, password: e.target.value }))}
            {...(editing ? {} : { required: true })}
          />
        </div>

        <div className="flex gap-2">
          <button type="submit" className="rounded bg-green-600 text-white px-3 py-2">
            {editing ? "Salvar alterações" : "Criar usuário"}
          </button>
          {editing && (
            <button
              type="button"
              className="rounded border px-3 py-2"
              onClick={() => {
                setEditing(null)
                resetForm()
              }}
            >
              Cancelar
            </button>
          )}
        </div>
      </form>

      {/* Lista */}
      <div className="bg-white border rounded-2xl overflow-x-auto">
        {isLoading ? (
          <div className="p-4 text-sm text-gray-600">Carregando…</div>
        ) : rows.length === 0 ? (
          <div className="p-4 text-sm text-gray-600">Nenhum usuário encontrado.</div>
        ) : (
          <table className="min-w-full text-sm">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-4 py-3 text-left">ID</th>
                <th className="px-4 py-3 text-left">Tenant</th>
                <th className="px-4 py-3 text-left">Nome</th>
                <th className="px-4 py-3 text-left">Email</th>
                <th className="px-4 py-3 text-left">Role</th>
                <th className="px-4 py-3" />
              </tr>
            </thead>
            <tbody>
              {rows.map((u) => (
                <tr key={u.id} className="border-t">
                  <td className="px-4 py-3">{u.id}</td>
                  <td className="px-4 py-3">{u.tenant_id ?? "—"}</td>
                  <td className="px-4 py-3">{u.name}</td>
                  <td className="px-4 py-3">{u.email}</td>
                  <td className="px-4 py-3">{u.role}</td>
                  <td className="px-4 py-3 text-right">
                    <div className="flex gap-2 justify-end">
                      <button
                        className="px-2 py-1 rounded border hover:bg-gray-50"
                        onClick={() => onEdit(u)}
                      >
                        Editar
                      </button>
                      <button
                        className="px-2 py-1 rounded border hover:bg-red-50"
                        onClick={() => {
                          if (confirm("Excluir usuário?")) mutDelete.mutate(u.id as number)
                        }}
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

      {/* paginação simples */}
      {data?.meta && data.meta.last_page > 1 && (
        <div className="flex items-center gap-2">
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
  )
}
