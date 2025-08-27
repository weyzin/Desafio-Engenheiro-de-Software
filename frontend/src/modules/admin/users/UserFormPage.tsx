import React from "react"
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { Link, useNavigate, useParams } from "react-router-dom"


import {
  createUser, deleteUser, getUser, updateUser,
  type User, type UserPayload
} from "./api"

import { listTenants } from "../tenants/api"

import type { ApiError } from "../../../lib/apiClient"
import { FormField } from "../../../shared/FormField"
import { useToast } from "../../../ui/Toast"
import { useConfirm } from "../../../ui/ConfirmDialog"

export default function UserFormPage({ mode }: { mode: "create" | "edit" }) {
  const { id } = useParams()
  const isEdit = mode === "edit"
  const nav = useNavigate()
  const qc = useQueryClient()
  const { push } = useToast()
  const { confirm, ui } = useConfirm()

  // dados existentes (se editar)
  const { data: existing } = useQuery<User>({
    queryKey: ["admin-user", id],
    queryFn: () => getUser(id!),
    enabled: isEdit && !!id,
  })

  // tenants para o select
  const { data: tenantsResp } = useQuery({
    queryKey: ["admin-tenants-all"],             
    queryFn: () => listTenants({ per_page: 999 }) 
  })
  const tenants = tenantsResp?.data ?? []

  const [payload, setPayload] = React.useState<UserPayload>({
    tenant_id: null,
    name: "",
    email: "",
    role: "agent",
    password: "",
  })
  const [errors, setErrors] = React.useState<Record<string, string | string[]>>({})
  const [showPass, setShowPass] = React.useState(false)

  React.useEffect(() => {
    if (existing) {
      setPayload({
        tenant_id: existing.role === "superuser" ? null : (existing.tenant_id ?? null),
        name: existing.name,
        email: existing.email,
        role: existing.role,
      } as UserPayload)
    }
  }, [existing])

  function set<K extends keyof UserPayload>(k: K, v: UserPayload[K]) {
    setPayload((p) => ({ ...p, [k]: v }))
  }

  const upsert = useMutation({
    mutationFn: async () =>
      isEdit && id ? updateUser(id, payload) : createUser(payload),
    onSuccess: () => {
      setErrors({})
      qc.invalidateQueries({ queryKey: ["admin-users"] })
      push({ kind: "success", message: isEdit ? "Usuário atualizado." : "Usuário criado." })
      nav("/admin/users")
    },
    onError: (e: any) => {
      const err = e as ApiError
      setErrors((err.errors as any) || {})
      push({ kind: "error", message: err.message || "Falha ao salvar usuário.", code: err.code })
    },
  })

  const del = useMutation({
    mutationFn: async () => {
      if (id) await deleteUser(id)
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["admin-users"] })
      push({ kind: "success", message: "Usuário excluído." })
      nav("/admin/users")
    },
    onError: (e: any) => push({ kind: "error", message: (e as ApiError).message || "Falha ao excluir.", code: e?.code }),
  })

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    setErrors({})
    // regra: tenant é obrigatório exceto superuser
    if (payload.role !== "superuser" && !payload.tenant_id) {
      setErrors((prev) => ({ ...prev, tenant_id: "Selecione um tenant." }))
      return
    }
    // regra: senha obrigatória na criação
    if (!isEdit && (!payload.password || payload.password.length < 8)) {
      setErrors((prev) => ({ ...prev, password: "A senha deve ter ao menos 8 caracteres." }))
      return
    }
    upsert.mutate()
  }

  return (
    <section className="grid gap-4">
      {ui}
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{isEdit ? "Editar usuário" : "Novo usuário"}</h1>
        <div className="flex gap-2">
          {isEdit && (
            <button
              onClick={() =>
                confirm({
                  title: "Excluir usuário",
                  message: "Confirmar exclusão?",
                  onConfirm: () => del.mutate(),
                })
              }
              className="rounded border px-3 py-2 text-red-600"
            >
              Excluir
            </button>
          )}
          <Link to="/admin/users" className="rounded border px-3 py-2">
            Voltar
          </Link>
        </div>
      </header>

      <form onSubmit={submit} className="grid gap-3 bg-white p-4 rounded-2xl border max-w-xl">
        <FormField label="Nome" htmlFor="name" error={errors["name"]}>
          <input
            id="name"
            value={payload.name}
            onChange={(e) => set("name", e.target.value)}
            className="rounded border px-3 py-2 w-full"
            required
          />
        </FormField>

        <FormField label="E-mail" htmlFor="email" error={errors["email"]}>
          <input
            id="email"
            type="email"
            value={payload.email}
            onChange={(e) => set("email", e.target.value)}
            className="rounded border px-3 py-2 w-full"
            required
          />
        </FormField>

        <FormField
          label="Senha (apenas criar/trocar)"
          htmlFor="password"
          error={errors["password"]}
        >
          <div className="relative">
            <input
              id="password"
              type={showPass ? "text" : "password"}
              value={payload.password ?? ""}
              onChange={(e) => set("password", e.target.value)}
              className="rounded border px-3 py-2 pr-16 w-full"
              {...(isEdit ? {} : { required: true, minLength: 8 })}
            />
            <button
              type="button"
              onClick={() => setShowPass((s) => !s)}
              className="absolute right-2 top-1/2 -translate-y-1/2 text-sm opacity-70 hover:opacity-100"
            >
              {showPass ? "Ocultar" : "Mostrar"}
            </button>
          </div>
        </FormField>

        <FormField label="Role" htmlFor="role" error={errors["role"]}>
          <select
            id="role"
            value={payload.role}
            onChange={(e) => set("role", e.target.value as any)}
            className="rounded border px-3 py-2 w-full"
          >
            <option value="agent">agent</option>
            <option value="owner">owner</option>
            <option value="superuser">superuser</option>
          </select>
        </FormField>

        <FormField
          label="Tenant (obrigatório exceto superuser)"
          htmlFor="tenant_id"
          error={errors["tenant_id"]}
        >
          <select
            id="tenant_id"
            className="rounded border px-3 py-2 w-full"
            value={payload.role === "superuser" ? "" : (payload.tenant_id ?? "")}
            onChange={(e) => set("tenant_id", e.target.value ? e.target.value : null)}
            disabled={payload.role === "superuser"}
            required={payload.role !== "superuser"}
          >
            <option value="">
              {payload.role === "superuser" ? "(superuser global)" : "(selecione um tenant)"}
            </option>
            {tenants.map((t) => (
              <option key={t.id} value={t.id}>
                {t.name} — {t.slug} — {t.id}
              </option>
            ))}
          </select>
        </FormField>

        <div className="flex gap-2">
          <button type="submit" className="rounded bg-blue-600 text-white px-4 py-2">
            {upsert.isPending ? "Salvando…" : "Salvar"}
          </button>
          <Link to="/admin/users" className="rounded border px-4 py-2">
            Cancelar
          </Link>
        </div>
      </form>
    </section>
  )
}
