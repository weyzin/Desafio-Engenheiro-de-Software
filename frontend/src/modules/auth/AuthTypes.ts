export type Role = "superuser" | "owner" | "agent"

export type Me = {
  id: number | string
  name: string
  email: string
  role: Role
  tenant_id: string | null
  tenant?: { id: string; name: string; slug: string } | null
}
