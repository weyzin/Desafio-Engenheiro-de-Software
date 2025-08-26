import React from "react"

type ToastKind = "info" | "success" | "error" | "warning"
export type Toast = {
  id: number
  kind: ToastKind
  message: string
  action?: { label: string; onClick: () => void }
}

const Ctx = React.createContext<{
  toasts: Toast[]
  push: (t: Omit<Toast, "id">) => void
  remove: (id: number) => void
}>({
  toasts: [],
  push: () => {},
  remove: () => {},
})

export function ToastProvider({ children }: { children: React.ReactNode }) {
  const [toasts, setToasts] = React.useState<Toast[]>([])

  const remove = React.useCallback((id: number) => {
    setToasts((prev) => prev.filter((t) => t.id !== id))
  }, [])

  const push = React.useCallback((t: Omit<Toast, "id">) => {
    const id = Date.now() + Math.random()
    setToasts((prev) => [...prev, { id, ...t }])
    // auto-dismiss em 6s (exceto se houver action)
    if (!t.action) {
      setTimeout(() => remove(id), 6000)
    }
  }, [remove])

  return (
    <Ctx.Provider value={{ toasts, push, remove }}>
      {children}
      {/* container visual */}
      <div aria-live="polite" className="fixed z-50 top-4 right-4 grid gap-2">
        {toasts.map((t) => (
          <div
            key={t.id}
            role="status"
            className={`rounded-2xl shadow px-4 py-3 text-sm border max-w-sm bg-white text-gray-900
              ${t.kind === "success" ? "border-green-200" :
                 t.kind === "error"   ? "border-red-200"   :
                 t.kind === "warning" ? "border-yellow-200": "border-gray-200"}`}
          >
            <div className="flex items-start gap-3">
              <div className="mt-0.5">
                {t.kind === "success" ? "✅" :
                 t.kind === "error"   ? "⛔" :
                 t.kind === "warning" ? "⚠️" : "ℹ️"}
              </div>
              <div className="grid gap-2">
                <span className="leading-5">{t.message}</span>
                {t.action && (
                  <button
                    onClick={t.action.onClick}
                    className="self-start underline text-blue-700"
                  >
                    {t.action.label}
                  </button>
                )}
              </div>
              <button
                aria-label="Fechar"
                onClick={() => remove(t.id)}
                className="ml-auto opacity-60 hover:opacity-100"
              >
                ✖
              </button>
            </div>
          </div>
        ))}
      </div>
    </Ctx.Provider>
  )
}

export function useToast() {
  return React.useContext(Ctx)
}
