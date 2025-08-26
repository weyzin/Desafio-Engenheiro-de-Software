import React from "react"
import { api, getCsrfCookie, type ApiError } from "../../lib/apiClient"
import { useNavigate } from "react-router-dom"

type User = { id: number | string; name?: string; email: string; tenant_id?: string | null }
type Status = "unknown" | "authenticated" | "anonymous"

type AuthState = { status: Status; user?: User }

const Ctx = React.createContext<{
  user?: User
  status: Status
  login: (email: string, password: string) => Promise<void>
  logout: () => Promise<void>
  refresh: () => Promise<void>
}>({
  status: "unknown",
  login: async () => {},
  logout: async () => {},
  refresh: async () => {},
})

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [state, setState] = React.useState<AuthState>({ status: "unknown" })
  const nav = useNavigate()

  async function refresh() {
    try {
      const { data } = await api.get<{ data: User }>("/me")
      setState({ status: "authenticated", user: data.data })
    } catch {
      setState({ status: "anonymous" })
    }
  }

  // login: só autentica e dispara refresh em background; quem navega é a tela
  async function login(email: string, password: string) {
    try {
      await getCsrfCookie()
      await api.post("/auth/login", { email, password })
      // refresh em background (não bloqueia a navegação)
      refresh().catch(() => {})
    } catch (err) {
      const e = err as ApiError
      throw e
    }
  }

  async function logout() {
    try { await api.post("/auth/logout") } catch {}
    setState({ status: "anonymous" })
    nav("/login", { replace: true })
  }

  return (
    <Ctx.Provider value={{ user: state.user, status: state.status, login, logout, refresh }}>
      {children}
    </Ctx.Provider>
  )
}

export function useAuth(){ return React.useContext(Ctx) }
