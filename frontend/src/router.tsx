import { createBrowserRouter, Navigate, Outlet } from "react-router-dom"
import { QueryClient, QueryClientProvider } from "@tanstack/react-query"
import { Protected } from "./shared/Protected"
import LoginPage from "./modules/auth/LoginPage"
import { AuthProvider } from "./modules/auth/AuthContext"
import Layout from "./shared/Layout"
import { ToastProvider } from "./ui/Toast"
import VehiclesList from "./modules/vehicles/ListPage"
import VehicleFormPage from "./modules/vehicles/FormPage"
import ProfilePage from "./modules/profile/ProfilePage"
import { RequireRoles } from "./modules/auth/RequireRoles"
import UsersListPage from "./modules/admin/users/UsersListPage.tsx"
import UserFormPage from "./modules/admin/users/UserFormPage.tsx"
import TenantsListPage from "./modules/admin/tenants/TenantsListPage.tsx"
import TenantFormPage from "./modules/admin/tenants/TenantFormPage.tsx"
import AdminHome from "./modules/admin/AdminHome";
import ForgotPasswordPage from "./modules/auth/ForgotPasswordPage";
import ResetPasswordPage from './modules/auth/ResetPasswordPage';

const queryClient = new QueryClient()

function Providers() {
  return (
    <ToastProvider>
      <QueryClientProvider client={queryClient}>
        <AuthProvider>
          <Outlet />
        </AuthProvider>
      </QueryClientProvider>
    </ToastProvider>
  )
}

export const router = createBrowserRouter([
  {
    element: <Providers />,
    children: [
      { path: "/", element: <Navigate to="/vehicles" replace /> },
      { path: "/login", element: <LoginPage /> },
      {
        path: "/forgot",
        element: <ForgotPasswordPage />,
      },
      { path: "/reset-password", 
      element: <ResetPasswordPage /> 
      },
      {
        element: (
          <Protected>
            <Layout />
          </Protected>
        ),
        children: [
          { path: "/vehicles", element: <VehiclesList /> },
          { path: "/vehicles/new", element: <VehicleFormPage mode="create" /> },
          { path: "/vehicles/:id", element: <VehicleFormPage mode="edit" /> },
          { path: "/profile", element: <ProfilePage /> },
        ],
      },

      {
        path: "/admin",
        element: (
          <RequireRoles roles={["superuser"]}>
            <Layout />
          </RequireRoles>
        ),
        children: [
          { index: true, element: <AdminHome /> },
          { index: true, element: <UsersListPage /> },
          { path: "users", element: <UsersListPage /> },
          { path: "users/new", element: <UserFormPage mode="create" /> },
          { path: "users/:id", element: <UserFormPage mode="edit" /> },
          { path: "tenants", element: <TenantsListPage /> },
          { path: "tenants/new", element: <TenantFormPage mode="create" /> },
          { path: "tenants/:id", element: <TenantFormPage mode="edit" /> },
        ],
      },

      { path: "*", element: <div className="p-8">Página não encontrada.</div> },
    ],
  },
])
