import { api } from "../../../lib/apiClient"
import type { ListResponse } from "../../vehicles/types"

// Tenants: id(uuid), name, slug (subdomínio/X-Tenant) :contentReference[oaicite:8]{index=8}
export type Tenant = { id: string; name: string; slug: string; created_at?: string; updated_at?: string }
export type TenantPayload = { name: string; slug: string }

export async function listTenants(params: { page?: number; q?: string }) {
  const { data } = await api.get<ListResponse<Tenant>>("/tenants", { params })
  return data
}
export async function getTenant(id: string) {
  const { data } = await api.get<{ data: Tenant }>(`/tenants/${id}`)
  return data.data
}
export async function createTenant(payload: TenantPayload) {
  const { data } = await api.post<{ data: Tenant }>("/tenants", payload)
  return data.data
}
export async function updateTenant(id: string, payload: TenantPayload) {
  const { data } = await api.put<{ data: Tenant }>(`/tenants/${id}`, payload)
  return data.data
}
export async function deleteTenant(id: string) {
  await api.delete(`/tenants/${id}`)
}
