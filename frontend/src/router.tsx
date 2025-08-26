import { createBrowserRouter, Navigate, Outlet } from "react-router-dom"
import { Protected } from "./shared/Protected"
import LoginPage from "./modules/auth/LoginPage"
import { AuthProvider } from "./modules/auth/AuthContext"
import Layout from "./shared/Layout"
import { ToastProvider } from "./ui/Toast"

function Providers() {
  return (
    <ToastProvider>
      <AuthProvider>
        <Outlet />
      </AuthProvider>
    </ToastProvider>
  )
}

export const router = createBrowserRouter([
  {
    element: <Providers />,
    children: [
      { path: "/", element: <Navigate to="/vehicles" replace /> },
      { path: "/login", element: <LoginPage /> },
      { path: "/forgot", element: <div className="p-6">Entre em contato com o suporte para redefinir sua senha.</div> },

      {
        element: (
          <Protected>
            <Layout />
          </Protected>
        ),
        children: [
          { path: "/vehicles", element: <div>TODO VehiclesList</div> },
          { path: "/vehicles/new", element: <div>TODO VehicleForm (create)</div> },
          { path: "/vehicles/:id", element: <div>TODO VehicleForm (edit)</div> },
          { path: "/profile", element: <div>TODO ProfilePage</div> },
        ],
      },

      { path: "*", element: <div className="p-8">Página não encontrada.</div> },
    ],
  },
])
