import { Link, NavLink, Outlet } from "react-router-dom"
import { useAuth } from "../modules/auth/AuthContext"

export default function Layout() {
  const { user, logout } = useAuth()

  return (
    <div className="min-h-screen grid grid-rows-[auto_1fr]">
      <header className="bg-white/80 backdrop-blur shadow">
        <div className="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
          <Link to="/vehicles" className="font-semibold tracking-tight">
            Desafio
          </Link>
          <nav className="flex items-center gap-6 text-sm">
            <NavLink
              to="/vehicles"
              className={({ isActive }) =>
                isActive ? "text-blue-700 font-medium" : "text-gray-700"
              }
            >
              Veículos
            </NavLink>
            <NavLink
              to="/profile"
              className={({ isActive }) =>
                isActive ? "text-blue-700 font-medium" : "text-gray-700"
              }
            >
              Perfil
            </NavLink>
            <div className="h-5 w-px bg-gray-200" aria-hidden />
            <span className="text-gray-700" aria-live="polite">
              {user?.name ?? user?.email}
            </span>
            <button className="text-red-600 hover:underline" onClick={logout}>
              Sair
            </button>
          </nav>
        </div>
      </header>

      <main className="max-w-6xl mx-auto w-full px-4 py-6">
        <Outlet />
      </main>
    </div>
  )
}
