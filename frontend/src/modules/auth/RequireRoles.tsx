import React from "react"
import { Navigate, useLocation } from "react-router-dom"
import { useAuth } from "./AuthContext"
import type { Role } from "./AuthTypes"

export function RequireRoles({ roles, children }: { roles: Role[]; children: React.ReactNode }) {
  const { status, hasAnyRole } = useAuth()
  const loc = useLocation()
  if (status === "unknown") return null
  if (!hasAnyRole(roles)) return <Navigate to="/vehicles" state={{ from: loc }} replace />
  return <>{children}</>
}
