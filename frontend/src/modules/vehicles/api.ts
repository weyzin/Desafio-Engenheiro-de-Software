import { api } from "../../lib/apiClient"
import type {
  ListResponse, Vehicle, ItemResponse, VehiclePayload, VehicleStatus,
} from "./types"

export type VehicleFilters = {
  brand?: string
  model?: string
  price_min?: string | number
  price_max?: string | number
  year?: number | string
  status?: VehicleStatus | string
  sort?: string // price_asc|price_desc|year_asc|year_desc|created_desc|created_asc
  page?: number
}

function mapSortToBackend(sort?: string): Record<string, string> {
  if (!sort) return {}
  const [field, dir] = sort.split("_")
  const order_dir = (dir?.toLowerCase() === "desc" ? "desc" : "asc")
  if (field === "created") return { order_by: "id", order_dir } 
  const order_by = field === "price" ? "price" : field === "year" ? "year" : field
  return { order_by, order_dir }
}

export async function listVehicles(params: VehicleFilters) {
  const normalized = {
    ...params,
    year: params.year ? Number(params.year) : undefined,
  }
  const sortParams = mapSortToBackend(params.sort)
  const { data } = await api.get<ListResponse<Vehicle>>("/vehicles", {
    params: { ...normalized, ...sortParams },
  })
  return data
}

export async function getVehicle(id: string | number) {
  const { data } = await api.get<ItemResponse<Vehicle>>(`/vehicles/${id}`)
  return data.data
}

export async function createVehicle(payload: VehiclePayload) {
  const { data } = await api.post<ItemResponse<Vehicle>>("/vehicles", {
    ...payload,
    images: payload.images ?? [],
  })
  return data.data
}

export async function updateVehicle(id: string | number, payload: VehiclePayload) {
  const { data } = await api.put<ItemResponse<Vehicle>>(`/vehicles/${id}`, {
    ...payload,
    images: payload.images ?? [],
  })
  return data.data
}

export async function deleteVehicle(id: string | number) {
  await api.delete(`/vehicles/${id}`)
}
