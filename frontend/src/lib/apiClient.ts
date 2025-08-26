import axios, { AxiosError } from "axios";
import type { AxiosRequestConfig } from "axios";

export type ApiError = {
  status?: number;
  code?: string;
  message: string;
  errors?: Record<string, string[] | string>;
  retryAfter?: number;
};

const baseURL = import.meta.env.VITE_API_URL as string;
if (!baseURL) {
  console.warn(" VITE_API_URL não definido");
}

/** ===== Auth storage ===== */
const TOKEN_KEY = "token";
const TENANT_KEY = "tenant";

export function getAuth() {
  return {
    token: localStorage.getItem(TOKEN_KEY) || "",
    tenant:
      localStorage.getItem(TENANT_KEY) ||
      (import.meta.env.VITE_TENANT as string) ||
      "",
  };
}

export function setAuth(token: string, tenant?: string) {
  localStorage.setItem(TOKEN_KEY, token);
  if (tenant) localStorage.setItem(TENANT_KEY, tenant);
}

export function clearAuth() {
  localStorage.removeItem(TOKEN_KEY);
  // não removo tenant por padrão; comente a linha abaixo se preferir manter
  // localStorage.removeItem(TENANT_KEY);
}

/** ===== Axios instance ===== */
export const api = axios.create({
  baseURL,
  headers: { Accept: "application/json" },
});

/** Anexa Authorization + X-Tenant a cada request */
api.interceptors.request.use((cfg) => {
  const { token, tenant } = getAuth();

  cfg.headers = cfg.headers ?? {};

  if (token) (cfg.headers as any).Authorization = `Bearer ${token}`;
  if (tenant) (cfg.headers as any)["X-Tenant"] = tenant;

  return cfg;
});

/** Normaliza erros em ApiError */
api.interceptors.response.use(
  (res) => res,
  (err: AxiosError<any>): Promise<never> => {
    const status = err.response?.status;
    const data = err.response?.data as any;
    const retry = Number(err.response?.headers?.["retry-after"]);
    const apiErr: ApiError = {
      status,
      code: data?.code || data?.error || "UNKNOWN_ERROR",
      message: data?.message || err.message || "Erro inesperado",
      errors: data?.errors || data?.details || undefined,
      retryAfter: Number.isFinite(retry) ? retry : undefined,
    };
    return Promise.reject(apiErr);
  }
);

/** Helpers opcionais para chamadas diretas com headers forçados */
export async function $get<T = any>(url: string, cfg?: AxiosRequestConfig) {
  return api.get<T>(url, cfg).then((r) => r.data);
}
export async function $post<T = any>(url: string, body?: any, cfg?: AxiosRequestConfig) {
  return api.post<T>(url, body, cfg).then((r) => r.data);
}
export async function $put<T = any>(url: string, body?: any, cfg?: AxiosRequestConfig) {
  return api.put<T>(url, body, cfg).then((r) => r.data);
}
export async function $del<T = any>(url: string, cfg?: AxiosRequestConfig) {
  return api.delete<T>(url, cfg).then((r) => r.data);
}
