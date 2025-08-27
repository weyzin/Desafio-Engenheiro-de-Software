import { Link } from "react-router-dom";

export default function AdminHome() {
  return (
    <section className="p-6 grid gap-4">
      <h1 className="text-xl font-semibold">Administração</h1>
      <div className="grid sm:grid-cols-2 gap-4 max-w-3xl">
        <Link
          to="/admin/tenants"
          className="rounded-2xl border p-6 bg-white hover:bg-gray-50"
        >
          <div className="text-lg font-medium">Tenants</div>
          <div className="text-sm text-gray-600">Gerenciar organizações</div>
        </Link>
        <Link
          to="/admin/users"
          className="rounded-2xl border p-6 bg-white hover:bg-gray-50"
        >
          <div className="text-lg font-medium">Usuários</div>
          <div className="text-sm text-gray-600">Gerenciar contas</div>
        </Link>
      </div>
    </section>
  );
}
