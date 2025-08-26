import React from "react"
import { useAuth } from "./AuthContext"
import { Link, useNavigate } from "react-router-dom"
import type { ApiError } from "../../lib/apiClient"

export default function LoginPage() {
  const { login } = useAuth()
  const nav = useNavigate()

  const [email, setEmail] = React.useState("owner@acme.com")
  const [password, setPassword] = React.useState("")
  const [show, setShow] = React.useState(false)
  const [loading, setLoading] = React.useState(false)
  const [formError, setFormError] = React.useState<string | null>(null)

  function humanizeError(err: ApiError | any): string {
    const status = err?.status
    const code = (err?.code || "").toString().toUpperCase()
    if (status === 401 && (code.includes("INVALID") || code.includes("CREDENTIAL"))) {
      return "E-mail ou senha incorretos."
    }
    if (status === 429) {
      const s = err?.retryAfter ? ` Aguarde ~${err.retryAfter}s e tente novamente.` : ""
      return "Muitas tentativas." + s
    }
    if (status === 403) return "Acesso negado."
    if (status === 404) return "Recurso não encontrado."
    if (status === 422) return "Dados inválidos. Verifique os campos."
    return err?.message || "Falha no login."
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    setFormError(null)
    try {
      await login(email, password)
      // sucesso: sem toast; só navega
      nav("/vehicles", { replace: true })
    } catch (err: any) {
      setFormError(humanizeError(err))
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen grid place-items-center p-6">
      <div className="w-full max-w-sm">
        {/* Banner de erro centralizado */}
        {formError && (
          <div
            role="alert"
            className="mb-4 rounded-2xl border border-red-300 bg-red-50 text-red-800 px-4 py-3 text-sm"
          >
            {formError}
          </div>
        )}

        <form
          onSubmit={onSubmit}
          className="bg-neutral-900 text-neutral-100 w-full rounded-2xl p-6 grid gap-4"
          aria-busy={loading}
        >
          <h1 className="text-xl font-semibold">Entrar</h1>

          <div className="grid gap-1">
            <label htmlFor="email" className="text-sm opacity-80">E-mail</label>
            <input
              id="email"
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="rounded px-3 py-2 bg-neutral-800 border border-neutral-700"
              autoComplete="username"
            />
          </div>

          <div className="grid gap-1">
            <label htmlFor="password" className="text-sm opacity-80">Senha</label>
            <div className="flex items-stretch">
              <input
                id="password"
                type={show ? "text" : "password"}
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="rounded-l px-3 py-2 bg-neutral-800 border border-neutral-700 flex-1"
                autoComplete="current-password"
              />
              <button
                type="button"
                onClick={() => setShow(v => !v)}
                className="rounded-r px-3 py-2 bg-neutral-800 border border-neutral-700 border-l-0 text-sm"
                aria-pressed={show}
                aria-label={show ? "Ocultar senha" : "Mostrar senha"}
                title={show ? "Ocultar senha" : "Mostrar senha"}
              >
                {show ? "Ocultar" : "Mostrar"}
              </button>
            </div>
          </div>

          <div className="flex items-center justify-between text-sm">
            <Link to="/forgot" className="underline underline-offset-2 text-blue-300 hover:text-blue-200">
              Esqueci minha senha
            </Link>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="mt-2 rounded bg-blue-600 text-white px-4 py-2 hover:bg-blue-700 disabled:opacity-50 w-full"
          >
            {loading ? "Entrando..." : "Entrar"}
          </button>
        </form>
      </div>
    </div>
  )
}
