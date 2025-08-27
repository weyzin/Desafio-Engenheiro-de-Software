import { api, type ApiError } from "./apiClient"

/** Helpers de paginação usados pelo backend */
export type PageMeta = {
  total: number
  page: number
  per_page: number
  last_page: number
  from?: number
  to?: number
  links?: any
}

export type ListResp<T> = {
  data: T[]
  meta: PageMeta
  links?: { next: string | null; prev: string | null }
}

/** Tipos Tenants */
export type Tenant = {
  id: string
  name: string
  slug: string
  created_at: string
  updated_at: string
}

export type TenantInput = {
  name: string
  slug: string
}

/** Tipos Users */
export type User = {
  id: number
  tenant_id: string | null
  name: string
  email: string
  role: "superuser" | "owner" | "agent"
  created_at: string
  updated_at: string
}

export type UserInput = {
  tenant_id: string | null
  name: string
  email: string
  role: "superuser" | "owner" | "agent"
  /** no update pode ser vazio para manter */
  password?: string
}

/** Discriminador de erro de validação vindo do interceptor */
export function isValidation(e: any): e is ApiError {
  return !!e && e.code === "VALIDATION_ERROR"
}

/* =======================
 * TENANTS
 * ======================= */

export async function listTenants(params: {
  q?: string
  page?: number
  per_page?: number
}): Promise<ListResp<Tenant>> {
  const { data } = await api.get<ListResp<Tenant>>("/tenants", { params })
  return data
}

export async function createTenant(input: TenantInput): Promise<{ data: Tenant }> {
  const { data } = await api.post<{ data: Tenant }>("/tenants", input)
  return data
}

export async function updateTenant(id: string, input: TenantInput): Promise<{ data: Tenant }> {
  const { data } = await api.put<{ data: Tenant }>(`/tenants/${id}`, input)
  return data
}

export async function deleteTenant(id: string): Promise<void> {
  await api.delete(`/tenants/${id}`)
}

/* =======================
 * USERS
 * ======================= */

export async function listUsers(params: {
  q?: string
  page?: number
  per_page?: number
}): Promise<ListResp<User>> {
  const { data } = await api.get<ListResp<User>>("/users", { params })
  return data
}

export async function createUser(input: UserInput): Promise<{ data: User }> {
  const { data } = await api.post<{ data: User }>("/users", input)
  return data
}

export async function updateUser(id: number, input: UserInput): Promise<{ data: User }> {
  const { data } = await api.put<{ data: User }>(`/users/${id}`, input)
  return data
}

export async function deleteUser(id: number): Promise<void> {
  await api.delete(`/users/${id}`)
}
