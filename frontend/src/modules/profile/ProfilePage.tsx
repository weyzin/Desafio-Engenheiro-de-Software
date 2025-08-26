import { useQuery } from "@tanstack/react-query"
import { api } from "../../lib/apiClient"

type Me = { id: number | string; name?: string; email: string }

export default function ProfilePage() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ["me"],
    queryFn: async () => {
      const { data } = await api.get<{ data: Me }>("/me")
      return data.data
    },
  })

  if (isLoading) return <div className="p-6">Carregando...</div>
  if (isError || !data) return <div className="p-6">Não foi possível carregar seu perfil.</div>

  return (
    <section className="grid gap-4">
      <h1 className="text-xl font-semibold">Meu perfil</h1>
      <div className="bg-white rounded-2xl border p-4 max-w-md">
        <div className="grid gap-2 text-sm">
          <div><span className="text-gray-600">ID:</span> {data.id}</div>
          <div><span className="text-gray-600">Nome:</span> {data.name ?? "—"}</div>
          <div><span className="text-gray-600">E-mail:</span> {data.email}</div>
        </div>
      </div>
    </section>
  )
}
