import axios, { AxiosError } from "axios";

export type ApiError = {
  status?: number;
  code?: string;
  message: string;
  errors?: Record<string, string[] | string>;
  retryAfter?: number;
};

const baseURL = import.meta.env.VITE_API_URL as string;
if (!baseURL) console.warn("⚠️ VITE_API_URL não definido");

// ===== Auth storage (token & tenant) =====
const TOKEN_KEY  = "token";
const TENANT_KEY = "tenant";

// getters/setters individuais (mantidos p/ compatibilidade)
export function getToken(): string | null {
  return localStorage.getItem(TOKEN_KEY);
}
export function setToken(token: string) {
  localStorage.setItem(TOKEN_KEY, token);
}
export function clearToken() {
  localStorage.removeItem(TOKEN_KEY);
}

/**
 * ⚠️ Importante: não herdamos mais o tenant do .env aqui.
 * O tenant ativo é sempre o que estiver no localStorage
 * (definido na tela de login). Isso evita “grudar” X-Tenant.
 */
export function getTenant(): string | null {
  return localStorage.getItem(TENANT_KEY);
}
export function setTenant(slug: string) {
  localStorage.setItem(TENANT_KEY, slug);
}
export function clearTenant() {
  localStorage.removeItem(TENANT_KEY);
}

/** Helpers combinados usados pelo AuthContext */
export function getAuth(): { token: string | null; tenant: string | null } {
  return { token: getToken(), tenant: getTenant() };
}

/**
 * setAuth:
 * - token: se vier string, salva; se vier undefined, mantém; se vier null, remove.
 * - tenant: se vier string, salva; se vier undefined, mantém; se vier null, remove.
 */
export function setAuth(token?: string | null, tenant?: string | null) {
  if (token === null) clearToken();
  else if (typeof token === "string") setToken(token);

  if (tenant === null) clearTenant();
  else if (typeof tenant === "string" && tenant.trim()) setTenant(tenant.trim());
}

export function clearAuth() {
  clearToken();
  clearTenant();
}

// ===== Axios =====
export const api = axios.create({
  baseURL,
  withCredentials: true,
  xsrfCookieName: "XSRF-TOKEN",
  xsrfHeaderName: "X-XSRF-TOKEN",
  headers: { Accept: "application/json" },
});

// Anexa Authorization e X-Tenant dinamicamente
api.interceptors.request.use((cfg) => {
  const { token, tenant } = getAuth();
  if (token)  cfg.headers.set("Authorization", `Bearer ${token}`);
  // só envia X-Tenant se houver (root sem tenant não manda header)
  if (tenant) cfg.headers.set("X-Tenant", tenant);
  else        cfg.headers.delete?.("X-Tenant");
  return cfg;
});

// Normaliza erros
api.interceptors.response.use(
  (res) => res,
  (err: AxiosError<any>): Promise<never> => {
    const status = err.response?.status;
    const data   = err.response?.data as any;
    const retry  = Number(err.response?.headers?.["retry-after"]);
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

// Origem p/ CSRF (se precisar)
const apiOrigin = (() => {
  try { return new URL(baseURL).origin; } catch { return ""; }
})();

let csrfFetched = false;
export async function getCsrfCookie() {
  if (!apiOrigin) return;
  if (csrfFetched) return;
  await axios.get(`${apiOrigin}/sanctum/csrf-cookie`, {
    withCredentials: true,
    headers: { Accept: "application/json" },
  });
  csrfFetched = true;
}
