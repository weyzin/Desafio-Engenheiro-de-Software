import React from "react"
import { Navigate } from "react-router-dom"
import { useQuery } from "@tanstack/react-query"
import { api } from "../lib/apiClient"

export default function SuperOnly({ children }: { children: React.ReactNode }) {
  const { data, isLoading, isError } = useQuery({
    queryKey: ["me"],
    queryFn: async () => {
      const { data } = await api.get<{ data: { role: string } }>("/me")
      return data.data
    },
  })

  if (isLoading) return null
  if (isError || !data) return <Navigate to="/login" replace />

  return data.role === "superuser" ? <>{children}</> : <Navigate to="/vehicles" replace />
}
