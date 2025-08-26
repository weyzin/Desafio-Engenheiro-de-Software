export type VehicleStatus = "available" | "reserved" | "sold"

export type Audit = {
  created_by?: number | string | null
  updated_by?: number | string | null
  deleted_by?: number | string | null
  created_at?: string | null
  updated_at?: string | null
  deleted_at?: string | null
}

export type Vehicle = Audit & {
  id: number
  brand: string
  model: string
  year: number
  price: number // decimal(12,2)
  status: VehicleStatus
  images?: string[] // API expõe como "images"
}

export type VehiclePayload = {
  brand: string
  model: string
  year: number
  price: number
  status?: VehicleStatus
  images?: string[]
}

export type ListResponse<T> = {
  data: T[]
  meta: {
    current_page: number
    from: number
    to: number
    total: number
    per_page: number
    last_page: number
    path: string
    links: { url: string | null; label: string; active: boolean }[]
  }
  links: Record<string, string | null>
}

export type ItemResponse<T> = { data: T }
