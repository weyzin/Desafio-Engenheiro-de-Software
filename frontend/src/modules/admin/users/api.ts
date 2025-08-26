import { api } from "../../../lib/apiClient"
import type { ListResponse } from "../../vehicles/types"

// Users seguem a migração + seed (inclui role e tenant_id) :contentReference[oaicite:6]{index=6} :contentReference[oaicite:7]{index=7}
export type User = {
  id: number
  tenant_id: string | null
  name: string
  email: string
  role: "superuser" | "owner" | "agent"
  created_at?: string
  updated_at?: string
}
export type UserPayload = {
  tenant_id?: string | null
  name: string
  email: string
  password?: string
  role: "superuser" | "owner" | "agent"
}

export async function listUsers(params: { page?: number; q?: string }) {
  const { data } = await api.get<ListResponse<User>>("/users", { params })
  return data
}
export async function getUser(id: string | number) {
  const { data } = await api.get<{ data: User }>(`/users/${id}`)
  return data.data
}
export async function createUser(payload: UserPayload) {
  const { data } = await api.post<{ data: User }>("/users", payload)
  return data.data
}
export async function updateUser(id: string | number, payload: UserPayload) {
  const { data } = await api.put<{ data: User }>(`/users/${id}`, payload)
  return data.data
}
export async function deleteUser(id: string | number) {
  await api.delete(`/users/${id}`)
}
