import React from "react"
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { Link, useNavigate, useParams } from "react-router-dom"
import { createTenant, deleteTenant, getTenant, updateTenant, type Tenant, type TenantPayload } from "./api"
import type { ApiError } from "../../../lib/apiClient"
import { FormField } from "../../../shared/FormField"
import { useToast } from "../../../ui/Toast"
import { useConfirm } from "../../../ui/ConfirmDialog"

export default function TenantFormPage({ mode }: { mode:"create"|"edit" }) {
  const { id } = useParams()
  const isEdit = mode==="edit"
  const nav = useNavigate()
  const qc = useQueryClient()
  const { push } = useToast()
  const { confirm, ui } = useConfirm()

  const { data: existing } = useQuery<Tenant>({ queryKey:["admin-tenant",id], queryFn:()=>getTenant(id!), enabled:isEdit && !!id })

  const [payload, setPayload] = React.useState<TenantPayload>({ name:"", slug:"" })
  const [errors, setErrors] = React.useState<Record<string,string|string[]>>({})

  React.useEffect(()=>{ if(existing){ setPayload({ name: existing.name, slug: existing.slug }) } }, [existing])

  function set<K extends keyof TenantPayload>(k:K, v:TenantPayload[K]){ setPayload(p=>({ ...p, [k]:v })) }

  const upsert = useMutation({
    mutationFn: async ()=> isEdit && id ? updateTenant(id, payload) : createTenant(payload),
    onSuccess: ()=>{ setErrors({}); qc.invalidateQueries({queryKey:["admin-tenants"]}); push({kind:"success", message:isEdit?"Tenant atualizado.":"Tenant criado."}); nav("/admin/tenants") },
    onError: (e:any)=>{ const err=e as ApiError; setErrors((err.errors as any)||{}); push({kind:"error", message:err.message||"Falha ao salvar tenant."}) }
  })

  const del = useMutation({
    mutationFn: async ()=>{ if(id) await deleteTenant(id) },
    onSuccess: ()=>{ qc.invalidateQueries({queryKey:["admin-tenants"]}); push({kind:"success", message:"Tenant excluído."}); nav("/admin/tenants") },
    onError: (e:any)=> push({kind:"error", message:(e as ApiError).message||"Falha ao excluir."})
  })

  return (
    <section className="grid gap-4">
      {ui}
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{isEdit? "Editar tenant":"Novo tenant"}</h1>
        <div className="flex gap-2">
          {isEdit && <button onClick={()=>confirm({ title:"Excluir tenant", message:"Confirmar exclusão?", onConfirm:()=>del.mutate() })} className="rounded border px-3 py-2 text-red-600">Excluir</button>}
          <Link to="/admin/tenants" className="rounded border px-3 py-2">Voltar</Link>
        </div>
      </header>

      <form onSubmit={(e)=>{e.preventDefault(); setErrors({}); upsert.mutate()}} className="grid gap-3 bg-white p-4 rounded-2xl border max-w-xl">
        <FormField label="Nome" htmlFor="name" error={errors["name"]}><input id="name" value={payload.name} onChange={(e)=>set("name", e.target.value)} className="rounded border px-3 py-2"/></FormField>
        <FormField label="Slug" htmlFor="slug" error={errors["slug"]}><input id="slug" value={payload.slug} onChange={(e)=>set("slug", e.target.value)} className="rounded border px-3 py-2"/></FormField>

        <div className="flex gap-2">
          <button type="submit" className="rounded bg-blue-600 text-white px-4 py-2">{upsert.isPending?"Salvando…":"Salvar"}</button>
          <Link to="/admin/tenants" className="rounded border px-4 py-2">Cancelar</Link>
        </div>
      </form>
    </section>
  )
}
