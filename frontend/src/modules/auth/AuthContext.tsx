import React from "react"
import { api, type ApiError } from "../../lib/apiClient"
import { useNavigate } from "react-router-dom"
import type { Me, Role } from "./AuthTypes"

type Status = "unknown" | "authenticated" | "anonymous"

type AuthState = { status: Status; user: Me | null }

type LoginFn = (email: string, password: string, tenant?: string) => Promise<void>

const Ctx = React.createContext<{
  user: Me | null
  status: Status
  login: LoginFn
  logout: () => Promise<void>
  refresh: () => Promise<void>
  hasRole: (role: Role) => boolean
  hasAnyRole: (roles: Role[]) => boolean
}>({
  user: null,
  status: "unknown",
  login: async () => {},
  logout: async () => {},
  refresh: async () => {},
  hasRole: () => false,
  hasAnyRole: () => false,
})

function normalizeRole(r: unknown): Role {
  const v = String(r ?? "").toLowerCase()
  return (["superuser", "owner", "agent"].includes(v) ? (v as Role) : "agent")
}

// helpers de storage/header
const TOKEN_KEY = "token"
const TENANT_KEY = "tenant"

function setAuthHeader(token?: string) {
  if (token) api.defaults.headers.common["Authorization"] = `Bearer ${token}`
  else delete api.defaults.headers.common["Authorization"]
}

function setTenantHeader(tenant?: string) {
  if (tenant) api.defaults.headers.common["X-Tenant"] = tenant
  else delete api.defaults.headers.common["X-Tenant"]
}

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [state, setState] = React.useState<AuthState>({ status: "unknown", user: null })
  const nav = useNavigate()

  // bootstrap headers a partir do storage
  React.useEffect(() => {
    const storedTenant = localStorage.getItem(TENANT_KEY) || (import.meta.env.VITE_TENANT as string) || undefined
    const storedToken  = localStorage.getItem(TOKEN_KEY)  || undefined
    setTenantHeader(storedTenant)
    setAuthHeader(storedToken)
    // tenta carregar o /me para definir estado inicial
    refresh().catch(() => setState({ status: "anonymous", user: null }))
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  async function refresh() {
    try {
      const { data } = await api.get<{ data: Me }>("/me")
      const user = { ...data.data, role: normalizeRole((data.data as any).role) }
      setState({ status: "authenticated", user })
    } catch {
      setState({ status: "anonymous", user: null })
    }
  }

  const login: LoginFn = async (email, password, tenant) => {
    try {
      // define/força o tenant no header ANTES do login
      const useTenant = tenant || localStorage.getItem(TENANT_KEY) || (import.meta.env.VITE_TENANT as string) || "acme"
      localStorage.setItem(TENANT_KEY, useTenant)
      setTenantHeader(useTenant)

      // faz o login (stateless) -> { data: { token, token_type, user } }
      const res = await api.post("/auth/login", { email, password })
      const token: string | undefined = res.data?.data?.token

      if (!token) throw { status: 500, message: "Token ausente na resposta de login." } as ApiError

      // persiste token e injeta Authorization para as próximas requisições
      localStorage.setItem(TOKEN_KEY, token)
      setAuthHeader(token)

      // já temos o usuário na resposta; mas pra simplificar usamos /me
      await refresh()
    } catch (err) {
      // limpa token em caso de falha
      localStorage.removeItem(TOKEN_KEY)
      setAuthHeader(undefined)
      throw err as ApiError
    }
  }

  async function logout() {
    try {
      await api.post("/auth/logout").catch(() => {}) // idempotente
    } finally {
      localStorage.removeItem(TOKEN_KEY)
      setAuthHeader(undefined)
      setState({ status: "anonymous", user: null })
      nav("/login", { replace: true })
    }
  }

  function hasRole(role: Role) {
    return state.user?.role === role
  }
  function hasAnyRole(roles: Role[]) {
    return !!state.user && roles.includes(state.user.role)
  }

  return (
    <Ctx.Provider value={{ user: state.user, status: state.status, login, logout, refresh, hasRole, hasAnyRole }}>
      {children}
    </Ctx.Provider>
  )
}

export function useAuth() {
  return React.useContext(Ctx)
}
