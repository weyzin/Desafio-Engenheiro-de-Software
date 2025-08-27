import React from "react"
import { useQuery } from "@tanstack/react-query"
import { Link, useSearchParams } from "react-router-dom"
import { listVehicles, type VehicleFilters } from "./api"
import type { Vehicle, ListResponse } from "./types"
import { Skeleton } from "../../ui/Skeleton"
import { Pagination } from "../../ui/Pagination"
import { useToast } from "../../ui/Toast"
import ImageThumb from "../../ui/ImageThumb"
import { getTenant } from "../../lib/apiClient"

const currency = new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" })
const kmFmt = new Intl.NumberFormat("pt-BR")
const SORT_LABEL: Record<string, string> = {
  price_asc: "Menor preço",
  price_desc: "Maior preço",
  year_desc: "Mais novos",
  year_asc: "Mais antigos",
}

export default function VehiclesList() {
  const [sp, setSp] = useSearchParams()
  const { push } = useToast()

  const filters: VehicleFilters = {
    brand: sp.get("brand") || undefined,
    model: sp.get("model") || undefined,
    price_min: sp.get("price_min") || undefined,
    price_max: sp.get("price_max") || undefined,
    year: sp.get("year") || undefined,
    status: sp.get("status") || undefined,
    sort: sp.get("sort") || undefined,
    page: sp.get("page") ? Number(sp.get("page")) : undefined,
  }

  // root sem tenant: não buscar/mostrar erro
  const hasTenant = !!getTenant()

  const { data, isLoading, isError } = useQuery<ListResponse<Vehicle>>({
    queryKey: ["vehicles", filters, hasTenant],
    queryFn: () => listVehicles(filters),
    placeholderData: (prev) => prev,
    enabled: hasTenant, // só consulta se houver tenant
    retry: false, // evita 3 tentativas automáticas
  })

  React.useEffect(() => {
    if (hasTenant && isError) {
      push({ kind: "error", message: "Falha ao carregar veículos." })
    }
  }, [isError, hasTenant, push])

  // ---- Fallback local (se o backend ignorar ano/status/sort) ----
  const pageData = React.useMemo(() => {
    if (!data) return undefined
    let arr = [...data.data]
    if (filters.year) arr = arr.filter((v) => v.year === Number(filters.year))
    if (filters.status) arr = arr.filter((v) => v.status === filters.status)

    const s = filters.sort
    if (s) {
      const cmpNum = (a: number, b: number) => a - b
      const cmpDate = (a?: string | null, b?: string | null) =>
        (a ? Date.parse(a) : 0) - (b ? Date.parse(b) : 0)

      switch (s) {
        case "price_asc":
          arr.sort((a, b) => cmpNum(Number(a.price), Number(b.price)))
          break
        case "price_desc":
          arr.sort((a, b) => cmpNum(Number(b.price), Number(a.price)))
          break
        case "year_asc":
          arr.sort((a, b) => cmpNum(a.year, b.year))
          break
        case "year_desc":
          arr.sort((a, b) => cmpNum(b.year, a.year))
          break
        // se um dia habilitar sort por criação no front
        case "created_asc":
          arr.sort((a, b) => cmpDate(a.created_at, b.created_at))
          break
        case "created_desc":
          arr.sort((a, b) => cmpDate(b.created_at, a.created_at))
          break
      }
    }
    return { ...data, data: arr }
  }, [data, filters.year, filters.status, filters.sort])
  // ----------------------------------------------------------------

  function setFilter(patch: Record<string, string | undefined>) {
    const next = new URLSearchParams(sp)
    Object.entries(patch).forEach(([k, v]) => {
      if (!v) next.delete(k)
      else next.set(k, v)
    })
    next.delete("page")
    setSp(next, { replace: true })
  }

  function clearAll() {
    setSp(new URLSearchParams(), { replace: true })
  }

  function onNavigateUrl(url: string) {
    const u = new URL(url)
    const page = u.searchParams.get("page")
    const next = new URLSearchParams(sp)
    if (page) next.set("page", page)
    setSp(next, { replace: true })
  }

  const d = pageData

  // Empty state específico para root sem tenant
  if (!hasTenant) {
    return (
      <section className="grid gap-4">
        <header className="flex items-end justify-between gap-4">
          <div>
            <h1 className="text-xl font-semibold">Veículos</h1>
            <p className="text-sm text-gray-600">Selecione um tenant para visualizar os veículos.</p>
          </div>
          {/* botão continua visível, mas você pode esconder se quiser */}
          <button
            disabled
            className="rounded bg-gray-300 text-white px-4 py-2 opacity-60 cursor-not-allowed"
          >
            Novo veículo
          </button>
        </header>

        <div className="rounded-2xl border p-6 bg-gray-50 text-sm text-gray-700">
          Nenhum tenant ativo. Use o seletor de tenant e tente novamente.
        </div>
      </section>
    )
  }

  return (
    <section className="grid gap-4">
      <header className="flex items-end justify-between gap-4">
        <div>
          <h1 className="text-xl font-semibold">Veículos</h1>
          <p className="text-sm text-gray-600">Filtre por marca, modelo, ano, status e preço.</p>
        </div>
        <Link to="/vehicles/new" className="rounded bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">
          Novo veículo
        </Link>
      </header>

      {/* Filtros */}
      <div className="grid gap-3 bg-white rounded-2xl p-4 border">
        <div className="grid grid-cols-2 md:grid-cols-8 gap-3">
          <input
            placeholder="Marca"
            value={filters.brand ?? ""}
            onChange={(e) => setFilter({ brand: e.target.value || undefined })}
            className="rounded border px-3 py-2"
          />
          <input
            placeholder="Modelo"
            value={filters.model ?? ""}
            onChange={(e) => setFilter({ model: e.target.value || undefined })}
            className="rounded border px-3 py-2"
          />
          <input
            placeholder="Ano"
            type="number"
            value={filters.year ?? ""}
            onChange={(e) => setFilter({ year: e.target.value || undefined })}
            className="rounded border px-3 py-2"
          />
          <select
            value={filters.status ?? ""}
            onChange={(e) => setFilter({ status: e.target.value || undefined })}
            className="rounded border px-3 py-2"
          >
            <option value="">Status</option>
            <option value="available">Disponível</option>
            <option value="reserved">Reservado</option>
            <option value="sold">Vendido</option>
          </select>
          <input
            placeholder="Preço mín."
            type="number"
            value={filters.price_min ?? ""}
            onChange={(e) => setFilter({ price_min: e.target.value || undefined })}
            className="rounded border px-3 py-2"
          />
          <input
            placeholder="Preço máx."
            type="number"
            value={filters.price_max ?? ""}
            onChange={(e) => setFilter({ price_max: e.target.value || undefined })}
            className="rounded border px-3 py-2"
          />
          <select
            value={filters.sort ?? ""}
            onChange={(e) => setFilter({ sort: e.target.value || undefined })}
            className="rounded border px-3 py-2"
            aria-label="Ordenação"
          >
            <option value="">Ordenar</option>
            <optgroup label="Preço">
              <option value="price_asc">Menor preço</option>
              <option value="price_desc">Maior preço</option>
            </optgroup>
            <optgroup label="Ano">
              <option value="year_desc">Mais novos</option>
              <option value="year_asc">Mais antigos</option>
            </optgroup>
          </select>
        </div>

        {/* chips + limpar */}
        <div className="flex flex-wrap items-center gap-2">
          {(["brand", "model", "year", "status", "price_min", "price_max"] as const).map((k) => {
            const val = sp.get(k)
            if (!val) return null
            const label =
              k === "price_min"
                ? `Preço ≥ ${currency.format(Number(val))}`
                : k === "price_max"
                ? `Preço ≤ ${currency.format(Number(val))}`
                : k === "status"
                ? val === "available"
                  ? "Disponível"
                  : val === "reserved"
                  ? "Reservado"
                  : "Vendido"
                : `${k}=${val}`
            return (
              <button
                key={k}
                onClick={() => setFilter({ [k]: undefined } as any)}
                className="px-2 py-1 text-sm rounded-full border bg-gray-50 hover:bg-gray-100"
              >
                {label} ✕
              </button>
            )
          })}
          {filters.sort && (
            <button
              onClick={() => setFilter({ sort: undefined })}
              className="px-2 py-1 text-sm rounded-full border bg-gray-50 hover:bg-gray-100"
            >
              {SORT_LABEL[filters.sort] ?? `ordenar=${filters.sort}`} ✕
            </button>
          )}
          <button onClick={clearAll} className="text-sm underline underline-offset-2">
            Limpar filtros
          </button>
        </div>
      </div>

      {/* Tabela */}
      <div className="bg-white rounded-2xl border overflow-x-auto">
        {isLoading ? (
          <div className="p-4 grid gap-2">
            <Skeleton className="h-8 w-full" />
            <Skeleton className="h-8 w-full" />
            <Skeleton className="h-8 w-full" />
          </div>
        ) : !d || d.data.length === 0 ? (
          <div className="p-6 text-sm text-gray-600">Nenhum veículo encontrado.</div>
        ) : (
          <table className="min-w-full text-sm">
            <thead>
              <tr className="text-left bg-gray-50">
                <th className="px-4 py-3">Img</th>
                <th className="px-4 py-3">ID</th>
                <th className="px-4 py-3">Marca</th>
                <th className="px-4 py-3">Modelo</th>
                <th className="px-4 py-3">Versão</th>
                <th className="px-4 py-3">Ano</th>
                <th className="px-4 py-3">KM</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Preço</th>
                <th className="px-4 py-3 max-w-[240px]">Notas</th>
                <th className="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody>
              {d.data.map((v: Vehicle) => (
                <tr key={v.id} className="border-t">
                  <td className="px-4 py-3">
                    <ImageThumb images={v.images} />
                  </td>
                  <td className="px-4 py-3">{v.id}</td>
                  <td className="px-4 py-3">{v.brand}</td>
                  <td className="px-4 py-3">{v.model}</td>
                  <td className="px-4 py-3">{v.version ?? "—"}</td>
                  <td className="px-4 py-3">{v.year}</td>
                  <td className="px-4 py-3">{kmFmt.format(v.km ?? 0)}</td>
                  <td className="px-4 py-3">
                    {v.status === "available"
                      ? "Disponível"
                      : v.status === "reserved"
                      ? "Reservado"
                      : "Vendido"}
                  </td>
                  <td className="px-4 py-3">{currency.format(Number(v.price))}</td>
                  <td className="px-4 py-3 max-w-[240px] overflow-hidden text-ellipsis whitespace-nowrap">
                    {v.notes ?? "—"}
                  </td>
                  <td className="px-4 py-3 text-right">
                    <div className="flex gap-2 justify-end">
                      <Link
                        to={`/vehicles/${v.id}`}
                        className="px-2 py-1 rounded border hover:bg-gray-50"
                      >
                        Editar
                      </Link>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {/* Paginação */}
      {d?.meta && (
        <Pagination
          meta={{ links: d.meta.links, from: d.meta.from, to: d.meta.to, total: d.meta.total }}
          onNavigate={onNavigateUrl}
        />
      )}
    </section>
  )
}
