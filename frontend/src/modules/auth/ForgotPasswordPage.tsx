import type { FormEvent } from "react";
import { useState } from "react";
import { api, getCsrfCookie } from "../../lib/apiClient";

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState(''); 
  const [loading, setLoading] = useState(false);
  const [done, setDone] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      await getCsrfCookie();
      await api.post("/auth/forgot", { email });
      setDone(true);
    } catch (err: any) {
      setError(err?.message ?? "Falha ao enviar e-mail.");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="max-w-md mx-auto p-6">
      <h1 className="text-xl font-semibold mb-4">Esqueci minha senha</h1>

      {done ? (
        <div className="rounded bg-emerald-900/30 border border-emerald-700 p-4">
          Se o e-mail existir, você receberá um link para redefinir a senha.
        </div>
      ) : (
        <form onSubmit={onSubmit} className="space-y-4">
          <label className="block text-sm">
            E-mail
            <input
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="mt-1 w-full rounded-md bg-neutral-900 border border-neutral-700 px-3 py-2 outline-none focus:border-blue-500"
              placeholder="seu@email.com"
            />
          </label>

          {error && (
            <div className="rounded bg-red-900/30 border border-red-700 p-3 text-sm">
              {error}
            </div>
          )}

          <button
            type="submit"
            disabled={loading}
            className="w-full rounded-md bg-blue-600 hover:bg-blue-700 disabled:opacity-60 px-4 py-2"
          >
            {loading ? "Enviando..." : "Enviar link de redefinição"}
          </button>
        </form>
      )}
    </div>
  );
}
