import React from "react"
import ReactDOM from "react-dom"

export function useConfirm() {
  const [state, setState] = React.useState<{
    open: boolean; title?: string; message?: string;
    onConfirm?: () => void; onCancel?: () => void;
  }>({ open: false })

  function confirm(opts: { title?: string; message?: string; onConfirm: () => void; onCancel?: () => void }) {
    setState({ open: true, ...opts })
  }
  function close(){ setState(s => ({ ...s, open: false })) }

  const ui = state.open ? (
    <ConfirmDialog
      title={state.title}
      message={state.message}
      onConfirm={() => { state.onConfirm?.(); close() }}
      onCancel={() => { state.onCancel?.(); close() }}
    />
  ) : null

  return { confirm, ui }
}

export default function ConfirmDialog({
  title = "Confirmar",
  message = "Tem certeza?",
  onConfirm, onCancel,
}: {
  title?: string
  message?: string
  onConfirm: () => void
  onCancel?: () => void
}) {
  return ReactDOM.createPortal(
    <div className="fixed inset-0 z-50 grid place-items-center">
      <div className="absolute inset-0 bg-black/60" aria-hidden="true" />
      <div role="dialog" aria-modal="true" className="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-5">
        <h2 className="text-lg font-semibold mb-2">{title}</h2>
        <p className="text-sm text-gray-700 mb-4">{message}</p>
        <div className="flex justify-end gap-2">
          <button className="px-3 py-2 border rounded" onClick={onCancel}>Cancelar</button>
          <button className="px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700" onClick={onConfirm}>Excluir</button>
        </div>
      </div>
    </div>,
    document.body
  )
}
