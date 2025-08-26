import React from "react";
import { api, getCsrfCookie, type ApiError } from "../../lib/apiClient";
import { useNavigate } from "react-router-dom";
import type { Me, Role } from "./AuthTypes";

// helpers que você adicionou no apiClient.ts
import { getAuth, setAuth, clearAuth } from "../../lib/apiClient";

type Status = "unknown" | "authenticated" | "anonymous";

type AuthState = { status: Status; user: Me | null };

const Ctx = React.createContext<{
  user: Me | null;
  status: Status;
  login: (tenant: string, email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  refresh: () => Promise<void>;
  hasRole: (role: Role) => boolean;
  hasAnyRole: (roles: Role[]) => boolean;
}>({
  user: null,
  status: "unknown",
  login: async () => {},
  logout: async () => {},
  refresh: async () => {},
  hasRole: () => false,
  hasAnyRole: () => false,
});

function normalizeRole(r: unknown): Role {
  const v = String(r ?? "").toLowerCase();
  return (["superuser", "owner", "agent"].includes(v) ? (v as Role) : "agent");
}

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [state, setState] = React.useState<AuthState>({ status: "unknown", user: null });
  const nav = useNavigate();

  async function refresh() {
    try {
      // só tenta se existir token
      const { token } = getAuth();
      if (!token) {
        setState({ status: "anonymous", user: null });
        return;
      }

      const { data } = await api.get<{ data: Me }>("/me");
      const user = { ...data.data, role: normalizeRole((data.data as any).role) };
      setState({ status: "authenticated", user });
    } catch {
      setState({ status: "anonymous", user: null });
    }
  }

  async function login(tenant: string, email: string, password: string) {
    try {
      // garante que essa request de login use o tenant escolhido;
      // se vazio => remove X-Tenant (para superuser)
      setAuth(undefined, tenant?.trim() ? tenant.trim() : null);

      await getCsrfCookie();
      const res = await api.post("/auth/login", { email, password });

      // salva token + tenant ativo informado pelo backend (ou o digitado)
      const token: string | undefined = res?.data?.data?.token;
      const activeTenant: string | null =
        res?.data?.data?.user?.active_tenant ?? (tenant?.trim() || null);

      if (token) setAuth(token, activeTenant);
      await refresh();
    } catch (err) {
      // falhou? limpa token/tenant para não “grudar” header
      clearAuth();
      throw err as ApiError;
    }
  }

  async function logout() {
    try {
      await api.post("/auth/logout");
    } catch {}
    clearAuth();
    setState({ status: "anonymous", user: null });
    nav("/login", { replace: true });
  }

  function hasRole(role: Role) {
    return state.user?.role === role;
  }
  function hasAnyRole(roles: Role[]) {
    return !!state.user && roles.includes(state.user.role);
  }

  // monta já respeitando o token salvo (sem bater /me à toa)
  React.useEffect(() => {
    const { token } = getAuth();
    if (token) refresh().catch(() => {});
    else setState({ status: "anonymous", user: null });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Ctx.Provider value={{ user: state.user, status: state.status, login, logout, refresh, hasRole, hasAnyRole }}>
      {children}
    </Ctx.Provider>
  );
}

export function useAuth() {
  return React.useContext(Ctx);
}
