import React from "react"
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { Link, useNavigate, useParams } from "react-router-dom"
import { createUser, deleteUser, getUser, updateUser, type User, type UserPayload } from "./api"
import type { ApiError } from "../../../lib/apiClient"
import { FormField } from "../../../shared/FormField"
import { useToast } from "../../../ui/Toast"
import { useConfirm } from "../../../ui/ConfirmDialog"

export default function UserFormPage({ mode }: { mode:"create"|"edit" }) {
  const { id } = useParams()
  const isEdit = mode==="edit"
  const nav = useNavigate()
  const qc = useQueryClient()
  const { push } = useToast()
  const { confirm, ui } = useConfirm()

  const { data: existing } = useQuery<User>({ queryKey:["admin-user",id], queryFn:()=>getUser(id!), enabled:isEdit && !!id })

  const [payload, setPayload] = React.useState<UserPayload>({ tenant_id: null, name:"", email:"", role:"agent", password:"" })
  const [errors, setErrors] = React.useState<Record<string,string|string[]>>({})

  React.useEffect(()=>{ if(existing){ setPayload({ tenant_id: existing.tenant_id ?? null, name:existing.name, email:existing.email, role:existing.role }) } },[existing])

  function set<K extends keyof UserPayload>(k:K, v:UserPayload[K]){ setPayload(p=>({ ...p, [k]:v })) }

  const upsert = useMutation({
    mutationFn: async ()=> isEdit && id ? updateUser(id, payload) : createUser(payload),
    onSuccess: ()=>{ setErrors({}); qc.invalidateQueries({queryKey:["admin-users"]}); push({kind:"success", message:isEdit?"Usuário atualizado.":"Usuário criado."}); nav("/admin/users") },
    onError: (e:any)=>{ const err=e as ApiError; setErrors((err.errors as any)||{}); push({kind:"error", message:err.message||"Falha ao salvar usuário."}) }
  })
  const del = useMutation({
    mutationFn: async ()=>{ if(id) await deleteUser(id) },
    onSuccess: ()=>{ qc.invalidateQueries({queryKey:["admin-users"]}); push({kind:"success", message:"Usuário excluído."}); nav("/admin/users") },
    onError: (e:any)=> push({kind:"error", message:(e as ApiError).message||"Falha ao excluir."})
  })

  return (
    <section className="grid gap-4">
      {ui}
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{isEdit? "Editar usuário":"Novo usuário"}</h1>
        <div className="flex gap-2">
          {isEdit && <button onClick={()=>confirm({ title:"Excluir usuário", message:"Confirmar exclusão?", onConfirm:()=>del.mutate() })} className="rounded border px-3 py-2 text-red-600">Excluir</button>}
          <Link to="/admin/users" className="rounded border px-3 py-2">Voltar</Link>
        </div>
      </header>

      <form onSubmit={(e)=>{e.preventDefault(); setErrors({}); upsert.mutate()}} className="grid gap-3 bg-white p-4 rounded-2xl border max-w-xl">
        <FormField label="Nome" htmlFor="name" error={errors["name"]}><input id="name" value={payload.name} onChange={(e)=>set("name", e.target.value)} className="rounded border px-3 py-2"/></FormField>
        <FormField label="E-mail" htmlFor="email" error={errors["email"]}><input id="email" type="email" value={payload.email} onChange={(e)=>set("email", e.target.value)} className="rounded border px-3 py-2"/></FormField>
        <FormField label="Senha (apenas criar/trocar)" htmlFor="password" error={errors["password"]}><input id="password" type="password" value={payload.password ?? ""} onChange={(e)=>set("password", e.target.value)} className="rounded border px-3 py-2"/></FormField>
        <FormField label="Role" htmlFor="role" error={errors["role"]}>
          <select id="role" value={payload.role} onChange={(e)=>set("role", e.target.value as any)} className="rounded border px-3 py-2">
            <option value="agent">agent</option><option value="owner">owner</option><option value="superuser">superuser</option>
          </select>
        </FormField>
        <FormField label="Tenant ID (vazio = global)" htmlFor="tenant_id" error={errors["tenant_id"]}>
          <input id="tenant_id" value={payload.tenant_id ?? ""} onChange={(e)=>set("tenant_id", e.target.value || null)} className="rounded border px-3 py-2"/>
        </FormField>

        <div className="flex gap-2">
          <button type="submit" className="rounded bg-blue-600 text-white px-4 py-2">{upsert.isPending?"Salvando…":"Salvar"}</button>
          <Link to="/admin/users" className="rounded border px-4 py-2">Cancelar</Link>
        </div>
      </form>
    </section>
  )
}
