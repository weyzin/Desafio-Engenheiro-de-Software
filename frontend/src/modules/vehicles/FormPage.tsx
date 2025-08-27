import React from "react"
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { Link, useNavigate, useParams } from "react-router-dom"
import { createVehicle, deleteVehicle, getVehicle, updateVehicle } from "./api"
import type { Vehicle, VehiclePayload, VehicleStatus } from "./types"
import { FormField } from "../../shared/FormField"
import type { ApiError } from "../../lib/apiClient"
import { useToast } from "../../ui/Toast"
import { useConfirm } from "../../ui/ConfirmDialog"
import { useAuth } from "../auth/AuthContext"

export default function VehicleFormPage({ mode }: { mode: "create" | "edit" }) {
  const { id } = useParams()
  const isEdit = mode === "edit"
  const nav = useNavigate()
  const qc = useQueryClient()
  const { push } = useToast()
  const { confirm, ui: confirmUI } = useConfirm()
  const { hasAnyRole } = useAuth()
  const canDelete = hasAnyRole(["owner", "superuser"])

  const { data: existing, isLoading } = useQuery<Vehicle>({
    queryKey: ["vehicle", id],
    queryFn: () => getVehicle(id!),
    enabled: isEdit && !!id,
  })

  const [payload, setPayload] = React.useState<VehiclePayload>({
    brand: "",
    model: "",
    version: "",
    year: new Date().getFullYear(),
    km: 0,
    price: 0,
    status: "available",
    notes: "",
    images: [],
  })
  const [fieldErrors, setFieldErrors] = React.useState<Record<string, string | string[]>>({})

  React.useEffect(() => {
    if (existing) {
      setPayload({
        brand: existing.brand,
        model: existing.model,
        version: existing.version ?? "",
        year: existing.year,
        km: existing.km ?? 0,
        price: Number(existing.price),
        status: existing.status as VehicleStatus,
        notes: existing.notes ?? "",
        images: existing.images ?? [],
      })
    }
  }, [existing])

  function set<K extends keyof VehiclePayload>(key: K, value: VehiclePayload[K]) {
    setPayload((p) => ({ ...p, [key]: value }))
  }

  const mutateUpsert = useMutation({
    mutationFn: async () => {
      if (isEdit && id) return updateVehicle(id, payload)
      return createVehicle(payload)
    },
    onSuccess: () => {
      setFieldErrors({})
      qc.invalidateQueries({ queryKey: ["vehicles"] })
      push({ kind: "success", message: isEdit ? "Veículo atualizado." : "Veículo criado." })
      nav("/vehicles")
    },
    onError: (e: any) => {
      const err = e as ApiError
      const map = (err.errors as any) || {}
      setFieldErrors(map)
      push({ kind: "error", message: err.message || "Falha ao salvar veículo." })
    },
  })

  const mutateDelete = useMutation({
    mutationFn: async () => {
      if (id) await deleteVehicle(id)
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["vehicles"] })
      push({ kind: "success", message: "Veículo excluído." })
      nav("/vehicles")
    },
    onError: (e: any) => {
      const err = e as ApiError
      push({ kind: "error", message: err.message || "Falha ao excluir." })
    },
  })

  function askDelete() {
    if (!id) return
    confirm({
      title: "Excluir veículo",
      message: "Tem certeza que deseja excluir este veículo?",
      onConfirm: () => mutateDelete.mutate(),
    })
  }

  if (isEdit && isLoading) return <div className="p-6">Carregando...</div>

  return (
    <section className="grid gap-4">
      {confirmUI}

      <header className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-semibold">{isEdit ? "Editar veículo" : "Novo veículo"}</h1>
          <p className="text-sm text-gray-600">Preencha os campos e salve.</p>
        </div>
        <div className="flex gap-2">
          {isEdit && canDelete && (
            <button onClick={askDelete} className="rounded border px-3 py-2 text-red-600">
              Excluir
            </button>
          )}
          <Link to="/vehicles" className="rounded border px-3 py-2">
            Voltar
          </Link>
        </div>
      </header>

      <form
        onSubmit={(e) => {
          e.preventDefault()
          setFieldErrors({})
          mutateUpsert.mutate()
        }}
        className="grid gap-4 bg-white p-4 rounded-2xl border max-w-2xl"
      >
        <div className="grid md:grid-cols-2 gap-4">
          <FormField label="Marca" htmlFor="brand" error={fieldErrors["brand"]}>
            <input
              id="brand"
              value={payload.brand}
              onChange={(e) => set("brand", e.target.value)}
              className="rounded border px-3 py-2"
            />
          </FormField>

          <FormField label="Modelo" htmlFor="model" error={fieldErrors["model"]}>
            <input
              id="model"
              value={payload.model}
              onChange={(e) => set("model", e.target.value)}
              className="rounded border px-3 py-2"
            />
          </FormField>

          <FormField label="Versão" htmlFor="version" error={fieldErrors["version"]}>
            <input
              id="version"
              value={payload.version ?? ""}
              onChange={(e) => set("version", e.target.value)}
              className="rounded border px-3 py-2"
              placeholder="Ex.: XEi 2.0"
            />
          </FormField>

          <FormField label="Ano" htmlFor="year" error={fieldErrors["year"]}>
            <input
              id="year"
              type="number"
              value={payload.year}
              onChange={(e) => set("year", Number(e.target.value))}
              className="rounded border px-3 py-2"
            />
          </FormField>

          <FormField label="KM" htmlFor="km" error={fieldErrors["km"]}>
            <input
              id="km"
              type="number"
              min={0}
              value={payload.km ?? 0}
              onChange={(e) => set("km", Number(e.target.value))}
              className="rounded border px-3 py-2"
            />
          </FormField>

          <FormField label="Status" htmlFor="status" error={fieldErrors["status"]}>
            <select
              id="status"
              value={payload.status ?? "available"}
              onChange={(e) => set("status", e.target.value as VehicleStatus)}
              className="rounded border px-3 py-2"
            >
              <option value="available">Disponível</option>
              <option value="reserved">Reservado</option>
              <option value="sold">Vendido</option>
            </select>
          </FormField>

          <FormField label="Preço (R$)" htmlFor="price" error={fieldErrors["price"]}>
            <input
              id="price"
              type="number"
              step="0.01"
              value={payload.price}
              onChange={(e) => set("price", Number(e.target.value))}
              className="rounded border px-3 py-2"
            />
          </FormField>
        </div>

        <FormField label="Notas" htmlFor="notes" error={fieldErrors["notes"]}>
          <textarea
            id="notes"
            value={payload.notes ?? ""}
            onChange={(e) => set("notes", e.target.value)}
            className="rounded border px-3 py-2 min-h-[96px]"
            placeholder="Anotações sobre interessados, observações do veículo etc."
          />
        </FormField>

        {/* Imagens */}
        <div className="grid gap-2">
          <h2 className="font-medium">Imagens</h2>
          {payload.images?.length ? (
            <ul className="grid gap-2">
              {payload.images!.map((url, idx) => (
                <li key={idx} className="flex items-center gap-2">
                  <img src={url || undefined} alt="" className="w-16 h-10 object-cover rounded border" />
                  <input
                    value={url}
                    onChange={(e) => {
                      const next = [...(payload.images ?? [])]
                      next[idx] = e.target.value
                      set("images", next)
                    }}
                    className="flex-1 rounded border px-3 py-2"
                    placeholder="https://..."
                  />
                  <button
                    type="button"
                    onClick={() => {
                      const next = [...(payload.images ?? [])]
                      next.splice(idx, 1)
                      set("images", next)
                    }}
                    className="px-2 py-2 border rounded"
                  >
                    Remover
                  </button>
                </li>
              ))}
            </ul>
          ) : (
            <p className="text-sm text-gray-600">Nenhuma imagem adicionada.</p>
          )}
          <button
            type="button"
            onClick={() => set("images", [...(payload.images ?? []), ""])}
            className="self-start rounded border px-3 py-2"
          >
            Adicionar imagem
          </button>
        </div>

        <div className="flex gap-2">
          <button
            type="submit"
            className="rounded bg-blue-600 text-white px-4 py-2 hover:bg-blue-700"
          >
            Salvar
          </button>
          <Link to="/vehicles" className="rounded border px-4 py-2">
            Cancelar
          </Link>
        </div>
      </form>

      {existing && (
        <div className="bg-white rounded-2xl border p-4 max-w-2xl">
          <h3 className="font-medium mb-2">Auditoria</h3>
          <div className="grid md:grid-cols-2 text-sm gap-x-8 gap-y-1">
            <div><span className="text-gray-500">Criado por:</span> {existing.created_by ?? "—"}</div>
            <div><span className="text-gray-500">Criado em:</span> {existing.created_at ?? "—"}</div>
            <div><span className="text-gray-500">Atualizado por:</span> {existing.updated_by ?? "—"}</div>
            <div><span className="text-gray-500">Atualizado em:</span> {existing.updated_at ?? "—"}</div>
            <div><span className="text-gray-500">Deletado por:</span> {existing.deleted_by ?? "—"}</div>
            <div><span className="text-gray-500">Deletado em:</span> {existing.deleted_at ?? "—"}</div>
          </div>
        </div>
      )}
    </section>
  )
}
