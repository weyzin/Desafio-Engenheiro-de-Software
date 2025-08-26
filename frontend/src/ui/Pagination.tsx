type Link = { url: string | null; label: string; active: boolean }
export type MetaLinks = { links: Link[]; from?: number; to?: number; total?: number }

export function Pagination({
  meta,
  onNavigate,
}: {
  meta: MetaLinks
  onNavigate: (url: string) => void
}) {
  if (!meta?.links?.length) return null
  return (
    <div className="flex items-center justify-between gap-3 mt-4">
      <div className="text-sm text-gray-600">
        {typeof meta.from === "number" && typeof meta.to === "number" && typeof meta.total === "number"
          ? <>Exibindo <strong>{meta.from}–{meta.to}</strong> de <strong>{meta.total}</strong></>
          : null}
      </div>
      <div className="flex items-center gap-1">
        {meta.links.map((l, i) => {
          const label = decodeHtml(l.label)
          const isEdge = i === 0 || i === meta.links.length - 1
          return (
            <button
              key={i}
              disabled={!l.url || l.active}
              onClick={() => l.url && onNavigate(l.url)}
              className={`px-3 py-1.5 rounded border text-sm ${
                l.active
                  ? "bg-blue-600 text-white border-blue-600"
                  : "bg-white text-gray-800 border-gray-200 hover:bg-gray-50 disabled:opacity-50"
              } ${isEdge ? "font-medium" : ""}`}
              dangerouslySetInnerHTML={{ __html: label }}
            />
          )
        })}
      </div>
    </div>
  )
}

function decodeHtml(s: string) {
  return s.replaceAll("&laquo;", "«").replaceAll("&raquo;", "»")
}
