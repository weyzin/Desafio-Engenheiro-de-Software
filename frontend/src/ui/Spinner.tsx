export function Spinner({ label }: { label?: string }) {
  return (
    <div className="flex items-center gap-3" role="status" aria-live="polite">
      <div className="h-5 w-5 animate-spin rounded-full border-2 border-gray-300 border-t-gray-600" />
      {label && <span className="text-sm text-gray-600">{label}</span>}
    </div>
  )
}
