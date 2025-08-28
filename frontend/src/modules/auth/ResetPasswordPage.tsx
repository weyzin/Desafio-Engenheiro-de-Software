import type { FormEvent } from "react";
import { useState, useMemo } from "react";
import { useSearchParams, useNavigate } from "react-router-dom";
import { api, getCsrfCookie } from "../../lib/apiClient";

export default function ResetPasswordPage() {
  const [params] = useSearchParams();
  const navigate = useNavigate();

  const token = useMemo(() => params.get("token") ?? "", [params]);
  const email = useMemo(() => params.get("email") ?? "", [params]);

  const [password, setPassword] = useState("");
  const [confirm, setConfirm] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [ok, setOk] = useState(false);

  const disabled =
    !token || !email || password.length < 8 || password !== confirm || loading;

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setError(null);

    if (!token || !email) {
      setError("Link inválido ou incompleto.");
      return;
    }
    if (password !== confirm) {
      setError("As senhas não conferem.");
      return;
    }

    setLoading(true);
    try {
      await getCsrfCookie();
      await api.post("/auth/reset-password", {
        email,
        token,
        password,
        password_confirmation: confirm,
      });
      setOk(true);
      // opcional: redireciona para login após 2s
      setTimeout(() => navigate("/login"), 2000);
    } catch (err: any) {
      const apiMsg =
        err?.response?.data?.message ||
        err?.response?.data?.errors?.token?.[0] ||
        err?.response?.data?.errors?.email?.[0];
        setError(apiMsg ?? "Não foi possível redefinir a senha.");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="max-w-md mx-auto p-6">
      <h1 className="text-xl font-semibold mb-4">Redefinir senha</h1>

      {!token || !email ? (
        <div className="rounded bg-red-900/30 border border-red-700 p-4">
          Link inválido. Solicite novamente em <a className="underline" href="/forgot">/forgot</a>.
        </div>
      ) : ok ? (
        <div className="rounded bg-emerald-900/30 border border-emerald-700 p-4">
          Senha alterada com sucesso. Redirecionando para o login…
        </div>
      ) : (
        <form onSubmit={onSubmit} className="space-y-4">
          <div className="text-sm">
            <div className="opacity-70">E-mail</div>
            <div className="mt-1 px-3 py-2 rounded-md bg-neutral-900 border border-neutral-700 break-all">
              {email}
            </div>
          </div>

          <label className="block text-sm">
            Nova senha
            <input
              type="password"
              required
              minLength={8}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="mt-1 w-full rounded-md bg-neutral-900 border border-neutral-700 px-3 py-2 outline-none focus:border-blue-500"
              placeholder="mínimo 8 caracteres"
            />
          </label>

          <label className="block text-sm">
            Confirmar senha
            <input
              type="password"
              required
              value={confirm}
              onChange={(e) => setConfirm(e.target.value)}
              className="mt-1 w-full rounded-md bg-neutral-900 border border-neutral-700 px-3 py-2 outline-none focus:border-blue-500"
            />
          </label>

          {error && (
            <div className="rounded bg-red-900/30 border border-red-700 p-3 text-sm">
              {error}
            </div>
          )}

          <button
            type="submit"
            disabled={disabled}
            className="w-full rounded-md bg-blue-600 hover:bg-blue-700 disabled:opacity-60 px-4 py-2"
          >
            {loading ? "Salvando..." : "Redefinir senha"}
          </button>
        </form>
      )}
    </div>
  );
}
