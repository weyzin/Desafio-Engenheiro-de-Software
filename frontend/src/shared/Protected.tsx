import { Navigate, Outlet, useLocation } from "react-router-dom"
import { useAuth } from "../modules/auth/AuthContext"
import React from "react"
import { Spinner } from "../ui/Spinner"

export function Protected({ children }: { children?: React.ReactNode }) {
  const { status, refresh } = useAuth()
  const loc = useLocation()

  React.useEffect(() => {
    if (status === "unknown") void refresh()
  }, [status, refresh])

  if (status === "unknown")
    return (
      <div className="grid place-items-center h-[50vh]" aria-busy>
        <Spinner label="Carregando sessão..." />
      </div>
    )

  if (status !== "authenticated")
    return <Navigate to="/login" state={{ from: loc }} replace />

  return children ? <>{children}</> : <Outlet />
}
