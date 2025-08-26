import axios, { AxiosError } from "axios"

export type ApiError = {
  status?: number
  code?: string
  message: string
  errors?: Record<string, string[] | string>
  retryAfter?: number
}

const baseURL = import.meta.env.VITE_API_URL as string
if (!baseURL) {
  console.warn("⚠️ VITE_API_URL não definido")
}

export const api = axios.create({
  baseURL,
  withCredentials: true,
  xsrfCookieName: "XSRF-TOKEN",
  xsrfHeaderName: "X-XSRF-TOKEN",
  headers: { Accept: "application/json" },
})

// Adiciona X-Tenant só em dev
api.interceptors.request.use((cfg) => {
  const tenant = import.meta.env.VITE_TENANT
  if (tenant) cfg.headers.set("X-Tenant", tenant)
  return cfg
})

// Normaliza erros
api.interceptors.response.use(
  (res) => res,
  (err: AxiosError<any>): Promise<never> => {
    const status = err.response?.status
    const data = err.response?.data as any
    const retry = Number(err.response?.headers?.["retry-after"])
    const apiErr: ApiError = {
      status,
      code: data?.code || data?.error || "UNKNOWN_ERROR",
      message: data?.message || err.message || "Erro inesperado",
      errors: data?.errors || data?.details || undefined,
      retryAfter: Number.isFinite(retry) ? retry : undefined,
    }
    return Promise.reject(apiErr)
  }
)

// origem para chamar /sanctum/csrf-cookie (sem /api/v1)
const apiOrigin = (() => {
  try { return new URL(baseURL).origin } catch { return "" }
})()

let csrfFetched = false
export async function getCsrfCookie() {
  if (!apiOrigin) return
  if (csrfFetched) return                // evita chamadas repetidas
  await axios.get(`${apiOrigin}/sanctum/csrf-cookie`, {
    withCredentials: true,
    headers: { Accept: "application/json" },
  })
  csrfFetched = true
}
